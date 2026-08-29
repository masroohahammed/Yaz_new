<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Short code for landlords (displayed in reports and index).
 */
class LandlordShortCode extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('landlords')) {
            return;
        }

        if (! $this->db->fieldExists('short_code', 'landlords')) {
            $this->forge->addColumn('landlords', [
                'short_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'full_name',
                ],
            ]);
        }

        // Backfill from legacy "Code: XYZ" notes when present.
        if ($this->db->fieldExists('short_code', 'landlords') && $this->db->fieldExists('notes', 'landlords')) {
            $rows = $this->db->table('landlords')
                ->select('id, notes')
                ->where('deleted_at', null)
                ->groupStart()
                    ->where('short_code', null)
                    ->orWhere('short_code', '')
                ->groupEnd()
                ->like('notes', 'Code:', 'after')
                ->get()->getResultArray();

            foreach ($rows as $row) {
                $notes = (string) ($row['notes'] ?? '');
                if (! preg_match('/Code:\s*([A-Za-z0-9_-]+)/', $notes, $m)) {
                    continue;
                }
                $this->db->table('landlords')->where('id', (int) $row['id'])->update([
                    'short_code' => strtoupper(substr($m[1], 0, 20)),
                ]);
            }
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('landlords') && $this->db->fieldExists('short_code', 'landlords')) {
            $this->forge->dropColumn('landlords', 'short_code');
        }
    }
}
