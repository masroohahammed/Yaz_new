<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

/**
 * AMC / contract recurring billing schedules → draft invoices.
 */
class AmcBillingService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** Ensure schedule row exists for active AMC-style contracts. */
    public function ensureScheduleForContract(int $contractId): void
    {
        if (! $this->db->tableExists('finance_amc_schedules')) {
            return;
        }

        $c = $this->db->table('contracts')->where('id', $contractId)->where('status', 'active')->get()->getRowArray();
        if (! $c || ! in_array($c['contract_type'] ?? '', ['amc', 'fm_services', 'cleaning', 'security'], true)) {
            return;
        }

        if ($this->db->table('finance_amc_schedules')->where('contract_id', $contractId)->countAllResults() > 0) {
            return;
        }

        $freq = $c['billing_frequency'] ?? 'quarterly';
        $this->db->table('finance_amc_schedules')->insert([
            'contract_id'    => $contractId,
            'frequency'      => $freq,
            'next_bill_date' => date('Y-m-d', strtotime('+1 month')),
            'amount'         => (float) ($c['value'] ?? 0) / ($freq === 'monthly' ? 12 : ($freq === 'annual' ? 1 : 4)),
            'auto_invoice'   => 1,
            'is_active'      => 1,
        ]);
    }

    /** Run due AMC bills — call from cron or Finance dashboard. */
    public function processDueSchedules(int $userId): int
    {
        if (! $this->db->tableExists('finance_amc_schedules')) {
            return 0;
        }

        $due = $this->db->table('finance_amc_schedules s')
            ->select('s.*, c.facility_id, c.contract_number')
            ->join('contracts c', 'c.id = s.contract_id')
            ->where('s.is_active', 1)
            ->where('s.auto_invoice', 1)
            ->where('s.next_bill_date <=', date('Y-m-d'))
            ->get()->getResultArray();

        $created = 0;
        foreach ($due as $row) {
            if ($this->createAmcInvoice($row, $userId)) {
                $created++;
            }
        }

        return $created;
    }

    private function createAmcInvoice(array $schedule, int $userId): bool
    {
        $amount = (float) $schedule['amount'];
        if ($amount <= 0) {
            return false;
        }

        $vatEnabled = ($this->getSetting('vat_enabled') ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float) ($this->getSetting('vat_rate') ?? 5) : 0;
        $vatAmt     = round($amount * $vatRate / 100, 2);
        $n          = (int) $this->db->table('invoices')->countAllResults() + 1;
        $invNum     = 'INV-' . date('Y') . '-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);

        $this->db->table('invoices')->insert([
            'invoice_number' => $invNum,
            'facility_id'    => (int) $schedule['facility_id'],
            'contract_id'    => (int) $schedule['contract_id'],
            'invoice_type'   => 'contract',
            'issue_date'     => date('Y-m-d'),
            'due_date'       => date('Y-m-d', strtotime('+30 days')),
            'subtotal'       => $amount,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmt,
            'total'          => $amount + $vatAmt,
            'status'         => 'draft',
            'notes'          => 'AMC recurring billing — ' . ($schedule['contract_number'] ?? ''),
            'created_by'     => $userId,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $invId = (int) $this->db->insertID();

        if ($this->db->tableExists('invoice_items')) {
            $this->db->table('invoice_items')->insert([
                'invoice_id'  => $invId,
                'line_type'   => 'amc',
                'description' => 'AMC / Contract services — ' . ($schedule['frequency'] ?? 'period'),
                'quantity'    => 1,
                'unit_price'  => $amount,
                'amount'      => $amount,
                'sort_order'  => 0,
            ]);
        }

        $next = match ($schedule['frequency'] ?? 'quarterly') {
            'monthly'   => '+1 month',
            'annual'    => '+1 year',
            default     => '+3 months',
        };
        $this->db->table('finance_amc_schedules')->where('id', $schedule['id'])->update([
            'next_bill_date'   => date('Y-m-d', strtotime($next)),
            'last_invoiced_at' => date('Y-m-d H:i:s'),
        ]);

        (new FinanceIntegrationService($this->db))->log('amc', 'recurring_invoice', 'contract', (int) $schedule['contract_id'], 'invoice', $invId);
        (new GlPostingService($this->db))->postInvoiceRevenue($invId, $amount, $vatAmt, $userId);

        return true;
    }

    private function getSetting(string $key): ?string
    {
        $row = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRowArray();

        return $row['setting_value'] ?? null;
    }
}
