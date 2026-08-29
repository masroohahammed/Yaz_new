<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PmWorkflowExtras extends Migration
{
    public function up()
    {
        // ── Lease amendments ─────────────────────────────────────────
        if (! $this->db->tableExists('lease_amendments')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'contract_id' => ['type' => 'INT', 'unsigned' => true],
                'new_rent'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'new_end_date'=> ['type' => 'DATE', 'null' => true],
                'effective_date' => ['type' => 'DATE'],
                'description' => ['type' => 'TEXT', 'null' => true],
                'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('contract_id');
            $this->forge->createTable('lease_amendments', true);
        }

        // ── Payment partials ─────────────────────────────────────────
        if (! $this->db->tableExists('payment_partials')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'payment_id' => ['type' => 'INT', 'unsigned' => true],
                'amount'     => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'paid_date'  => ['type' => 'DATE', 'null' => true],
                'method'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'notes'      => ['type' => 'TEXT', 'null' => true],
                'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('payment_id');
            $this->forge->createTable('payment_partials', true);
        }

        // ── Refunds ──────────────────────────────────────────────────
        if (! $this->db->tableExists('refunds')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'payment_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'contract_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'refund_type'   => ['type' => 'VARCHAR', 'constraint' => 50],
                'refund_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'refund_date'   => ['type' => 'DATE', 'null' => true],
                'reference_no'  => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'notes'         => ['type' => 'TEXT', 'null' => true],
                'created_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('refunds', true);
        }

        // ── Landlord payouts ─────────────────────────────────────────
        if (! $this->db->tableExists('landlord_payouts')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'landlord_id'    => ['type' => 'INT', 'unsigned' => true],
                'period_from'    => ['type' => 'DATE', 'null' => true],
                'period_to'      => ['type' => 'DATE', 'null' => true],
                'gross_rent'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'commission'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'deductions'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'net_amount'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'paid'], 'default' => 'pending'],
                'paid_date'      => ['type' => 'DATE', 'null' => true],
                'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'reference_no'   => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'notes'          => ['type' => 'TEXT', 'null' => true],
                'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('landlord_id');
            $this->forge->createTable('landlord_payouts', true);
        }

        // ── Extra cheque columns for legal tracking & cash conversion ─
        if ($this->db->tableExists('cheques')) {
            $chequeCols = [
                'file_legal'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'case_no'              => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'filed_date'           => ['type' => 'DATE', 'null' => true],
                'case_notes'           => ['type' => 'TEXT', 'null' => true],
                'cash_conversion_date' => ['type' => 'DATE', 'null' => true],
            ];
            foreach ($chequeCols as $col => $def) {
                if (! $this->db->fieldExists($col, 'cheques')) {
                    $this->forge->addColumn('cheques', [$col => $def]);
                }
            }
        }
    }

    public function down()
    {
        foreach (['landlord_payouts', 'refunds', 'payment_partials', 'lease_amendments'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($this->db->tableExists('cheques')) {
            foreach (['file_legal', 'case_no', 'filed_date', 'case_notes', 'cash_conversion_date'] as $col) {
                if ($this->db->fieldExists($col, 'cheques')) {
                    $this->forge->dropColumn('cheques', $col);
                }
            }
        }
    }
}
