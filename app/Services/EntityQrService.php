<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * QR tokens and scan URLs for facilities (properties), units, and assets.
 */
class EntityQrService
{
    /** @var array<string, array{table: string, scanPath: string, idPath: string}> */
    private const ENTITIES = [
        'property' => ['table' => 'facilities', 'scanPath' => 'scan/property', 'idPath' => 'scan/property/id'],
        'unit'     => ['table' => 'units', 'scanPath' => 'scan/unit', 'idPath' => 'scan/unit/id'],
        'asset'    => ['table' => 'assets', 'scanPath' => 'scan/asset', 'idPath' => 'scan/asset/id'],
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function qrImageUrl(string $payload, int $size = 220): string
    {
        $base = config('ErpDefaults')->qrServiceUrl ?? 'https://api.qrserver.com/v1/create-qr-code/';

        return rtrim((string) $base, '/') . '/?size=' . $size . 'x' . $size
            . '&data=' . rawurlencode($payload);
    }

    /**
     * @param array<string, mixed> $entity
     */
    public function scanUrl(string $type, array $entity): string
    {
        $cfg = self::ENTITIES[$type] ?? null;
        if ($cfg === null) {
            return base_url('/');
        }

        $token = (string) ($entity['qr_token'] ?? '');
        if ($token !== '') {
            return base_url($cfg['scanPath'] . '/' . $token);
        }

        return base_url($cfg['idPath'] . '/' . (int) ($entity['id'] ?? 0));
    }

    public function ensureToken(string $type, int $entityId): ?string
    {
        $cfg = self::ENTITIES[$type] ?? null;
        if ($cfg === null || $entityId <= 0 || ! $this->db->tableExists($cfg['table'])) {
            return null;
        }
        if (! $this->db->fieldExists('qr_token', $cfg['table'])) {
            return null;
        }

        $row = $this->db->table($cfg['table'])->select('qr_token')->where('id', $entityId)->get()->getRowArray();
        if (! empty($row['qr_token'])) {
            return (string) $row['qr_token'];
        }

        $token = $this->generateToken();
        $update = ['qr_token' => $token];
        if ($this->db->fieldExists('qr_generated_at', $cfg['table'])) {
            $update['qr_generated_at'] = date('Y-m-d H:i:s');
        }
        $this->db->table($cfg['table'])->where('id', $entityId)->update($update);

        return $token;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findPropertyByToken(string $token): ?array
    {
        return $this->findByToken('property', $token);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findUnitByToken(string $token): ?array
    {
        return $this->findByToken('unit', $token);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findByToken(string $type, string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $cfg = self::ENTITIES[$type] ?? null;
        if ($cfg === null || ! $this->db->fieldExists('qr_token', $cfg['table'])) {
            return null;
        }

        if ($type === 'property') {
            return $this->db->table('facilities f')
                ->select('f.*, u.name AS manager_name')
                ->join('users u', 'u.id = f.manager_id', 'left')
                ->where('f.qr_token', $token)
                ->where('f.deleted_at', null)
                ->get()->getRowArray() ?: null;
        }

        if ($type === 'unit') {
            return $this->db->table('units u')
                ->select('u.*, f.name AS facility_name, f.id AS facility_id')
                ->join('facilities f', 'f.id = u.facility_id', 'left')
                ->where('u.qr_token', $token)
                ->where('u.deleted_at', null)
                ->get()->getRowArray() ?: null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findPropertyById(int $id): ?array
    {
        return $this->db->table('facilities f')
            ->select('f.*, u.name AS manager_name')
            ->join('users u', 'u.id = f.manager_id', 'left')
            ->where('f.id', $id)
            ->where('f.deleted_at', null)
            ->get()->getRowArray() ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findUnitById(int $id): ?array
    {
        return $this->db->table('units u')
            ->select('u.*, f.name AS facility_name, f.id AS facility_id')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.id', $id)
            ->where('u.deleted_at', null)
            ->get()->getRowArray() ?: null;
    }

    /** @return list<string> */
    public static function scannerRoles(): array
    {
        return [
            'super_admin',
            'facility_manager',
            'property_manager',
            'supervisor',
            'maintenance_supervisor',
            'technician',
            'qa_inspector',
            'caretaker',
            'maintenance',
            'maintenance_staff',
        ];
    }

    public static function roleCanScan(string $role): bool
    {
        return in_array(strtolower(trim($role)), self::scannerRoles(), true);
    }
}
