<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Central document management — polymorphic links, filtering, expiry status, multi-file uploads.
 */
class DocumentManagementService
{
    public const TABLE = 'documents';

    private ?BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db;
    }

    private function connection(): BaseConnection
    {
        return $this->db ?? \Config\Database::connect();
    }

    /** @return list<string> */
    public function moduleAliases(string $module): array
    {
        $module = strtolower(trim($module));

        return match ($module) {
            'facility', 'property', 'properties', 'facilities' => ['facility', 'property', 'facilities', 'properties'],
            'unit', 'units' => ['unit', 'units'],
            'tenant', 'tenants' => ['tenant', 'tenants'],
            'employee', 'employees', 'hr' => ['employee', 'employees', 'hr'],
            'lease', 'leases', 'contract', 'contracts' => ['lease', 'leases', 'contract', 'contracts'],
            'inspection', 'inspections', 'unit_checklist', 'unit_checklists' => ['inspection', 'inspections', 'unit_checklist', 'unit_checklists'],
            default => [$module],
        };
    }

    /** @return list<array<string, mixed>> */
    public function listForEntity(string $module, int $refId, array $filters = []): array
    {
        $db = $this->connection();
        if (! $db->tableExists(self::TABLE) || $refId <= 0) {
            return [];
        }

        $aliases = $this->moduleAliases($module);
        $q = $db->table(self::TABLE . ' d')
            ->select('d.*, u.name AS uploaded_by_name')
            ->join('users u', 'u.id = d.uploaded_by', 'left')
            ->whereIn('d.module', $aliases)
            ->where('d.ref_id', $refId);

        $this->applyFilters($q, $filters);

        return $q->orderBy('d.created_at', 'DESC')->limit(500)->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function listGeneral(array $filters = [], ?callable $scope = null): array
    {
        $db = $this->connection();
        if (! $db->tableExists(self::TABLE)) {
            return [];
        }

        $q = $db->table(self::TABLE . ' d')
            ->select('d.*, u.name AS uploaded_by_name')
            ->join('users u', 'u.id = d.uploaded_by', 'left');

        if (empty($filters['module'])) {
            $q->groupStart()
                ->where('d.module', null)
                ->orWhere('d.module', '')
                ->orWhere('d.module', 'general')
                ->groupEnd();
        } else {
            $aliases = $this->moduleAliases((string) $filters['module']);
            $q->whereIn('d.module', $aliases);
            if (! empty($filters['ref_id'])) {
                $q->where('d.ref_id', (int) $filters['ref_id']);
            }
        }

        if ($scope) {
            $scope($q);
        }

        $this->applyFilters($q, $filters);

        return $q->orderBy('d.created_at', 'DESC')->limit(500)->get()->getResultArray();
    }

  /**
   * @param object $q CI4 query builder on documents alias `d`
   * @param array<string, mixed> $filters
   */
    public function applyFilters(object $q, array $filters): void
    {
        if (! empty($filters['doc_type'])) {
            $q->where('d.doc_type', $filters['doc_type']);
        }
        if (! empty($filters['month'])) {
            $q->where('d.created_at >=', $filters['month'] . '-01 00:00:00');
            $q->where('d.created_at <', date('Y-m-d H:i:s', strtotime($filters['month'] . '-01 +1 month')));
        }
        if (! empty($filters['date_from'])) {
            $q->where('d.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $q->where('d.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        if (! empty($filters['expiry_status'])) {
            $status = $filters['expiry_status'];
            if ($status === 'expired') {
                $q->where('d.expiry_date <', date('Y-m-d'));
            } elseif ($status === 'expiring') {
                $q->where('d.expiry_date >=', date('Y-m-d'));
                $q->where('d.expiry_date <=', date('Y-m-d', strtotime('+30 days')));
            } elseif ($status === 'valid') {
                $q->groupStart()
                    ->where('d.expiry_date', null)
                    ->orWhere('d.expiry_date >=', date('Y-m-d', strtotime('+31 days')))
                    ->groupEnd();
            }
        }
        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $q->groupStart()
                ->like('d.title', $s)
                ->orLike('d.description', $s)
                ->orLike('d.doc_number', $s)
                ->groupEnd();
        }
    }

    public function computeExpiryStatus(?string $expiryDate): string
    {
        if ($expiryDate === null || $expiryDate === '') {
            return 'valid';
        }
        $exp = strtotime($expiryDate);
        $today = strtotime(date('Y-m-d'));
        if ($exp < $today) {
            return 'expired';
        }
        if ($exp <= strtotime('+30 days')) {
            return 'expiring';
        }

        return 'valid';
    }

    /**
     * @param array<string, mixed> $meta
     * @param list<\CodeIgniter\HTTP\Files\UploadedFile> $files
     * @return list<int> inserted document ids
     */
    public function storeBatch(array $meta, array $files, int $uploadedBy): array
    {
        $db = $this->connection();
        $ids      = [];
        $batchId  = bin2hex(random_bytes(8));
        $dir      = WRITEPATH . 'uploads/documents';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $baseTitle = trim((string) ($meta['title'] ?? ''));
        $expiry    = $meta['expiry_date'] ?? null;
        $status    = $this->computeExpiryStatus($expiry ? (string) $expiry : null);

        foreach ($files as $i => $file) {
            if (! $file || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }
            $newName  = $file->getRandomName();
            $file->move($dir, $newName);
            $title    = $baseTitle !== '' ? $baseTitle : $file->getClientName();
            if ($i > 0 && $baseTitle !== '') {
                $title = $baseTitle . ' (' . ($i + 1) . ')';
            }

            $row = [
                'module'          => $meta['module'] ?? null,
                'ref_id'          => ! empty($meta['ref_id']) ? (int) $meta['ref_id'] : null,
                'title'           => esc($title),
                'doc_type'        => esc($meta['doc_type'] ?? 'general'),
                'description'     => esc($meta['description'] ?? '') ?: null,
                'file_path'       => 'documents/' . $newName,
                'doc_number'      => esc($meta['doc_number'] ?? '') ?: null,
                'issued_by'       => esc($meta['issued_by'] ?? '') ?: null,
                'doc_date'        => $meta['doc_date'] ?? null,
                'issue_date'      => $meta['issue_date'] ?? null,
                'expiry_date'     => $expiry,
                'status'          => $status,
                'is_confidential' => ! empty($meta['is_confidential']) ? 1 : 0,
                'uploaded_by'     => $uploadedBy > 0 ? $uploadedBy : null,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];

            if ($db->fieldExists('facility_id', self::TABLE) && ! empty($meta['facility_id'])) {
                $row['facility_id'] = (int) $meta['facility_id'];
            }
            if ($db->fieldExists('unit_id', self::TABLE) && ! empty($meta['unit_id'])) {
                $row['unit_id'] = (int) $meta['unit_id'];
            }
            if ($db->fieldExists('tenant_id', self::TABLE) && ! empty($meta['tenant_id'])) {
                $row['tenant_id'] = (int) $meta['tenant_id'];
            }
            if ($db->fieldExists('contract_id', self::TABLE) && ! empty($meta['contract_id'])) {
                $row['contract_id'] = (int) $meta['contract_id'];
            }
            if ($db->fieldExists('inspection_id', self::TABLE) && ! empty($meta['inspection_id'])) {
                $row['inspection_id'] = (int) $meta['inspection_id'];
            }
            if ($db->fieldExists('batch_id', self::TABLE)) {
                $row['batch_id'] = $batchId;
            }

            $db->table(self::TABLE)->insert($row);
            $ids[] = (int) $db->insertID();
        }

        return $ids;
    }

    /** Link inspection report into DMS and relate property + unit. */
    public function linkInspection(int $inspectionId, int $unitId, int $facilityId, string $filePath, string $title, int $uploadedBy): int
    {
        return $this->createFromPath([
            'module'        => 'inspections',
            'ref_id'        => $inspectionId,
            'title'         => $title,
            'doc_type'      => 'inspection_report',
            'facility_id'   => $facilityId,
            'unit_id'       => $unitId,
            'inspection_id' => $inspectionId,
            'issue_date'    => date('Y-m-d'),
        ], $filePath, $uploadedBy);
    }

    public function createFromPath(array $meta, string $relativePath, int $uploadedBy): int
    {
        $db = $this->connection();
        $expiry = $meta['expiry_date'] ?? null;
        $row = [
            'module'          => $meta['module'] ?? null,
            'ref_id'          => ! empty($meta['ref_id']) ? (int) $meta['ref_id'] : null,
            'title'           => esc($meta['title'] ?? 'Document'),
            'doc_type'        => esc($meta['doc_type'] ?? 'general'),
            'file_path'       => $relativePath,
            'issue_date'      => $meta['issue_date'] ?? null,
            'expiry_date'     => $expiry,
            'status'          => $this->computeExpiryStatus($expiry ? (string) $expiry : null),
            'is_confidential' => ! empty($meta['is_confidential']) ? 1 : 0,
            'uploaded_by'     => $uploadedBy > 0 ? $uploadedBy : null,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        if ($db->fieldExists('facility_id', self::TABLE) && ! empty($meta['facility_id'])) {
            $row['facility_id'] = (int) $meta['facility_id'];
        }
        if ($db->fieldExists('unit_id', self::TABLE) && ! empty($meta['unit_id'])) {
            $row['unit_id'] = (int) $meta['unit_id'];
        }
        if ($db->fieldExists('inspection_id', self::TABLE) && ! empty($meta['inspection_id'])) {
            $row['inspection_id'] = (int) $meta['inspection_id'];
        }

        $db->table(self::TABLE)->insert($row);

        return (int) $db->insertID();
    }

    /** Register metadata-only document (e.g. inspection report link). */
    public function registerRecord(array $meta, int $uploadedBy): int
    {
        $db = $this->connection();
        $expiry = $meta['expiry_date'] ?? null;
        $row = [
            'module'          => $meta['module'] ?? null,
            'ref_id'          => ! empty($meta['ref_id']) ? (int) $meta['ref_id'] : null,
            'title'           => esc($meta['title'] ?? 'Document'),
            'doc_type'        => esc($meta['doc_type'] ?? 'general'),
            'description'     => esc($meta['description'] ?? '') ?: null,
            'file_path'       => $meta['file_path'] ?? null,
            'issue_date'      => $meta['issue_date'] ?? null,
            'expiry_date'     => $expiry,
            'status'          => $this->computeExpiryStatus($expiry ? (string) $expiry : null),
            'is_confidential' => ! empty($meta['is_confidential']) ? 1 : 0,
            'uploaded_by'     => $uploadedBy > 0 ? $uploadedBy : null,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        foreach (['facility_id', 'unit_id', 'tenant_id', 'contract_id', 'inspection_id'] as $col) {
            if ($db->fieldExists($col, self::TABLE) && ! empty($meta[$col])) {
                $row[$col] = (int) $meta[$col];
            }
        }
        $db->table(self::TABLE)->insert($row);

        return (int) $db->insertID();
    }
}
