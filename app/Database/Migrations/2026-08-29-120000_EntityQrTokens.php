<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * QR tokens for properties (facilities) and units, plus unified scan logs.
 */
class EntityQrTokens extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('facilities') && ! $this->db->fieldExists('qr_token', 'facilities')) {
            $this->forge->addColumn('facilities', [
                'qr_token'        => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'finance_notes'],
                'qr_generated_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'qr_token'],
            ]);
        }

        if ($this->db->tableExists('units') && ! $this->db->fieldExists('qr_token', 'units')) {
            $this->forge->addColumn('units', [
                'qr_token'        => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'notes'],
                'qr_generated_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'qr_token'],
            ]);
        }

        if ($this->db->tableExists('facilities') && $this->db->fieldExists('qr_token', 'facilities')) {
            $idx = $this->db->query("SHOW INDEX FROM facilities WHERE Key_name = 'uq_facilities_qr_token'")->getResultArray();
            if ($idx === []) {
                $this->db->query('ALTER TABLE facilities ADD UNIQUE KEY uq_facilities_qr_token (qr_token)');
            }
        }

        if ($this->db->tableExists('units') && $this->db->fieldExists('qr_token', 'units')) {
            $idx = $this->db->query("SHOW INDEX FROM units WHERE Key_name = 'uq_units_qr_token'")->getResultArray();
            if ($idx === []) {
                $this->db->query('ALTER TABLE units ADD UNIQUE KEY uq_units_qr_token (qr_token)');
            }
        }

        if (! $this->db->tableExists('qr_scan_logs')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'entity_type'  => ['type' => 'VARCHAR', 'constraint' => 20],
                'entity_id'    => ['type' => 'INT', 'unsigned' => true],
                'scanned_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'scan_source'  => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'qr'],
                'action_taken' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'view'],
                'ip_address'   => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
                'user_agent'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at'   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['entity_type', 'entity_id']);
            $this->forge->addKey('created_at');
            $this->forge->createTable('qr_scan_logs', true);
        }

        $this->backfillTokens('facilities');
        $this->backfillTokens('units');
    }

    public function down()
    {
        if ($this->db->tableExists('qr_scan_logs')) {
            $this->forge->dropTable('qr_scan_logs', true);
        }
        foreach (['facilities', 'units'] as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('qr_token', $table)) {
                $this->forge->dropColumn($table, ['qr_token', 'qr_generated_at']);
            }
        }
    }

    private function backfillTokens(string $table): void
    {
        if (! $this->db->tableExists($table) || ! $this->db->fieldExists('qr_token', $table)) {
            return;
        }

        $rows = $this->db->table($table)
            ->select('id')
            ->groupStart()
            ->where('qr_token', null)
            ->orWhere('qr_token', '')
            ->groupEnd()
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $this->db->table($table)->where('id', (int) $row['id'])->update([
                'qr_token'        => bin2hex(random_bytes(16)),
                'qr_generated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
