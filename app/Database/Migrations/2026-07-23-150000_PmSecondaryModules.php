<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PmSecondaryModules extends Migration
{
    public function up()
    {
        // ── sales_deals: add commission_rule_id ──────────────────────
        if ($this->db->tableExists('sales_deals') && ! $this->db->fieldExists('commission_rule_id', 'sales_deals')) {
            $this->forge->addColumn('sales_deals', [
                'commission_rule_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'agent_id'],
            ]);
        }

        // ── commission_records ───────────────────────────────────────
        if (! $this->db->tableExists('commission_records')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'deal_id'        => ['type' => 'INT', 'unsigned' => true],
                'rule_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'agent_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'agent_amount'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'company_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'paid', 'cancelled'], 'default' => 'pending'],
                'notes'          => ['type' => 'TEXT', 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('deal_id');
            $this->forge->createTable('commission_records', true);
        }

        // ── utility_accounts ─────────────────────────────────────────
        if (! $this->db->tableExists('utility_accounts')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'unit_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'utility_name'    => ['type' => 'VARCHAR', 'constraint' => 80],
                'provider_name'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'account_number'  => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'meter_number'    => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'managed_by'      => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'billing_mode'    => ['type' => 'ENUM', 'constraint' => ['included', 'billed_separately', 'tenant_pays_direct', 'complimentary'], 'default' => 'included'],
                'monthly_charge'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
                'notes'           => ['type' => 'TEXT', 'null' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
                'updated_at'      => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('utility_accounts', true);
        }

        // ── utility_bills ─────────────────────────────────────────────
        if (! $this->db->tableExists('utility_bills')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'account_id'       => ['type' => 'INT', 'unsigned' => true],
                'bill_no'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'bill_date'        => ['type' => 'DATE', 'null' => true],
                'period_from'      => ['type' => 'DATE', 'null' => true],
                'period_to'        => ['type' => 'DATE', 'null' => true],
                'reading_prev'     => ['type' => 'DECIMAL', 'constraint' => '10,3', 'null' => true],
                'reading_curr'     => ['type' => 'DECIMAL', 'constraint' => '10,3', 'null' => true],
                'amount'           => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'charge_to_tenant' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'due_date'         => ['type' => 'DATE', 'null' => true],
                'paid_by'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'paid', 'transferred', 'cancelled'], 'default' => 'pending'],
                'notes'            => ['type' => 'TEXT', 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('account_id');
            $this->forge->createTable('utility_bills', true);
        }

        // ── property_budgets ─────────────────────────────────────────
        if (! $this->db->tableExists('property_budgets')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'year'        => ['type' => 'SMALLINT', 'unsigned' => true],
                'month'       => ['type' => 'TINYINT', 'unsigned' => true],
                'income'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'expense'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'notes'       => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
                'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['facility_id', 'year', 'month']);
            $this->forge->createTable('property_budgets', true);
        }

        // ── cost_reminders ───────────────────────────────────────────
        if (! $this->db->tableExists('cost_reminders')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'type'        => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'general'],
                'title'       => ['type' => 'VARCHAR', 'constraint' => 200],
                'due_date'    => ['type' => 'DATE', 'null' => true],
                'recurrence'  => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'notes'       => ['type' => 'TEXT', 'null' => true],
                'status'      => ['type' => 'ENUM', 'constraint' => ['pending', 'done', 'snoozed'], 'default' => 'pending'],
                'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
                'updated_at'  => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('cost_reminders', true);
        }
    }

    public function down()
    {
        foreach ([
            'cost_reminders', 'property_budgets', 'utility_bills',
            'utility_accounts', 'commission_records',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
