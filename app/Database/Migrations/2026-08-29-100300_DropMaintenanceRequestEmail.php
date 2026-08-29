<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 2: drop duplicate maintenance_requests.email after confirming
 * the application writes/reads requester_email only.
 *
 * Before dropping, copies any leftover email values into requester_email
 * when requester_email is empty so no contact data is lost.
 */
class DropMaintenanceRequestEmail extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }
        if (! $this->db->fieldExists('email', 'maintenance_requests')) {
            return;
        }

        if ($this->db->fieldExists('requester_email', 'maintenance_requests')) {
            try {
                $this->db->query(
                    "UPDATE maintenance_requests
                     SET requester_email = email
                     WHERE (requester_email IS NULL OR requester_email = '')
                       AND email IS NOT NULL AND email <> ''"
                );
            } catch (\Throwable $e) {
                log_message('error', 'Could not backfill requester_email: ' . $e->getMessage());
            }
        }

        $this->forge->dropColumn('maintenance_requests', 'email');
    }

    public function down(): void
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }
        if ($this->db->fieldExists('email', 'maintenance_requests')) {
            return;
        }

        $this->forge->addColumn('maintenance_requests', [
            'email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
        ]);
        if ($this->db->fieldExists('requester_email', 'maintenance_requests')) {
            $this->db->query(
                'UPDATE maintenance_requests SET email = requester_email WHERE requester_email IS NOT NULL'
            );
        }
    }
}
