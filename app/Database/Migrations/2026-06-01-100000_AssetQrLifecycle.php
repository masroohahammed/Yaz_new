<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Asset QR/barcode, scan logs, documents, complaint linkage.
 */
class AssetQrLifecycle extends Migration
{
    public function up()
    {
        $this->migrateAssets();
        $this->createScanLogs();
        $this->createAssetDocuments();
        $this->migrateMaintenanceRequests();
        $this->backfillQrTokens();
    }

    private function migrateAssets(): void
    {
        if (! $this->db->tableExists('assets')) {
            return;
        }

        $cols = [
            'tag_number'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'asset_code'],
            'asset_type'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'category'],
            'manufacturer'       => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'brand'],
            'warranty_start'     => ['type' => 'DATE', 'null' => true, 'after' => 'purchase_date'],
            'department'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'location_in_facility'],
            'cost_center'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'department'],
            'assigned_to'        => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'cost_center'],
            'criticality'        => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high', 'critical'], 'default' => 'medium', 'after' => 'status'],
            'qr_token'           => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'notes'],
            'barcode_value'      => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'qr_token'],
            'qr_generated_at'    => ['type' => 'DATETIME', 'null' => true, 'after' => 'barcode_value'],
            'floor_room'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'location_in_facility'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'assets')) {
                $this->forge->addColumn('assets', [$name => $def]);
            }
        }

        if ($this->db->fieldExists('qr_token', 'assets')) {
            $idx = $this->db->query("SHOW INDEX FROM assets WHERE Key_name = 'uq_assets_qr_token'")->getResultArray();
            if ($idx === []) {
                $this->db->query('ALTER TABLE assets ADD UNIQUE KEY uq_assets_qr_token (qr_token)');
            }
        }

        // Extend status enum for faulty assets (best-effort).
        try {
            $this->db->query("ALTER TABLE assets MODIFY COLUMN status ENUM('active','under_maintenance','faulty','retired','disposed') NOT NULL DEFAULT 'active'");
        } catch (\Throwable $e) {
            // ignore if already applied or unsupported
        }
    }

    private function createScanLogs(): void
    {
        if ($this->db->tableExists('asset_scan_logs')) {
            return;
        }

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asset_id'    => ['type' => 'INT', 'unsigned' => true],
            'scanned_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'scan_source' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'qr'],
            'action_taken'=> ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'gps_lat'     => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'gps_lng'     => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('asset_scan_logs', true);
    }

    private function createAssetDocuments(): void
    {
        if ($this->db->tableExists('asset_documents')) {
            return;
        }

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asset_id'    => ['type' => 'INT', 'unsigned' => true],
            'file_name'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_path'   => ['type' => 'VARCHAR', 'constraint' => 500],
            'doc_type'    => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'general'],
            'uploaded_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->createTable('asset_documents', true);
    }

    private function migrateMaintenanceRequests(): void
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return;
        }
        if (! $this->db->fieldExists('asset_id', 'maintenance_requests')) {
            $this->forge->addColumn('maintenance_requests', [
                'asset_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'unit_id'],
            ]);
        }
        if (! $this->db->fieldExists('scan_source', 'maintenance_requests')) {
            $this->forge->addColumn('maintenance_requests', [
                'scan_source' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'asset_id'],
            ]);
        }
    }

    private function backfillQrTokens(): void
    {
        if (! $this->db->fieldExists('qr_token', 'assets')) {
            return;
        }

        $rows = $this->db->table('assets')
            ->select('id, asset_code, barcode_value, qr_token')
            ->groupStart()
            ->where('qr_token', null)
            ->orWhere('qr_token', '')
            ->groupEnd()
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $token = bin2hex(random_bytes(16));
            $update = [
                'qr_token'        => $token,
                'qr_generated_at' => date('Y-m-d H:i:s'),
            ];
            if (empty($row['barcode_value'])) {
                $update['barcode_value'] = $row['asset_code'];
            }
            $this->db->table('assets')->where('id', $row['id'])->update($update);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('asset_scan_logs')) {
            $this->forge->dropTable('asset_scan_logs', true);
        }
        if ($this->db->tableExists('asset_documents')) {
            $this->forge->dropTable('asset_documents', true);
        }
    }
}
