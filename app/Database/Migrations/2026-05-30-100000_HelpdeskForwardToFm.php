<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * RE Manager / Property Manager pass facility complaints to Facility Manager before WO conversion.
 */
class HelpdeskForwardToFm extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }

        if (! $this->db->fieldExists('forwarded_to_fm', 'maintenance_requests')) {
            $this->forge->addColumn('maintenance_requests', [
                'forwarded_to_fm' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'approved_at',
                ],
                'forwarded_by' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'forwarded_to_fm',
                ],
                'forwarded_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'forwarded_by',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }

        foreach (['forwarded_at', 'forwarded_by', 'forwarded_to_fm'] as $col) {
            if ($this->db->fieldExists($col, 'maintenance_requests')) {
                $this->forge->dropColumn('maintenance_requests', $col);
            }
        }
    }
}
