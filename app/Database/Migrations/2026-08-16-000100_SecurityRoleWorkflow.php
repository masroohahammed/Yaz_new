<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Security settings, role approval workflow, and property column fixes.
 * SQL equivalent: database/patch_security_role_workflow.sql
 */
class SecurityRoleWorkflow20260816 extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('users')) {
            foreach ([
                'mfa_secret'          => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'mfa_enabled'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'password_changed_at' => ['type' => 'DATETIME', 'null' => true],
            ] as $col => $def) {
                if (! $this->db->fieldExists($col, 'users')) {
                    $this->forge->addColumn('users', [$col => $def]);
                }
            }
        }

        if (! $this->db->tableExists('login_attempts')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 150],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
                'success'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['email', 'created_at']);
            $this->forge->createTable('login_attempts', true);
        }

        if ($this->db->tableExists('roles') && ! $this->db->fieldExists('description', 'roles')) {
            $this->forge->addColumn('roles', [
                'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'display_name'],
                'is_system'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'workspace'],
            ]);
        }

        if (! $this->db->tableExists('role_approval_settings')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'role_id'     => ['type' => 'INT', 'unsigned' => true],
                'workspace'   => ['type' => 'ENUM', 'constraint' => ['fm', 'pm']],
                'setting_key' => ['type' => 'VARCHAR', 'constraint' => 80],
                'enabled'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['role_id', 'workspace', 'setting_key']);
            $this->forge->createTable('role_approval_settings', true);
        }

        if ($this->db->tableExists('facilities') && ! $this->db->fieldExists('price_per_sqm', 'facilities')) {
            $this->forge->addColumn('facilities', [
                'price_per_sqm' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            ]);
        }

        foreach ([
            'vendor_quotation_items', 'vendor_quotations',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->seedSettings();
    }

    public function down(): void
    {
        if ($this->db->tableExists('role_approval_settings')) {
            $this->forge->dropTable('role_approval_settings', true);
        }
    }

    private function seedSettings(): void
    {
        if (! $this->db->tableExists('system_settings')) {
            return;
        }

        $keys = array_merge(
            \App\Services\SecuritySettingsService::KEYS,
            \App\Services\WorkflowSettingsService::KEYS
        );

        foreach ($keys as $key => $default) {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if ($exists) {
                continue;
            }
            $cat = str_starts_with($key, 'sec_') ? 'security' : 'workflow';
            $this->db->table('system_settings')->insert([
                'setting_key'   => $key,
                'setting_value' => $default,
                'setting_group' => $cat,
            ]);
        }
    }
}
