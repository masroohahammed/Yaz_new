<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ComplianceInspections extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('units')) {
            return;
        }

        if (! $this->db->fieldExists('facility_id', 'units')) {
            $this->forge->addColumn('units', [
                'facility_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => false,
                    'default'  => 0,
                    'after'    => 'id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('deleted_at', 'units')) {
            $this->forge->addColumn('units', [
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
        }

        if (! $this->db->tableExists('inspection_checklists')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'auto_increment' => true],
                'facility_id' => ['type' => 'INT', 'default' => 0],
                'unit_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'type' => ['type' => 'VARCHAR', 'constraint' => 100],
                'inspection_date' => ['type' => 'DATE'],
                'inspector_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'passed', 'failed'], 'default' => 'pending'],
                'score' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'overall_remarks' => ['type' => 'TEXT', 'null' => true],
                'created_by' => ['type' => 'INT', 'null' => true],
                'completed_by' => ['type' => 'INT', 'null' => true],
                'completed_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'damage_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('facility_id');
            $this->forge->addKey('unit_id');
            $this->forge->createTable('inspection_checklists', true);
        } else {
            if (! $this->db->fieldExists('facility_id', 'inspection_checklists')) {
                $this->forge->addColumn('inspection_checklists', [
                    'facility_id' => ['type' => 'INT', 'default' => 0, 'after' => 'id'],
                ]);
            }
            if (! $this->db->fieldExists('unit_id', 'inspection_checklists')) {
                $this->forge->addColumn('inspection_checklists', [
                    'unit_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'facility_id'],
                ]);
            }
        }

        if (! $this->db->tableExists('inspection_items')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'auto_increment' => true],
                'checklist_id' => ['type' => 'INT', 'default' => 0],
                'item_text' => ['type' => 'VARCHAR', 'constraint' => 500],
                'result' => ['type' => 'ENUM', 'constraint' => ['pending', 'pass', 'fail', 'na'], 'default' => 'pending'],
                'remarks' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('checklist_id');
            $this->forge->createTable('inspection_items', true);
        }

        if (! $this->db->tableExists('unit_checklists')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'unit_id' => ['type' => 'INT', 'unsigned' => true],
                'type' => ['type' => 'ENUM', 'constraint' => ['move_in', 'move_out', 'routine', 'handover'], 'default' => 'routine'],
                'items_json' => ['type' => 'MEDIUMTEXT', 'null' => true],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'completed'], 'default' => 'draft'],
                'created_by' => ['type' => 'INT', 'unsigned' => true],
                'created_at' => ['type' => 'DATETIME'],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('unit_id');
            $this->forge->createTable('unit_checklists', true);
        }

        $ucCols = [
            'inspection_date' => ['type' => 'DATE', 'null' => true],
            'inspector_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'overall_condition' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'link_to' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'ref_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'areas_json' => ['type' => 'MEDIUMTEXT', 'null' => true],
        ];
        foreach ($ucCols as $name => $def) {
            if (! $this->db->fieldExists($name, 'unit_checklists')) {
                $this->forge->addColumn('unit_checklists', [$name => $def]);
            }
        }

        foreach (['inspection_checklists', 'inspection_items'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            $col = $this->db->query("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'")->getRowArray();
            if (! $col || stripos((string) ($col['Extra'] ?? ''), 'auto_increment') !== false) {
                continue;
            }
            $maxRow = $this->db->table($table)->selectMax('id', 'max_id')->get()->getRowArray();
            $maxId  = (int) ($maxRow['max_id'] ?? 0);
            if ($this->db->table($table)->where('id', 0)->countAllResults() > 0) {
                $newId = $maxId + 1;
                $this->db->table($table)->where('id', 0)->update(['id' => $newId]);
                $maxId = $newId;
            }
            $type = $col['Type'] ?? 'int(11)';
            $next = $maxId + 1;
            $this->db->query(
                "ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, AUTO_INCREMENT={$next}"
            );
        }
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
