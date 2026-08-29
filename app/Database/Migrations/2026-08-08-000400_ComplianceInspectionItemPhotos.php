<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ComplianceInspectionItemPhotos extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('inspection_items')
            && ! $this->db->fieldExists('photos_json', 'inspection_items')) {
            $this->forge->addColumn('inspection_items', [
                'photos_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'remarks',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
