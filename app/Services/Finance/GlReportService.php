<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

/**
 * Trial balance, balance sheet, and account balances from posted GL.
 */
class GlReportService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function isEnabled(): bool
    {
        return $this->db->tableExists('finance_journal_lines')
            && $this->db->tableExists('finance_accounts')
            && $this->db->tableExists('finance_account_groups');
    }

    /**
     * @return list<array{code: string, name: string, account_type: string, debit: float, credit: float, balance: float}>
     */
    public function trialBalance(string $asOf): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $rows = $this->db->query("
            SELECT fa.code, fa.name, fg.account_type,
                   COALESCE(SUM(jl.debit), 0) AS total_debit,
                   COALESCE(SUM(jl.credit), 0) AS total_credit,
                   fa.opening_balance
            FROM finance_accounts fa
            JOIN finance_account_groups fg ON fg.id = fa.group_id
            LEFT JOIN finance_journal_lines jl ON jl.account_id = fa.id
            LEFT JOIN finance_journal_entries je ON je.id = jl.journal_id
                AND je.status = 'posted' AND je.entry_date <= ?
            WHERE fa.is_active = 1
            GROUP BY fa.id, fa.code, fa.name, fg.account_type, fa.opening_balance
            ORDER BY fa.code
        ", [$asOf])->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $debit  = (float) $r['total_debit'];
            $credit = (float) $r['total_credit'];
            $open   = (float) $r['opening_balance'];
            $type   = (string) $r['account_type'];
            $bal    = in_array($type, ['asset', 'expense'], true)
                ? $open + $debit - $credit
                : $open + $credit - $debit;
            if (abs($bal) < 0.005 && $debit < 0.005 && $credit < 0.005) {
                continue;
            }
            $out[] = [
                'code'         => $r['code'],
                'name'         => $r['name'],
                'account_type' => $type,
                'debit'        => round($debit, 2),
                'credit'       => round($credit, 2),
                'balance'      => round($bal, 2),
            ];
        }

        return $out;
    }

    /**
     * @return array{assets: list<array>, liabilities: list<array>, equity: list<array>, income: list<array>, expense: list<array>, totals: array<string, float>}
     */
    public function balanceSheet(string $asOf): array
    {
        $tb     = $this->trialBalance($asOf);
        $groups = ['asset' => [], 'liability' => [], 'equity' => [], 'income' => [], 'expense' => []];
        $totals = ['assets' => 0.0, 'liabilities' => 0.0, 'equity' => 0.0, 'income' => 0.0, 'expense' => 0.0];

        foreach ($tb as $row) {
            $type = $row['account_type'];
            if (! isset($groups[$type])) {
                continue;
            }
            $groups[$type][] = $row;
            if ($type === 'asset') {
                $totals['assets'] += $row['balance'];
            } elseif ($type === 'liability') {
                $totals['liabilities'] += $row['balance'];
            } elseif ($type === 'equity') {
                $totals['equity'] += $row['balance'];
            } elseif ($type === 'income') {
                $totals['income'] += $row['balance'];
            } elseif ($type === 'expense') {
                $totals['expense'] += $row['balance'];
            }
        }

        $netIncome = round($totals['income'] - $totals['expense'], 2);
        $totals['net_income']      = $netIncome;
        $totals['liabilities_eq']  = round($totals['liabilities'] + $totals['equity'] + $netIncome, 2);

        return [
            'assets'      => $groups['asset'],
            'liabilities' => $groups['liability'],
            'equity'      => $groups['equity'],
            'income'      => $groups['income'],
            'expense'     => $groups['expense'],
            'totals'      => $totals,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function arAging(): array
    {
        if (! $this->db->tableExists('invoices')) {
            return [];
        }
        $rows = $this->db->table('invoices i')
            ->select('i.id, i.invoice_number, i.issue_date, i.due_date, i.total, i.status, f.name as facility_name')
            ->join('facilities f', 'f.id = i.facility_id', 'left')
            ->whereIn('i.status', ['sent', 'overdue', 'partial'])
            ->orderBy('i.due_date', 'ASC')
            ->get()
            ->getResultArray();

        $today = strtotime(date('Y-m-d'));
        foreach ($rows as &$r) {
            $due = strtotime($r['due_date'] ?? $r['issue_date']);
            $days = (int) floor(($today - $due) / 86400);
            $r['days_overdue'] = max(0, $days);
            if ($days <= 0) {
                $r['bucket'] = 'current';
            } elseif ($days <= 30) {
                $r['bucket'] = '1-30';
            } elseif ($days <= 60) {
                $r['bucket'] = '31-60';
            } elseif ($days <= 90) {
                $r['bucket'] = '61-90';
            } else {
                $r['bucket'] = '90+';
            }
        }
        unset($r);

        return $rows;
    }

    /** @return array{payments: list<array>, total_in: float, total_out: float} */
    public function bankActivity(string $from, string $to): array
    {
        $payments = [];
        $totalIn  = 0.0;
        $totalOut = 0.0;

        if ($this->db->tableExists('invoice_payments')) {
            $in = $this->db->table('invoice_payments ip')
                ->select('ip.payment_date as txn_date, ip.amount, ip.payment_method, ip.reference_no, i.invoice_number as ref')
                ->join('invoices i', 'i.id = ip.invoice_id', 'left')
                ->where('DATE(ip.payment_date) >=', $from)
                ->where('DATE(ip.payment_date) <=', $to)
                ->orderBy('ip.payment_date', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($in as $row) {
                $amt = (float) $row['amount'];
                $payments[] = [
                    'date'    => $row['txn_date'],
                    'type'    => 'in',
                    'ref'     => $row['ref'] ?? 'Payment',
                    'method'  => $row['payment_method'] ?? '',
                    'amount'  => $amt,
                ];
                $totalIn += $amt;
            }
        }

        if ($this->db->tableExists('finance_vendor_bills')) {
            $bills = $this->db->table('finance_vendor_bills')
                ->select('bill_number, total, bill_date, status')
                ->where('status', 'paid')
                ->where('DATE(bill_date) >=', $from)
                ->where('DATE(bill_date) <=', $to)
                ->get()
                ->getResultArray();
            foreach ($bills as $b) {
                $amt = (float) $b['total'];
                $payments[] = [
                    'date'   => $b['bill_date'],
                    'type'   => 'out',
                    'ref'    => $b['bill_number'],
                    'method' => 'AP',
                    'amount' => $amt,
                ];
                $totalOut += $amt;
            }
        }

        usort($payments, fn ($a, $b) => strcmp((string) $b['date'], (string) $a['date']));

        return ['payments' => $payments, 'total_in' => round($totalIn, 2), 'total_out' => round($totalOut, 2)];
    }
}
