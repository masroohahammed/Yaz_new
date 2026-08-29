<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Remove standalone vendor_quotations module (consolidated into Estimations + RFQ bids).
 */
class DropVendorQuotations extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('vendor_quotation_items')) {
            $this->forge->dropTable('vendor_quotation_items', true);
        }
        if ($this->db->tableExists('vendor_quotations')) {
            $this->forge->dropTable('vendor_quotations', true);
        }

        if ($this->db->tableExists('role_permissions')) {
            $this->db->table('role_permissions')->where('module', 'quotations')->delete();
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('vendor_quotations')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'facility_id'    => ['type' => 'INT', 'unsigned' => true],
                'vendor_name'    => ['type' => 'VARCHAR', 'constraint' => 200],
                'vendor_contact' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'description'    => ['type' => 'TEXT', 'null' => true],
                'valid_until'    => ['type' => 'DATE', 'null' => true],
                'status'         => ['type' => 'ENUM', 'constraint' => ['draft', 'submitted', 'approved', 'rejected', 'expired'], 'default' => 'draft'],
                'notes'          => ['type' => 'TEXT', 'null' => true],
                'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('facility_id');
            $this->forge->createTable('vendor_quotations', true);
        }

        if (! $this->db->tableExists('vendor_quotation_items')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'quotation_id' => ['type' => 'INT', 'unsigned' => true],
                'description'  => ['type' => 'TEXT'],
                'qty'          => ['type' => 'DECIMAL', 'constraint' => '10,3', 'default' => 1],
                'unit'         => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pcs'],
                'unit_price'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'sort_order'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('quotation_id');
            $this->forge->createTable('vendor_quotation_items', true);
        }
    }
}
