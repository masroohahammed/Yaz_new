<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class QrScanLogService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function log(
        string $entityType,
        int $entityId,
        ?int $scannedBy,
        string $source,
        string $action,
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        if (! $this->db->tableExists('qr_scan_logs')) {
            return;
        }

        $this->db->table('qr_scan_logs')->insert([
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'scanned_by'   => $scannedBy,
            'scan_source'  => $source,
            'action_taken' => $action,
            'ip_address'   => $ip,
            'user_agent'   => $userAgent ? substr($userAgent, 0, 255) : null,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
