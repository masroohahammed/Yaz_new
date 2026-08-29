<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Enterprise production schema: payments, security, indexes, invoice partial status.
 */
class EnterpriseProduction extends Migration
{
    public function up()
    {
        // Partial payments ledger
        if (!$this->db->tableExists('invoice_payments')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'invoice_id'      => ['type' => 'INT', 'unsigned' => true],
                'amount'          => ['type' => 'DECIMAL', 'constraint' => '12,2'],
                'payment_method'  => ['type' => 'ENUM', 'constraint' => ['cash', 'bank', 'card', 'cheque', 'online'], 'default' => 'bank'],
                'reference_no'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'notes'           => ['type' => 'TEXT', 'null' => true],
                'paid_at'         => ['type' => 'DATETIME'],
                'recorded_by'     => ['type' => 'INT', 'unsigned' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('invoice_id');
            $this->forge->addForeignKey('invoice_id', 'invoices', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('invoice_payments', true);
        }

        // Login attempt tracking (brute-force protection)
        if (!$this->db->tableExists('login_attempts')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 120],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
                'success'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['email', 'created_at']);
            $this->forge->createTable('login_attempts', true);
        }

        // Extend invoice status for partial payments (MariaDB)
        if ($this->db->fieldExists('status', 'invoices')) {
            $this->db->query("ALTER TABLE `invoices` MODIFY `status` enum('draft','sent','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'draft'");
        }

        // Company isolation columns
        foreach (['work_orders', 'invoices', 'contracts', 'assets', 'expenses', 'utility_readings'] as $table) {
            if ($this->db->tableExists($table) && !$this->db->fieldExists('company_id', $table)) {
                $this->forge->addColumn($table, [
                    'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
                ]);
                $this->db->query("UPDATE `$table` t JOIN `facilities` f ON f.id = t.facility_id SET t.company_id = f.company_id WHERE t.company_id IS NULL AND f.company_id IS NOT NULL");
            }
        }

        if ($this->db->tableExists('employees') && !$this->db->fieldExists('company_id', 'employees')) {
            $this->forge->addColumn('employees', [
                'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
            ]);
            $this->db->query('UPDATE employees e JOIN facilities f ON f.id = e.facility_id SET e.company_id = f.company_id WHERE e.company_id IS NULL');
        }

        if ($this->db->tableExists('purchase_orders') && !$this->db->fieldExists('company_id', 'purchase_orders')) {
            $this->forge->addColumn('purchase_orders', [
                'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('invoice_payments')) {
            $this->forge->dropTable('invoice_payments', true);
        }
        if ($this->db->tableExists('login_attempts')) {
            $this->forge->dropTable('login_attempts', true);
        }
    }
}
