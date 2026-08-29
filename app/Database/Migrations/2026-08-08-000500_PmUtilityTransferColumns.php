<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PmUtilityTransferColumns extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('utility_accounts')) {
            $cols = [
                'paid_by' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'company', 'null' => true],
                'tenant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'transfer_date' => ['type' => 'DATE', 'null' => true],
                'transfer_reason' => ['type' => 'TEXT', 'null' => true],
            ];
            foreach ($cols as $name => $def) {
                if (! $this->db->fieldExists($name, 'utility_accounts')) {
                    $this->forge->addColumn('utility_accounts', [$name => $def]);
                }
            }
        }

        if ($this->db->tableExists('units') && ! $this->db->fieldExists('facility_id', 'units')) {
            $this->forge->addColumn('units', [
                'facility_id' => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'id'],
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
