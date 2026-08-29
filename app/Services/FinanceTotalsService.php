<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Single source of truth for invoice / payment totals.
 *
 * Buckets are mutually exclusive by invoice status:
 *   revenue     = collected (paid + paid portion of partial)
 *   outstanding = still due (sent / overdue / unpaid portion of partial)
 *   cancelled   = voided / cancelled invoices (never counted as revenue)
 *   overdue     = subset of outstanding that is past due
 *
 * This ERP has no kitchen / POS register. Work-order updates and invoice
 * payments are the operational equivalents of "order" and "sale" totals.
 */
class FinanceTotalsService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * Flip sent invoices past due_date to overdue at most once per 15 minutes.
     */
    public function syncOverdueInvoices(): void
    {
        if (cache()->get('fm_sync_overdue_invoices')) {
            return;
        }
        try {
            $this->db->query("
                UPDATE invoices
                SET status = 'overdue'
                WHERE status = 'sent'
                  AND due_date < CURDATE()
                  AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ");
            cache()->save('fm_sync_overdue_invoices', 1, 900);
        } catch (\Throwable $e) {
            log_message('error', 'FinanceTotalsService overdue sync: ' . $e->getMessage());
        }
    }

    /**
     * @param list<int>|null $facilityIds null = all companies
     * @return array{revenue: float, outstanding: float, cancelled: float, overdue: float, overdue_count: int}
     */
    public function invoiceTotals(?array $facilityIds = null, ?int $companyId = null): array
    {
        $empty = ['revenue' => 0.0, 'outstanding' => 0.0, 'cancelled' => 0.0, 'overdue' => 0.0, 'overdue_count' => 0];
        if ($facilityIds !== null && $facilityIds === []) {
            return $empty;
        }

        $hasPaidAmount    = $this->db->fieldExists('paid_amount', 'invoices');
        $hasPendingAmount = $this->db->fieldExists('pending_amount', 'invoices');
        $hasDeletedAt     = $this->db->fieldExists('deleted_at', 'invoices');
        $hasCompanyId     = $this->db->fieldExists('company_id', 'invoices');

        $paidExpr = $hasPaidAmount
            ? "COALESCE(paid_amount, 0)"
            : "CASE WHEN status = 'paid' THEN total ELSE 0 END";
        $dueExpr = $hasPendingAmount
            ? "COALESCE(NULLIF(pending_amount, 0), GREATEST(total - {$paidExpr}, 0), total)"
            : ($hasPaidAmount
                ? "GREATEST(total - COALESCE(paid_amount, 0), 0)"
                : 'total');

        $sql = "SELECT
            COALESCE(SUM(CASE
                WHEN status = 'paid' THEN total
                WHEN status = 'partial' THEN {$paidExpr}
                ELSE 0 END), 0) AS revenue,
            COALESCE(SUM(CASE
                WHEN status IN ('sent','overdue') THEN {$dueExpr}
                WHEN status = 'partial' THEN {$dueExpr}
                ELSE 0 END), 0) AS outstanding,
            COALESCE(SUM(CASE WHEN status IN ('cancelled','void','voided') THEN total ELSE 0 END), 0) AS cancelled,
            COALESCE(SUM(CASE WHEN status IN ('overdue','sent') AND due_date < CURDATE() THEN {$dueExpr} ELSE 0 END), 0) AS overdue,
            COALESCE(SUM(CASE WHEN status IN ('overdue','sent') AND due_date < CURDATE() THEN 1 ELSE 0 END), 0) AS overdue_count
            FROM invoices WHERE 1=1";
        $params = [];

        if ($hasDeletedAt) {
            $sql .= ' AND deleted_at IS NULL';
        }
        if ($facilityIds !== null && $companyId && $hasCompanyId) {
            $sql .= ' AND (facility_id IN (' . implode(',', array_fill(0, count($facilityIds), '?')) . ') OR (facility_id IS NULL AND company_id = ?))';
            $params = array_merge($params, $facilityIds, [$companyId]);
        } elseif ($facilityIds !== null) {
            $sql .= ' AND facility_id IN (' . implode(',', array_fill(0, count($facilityIds), '?')) . ')';
            $params = array_merge($params, $facilityIds);
        } elseif ($companyId && $hasCompanyId) {
            $sql .= ' AND company_id = ?';
            $params[] = $companyId;
        }

        $row = $this->db->query($sql, $params)->getRowArray() ?: [];

        return [
            'revenue'       => (float) ($row['revenue'] ?? 0),
            'outstanding'   => (float) ($row['outstanding'] ?? 0),
            'cancelled'     => (float) ($row['cancelled'] ?? 0),
            'overdue'       => (float) ($row['overdue'] ?? 0),
            'overdue_count' => (int) ($row['overdue_count'] ?? 0),
        ];
    }

    /**
     * @param list<int>|null $facilityIds
     * @return array{paid: float, cancelled: float}
     */
    public function paymentTotals(?array $facilityIds = null, ?int $companyId = null): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return ['paid' => 0.0, 'cancelled' => 0.0];
        }
        if ($facilityIds !== null && $facilityIds === []) {
            return ['paid' => 0.0, 'cancelled' => 0.0];
        }

        $q = $this->db->table('lease_payments lp')->select("
            COALESCE(SUM(CASE WHEN lp.status = 'paid' THEN lp.amount ELSE 0 END), 0) AS paid,
            COALESCE(SUM(CASE WHEN lp.status IN ('cancelled','void','voided') THEN lp.amount ELSE 0 END), 0) AS cancelled
        ", false);

        if ($facilityIds !== null && $this->db->fieldExists('facility_id', 'lease_payments')) {
            $q->whereIn('lp.facility_id', $facilityIds);
        } elseif ($companyId && $this->db->fieldExists('company_id', 'lease_payments')) {
            $q->where('lp.company_id', $companyId);
        } elseif ($facilityIds !== null && $this->db->fieldExists('unit_id', 'lease_payments')) {
            $q->join('units u', 'u.id = lp.unit_id', 'left')->whereIn('u.facility_id', $facilityIds);
        }

        $row = $q->get()->getRowArray() ?: [];

        return [
            'paid'      => (float) ($row['paid'] ?? 0),
            'cancelled' => (float) ($row['cancelled'] ?? 0),
        ];
    }

    public function bustDashboardCaches(): void
    {
        cache()->delete('fm_sync_overdue_invoices');
        try {
            $cache = cache();
            if (method_exists($cache, 'clean')) {
                // File handler has no prefix scan; next dashboard load rebuilds 45s stats.
            }
        } catch (\Throwable $e) {
        }
    }
}
