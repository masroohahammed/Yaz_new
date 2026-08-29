<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Finance & Bank Management — bank/cash accounts, ledger, deposits, withdrawals.
 * SQL equivalent: database/patch_finance_bank_management.sql
 */
class FinanceBankManagement20260816 extends Migration
{
    public function up(): void
    {
        $this->extendBankAccounts();
        $this->createCashAccounts();
        $this->createCategories();
        $this->createDocuments();
        $this->createLedger();
        $this->createReconciliation();
        $this->createAuditLogs();
        $this->seedCategories();
        $this->seedSettings();
    }

    public function down(): void
    {
        foreach ([
            'finance_audit_logs', 'finance_reconciliation_items', 'finance_reconciliations',
            'finance_transaction_approvals', 'finance_transactions',
            'finance_expense_records', 'finance_income_records', 'finance_transfers',
            'finance_withdrawals', 'finance_deposits', 'finance_categories', 'finance_cash_accounts',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }

    private function extendBankAccounts(): void
    {
        if (! $this->db->tableExists('finance_bank_accounts')) {
            return;
        }

        $cols = [
            'company_id'                 => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'branch_id'                  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'facility_id'                => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'department'                 => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'scope_type'                 => ['type' => 'ENUM', 'constraint' => ['company', 'branch', 'property'], 'default' => 'company'],
            'branch_name'                => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'iban'                       => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'swift_bic'                  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'account_type'               => ['type' => 'ENUM', 'constraint' => ['current', 'savings', 'corporate', 'other'], 'default' => 'current'],
            'opening_balance'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'opening_balance_date'       => ['type' => 'DATE', 'null' => true],
            'opening_balance_notes'      => ['type' => 'TEXT', 'null' => true],
            'opening_balance_created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'opening_balance_created_at' => ['type' => 'DATETIME', 'null' => true],
            'bank_contact'               => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'bank_address'               => ['type' => 'TEXT', 'null' => true],
            'notes'                      => ['type' => 'TEXT', 'null' => true],
            'status'                     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive', 'closed'], 'default' => 'active'],
            'current_balance'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'available_balance'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'min_balance_alert'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'last_transaction_date'      => ['type' => 'DATE', 'null' => true],
            'account_opening_date'       => ['type' => 'DATE', 'null' => true],
            'created_by'                 => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'                 => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                 => ['type' => 'DATETIME', 'null' => true],
        ];

        foreach ($cols as $col => $def) {
            if (! $this->db->fieldExists($col, 'finance_bank_accounts')) {
                $this->forge->addColumn('finance_bank_accounts', [$col => $def]);
            }
        }
    }

    private function createCashAccounts(): void
    {
        if ($this->db->tableExists('finance_cash_accounts')) {
            return;
        }

        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'branch_id'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'facility_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'department'            => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'name'                  => ['type' => 'VARCHAR', 'constraint' => 120],
            'account_type'          => ['type' => 'ENUM', 'constraint' => ['main', 'branch', 'petty', 'property', 'other'], 'default' => 'main'],
            'currency'              => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'QAR'],
            'opening_balance'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'opening_balance_date'  => ['type' => 'DATE', 'null' => true],
            'opening_balance_notes' => ['type' => 'TEXT', 'null' => true],
            'responsible_user_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'current_balance'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'available_balance'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'min_balance_alert'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'last_transaction_date' => ['type' => 'DATE', 'null' => true],
            'status'                => ['type' => 'ENUM', 'constraint' => ['active', 'inactive', 'closed'], 'default' => 'active'],
            'notes'                 => ['type' => 'TEXT', 'null' => true],
            'created_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'branch_id', 'facility_id', 'status']);
        $this->forge->createTable('finance_cash_accounts', true);
    }

    private function createCategories(): void
    {
        if ($this->db->tableExists('finance_categories')) {
            return;
        }

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'category_type' => ['type' => 'ENUM', 'constraint' => ['income', 'expense', 'deposit', 'withdrawal', 'transfer', 'adjustment', 'refund']],
            'code'          => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'    => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['category_type', 'code']);
        $this->forge->createTable('finance_categories', true);
    }

    private function createDocuments(): void
    {
        if (! $this->db->tableExists('finance_deposits')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'deposit_number'   => ['type' => 'VARCHAR', 'constraint' => 30],
                'deposit_date'     => ['type' => 'DATE'],
                'bank_account_id'  => ['type' => 'INT', 'unsigned' => true],
                'amount'           => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'currency'         => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'QAR'],
                'deposit_source'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'category_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'branch_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'client_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'contract_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'reference_number' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'payment_method'   => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'description'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'attachment'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'notes'            => ['type' => 'TEXT', 'null' => true],
                'status'           => ['type' => 'ENUM', 'constraint' => ['draft', 'pending_approval', 'approved', 'rejected', 'posted', 'cancelled'], 'default' => 'draft'],
                'created_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'approved_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'posted_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'posted_at'        => ['type' => 'DATETIME', 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('deposit_number');
            $this->forge->addKey(['bank_account_id', 'status', 'deposit_date']);
            $this->forge->createTable('finance_deposits', true);
        }

        foreach (['finance_withdrawals', 'finance_transfers', 'finance_income_records', 'finance_expense_records'] as $table) {
            if ($this->db->tableExists($table)) {
                continue;
            }
        }

        if (! $this->db->tableExists('finance_withdrawals')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'withdrawal_number' => ['type' => 'VARCHAR', 'constraint' => 30],
                'withdrawal_date'   => ['type' => 'DATE'],
                'bank_account_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'cash_account_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'amount'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'currency'          => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'QAR'],
                'category_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'vendor_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'employee_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'branch_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'department'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'expense_reference' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'description'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'attachment'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'notes'             => ['type' => 'TEXT', 'null' => true],
                'status'            => ['type' => 'ENUM', 'constraint' => ['draft', 'pending_approval', 'approved', 'rejected', 'posted', 'cancelled'], 'default' => 'draft'],
                'created_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'approved_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'posted_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'posted_at'         => ['type' => 'DATETIME', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('withdrawal_number');
            $this->forge->createTable('finance_withdrawals', true);
        }

