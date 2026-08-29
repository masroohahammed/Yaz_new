<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ComplianceInspectionFrequency extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('inspection_checklists')
            && ! $this->db->fieldExists('frequency', 'inspection_checklists')) {
            $this->forge->addColumn('inspection_checklists', [
                'frequency' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'regular',
                    'after'      => 'type',
                ],
            ]);
        }

        if ($this->db->tableExists('unit_checklists')
            && ! $this->db->fieldExists('frequency', 'unit_checklists')) {
            $this->forge->addColumn('unit_checklists', [
                'frequency' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'regular',
                    'after'      => 'type',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
