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
    public function invoiceOverdueStats(): array
    {
        $row = $this->db->query("
            SELECT COALESCE(SUM(total),0) AS overdue_amount,
                   COUNT(*) AS overdue_count
            FROM invoices
            WHERE status IN ('overdue','sent') AND due_date < CURDATE()
        ")->getRowArray();

        return [
            'overdue_amount' => (float) ($row['overdue_amount'] ?? 0),
            'overdue_count'  => (int) ($row['overdue_count'] ?? 0),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function facilityStatsWithOccupancy(?array $facilityIds = null): array
    {
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

        return $this->db->query("
            SELECT f.id, f.name,
                (SELECT COUNT(*) FROM assets a WHERE a.facility_id=f.id AND a.deleted_at IS NULL) AS asset_count,
                (SELECT COUNT(*) FROM work_orders w WHERE w.facility_id=f.id
                    AND w.status IN ('new','assigned','in_progress')) AS open_wo,
                (SELECT COUNT(*) FROM work_orders w WHERE w.facility_id=f.id AND w.sla_breached=1) AS sla_breaches,
                (SELECT COALESCE(AVG(a.health_score),100) FROM assets a WHERE a.facility_id=f.id AND a.status='active') AS avg_health,
                (SELECT COALESCE(SUM(i.total),0) FROM invoices i WHERE i.facility_id=f.id AND i.status='paid') AS revenue,
                (SELECT COALESCE(SUM(e.amount),0) FROM expenses e WHERE e.facility_id=f.id AND e.status='approved') AS expenses,
                (SELECT COUNT(*) FROM units un WHERE un.facility_id=f.id AND un.deleted_at IS NULL) AS total_units,
                (SELECT COUNT(*) FROM units un WHERE un.facility_id=f.id AND un.status='occupied' AND un.deleted_at IS NULL) AS occupied_units
            FROM facilities f
            WHERE f.status='active' $facilityFilter
            ORDER BY f.name ASC
        ", $params)->getResultArray();
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
