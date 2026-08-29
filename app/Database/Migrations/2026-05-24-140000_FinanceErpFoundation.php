<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinanceErpFoundation extends Migration
{
    public function up()
    {
        $sqlFile = APPPATH . 'Database/SQL/patch_finance_erp_foundation.sql';
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt === '' || str_starts_with($stmt, '--')) {
                    continue;
                }
                try {
                    $this->db->query($stmt);
                } catch (\Throwable $e) {
                    if (! str_contains($e->getMessage(), 'Duplicate') && ! str_contains($e->getMessage(), 'already exists')) {
                        log_message('warning', 'FinanceErpFoundation: ' . $e->getMessage());
                    }
                }
            }
        }

        if ($this->db->tableExists('contracts') && ! $this->db->fieldExists('billing_frequency', 'contracts')) {
            $this->forge->addColumn('contracts', [
                'billing_frequency' => ['type' => 'ENUM', 'constraint' => ['monthly', 'quarterly', 'annual', 'one_time'], 'default' => 'quarterly', 'after' => 'payment_terms'],
                'billing_day'       => ['type' => 'TINYINT', 'constraint' => 2, 'unsigned' => true, 'default' => 1, 'after' => 'billing_frequency'],
            ]);
        }

        $this->seedCoa();
    }

    public function down()
    {
        foreach ([
            'finance_integration_log', 'finance_amc_schedules', 'finance_bank_accounts',
            'finance_budget_lines', 'finance_budgets', 'finance_vendor_bills',
            'finance_journal_lines', 'finance_journal_entries', 'finance_accounts',
            'finance_account_groups', 'finance_cost_centers', 'finance_branches', 'invoice_items',
        ] as $t) {
            if ($this->db->tableExists($t)) {
                $this->forge->dropTable($t, true);
            }
        }
    }

    private function seedCoa(): void
    {
        if (! $this->db->tableExists('finance_account_groups')) {
            return;
        }
        if ($this->db->table('finance_account_groups')->countAllResults() > 0) {
            return;
        }

        $groups = [
            ['1000', 'Assets', 'asset', 10],
            ['2000', 'Liabilities', 'liability', 20],
            ['3000', 'Equity', 'equity', 30],
            ['4000', 'Income', 'income', 40],
            ['5000', 'Expenses', 'expense', 50],
        ];
        foreach ($groups as $g) {
            $this->db->table('finance_account_groups')->insert([
                'code' => $g[0], 'name' => $g[1], 'account_type' => $g[2], 'sort_order' => $g[3],
            ]);
        }

        $gid = fn(string $code) => (int) ($this->db->table('finance_account_groups')->where('code', $code)->get()->getRowArray()['id'] ?? 0);

        $accounts = [
            ['1100', 'Accounts Receivable', '1000'],
            ['1200', 'Bank / Cash', '1000'],
            ['2100', 'Accounts Payable', '2000'],
            ['2150', 'Output VAT Payable', '2000'],
            ['4100', 'Service Revenue', '4000'],
            ['4200', 'AMC Contract Revenue', '4000'],
            ['5100', 'Maintenance Expense', '5000'],
            ['5200', 'Procurement Expense', '5000'],
            ['5300', 'Payroll Expense', '5000'],
            ['5400', 'Petty Cash Expense', '5000'],
        ];
        foreach ($accounts as $a) {
            $this->db->table('finance_accounts')->insert([
                'group_id' => $gid($a[2]),
                'code'     => $a[0],
                'name'     => $a[1],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