        if (! $this->db->tableExists('finance_transfers')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'transfer_number'   => ['type' => 'VARCHAR', 'constraint' => 30],
                'transfer_date'     => ['type' => 'DATE'],
                'from_account_type' => ['type' => 'ENUM', 'constraint' => ['bank', 'cash']],
                'from_account_id'   => ['type' => 'INT', 'unsigned' => true],
                'to_account_type'   => ['type' => 'ENUM', 'constraint' => ['bank', 'cash']],
                'to_account_id'     => ['type' => 'INT', 'unsigned' => true],
                'amount'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'currency'          => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'QAR'],
                'transfer_fee'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'reference'         => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'purpose'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'attachment'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'notes'             => ['type' => 'TEXT', 'null' => true],
                'status'            => ['type' => 'ENUM', 'constraint' => ['draft', 'pending_approval', 'approved', 'rejected', 'posted', 'cancelled'], 'default' => 'draft'],
                'created_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'approved_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'posted_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'posted_at'         => ['type' => 'DATETIME', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('transfer_number');
            $this->forge->createTable('finance_transfers', true);
        }
    }

    private function createLedger(): void
    {
        if ($this->db->tableExists('finance_transactions')) {
            return;
        }

        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_number'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'transaction_date'     => ['type' => 'DATE'],
            'account_type'         => ['type' => 'ENUM', 'constraint' => ['bank', 'cash']],
            'account_id'           => ['type' => 'INT', 'unsigned' => true],
            'transaction_type'     => ['type' => 'ENUM', 'constraint' => ['opening_balance', 'income', 'expense', 'deposit', 'withdrawal', 'bank_transfer', 'cash_transfer', 'refund', 'adjustment', 'payment', 'receipt']],
            'debit'                => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'credit'               => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'balance_after'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'currency'             => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'QAR'],
            'exchange_rate'        => ['type' => 'DECIMAL', 'constraint' => '12,6', 'null' => true],
            'base_amount'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'reference_type'       => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'reference_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'linked_transaction_id'=> ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'branch_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'facility_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'unit_id'              => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'department'           => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'client_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'vendor_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'contract_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'invoice_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'work_order_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'category_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'payment_method'       => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'description'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'attachment'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'notes'                => ['type' => 'TEXT', 'null' => true],
            'status'               => ['type' => 'ENUM', 'constraint' => ['draft', 'pending_approval', 'approved', 'rejected', 'posted', 'cancelled', 'reversed'], 'default' => 'draft'],
            'reversal_of'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'is_reversal'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'counts_as_income'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'counts_as_expense'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'posted_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'submitted_at'         => ['type' => 'DATETIME', 'null' => true],
            'approved_at'          => ['type' => 'DATETIME', 'null' => true],
            'posted_at'            => ['type' => 'DATETIME', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('transaction_number');
        $this->forge->addKey(['account_type', 'account_id', 'transaction_date', 'status']);
        $this->forge->createTable('finance_transactions', true);

        if (! $this->db->tableExists('finance_transaction_approvals')) {
            $this->forge->addField([
                'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'transaction_ref_type' => ['type' => 'VARCHAR', 'constraint' => 40],
                'transaction_ref_id'   => ['type' => 'INT', 'unsigned' => true],
                'approval_level'       => ['type' => 'INT', 'default' => 1],
                'required_role'        => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'approver_user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'status'               => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'skipped'], 'default' => 'pending'],
                'comments'             => ['type' => 'TEXT', 'null' => true],
                'acted_by'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'acted_at'             => ['type' => 'DATETIME', 'null' => true],
                'created_at'           => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['transaction_ref_type', 'transaction_ref_id']);
            $this->forge->createTable('finance_transaction_approvals', true);
        }
    }

    private function createReconciliation(): void
    {
        if ($this->db->tableExists('finance_reconciliations')) {
            return;
        }

        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'bank_account_id'    => ['type' => 'INT', 'unsigned' => true],
            'statement_date'     => ['type' => 'DATE'],
            'statement_opening'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'statement_closing'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'system_balance'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'difference'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'status'             => ['type' => 'ENUM', 'constraint' => ['not_started', 'in_progress', 'reconciled'], 'default' => 'not_started'],
            'notes'              => ['type' => 'TEXT', 'null' => true],
            'locked_at'          => ['type' => 'DATETIME', 'null' => true],
            'locked_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('finance_reconciliations', true);

        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reconciliation_id' => ['type' => 'INT', 'unsigned' => true],
            'transaction_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'statement_ref'     => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'match_status'      => ['type' => 'ENUM', 'constraint' => ['matched', 'unmatched', 'partial', 'bank_charge', 'bank_interest', 'adjustment'], 'default' => 'unmatched'],
            'notes'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('finance_reconciliation_items', true);
    }

    private function createAuditLogs(): void
    {
        if ($this->db->tableExists('finance_audit_logs')) {
            return;
        }

        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_role'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'action'         => ['type' => 'VARCHAR', 'constraint' => 40],
            'module'         => ['type' => 'VARCHAR', 'constraint' => 60],
            'record_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'transaction_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'old_value'      => ['type' => 'TEXT', 'null' => true],
            'new_value'      => ['type' => 'TEXT', 'null' => true],
            'reason'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'ip_address'     => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('finance_audit_logs', true);
    }

    private function seedCategories(): void
    {
        if (! $this->db->tableExists('finance_categories')) {
            return;
        }

        $rows = [
            ['deposit', 'client_payment', 'Client Payment', 1],
            ['deposit', 'rental_income', 'Rental Income', 2],
            ['withdrawal', 'vendor_payment', 'Vendor Payment', 1],
            ['withdrawal', 'utility', 'Utility', 3],
            ['income', 'invoice', 'Client Invoice', 1],
            ['income', 'rent', 'Property Rent', 2],
            ['expense', 'vendor', 'Vendor Bill', 1],
            ['expense', 'maintenance', 'Maintenance', 3],
        ];

        foreach ($rows as [$type, $code, $name, $sort]) {
            $exists = $this->db->table('finance_categories')
                ->where('category_type', $type)->where('code', $code)->countAllResults();
            if (! $exists) {
                $this->db->table('finance_categories')->insert([
                    'category_type' => $type,
                    'code'          => $code,
                    'name'          => $name,
                    'sort_order'    => $sort,
                ]);
            }
        }
    }

    private function seedSettings(): void
    {
        if (! $this->db->tableExists('system_settings')) {
            return;
        }

        foreach (\App\Services\Finance\FinanceSettingsService::KEYS as $key => $default) {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if ($exists) {
                continue;
            }
            $this->db->table('system_settings')->insert([
                'setting_key'   => $key,
                'setting_value' => $default,
                'setting_group' => 'finance',
            ]);
        }
    }
}
