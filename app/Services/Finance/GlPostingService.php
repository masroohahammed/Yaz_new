<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

/**
 * Auto-post double-entry journals from operational transactions (when GL tables exist).
 */
class GlPostingService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function isEnabled(): bool
    {
        return $this->db->tableExists('finance_journal_entries')
            && $this->db->tableExists('finance_journal_lines')
            && $this->db->tableExists('finance_accounts');
    }

    /**
     * @param list<array{account_code: string, debit: float, credit: float, memo?: string}> $lines
     */
    public function post(
        string $module,
        string $sourceType,
        int $sourceId,
        string $description,
        array $lines,
        ?int $createdBy = null,
        string $status = 'posted'
    ): ?int {
        if (! $this->isEnabled() || $lines === []) {
            return null;
        }

        $debit  = round(array_sum(array_column($lines, 'debit')), 2);
        $credit = round(array_sum(array_column($lines, 'credit')), 2);
        if (abs($debit - $credit) > 0.02) {
            log_message('error', "GL imbalance {$debit} vs {$credit} for {$sourceType}#{$sourceId}");

            return null;
        }

        $entryNo = $this->nextEntryNumber();
        $this->db->table('finance_journal_entries')->insert([
            'entry_number'  => $entryNo,
            'entry_date'    => date('Y-m-d'),
            'description'   => $description,
            'source_module' => $module,
            'source_type'   => $sourceType,
            'source_id'     => $sourceId,
            'status'        => $status,
            'created_by'    => $createdBy,
            'posted_at'     => $status === 'posted' ? date('Y-m-d H:i:s') : null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $jid = (int) $this->db->insertID();

        foreach ($lines as $line) {
            $acct = $this->accountIdByCode($line['account_code']);
            if (! $acct) {
                continue;
            }
            $this->db->table('finance_journal_lines')->insert([
                'journal_id' => $jid,
                'account_id' => $acct,
                'debit'      => round((float) ($line['debit'] ?? 0), 2),
                'credit'     => round((float) ($line['credit'] ?? 0), 2),
                'memo'       => $line['memo'] ?? null,
            ]);
        }

        (new FinanceIntegrationService($this->db))->log($module, 'gl_posted', $sourceType, $sourceId, 'journal', $jid);

        return $jid;
    }

    public function postCustomerPayment(int $invoiceId, float $amount, int $userId): ?int
    {
        if (! $this->vatEnabled()) {
            return $this->post('finance', 'invoice_payment', $invoiceId, "Customer payment INV#{$invoiceId}", [
                ['account_code' => '1200', 'debit' => $amount, 'credit' => 0, 'memo' => 'Bank/Cash'],
                ['account_code' => '1100', 'debit' => 0, 'credit' => $amount, 'memo' => 'AR clearance'],
            ], $userId);
        }

        return $this->post('finance', 'invoice_payment', $invoiceId, "Customer payment INV#{$invoiceId}", [
            ['account_code' => '1200', 'debit' => $amount, 'credit' => 0],
            ['account_code' => '1100', 'debit' => 0, 'credit' => $amount],
        ], $userId);
    }

    public function postExpenseApproved(int $expenseId, float $amount, int $userId): ?int
    {
        return $this->post('finance', 'expense', $expenseId, "Approved expense #{$expenseId}", [
            ['account_code' => '5100', 'debit' => $amount, 'credit' => 0, 'memo' => 'Maintenance/ops expense'],
            ['account_code' => '1200', 'debit' => 0, 'credit' => $amount, 'memo' => 'Cash/bank'],
        ], $userId);
    }

    public function postVendorBill(int $billId, float $total, int $userId): ?int
    {
        return $this->post('finance', 'vendor_bill', $billId, "Vendor bill #{$billId}", [
            ['account_code' => '5200', 'debit' => $total, 'credit' => 0],
            ['account_code' => '2100', 'debit' => 0, 'credit' => $total],
        ], $userId);
    }

    public function postPayrollFinalized(int $runId, float $netAmount, int $userId, ?int $companyId = null): ?int
    {
        $payroll = new \App\Services\Hr\HrPayrollService($this->db);
        $expense = $payroll->glAccountCode('gl_payroll_expense', $companyId);
        $payable = $payroll->glAccountCode('gl_salary_payable', $companyId);

        return $this->post('hr', 'payroll', $runId, "Payroll run #{$runId} finalized", [
            ['account_code' => $expense, 'debit' => $netAmount, 'credit' => 0, 'memo' => 'Payroll expense'],
            ['account_code' => $payable, 'debit' => 0, 'credit' => $netAmount, 'memo' => 'Salary payable'],
        ], $userId);
    }

    public function postInvoiceRevenue(int $invoiceId, float $subtotal, float $vat, int $userId): ?int
    {
        $lines = [
            ['account_code' => '1100', 'debit' => $subtotal + $vat, 'credit' => 0, 'memo' => 'AR'],
            ['account_code' => '4100', 'debit' => 0, 'credit' => $subtotal, 'memo' => 'Service revenue'],
        ];
        if ($vat > 0) {
            $lines[] = ['account_code' => '2150', 'debit' => 0, 'credit' => $vat, 'memo' => 'Output VAT payable'];
        }

        return $this->post('finance', 'invoice', $invoiceId, "Revenue recognition INV#{$invoiceId}", $lines, $userId);
    }

    private function accountIdByCode(string $code): ?int
    {
        $row = $this->db->table('finance_accounts')->where('code', $code)->get()->getRowArray();

        return $row ? (int) $row['id'] : null;
    }

    private function nextEntryNumber(): string
    {
        $n = (int) $this->db->table('finance_journal_entries')->countAllResults() + 1;

        return 'JE-' . date('Y') . '-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }

    private function vatEnabled(): bool
    {
        try {
            $row = $this->db->table('system_settings')->where('setting_key', 'vat_enabled')->get()->getRowArray();

            return ($row['setting_value'] ?? '0') === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
