<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Dashboard KPI and chart data — all DB access stays out of views.
 */
class DashboardService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return list<array{mon: string, revenue: float, expenses: float}> */
    public function revenueExpenseTrend(int $months = 12): array
    {
        $revRaw = $this->db->query("
            SELECT DATE_FORMAT(issue_date,'%b %y') AS mon,
                   DATE_FORMAT(issue_date,'%Y-%m') AS ym,
                   SUM(CASE WHEN status='paid' THEN total ELSE 0 END) AS revenue
            FROM invoices
            WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(issue_date,'%Y-%m'), DATE_FORMAT(issue_date,'%b %y')
            ORDER BY ym
        ", [$months])->getResultArray();

        $expRaw = $this->db->query("
            SELECT DATE_FORMAT(expense_date,'%Y-%m') AS ym,
                   SUM(amount) AS expenses
            FROM expenses
            WHERE status='approved' AND expense_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(expense_date,'%Y-%m')
        ", [$months])->getResultArray();

        $expByMonth = array_column($expRaw, 'expenses', 'ym');
        $trend      = [];

        foreach ($revRaw as $r) {
            $trend[] = [
                'mon'      => $r['mon'],
                'revenue'  => (float) $r['revenue'],
                'expenses' => (float) ($expByMonth[$r['ym']] ?? 0),
            ];
        }

        return $trend;
    }

    /** @return list<array{priority: string, cnt: int}> */
    public function workOrderPriorityBreakdown(): array
    {
        return $this->db->query("
            SELECT priority, COUNT(*) AS cnt
            FROM work_orders
            WHERE status NOT IN ('completed','closed','cancelled')
            GROUP BY priority
        ")->getResultArray();
    }

    /** @return array{overdue_amount: float, overdue_count: int} */
    public function invoiceOverdueStats(?array $facilityIds = null): array
    {
        $totals = (new FinanceTotalsService($this->db))->invoiceTotals($facilityIds);

        return [
            'overdue_amount' => $totals['overdue'],
            'overdue_count'  => $totals['overdue_count'],
        ];
    }

    /**
     * Single GROUP BY for work-order status / priority / cancelled counts.
     *
     * @param list<int>|null $facilityIds
     * @return array{total: int, open: int, cancelled: int, completed: int, critical: int, breached: int}
     */
    public function workOrderTotals(?array $facilityIds = null): array
    {
        $empty = ['total' => 0, 'open' => 0, 'cancelled' => 0, 'completed' => 0, 'critical' => 0, 'breached' => 0];
        if ($facilityIds !== null && $facilityIds === []) {
            return $empty;
        }

        $sql = "SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN status IN ('new','assigned','in_progress') THEN 1 ELSE 0 END), 0) AS open_cnt,
            COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancelled,
            COALESCE(SUM(CASE WHEN status IN ('completed','closed') THEN 1 ELSE 0 END), 0) AS completed,
            COALESCE(SUM(CASE WHEN priority = 'critical' AND status IN ('new','assigned','in_progress') THEN 1 ELSE 0 END), 0) AS critical,
            COALESCE(SUM(CASE WHEN sla_breached = 1 THEN 1 ELSE 0 END), 0) AS breached
            FROM work_orders WHERE 1=1";
        $params = [];
        if ($facilityIds !== null) {
            $sql .= ' AND facility_id IN (' . implode(',', array_fill(0, count($facilityIds), '?')) . ')';
            $params = $facilityIds;
        }
        $row = $this->db->query($sql, $params)->getRowArray() ?: [];

        return [
            'total'     => (int) ($row['total'] ?? 0),
            'open'      => (int) ($row['open_cnt'] ?? 0),
            'cancelled' => (int) ($row['cancelled'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'critical'  => (int) ($row['critical'] ?? 0),
            'breached'  => (int) ($row['breached'] ?? 0),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function facilityStatsWithOccupancy(?array $facilityIds = null): array
    {
        $cacheKey = 'fm_facility_stats_' . md5(json_encode($facilityIds));
        $cached   = cache()->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $facilityFilter = '';
        $params         = [];
        if ($facilityIds !== null) {
            if ($facilityIds === []) {
                return [];
            }
            $placeholders   = implode(',', array_fill(0, count($facilityIds), '?'));
            $facilityFilter = " AND f.id IN ($placeholders)";
            $params         = $facilityIds;
        }

        $rows = $this->db->query("
            SELECT f.id, f.name,
                COALESCE(a.asset_count, 0) AS asset_count,
                COALESCE(w.open_wo, 0) AS open_wo,
                COALESCE(w.sla_breaches, 0) AS sla_breaches,
                COALESCE(a.avg_health, 100) AS avg_health,
                COALESCE(i.revenue, 0) AS revenue,
                COALESCE(e.expenses, 0) AS expenses,
                COALESCE(u.total_units, 0) AS total_units,
                COALESCE(u.occupied_units, 0) AS occupied_units
            FROM facilities f
            LEFT JOIN (
                SELECT facility_id,
                       SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS asset_count,
                       AVG(CASE WHEN status = 'active' THEN health_score END) AS avg_health
                FROM assets
                GROUP BY facility_id
            ) a ON a.facility_id = f.id
            LEFT JOIN (
                SELECT facility_id,
                       SUM(CASE WHEN status IN ('new','assigned','in_progress') THEN 1 ELSE 0 END) AS open_wo,
                       SUM(CASE WHEN sla_breached = 1 THEN 1 ELSE 0 END) AS sla_breaches
                FROM work_orders
                GROUP BY facility_id
            ) w ON w.facility_id = f.id
            LEFT JOIN (
                SELECT facility_id,
                       SUM(CASE WHEN status = 'paid' AND deleted_at IS NULL THEN total ELSE 0 END) AS revenue
                FROM invoices
                GROUP BY facility_id
            ) i ON i.facility_id = f.id
            LEFT JOIN (
                SELECT facility_id,
                       SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) AS expenses
                FROM expenses
                GROUP BY facility_id
            ) e ON e.facility_id = f.id
            LEFT JOIN (
                SELECT facility_id,
                       SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS total_units,
                       SUM(CASE WHEN status = 'occupied' AND deleted_at IS NULL THEN 1 ELSE 0 END) AS occupied_units
                FROM units
                GROUP BY facility_id
            ) u ON u.facility_id = f.id
            WHERE f.status='active' $facilityFilter
            ORDER BY f.name ASC
        ", $params)->getResultArray();

        cache()->save($cacheKey, $rows, 45);

        return $rows;
    }

    /** @return list<array{month: string, total: int}> */
    public function workOrderMonthlyTrend(int $months = 12): array
    {
        $woRaw = $this->db->query("
            SELECT DATE_FORMAT(created_at,'%b %y') AS month,
                   COUNT(*) AS total
            FROM work_orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at,'%Y-%m'), DATE_FORMAT(created_at,'%b %y')
            ORDER BY DATE_FORMAT(created_at,'%Y-%m')
        ", [$months])->getResultArray();

        return $woRaw;
    }
}
