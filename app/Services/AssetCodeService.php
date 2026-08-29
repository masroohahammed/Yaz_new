<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * QR token, barcode value, and scan URL generation for assets.
 */
class AssetCodeService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function generateTagNumber(string $assetCode): string
    {
        return 'TAG-' . preg_replace('/[^A-Z0-9]/', '', strtoupper($assetCode));
    }

    /**
     * @param array<string, mixed> $asset
     */
    public function scanUrl(array $asset): string
    {
        $token = $asset['qr_token'] ?? '';
        if ($token !== '') {
            return base_url('scan/asset/' . $token);
        }

        return base_url('scan/asset/id/' . (int) $asset['id']);
    }

    public function qrImageUrl(string $payload, int $size = 220): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&data=' . rawurlencode($payload);
    }

    /**
     * Ensure QR token and barcode exist; persist when $assetId > 0.
     *
     * @param array<string, mixed> $data  Insert/update payload (by reference)
     */
    public function ensureCodes(array &$data, int $assetId = 0): void
    {
        if (empty($data['barcode_value']) && ! empty($data['asset_code'])) {
            $data['barcode_value'] = $data['asset_code'];
        }
        if (empty($data['tag_number']) && ! empty($data['asset_code'])) {
            $data['tag_number'] = $this->generateTagNumber((string) $data['asset_code']);
        }

        $needsToken = $this->db->fieldExists('qr_token', 'assets')
            && empty($data['qr_token']);

        if ($needsToken) {
            $data['qr_token']        = $this->generateToken();
            $data['qr_generated_at'] = date('Y-m-d H:i:s');
        }

        if ($assetId > 0) {
            $update = [];
            foreach (['qr_token', 'barcode_value', 'tag_number', 'qr_generated_at'] as $field) {
                if (! empty($data[$field]) && $this->db->fieldExists($field, 'assets')) {
                    $update[$field] = $data[$field];
                }
            }
            if ($update !== []) {
                $this->db->table('assets')->where('id', $assetId)->update($update);
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByToken(string $token): ?array
    {
        if ($token === '' || ! $this->db->fieldExists('qr_token', 'assets')) {
            return null;
        }

        return $this->db->table('assets a')
            ->select('a.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->where('a.qr_token', $token)
            ->where('a.deleted_at', null)
            ->get()->getRowArray() ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(): array
    {
        if (! $this->db->tableExists('assets')) {
            return [];
        }

        $total = (int) $this->db->table('assets')->where('deleted_at', null)->countAllResults();
        $active = (int) $this->db->table('assets')->where('status', 'active')->where('deleted_at', null)->countAllResults();
        $faulty = 0;
        if ($this->db->fieldExists('status', 'assets')) {
            $faulty = (int) $this->db->table('assets')->where('status', 'faulty')->where('deleted_at', null)->countAllResults();
        }

        $withQr = $total;
        if ($this->db->fieldExists('qr_token', 'assets')) {
            $withQr = (int) $this->db->table('assets')
                ->where('deleted_at', null)
                ->groupStart()
                ->where('qr_token IS NOT NULL', null, false)
                ->where('qr_token !=', '')
                ->groupEnd()
                ->countAllResults();
        }

        $warrantySoon = 0;
        if ($this->db->fieldExists('warranty_expiry', 'assets')) {
            $warrantySoon = (int) $this->db->table('assets')
                ->where('deleted_at', null)
                ->where('warranty_expiry <=', date('Y-m-d', strtotime('+60 days')))
                ->where('warranty_expiry >=', date('Y-m-d'))
                ->countAllResults();
        }

        $openWoAssets = 0;
        if ($this->db->tableExists('work_orders') && $this->db->fieldExists('asset_id', 'work_orders')) {
            $openWoAssets = (int) $this->db->table('work_orders')
                ->select('asset_id')
                ->where('asset_id >', 0)
                ->where('deleted_at', null)
                ->whereIn('status', ['new', 'assigned', 'in_progress', 'on_hold'])
                ->groupBy('asset_id')
                ->countAllResults();
        }

        $scansToday = 0;
        $topScanned = [];
        if ($this->db->tableExists('asset_scan_logs')) {
            $scansToday = (int) $this->db->table('asset_scan_logs')
                ->where('created_at >=', date('Y-m-d 00:00:00'))
                ->countAllResults();

            $topScanned = $this->db->table('asset_scan_logs s')
                ->select('a.asset_code, a.name, COUNT(*) AS scan_count')
                ->join('assets a', 'a.id = s.asset_id', 'left')
                ->where('s.created_at >=', date('Y-m-d', strtotime('-30 days')))
                ->groupBy('s.asset_id')
                ->orderBy('scan_count', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
        }

        return [
            'total_assets'        => $total,
            'active_assets'       => $active,
            'faulty_assets'       => $faulty,
            'assets_with_qr'      => $withQr,
            'assets_without_qr'   => max(0, $total - $withQr),
            'warranty_expiring'   => $warrantySoon,
            'assets_open_wo'      => $openWoAssets,
            'scans_today'         => $scansToday,
            'top_scanned_assets'  => $topScanned,
        ];
    }
}
