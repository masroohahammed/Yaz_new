<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PortalRequirements extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('facility_id', 'work_orders')) {
            $this->db->query('ALTER TABLE `work_orders` MODIFY `facility_id` int unsigned NULL');
        }
        if ($this->db->tableExists('maintenance_requests') && ! $this->db->fieldExists('unit_id', 'maintenance_requests')) {
            $this->forge->addColumn('maintenance_requests', [
                'unit_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'facility_id'],
            ]);
        }
        if ($this->db->tableExists('invoice_items') && ! $this->db->fieldExists('unit_cost_internal', 'invoice_items')) {
            $this->forge->addColumn('invoice_items', [
                'unit_cost_internal' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true, 'after' => 'unit_price'],
            ]);
        }
        foreach (['wf_auto_invoice_on_client_approve' => '0'] as $key => $val) {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if (! $exists) {
                $this->db->table('system_settings')->insert(['setting_key' => $key, 'setting_value' => $val]);
            }
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('unit_id', 'maintenance_requests')) {
            $this->forge->dropColumn('maintenance_requests', 'unit_id');
        }
        if ($this->db->fieldExists('unit_cost_internal', 'invoice_items')) {
            $this->forge->dropColumn('invoice_items', 'unit_cost_internal');
        }
    }
}
