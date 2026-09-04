<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ParkingContractPhotos extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('lease_contracts') && ! $this->db->fieldExists('photos_json', 'lease_contracts')) {
            $this->forge->addColumn('lease_contracts', [
                'photos_json' => ['type' => 'TEXT', 'null' => true],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('lease_contracts') && $this->db->fieldExists('photos_json', 'lease_contracts')) {
            $this->forge->dropColumn('lease_contracts', 'photos_json');
        }
    }
}
