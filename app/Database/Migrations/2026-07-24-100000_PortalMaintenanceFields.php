<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ensures maintenance_requests has portal-friendly columns used by
 * Tenant Portal web + mobile API (title, tenant_id, photo).
 */
class PortalMaintenanceFields extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }

        $cols = [
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'requester_phone',
            ],
            'tenant_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'unit_id',
            ],
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'image_path',
            ],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'maintenance_requests')) {
                $this->forge->addColumn('maintenance_requests', [$name => $def]);
            }
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }

        foreach (['photo', 'tenant_id', 'title'] as $col) {
            if ($this->db->fieldExists($col, 'maintenance_requests')) {
                $this->forge->dropColumn('maintenance_requests', $col);
            }
        }
    }
}
