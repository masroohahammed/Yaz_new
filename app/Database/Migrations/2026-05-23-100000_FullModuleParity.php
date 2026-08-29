<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FullModuleParity extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('petty_cash')) {
            $this->db->query("
                ALTER TABLE `petty_cash`
                MODIFY `status` enum('pending','approved','issued','reconciliation','closed','rejected') NOT NULL DEFAULT 'pending'
            ");
            if (!$this->db->fieldExists('issued_by', 'petty_cash')) {
                $this->forge->addColumn('petty_cash', [
                    'issued_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'approved_at'],
                    'issued_at'      => ['type' => 'DATETIME', 'null' => true, 'after' => 'issued_by'],
                    'reconciled_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'issued_at'],
                    'reconciled_at'  => ['type' => 'DATETIME', 'null' => true, 'after' => 'reconciled_by'],
                    'closed_at'      => ['type' => 'DATETIME', 'null' => true, 'after' => 'settled_at'],
                ]);
            }
            $this->db->query("UPDATE `petty_cash` SET `status` = 'closed' WHERE `status` = 'settled'");
        }

        $newRoles = [
            ['property_manager', 'Property Manager'],
            ['supervisor', 'Supervisor'],
            ['qa_inspector', 'QA Inspector'],
        ];
        foreach ($newRoles as [$name, $display]) {
            $exists = $this->db->table('roles')->where('name', $name)->countAllResults();
            if (!$exists) {
                $this->db->table('roles')->insert([
                    'name'         => $name,
                    'display_name' => $display,
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        foreach (\App\Services\WorkflowSettingsService::KEYS as $key => $default) {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if (!$exists) {
                $this->db->table('system_settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $default,
                ]);
            }
        }
    }

    public function down()
    {
        // Non-destructive rollback omitted for production safety
    }
}
