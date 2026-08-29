<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PortalPhase23 extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('site_visits')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'visit_number'    => ['type' => 'VARCHAR', 'constraint' => 30],
                'facility_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'unit_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'work_order_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'scheduled_at'    => ['type' => 'DATETIME', 'null' => true],
                'visited_at'      => ['type' => 'DATETIME', 'null' => true],
                'status'          => ['type' => 'ENUM', 'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'], 'default' => 'scheduled'],
                'purpose'         => ['type' => 'TEXT', 'null' => true],
                'requirements'    => ['type' => 'TEXT', 'null' => true],
                'observations'    => ['type' => 'TEXT', 'null' => true],
                'follow_up_date'  => ['type' => 'DATE', 'null' => true],
                'technician_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'supervisor_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'technician_remarks' => ['type' => 'TEXT', 'null' => true],
                'supervisor_remarks' => ['type' => 'TEXT', 'null' => true],
                'client_signature'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'technician_signature' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'photo_path'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
                'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('site_visits', true);
        }

        if (! $this->db->tableExists('procurement_three_way_matches')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'po_id'          => ['type' => 'INT', 'unsigned' => true],
                'grn_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'vendor_bill_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'po_amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'grn_amount'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'bill_amount'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'variance'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'match_status'   => ['type' => 'ENUM', 'constraint' => ['pending', 'matched', 'exception'], 'default' => 'pending'],
                'notes'          => ['type' => 'TEXT', 'null' => true],
                'matched_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'matched_at'     => ['type' => 'DATETIME', 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('procurement_three_way_matches', true);
        }

        if (! $this->db->tableExists('report_saved_queries')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'name'         => ['type' => 'VARCHAR', 'constraint' => 120],
                'report_type'  => ['type' => 'VARCHAR', 'constraint' => 60],
                'columns_json' => ['type' => 'TEXT', 'null' => true],
                'filters_json' => ['type' => 'TEXT', 'null' => true],
                'show_cost'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'group_by'     => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'created_at'   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('report_saved_queries', true);
        }

        foreach ([
            'alert_email_enabled'     => '1',
            'alert_whatsapp_enabled'  => '0',
            'alert_whatsapp_webhook'  => '',
            'site_visits_enabled'     => '1',
        ] as $key => $val) {
            if ($this->db->tableExists('system_settings')) {
                $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
                if (! $exists) {
                    $this->db->table('system_settings')->insert(['setting_key' => $key, 'setting_value' => $val]);
                }
            }
        }

        if ($this->db->tableExists('wo_approvals') && ! $this->db->fieldExists('signature_path', 'wo_approvals')) {
            $this->forge->addColumn('wo_approvals', [
                'signature_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'notes'],
            ]);
        }
    }

    public function down()
    {
        foreach (['site_visits', 'procurement_three_way_matches', 'report_saved_queries'] as $t) {
            if ($this->db->tableExists($t)) {
                $this->forge->dropTable($t, true);
            }
        }
        if ($this->db->fieldExists('signature_path', 'wo_approvals')) {
            $this->forge->dropColumn('wo_approvals', 'signature_path');
        }
    }
}
