<?php

namespace App\Services;

use App\Database\AutoIncrementRepair;
use CodeIgniter\Database\BaseConnection;

class AssetScanLogService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function log(
        int $assetId,
        ?int $userId,
        string $source = 'qr',
        ?string $action = 'view',
        ?string $ip = null,
        ?string $userAgent = null,
        ?float $lat = null,
        ?float $lng = null
    ): void {
        if (! $this->db->tableExists('asset_scan_logs') || $assetId <= 0) {
            return;
        }

        try {
            AutoIncrementRepair::ensure($this->db, 'asset_scan_logs');
            $this->db->table('asset_scan_logs')->insert([
                'asset_id'     => $assetId,
                'scanned_by'   => $userId,
                'scan_source'  => $source,
                'action_taken' => $action,
                'ip_address'   => $ip,
                'user_agent'   => $userAgent ? substr($userAgent, 0, 255) : null,
                'gps_lat'      => $lat,
                'gps_lng'      => $lng,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'AssetScanLogService::log: ' . $e->getMessage());
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forAsset(int $assetId, int $limit = 50): array
    {
        if (! $this->db->tableExists('asset_scan_logs')) {
            return [];
        }

        return $this->db->table('asset_scan_logs s')
            ->select('s.*, u.name AS scanned_by_name')
            ->join('users u', 'u.id = s.scanned_by', 'left')
            ->where('s.asset_id', $assetId)
            ->orderBy('s.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}
