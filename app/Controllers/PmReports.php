<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Services\DashboardService;

/**
 * Property Management reports hub and PM-scoped operational reports.
 */
class PmReports extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    public function index()
    {
        return view('pm_reports/index', $this->viewData(['title' => 'Property Management Reports']));
    }

    public function portalHub()
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user', 'property_manager', 'real_estate_manager');

        return view('pm_reports/portal_hub', $this->viewData(['title' => 'PM Reports Portal']));
    }

    public function kpi()
    {
        $currency = $this->settings['currency'] ?? 'QAR';
        $dash     = new DashboardService($this->db);

        $totalUnits = $this->scopeFacilities($this->db->table('units'))->countAllResults();
        $occupied   = $this->scopeFacilities($this->db->table('units'))->where('status', 'occupied')->countAllResults();
        $occupancy  = $totalUnits > 0 ? round(($occupied / $totalUnits) * 100, 1) : 0;

        $activeLeases = 0;
        $expiringSoon = 0;
        if ($this->db->tableExists('lease_contracts')) {
            $leaseQ = $this->db->table('lease_contracts lc');
            $this->scopeCompany($leaseQ, 'lc.company_id');
            $this->scopeFacilities($leaseQ, 'lc.facility_id');
            $activeLeases = (clone $leaseQ)->where('lc.status', 'active')->countAllResults();
            $expiringSoon = (clone $leaseQ)
                ->where('lc.status', 'active')
                ->where('lc.end_date <=', date('Y-m-d', strtotime('+60 days')))
                ->where('lc.end_date >=', date('Y-m-d'))
                ->countAllResults();
        }

        $overdue   = $dash->invoiceOverdueStats();
        $revenue   = (float) ($this->db->table('invoices')->where('status', 'paid')->selectSum('total', 't')->get()->getRowArray()['t'] ?? 0);
        $pending   = (float) ($this->db->table('invoices')->whereIn('status', ['sent', 'overdue'])->selectSum('total', 't')->get()->getRowArray()['t'] ?? 0);
        $openMaint = $this->scopeFacilities($this->db->table('maintenance_requests'))
            ->whereIn('status', ['pending', 'reviewed', 'approved'])
            ->countAllResults();

        return view('pm_reports/kpi', $this->viewData([
            'title'         => 'PM KPI Analytics',
            'currency'      => $currency,
            'occupancy'     => $occupancy,
            'totalUnits'    => $totalUnits,
            'occupiedUnits' => $occupied,
            'activeLeases'  => $activeLeases,
            'expiringSoon'  => $expiringSoon,
            'revenue'       => $revenue,
            'pending'       => $pending,
            'overdueAmount' => $overdue['overdue_amount'],
            'overdueCount'  => $overdue['overdue_count'],
            'openMaint'     => $openMaint,
            'revTrend'      => $dash->revenueExpenseTrend(12),
            'facilityStats' => $dash->facilityStatsWithOccupancy($this->companyScope()->facilityIds()),
        ]));
    }

    public function occupancy()
    {
        [$facilitySql, $facilityParams] = $this->scopedFacilitySql('f.id');

        $facilities = $this->db->query("
            SELECT f.id, f.name, f.code,
              COUNT(u.id) AS total_units,
              SUM(CASE WHEN u.status='occupied' THEN 1 ELSE 0 END) AS occupied,
              SUM(CASE WHEN u.status='vacant' THEN 1 ELSE 0 END) AS vacant,
              SUM(CASE WHEN u.status='maintenance' THEN 1 ELSE 0 END) AS maintenance,
              ROUND(SUM(CASE WHEN u.status='occupied' THEN 1 ELSE 0 END)/NULLIF(COUNT(u.id),0)*100,1) AS occupancy_pct,
              COALESCE(SUM(u.rent_amount),0) AS total_rent
            FROM facilities f
            LEFT JOIN units u ON u.facility_id=f.id AND (u.deleted_at IS NULL OR u.deleted_at='0000-00-00 00:00:00')
            WHERE f.status='active' {$facilitySql}
            GROUP BY f.id, f.name, f.code
            ORDER BY occupancy_pct DESC
        ", $facilityParams)->getResultArray();

        $expiringQ = $this->db->table('units u')
            ->select('u.*, f.name as facility_name')
            ->join('facilities f', 'f.id=u.facility_id', 'left')
            ->where('u.status', 'occupied')
            ->where('u.contract_end >=', date('Y-m-d'))
            ->where('u.contract_end <=', date('Y-m-d', strtotime('+60 days')))
            ->orderBy('u.contract_end', 'ASC');
        $this->scopeFacilities($expiringQ, 'u.facility_id');
        $expiringContracts = $expiringQ->get()->getResultArray();

        return view('reports/occupancy', $this->viewData([
            'title'             => 'Occupancy Report',
            'facilities'        => $facilities,
            'expiringContracts' => $expiringContracts,
            'reportsBase'       => 'reports/pm',
        ]));
    }

    public function leases()
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return $this->migrationRedirect('lease_contracts');
        }

        $status   = $this->request->getGet('status') ?? '';
        $facility = (int) ($this->request->getGet('facility') ?? 0);
        $expiring = $this->request->getGet('expiring') ?? '';

        $q = $this->db->table('lease_contracts lc')
            ->select('lc.*, t.full_name AS client_name, t.email AS client_email, t.phone AS client_mobile, f.name AS facility_name, u.unit_number')
            ->join('tenants t', 't.id=lc.tenant_id', 'left')
            ->join('facilities f', 'f.id=lc.facility_id', 'left')
            ->join('units u', 'u.id=lc.unit_id', 'left')
            ->where('lc.deleted_at', null);
        $this->scopeCompany($q, 'lc.company_id');
        $this->scopeFacilities($q, 'lc.facility_id');

        if ($status !== '') {
            $q->where('lc.status', $status);
        }
        if ($facility > 0) {
            $q->where('lc.facility_id', $facility);
        }
        if ($expiring) {
            $q->where('lc.end_date <=', date('Y-m-d', strtotime('+60 days')))
                ->where('lc.end_date >=', date('Y-m-d'));
        }

        $leases     = $q->orderBy('lc.end_date', 'ASC')->get()->getResultArray();
        $facilities = $this->scopedActiveFacilities();

        return view('pm_reports/leases', $this->viewData([
            'title'          => 'Lease Expiry Report',
            'leases'         => $leases,
            'facilities'     => $facilities,
            'filterStatus'   => $status,
            'filterFacility' => $facility,
            'filterExpiring' => $expiring,
        ]));
    }

    public function invoices()
    {
        $from     = $this->request->getGet('from') ?? date('Y-m-01');
        $to       = $this->request->getGet('to') ?? date('Y-m-d');
        $facility = (int) ($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status') ?? '';

        $q = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left')
            ->where('DATE(i.issue_date) >=', $from)
            ->where('DATE(i.issue_date) <=', $to);
        $this->scopeFacilities($q, 'i.facility_id');

        if ($facility > 0) {
            $q->where('i.facility_id', $facility);
        }
        if ($status !== '') {
            $q->where('i.status', $status);
        }

        $invoices = $q->orderBy('i.issue_date', 'DESC')->get()->getResultArray();
        $paid     = 0.0;
        $outstanding = 0.0;
        foreach ($invoices as $inv) {
            $total = (float) ($inv['total'] ?? 0);
            if (($inv['status'] ?? '') === 'paid') {
                $paid += $total;
            } elseif (in_array($inv['status'] ?? '', ['sent', 'overdue'], true)) {
                $outstanding += $total;
            }
        }

        return view('pm_reports/invoices', $this->viewData([
            'title'          => 'Invoice Report',
            'invoices'       => $invoices,
            'from'           => $from,
            'to'             => $to,
            'facilities'     => $this->scopedActiveFacilities(),
            'filterFacility' => $facility,
            'filterStatus'   => $status,
            'stats'          => [
                'count'       => count($invoices),
                'paid'        => $paid,
                'outstanding' => $outstanding,
            ],
        ]));
    }

    public function payments()
    {
        if (! $this->pmTableExists('lease_payments')) {
            return $this->migrationRedirect('lease_payments');
        }

        $from     = $this->request->getGet('from') ?? date('Y-m-01');
        $to       = $this->request->getGet('to') ?? date('Y-m-d');
        $facility = (int) ($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status') ?? '';

        $q = $this->db->table('lease_payments lp')
            ->select('lp.*, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name')
            ->join('lease_contracts lc', 'lc.id=lp.contract_id', 'left')
            ->join('tenants t', 't.id=lp.tenant_id', 'left')
            ->join('facilities f', 'f.id=lp.facility_id', 'left')
            ->where('DATE(lp.due_date) >=', $from)
            ->where('DATE(lp.due_date) <=', $to);
        $this->scopeCompany($q, 'lp.company_id');
        $this->scopeFacilities($q, 'lp.facility_id');

        if ($facility > 0) {
            $q->where('lp.facility_id', $facility);
        }
        if ($status !== '') {
            $q->where('lp.status', $status);
        }

        $payments = $q->orderBy('lp.due_date', 'DESC')->get()->getResultArray();
        $collected = 0.0;
        $due       = 0.0;
        foreach ($payments as $p) {
            $amt = (float) ($p['amount'] ?? 0);
            if (($p['status'] ?? '') === 'paid') {
                $collected += $amt;
            } elseif (in_array($p['status'] ?? '', ['pending', 'overdue'], true)) {
                $due += $amt;
            }
        }

        return view('pm_reports/payments', $this->viewData([
            'title'          => 'Lease Payments Report',
            'payments'       => $payments,
            'from'           => $from,
            'to'             => $to,
            'facilities'     => $this->scopedActiveFacilities(),
            'filterFacility' => $facility,
            'filterStatus'   => $status,
            'stats'          => [
                'count'     => count($payments),
                'collected' => $collected,
                'due'       => $due,
            ],
        ]));
    }

    public function cheques()
    {
        if (! $this->pmTableExists('cheques')) {
            return $this->migrationRedirect('cheques');
        }

        $from     = $this->request->getGet('from') ?? date('Y-m-01');
        $to       = $this->request->getGet('to') ?? date('Y-m-d');
        $facility = (int) ($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status') ?? '';

        $q = $this->db->table('cheques c')
            ->select('c.*, t.full_name AS tenant_name, lc.contract_number, f.name AS facility_name')
            ->join('tenants t', 't.id=c.tenant_id', 'left')
            ->join('lease_contracts lc', 'lc.id=c.contract_id', 'left')
            ->join('facilities f', 'f.id=c.facility_id', 'left')
            ->where('DATE(c.cheque_date) >=', $from)
            ->where('DATE(c.cheque_date) <=', $to);
        $this->scopeCompany($q, 'c.company_id');
        $this->scopeFacilities($q, 'c.facility_id');

        if ($facility > 0) {
            $q->where('c.facility_id', $facility);
        }
        if ($status !== '') {
            $q->where('c.status', $status);
        }

        $cheques = $q->orderBy('c.cheque_date', 'DESC')->get()->getResultArray();
        $totalAmount = array_sum(array_map(fn ($c) => (float) ($c['amount'] ?? 0), $cheques));

        return view('pm_reports/cheques', $this->viewData([
            'title'          => 'Cheques (PDC) Report',
            'cheques'        => $cheques,
            'from'           => $from,
            'to'             => $to,
            'facilities'     => $this->scopedActiveFacilities(),
            'filterFacility' => $facility,
            'filterStatus'   => $status,
            'totalAmount'    => $totalAmount,
        ]));
    }

    public function expenses()
    {
        $from     = $this->request->getGet('from') ?? date('Y-m-01');
        $to       = $this->request->getGet('to') ?? date('Y-m-d');
        $facility = (int) ($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status') ?? '';

        $q = $this->db->table('expenses e')
            ->select('e.*, f.name as facility_name')
            ->join('facilities f', 'f.id=e.facility_id', 'left')
            ->where('DATE(e.expense_date) >=', $from)
            ->where('DATE(e.expense_date) <=', $to);
        $this->scopeFacilities($q, 'e.facility_id');

        if ($facility > 0) {
            $q->where('e.facility_id', $facility);
        }
        if ($status !== '') {
            $q->where('e.status', $status);
        }

        $expenses = $q->orderBy('e.expense_date', 'DESC')->get()->getResultArray();
        $approved = array_sum(array_map(
            fn ($e) => ($e['status'] ?? '') === 'approved' ? (float) ($e['amount'] ?? 0) : 0,
            $expenses
        ));

        return view('pm_reports/expenses', $this->viewData([
            'title'          => 'Expense Report',
            'expenses'       => $expenses,
            'from'           => $from,
            'to'             => $to,
            'facilities'     => $this->scopedActiveFacilities(),
            'filterFacility' => $facility,
            'filterStatus'   => $status,
            'approvedTotal'  => $approved,
        ]));
    }

    public function properties()
    {
        $properties = $this->scopedActiveFacilities();

        return view('pm_reports/properties', $this->viewData([
            'title'      => 'Property P&amp;L Reports',
            'properties' => $properties,
        ]));
    }

    /**
     * SQL fragment for scoping facilities in raw queries.
     *
     * @return array{0: string, 1: list<int>}
     */
    private function scopedFacilitySql(string $column = 'f.id'): array
    {
        $ids = $this->companyScope()->facilityIds();
        if ($ids === null) {
            return ['', []];
        }
        if ($ids === []) {
            return [' AND 1=0 ', []];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return [" AND {$column} IN ({$placeholders}) ", $ids];
    }

    private function migrationRedirect(string $table): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(base_url('reports/pm'))
            ->with('error', 'Required table missing: ' . $table . '. Please run database migrations.');
    }
}
