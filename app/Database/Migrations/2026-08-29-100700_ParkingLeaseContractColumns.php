<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Parking lease columns used by ParkingContractService and lease print.
 */
class ParkingLeaseContractColumns extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('lease_contracts')) {
            $cols = [];
            foreach ([
                'contract_kind'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'tenant_qid'          => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'plate_number'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'vehicle_type'        => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'vehicle_description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'title_deed_no'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'zone_no'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'street_no'           => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'building_no'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            ] as $name => $def) {
                if (! $this->db->fieldExists($name, 'lease_contracts')) {
                    $cols[$name] = $def;
                }
            }
            if ($cols !== []) {
                $this->forge->addColumn('lease_contracts', $cols);
            }
        }

        if ($this->db->tableExists('units') && ! $this->db->fieldExists('plate_number', 'units')) {
            $this->forge->addColumn('units', [
                'plate_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'rent_amount'],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('lease_contracts')) {
            foreach ([
                'contract_kind', 'tenant_qid', 'plate_number', 'vehicle_type', 'vehicle_description',
                'title_deed_no', 'zone_no', 'street_no', 'building_no',
            ] as $col) {
                if ($this->db->fieldExists($col, 'lease_contracts')) {
                    $this->forge->dropColumn('lease_contracts', $col);
                }
            }
        }
        if ($this->db->tableExists('units') && $this->db->fieldExists('plate_number', 'units')) {
            $this->forge->dropColumn('units', 'plate_number');
        }
    }
}
