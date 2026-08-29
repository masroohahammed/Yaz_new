<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tenant blacklist metadata + history. Existing status/is_blacklisted are retained.
 */
class TenantBlacklistAudit extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('tenants')) {
            $cols = [];
            if (! $this->db->fieldExists('blacklist_reason', 'tenants')) {
                $cols['blacklist_reason'] = ['type' => 'TEXT', 'null' => true];
            }
            if (! $this->db->fieldExists('blacklisted_at', 'tenants')) {
                $cols['blacklisted_at'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (! $this->db->fieldExists('blacklisted_by', 'tenants')) {
                $cols['blacklisted_by'] = ['type' => 'INT', 'unsigned' => true, 'null' => true];
            }
            if (! $this->db->fieldExists('unblacklist_reason', 'tenants')) {
                $cols['unblacklist_reason'] = ['type' => 'TEXT', 'null' => true];
            }
            if (! $this->db->fieldExists('unblacklisted_at', 'tenants')) {
                $cols['unblacklisted_at'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (! $this->db->fieldExists('unblacklisted_by', 'tenants')) {
                $cols['unblacklisted_by'] = ['type' => 'INT', 'unsigned' => true, 'null' => true];
            }
            if ($cols !== []) {
                $this->forge->addColumn('tenants', $cols);
            }
        }

        if (! $this->db->tableExists('tenant_blacklist_history')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
                'company_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'action'       => ['type' => 'VARCHAR', 'constraint' => 20],
                'reason'       => ['type' => 'TEXT', 'null' => true],
                'performed_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('tenant_id');
            $this->forge->addKey('company_id');
            $this->forge->createTable('tenant_blacklist_history', true);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('tenant_blacklist_history')) {
            $this->forge->dropTable('tenant_blacklist_history', true);
        }
        if (! $this->db->tableExists('tenants')) {
            return;
        }
        foreach ([
            'blacklist_reason', 'blacklisted_at', 'blacklisted_by',
            'unblacklist_reason', 'unblacklisted_at', 'unblacklisted_by',
        ] as $col) {
            if ($this->db->fieldExists($col, 'tenants')) {
                $this->forge->dropColumn('tenants', $col);
            }
        }
    }
}
