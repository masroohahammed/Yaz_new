<?php

namespace App\Services\Hr;

use App\Services\AlertDispatchService;
use CodeIgniter\Database\BaseConnection;

class HrDocumentService
{
    public const EXPIRY_BUCKETS = [
        'expired' => ['label' => 'Expired', 'min' => null, 'max' => 0],
        '7'       => ['label' => 'Expiring in 7 days', 'min' => 0, 'max' => 7],
        '15'      => ['label' => 'Expiring in 15 days', 'min' => 8, 'max' => 15],
        '30'      => ['label' => 'Expiring in 30 days', 'min' => 16, 'max' => 30],
        '60'      => ['label' => 'Expiring in 60 days', 'min' => 31, 'max' => 60],
        '90'      => ['label' => 'Expiring in 90 days', 'min' => 61, 'max' => 90],
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('documents')
            && $this->db->fieldExists('module', 'documents');
    }

    /** @return list<array<string, mixed>> */
    public function categories(?int $companyId = null): array
    {
        if (! $this->db->tableExists('hr_document_categories')) {
            return [];
        }
        $q = $this->db->table('hr_document_categories')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');
        if ($companyId !== null) {
            $q->groupStart()
                ->where('company_id', $companyId)
                ->orWhere('company_id', null)
                ->groupEnd();
        }

        return $q->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function forEmployee(int $employeeId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('documents d')
            ->select('d.*, u.name AS uploaded_by_name, c.name AS category_name')
            ->join('users u', 'u.id = d.uploaded_by', 'left')
            ->join('hr_document_categories c', 'c.id = d.category_id', 'left')
            ->where('d.module', 'employee')
            ->where('d.ref_id', $employeeId)
            ->orderBy('d.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, list<array<string, mixed>>>
     */
    public function expiryDashboard(array $filters = []): array
    {
        $buckets = [];
        foreach (self::EXPIRY_BUCKETS as $key => $meta) {
            $buckets[$key] = $this->expiryQuery($key, $filters);
        }

        return $buckets;
    }

    /** @return array{expired: int, expiring_30: int, expiring_90: int, total: int} */
    public function expiryCounts(array $filters = []): array
    {
        $today = date('Y-m-d');
        $base  = $this->baseEmployeeDocumentQuery($filters);
        $clone = clone $base;

        $expired = (clone $base)
            ->where('d.expiry_date IS NOT NULL')
            ->where('d.expiry_date <', $today)
            ->countAllResults();

        $exp30 = (clone $clone)
            ->where('d.expiry_date IS NOT NULL')
            ->where('d.expiry_date >=', $today)
            ->where('d.expiry_date <=', date('Y-m-d', strtotime('+30 days')))
            ->countAllResults();

        $exp90 = (clone $clone)
            ->where('d.expiry_date IS NOT NULL')
            ->where('d.expiry_date >=', $today)
            ->where('d.expiry_date <=', date('Y-m-d', strtotime('+90 days')))
            ->countAllResults();

        $total = (clone $clone)->countAllResults();

        return [
            'expired'      => $expired,
            'expiring_30'  => $exp30,
            'expiring_90'  => $exp90,
            'total'        => $total,
        ];
    }

    public function syncDocumentStatus(int $documentId): void
    {
        if (! $this->tablesReady() || ! $this->db->fieldExists('status', 'documents')) {
            return;
        }
        $doc = $this->db->table('documents')->where('id', $documentId)->get()->getRowArray();
        if (! $doc || empty($doc['expiry_date'])) {
            return;
        }
        $status = $this->expiryStatus((string) $doc['expiry_date']);
        $this->db->table('documents')->where('id', $documentId)->update([
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function syncAllEmployeeDocumentStatuses(): int
    {
        if (! $this->tablesReady() || ! $this->db->fieldExists('status', 'documents')) {
            return 0;
        }
        $docs = $this->db->table('documents')
            ->select('id, expiry_date')
            ->where('module', 'employee')
            ->where('expiry_date IS NOT NULL')
            ->get()->getResultArray();
        $count = 0;
        foreach ($docs as $doc) {
            $status = $this->expiryStatus((string) $doc['expiry_date']);
            $this->db->table('documents')->where('id', $doc['id'])->update([
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Process expiry alerts (idempotent per document + alert window per day).
     *
     * @return array{synced: int, alerts_sent: int}
     */
    public function processExpiryAlerts(AlertDispatchService $alerts, array $settings = []): array
    {
        if (! $this->tablesReady()) {
            return ['synced' => 0, 'alerts_sent' => 0];
        }

        $synced = $this->syncAllEmployeeDocumentStatuses();
        $sent   = 0;
        $today  = date('Y-m-d');

        foreach (['expired', '7', '15', '30'] as $bucket) {
            $docs = $this->expiryQuery($bucket, ['active_only' => true]);
            foreach ($docs as $doc) {
                if ($this->alertAlreadySentToday((int) $doc['id'], $bucket)) {
                    continue;
                }
                $employeeId = (int) ($doc['ref_id'] ?? 0);
                $emp        = $employeeId > 0
                    ? $this->db->table('employees e')
                        ->select('e.*, u.name, u.id AS user_id')
                        ->join('users u', 'u.id = e.user_id', 'left')
                        ->where('e.id', $employeeId)
                        ->get()->getRowArray()
                    : null;

                $title   = 'Document expiry: ' . ($doc['title'] ?? 'HR document');
                $message = sprintf(
                    '%s — %s expires on %s (%s).',
                    $emp['name'] ?? ('Employee #' . $employeeId),
                    $doc['title'] ?? 'Document',
                    $doc['expiry_date'] ?? '—',
                    self::EXPIRY_BUCKETS[$bucket]['label'] ?? $bucket
                );

                // Notify employee
                if (! empty($emp['user_id'])) {
                    $alerts->notifyUser((int) $emp['user_id'], $title, $message, 'document_expiry', (int) $doc['id']);
                    $this->logAlert((int) $doc['id'], $employeeId, $bucket, (int) $emp['user_id'], $doc['expiry_date'] ?? null);
                    $sent++;
                }

                // Notify HR managers
                $managers = $this->db->table('users u')
                    ->select('u.id')
                    ->join('roles r', 'r.id = u.role_id')
                    ->whereIn('r.name', ['super_admin', 'facility_manager'])
                    ->where('u.status', 'active')
                    ->get()->getResultArray();
                foreach ($managers as $mgr) {
                    $alerts->notifyUser((int) $mgr['id'], $title, $message, 'document_expiry', (int) $doc['id']);
                }

                // Reporting manager
                if (! empty($emp['reporting_manager_id'])) {
                    $rm = $this->db->table('employees')->select('user_id')->where('id', (int) $emp['reporting_manager_id'])->get()->getRowArray();
                    if (! empty($rm['user_id'])) {
                        $alerts->notifyUser((int) $rm['user_id'], $title, $message, 'document_expiry', (int) $doc['id']);
                    }
                }
            }
        }

        return ['synced' => $synced, 'alerts_sent' => $sent];
    }

    public function expiryStatus(?string $expiryDate): string
    {
        if ($expiryDate === null || $expiryDate === '') {
            return 'valid';
        }
        $days = (int) floor((strtotime($expiryDate) - strtotime(date('Y-m-d'))) / 86400);
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= 30) {
            return 'expiring';
        }

        return 'valid';
    }

    public function expiryBadgeClass(?string $expiryDate): string
    {
        $status = $this->expiryStatus($expiryDate);

        return match ($status) {
            'expired'  => 'bg-danger-subtle text-danger',
            'expiring' => 'bg-warning-subtle text-warning',
            default    => 'bg-success-subtle text-success',
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function bucketRows(string $bucket, array $filters = []): array
    {
        return $this->expiryQuery($bucket, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function expiryQuery(string $bucket, array $filters = []): array
    {
        if (! isset(self::EXPIRY_BUCKETS[$bucket])) {
            return [];
        }
        $meta  = self::EXPIRY_BUCKETS[$bucket];
        $today = date('Y-m-d');
        $q     = $this->baseEmployeeDocumentQuery($filters)
            ->where('d.expiry_date IS NOT NULL');

        if ($bucket === 'expired') {
            $q->where('d.expiry_date <', $today);
        } else {
            $minDays = (int) $meta['min'];
            $maxDays = (int) $meta['max'];
            $q->where('d.expiry_date >=', $today)
                ->where('d.expiry_date <=', date('Y-m-d', strtotime("+{$maxDays} days")));
            if ($minDays > 0) {
                $q->where('d.expiry_date >=', date('Y-m-d', strtotime("+{$minDays} days")));
            }
        }

        return $q->orderBy('d.expiry_date', 'ASC')->limit(200)->get()->getResultArray();
    }

    /** @param array<string, mixed> $filters */
    private function baseEmployeeDocumentQuery(array $filters): \CodeIgniter\Database\BaseBuilder
    {
        $q = $this->db->table('documents d')
            ->select('d.*, e.emp_code, u.name AS employee_name, e.id AS employee_id, f.name AS facility_name, c.name AS category_name')
            ->join('employees e', 'e.id = d.ref_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('facilities f', 'f.id = e.facility_id', 'left')
            ->join('hr_document_categories c', 'c.id = d.category_id', 'left')
            ->where('d.module', 'employee');

        if (! empty($filters['company_id'])) {
            $q->where('e.company_id', (int) $filters['company_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('e.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['department_id'])) {
            $q->where('e.department_id', (int) $filters['department_id']);
        }
        if (! empty($filters['active_only'])) {
            $q->where('e.status !=', 'inactive');
        }
        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $q->groupStart()
                ->like('d.title', $term)
                ->orLike('e.emp_code', $term)
                ->orLike('u.name', $term)
                ->groupEnd();
        }

        return $q;
    }

    private function alertAlreadySentToday(int $documentId, string $alertType): bool
    {
        if (! $this->db->tableExists('hr_document_expiry_alerts')) {
            return false;
        }

        return $this->db->table('hr_document_expiry_alerts')
            ->where('document_id', $documentId)
            ->where('alert_type', $alertType)
            ->where('DATE(notified_at)', date('Y-m-d'))
            ->countAllResults() > 0;
    }

    private function logAlert(int $documentId, int $employeeId, string $alertType, int $userId, ?string $expiryDate): void
    {
        if (! $this->db->tableExists('hr_document_expiry_alerts')) {
            return;
        }
        $this->db->table('hr_document_expiry_alerts')->insert([
            'document_id'      => $documentId,
            'employee_id'      => $employeeId,
            'alert_type'       => $alertType,
            'expiry_date'      => $expiryDate,
            'notified_user_id' => $userId,
            'notified_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
