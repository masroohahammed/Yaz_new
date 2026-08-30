<?php

namespace App\Database\Migrations;

use App\Database\AutoIncrementRepair;
use CodeIgniter\Database\Migration;

/**
 * Property / asset inspections on unit_checklists + scan log AUTO_INCREMENT repair.
 */
class PropertyInspectionColumns extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('unit_checklists')) {
            $cols = [
                'facility_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
                'asset_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'facility_id'],
                'scope_type'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unit', 'after' => 'asset_id'],
                'floor_label' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'scope_type'],
            ];
            foreach ($cols as $name => $def) {
                if (! $this->db->fieldExists($name, 'unit_checklists')) {
                    $this->forge->addColumn('unit_checklists', [$name => $def]);
                }
            }

            // Allow property-level inspections without a unit
            $unitCol = $this->db->query("SHOW COLUMNS FROM unit_checklists WHERE Field = 'unit_id'")->getRowArray();
            if ($unitCol && strtoupper((string) ($unitCol['Null'] ?? '')) === 'NO') {
                $this->db->query('ALTER TABLE unit_checklists MODIFY unit_id INT(10) UNSIGNED NULL DEFAULT NULL');
            }

            AutoIncrementRepair::ensure($this->db, 'unit_checklists');
        }

        foreach (['asset_scan_logs', 'qr_scan_logs'] as $table) {
            if ($this->db->tableExists($table)) {
                AutoIncrementRepair::ensure($this->db, $table);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
