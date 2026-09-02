<?php
namespace App\Controllers;

use App\Services\DashboardService;

class Dashboard extends BaseController
{
    public function index()
    {
        $role      = session()->get('user_role');
        $workspace = session()->get('workspace') ?: (new \App\Services\WorkspaceService($this->db))->sessionWorkspace((string) $role);

        if ($workspace === 'portal') {
            return redirect()->to(base_url('portal'));
        }
        if ($workspace === 'collector') {
            return redirect()->to(base_url('collector'));
        }
        if ($workspace === 'pm') {
            return $this->propertyManagementDashboard();
        }
        if ($workspace === 'fm') {
            return $this->facilityManagementDashboard();
        }

        return match($role) {
            'super_admin'           => $this->superAdminDashboard(),
            'facility_manager',
            'property_manager'      => $this->facilityManagementDashboard(),
            'supervisor'            => $this->propertyManagementDashboard(),
            'qa_inspector'          => $this->facilityManagementDashboard(),
            'finance_manager',
            'finance_user'          => $this->propertyManagementDashboard(),
            'procurement_officer'   => $this->procurementDashboard(),
            'technician'            => $this->technicianDashboard(),
            default                 => $this->clientDashboard(),
        };
    }

    private function propertyManagementDashboard(): string
    {
        $currency = $this->settings['currency'] ?? 'QAR';
        $dash     = new DashboardService($this->db);

        $totalFacilities = $this->scopeCompany(
            $this->db->table('facilities')->where('status', 'active')
        )->countAllResults();

        $unitCounts = $this->scopeFacilities($this->db->table('units')->select('status, COUNT(*) AS cnt', false)->groupBy('status'))->get()->getResultArray();
        $totalUnits = 0;
        $occupied   = 0;
        foreach ($unitCounts as $uc) {
            $cnt = (int) ($uc['cnt'] ?? 0);
            $totalUnits += $cnt;
            if (($uc['status'] ?? '') === 'occupied') {
                $occupied = $cnt;
            }
        }
        $occupancy  = $totalUnits > 0 ? round(($occupied / $totalUnits) * 100, 1) : 0;

        $activeContracts = 0;
        $expiringSoon    = 0;
        if ($this->db->tableExists('lease_contracts')) {
            $leaseBase = function () {
                $q = $this->db->table('lease_contracts')->where('status', 'active');
                if ($this->db->fieldExists('deleted_at', 'lease_contracts')) {
                    $q->where('deleted_at', null);
                }
                $this->scopeCompany($q, 'company_id');

                return $q;
            };
            $activeContracts = $leaseBase()->countAllResults();
            $expiringSoon    = $leaseBase()
                ->where('end_date <=', date('Y-m-d', strtotime('+60 days')))
                ->where('end_date >=', date('Y-m-d'))
                ->countAllResults();
        } else {
            $activeContracts = $this->db->table('contracts')->where('status', 'active')->countAllResults();
            $expiringSoon    = $this->db->table('contracts')
                ->where('status', 'active')
                ->where('end_date <=', date('Y-m-d', strtotime('+60 days')))
                ->where('end_date >=', date('Y-m-d'))
                ->countAllResults();
        }

        $totalsSvc = new \App\Services\FinanceTotalsService($this->db);
        $totalsSvc->syncOverdueInvoices();
        $totals   = $totalsSvc->invoiceTotals($this->companyScope()->facilityIds(), $this->pmCompanyIdFromSession());
        $overdue  = ['overdue_amount' => $totals['overdue'], 'overdue_count' => $totals['overdue_count']];
        $revenue  = $totals['revenue'];
        $pending  = $totals['outstanding'];
        $cancelled = $totals['cancelled'];

        $openMaintenance = $this->scopeFacilities($this->db->table('maintenance_requests'))
            ->whereIn('status', ['pending', 'reviewed', 'approved'])
            ->countAllResults();

        $expiringContracts = [];
        if ($this->db->tableExists('lease_contracts')) {
            $expiringQ = $this->db->table('lease_contracts lc')
                ->select('lc.id, lc.contract_number, lc.contract_kind, t.full_name AS client_name, lc.end_date, f.name AS facility_name, u.unit_type')
                ->join('tenants t', 't.id=lc.tenant_id', 'left')
                ->join('facilities f', 'f.id=lc.facility_id', 'left')
                ->join('units u', 'u.id=lc.unit_id', 'left')
                ->where('lc.status', 'active')
                ->where('lc.end_date <=', date('Y-m-d', strtotime('+90 days')))
                ->where('lc.end_date >=', date('Y-m-d'));
            if ($this->db->fieldExists('deleted_at', 'lease_contracts')) {
                $expiringQ->where('lc.deleted_at', null);
            }
            $this->scopeCompany($expiringQ, 'lc.company_id');
            $this->scopeFacilities($expiringQ, 'lc.facility_id');
            $expiringContracts = $expiringQ->orderBy('lc.end_date', 'ASC')->get()->getResultArray();
        } else {
            $expiringQ = $this->db->table('contracts c')
                ->select('c.id, c.contract_number, c.client_name, c.end_date, f.name AS facility_name')
                ->join('facilities f', 'f.id=c.facility_id', 'left')
                ->where('c.status', 'active')
                ->where('c.end_date <=', date('Y-m-d', strtotime('+90 days')))
                ->where('c.end_date >=', date('Y-m-d'));
            $this->scopeFacilities($expiringQ, 'c.facility_id');
            $expiringContracts = $expiringQ->orderBy('c.end_date', 'ASC')->get()->getResultArray();
        }

        $overdueInvoices = $this->db->table('invoices i')
            ->select('i.id, i.invoice_number, i.total, i.due_date, f.name AS facility_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left')
            ->where('i.status', 'overdue');
        $this->scopeFacilities($overdueInvoices, 'i.facility_id');
        $overdueInvoices = $overdueInvoices->orderBy('i.due_date', 'ASC')->get()->getResultArray();

        $recentMaintenance = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.category, mr.priority, mr.status, mr.created_at, f.name AS facility_name')
            ->join('facilities f', 'f.id=mr.facility_id', 'left');
        $this->scopeFacilities($recentMaintenance, 'mr.facility_id');
        $recentMaintenance = $recentMaintenance->orderBy('mr.created_at', 'DESC')
            ->limit(6)->get()->getResultArray();

        $facilityRows = $dash->facilityStatsWithOccupancy($this->companyScope()->facilityIds());
        $revTrend     = $dash->revenueExpenseTrend(6);

        $aiFlags = $this->cachedAiFlags('pm', $this->pmCompanyIdFromSession());

        return view('dashboard/pm_dashboard', $this->viewData([
            'title'             => 'Property Management Dashboard',
            'currency'          => $currency,
            'totalFacilities'   => $totalFacilities,
            'totalUnits'        => $totalUnits,
            'occupancy'         => $occupancy,
            'activeContracts'   => $activeContracts,
            'expiringSoon'      => $expiringSoon,
            'revenue'           => $revenue,
            'pendingReceivable' => $pending,
            'cancelledAmount'   => $cancelled,
            'overdueAmount'     => $overdue['overdue_amount'],
            'overdueCount'      => $overdue['overdue_count'],
            'openMaintenance'   => $openMaintenance,
            'expiringContracts' => $expiringContracts,
            'overdueInvoices'   => $overdueInvoices,
            'recentMaintenance' => $recentMaintenance,
            'facilityStats'     => $facilityRows,
            'revTrend'          => $revTrend,
            'aiFlags'           => $aiFlags,
        ]));
    }

    private function pmCompanyIdFromSession(): ?int
    {
        $id = session()->get('company_id');

        return $id ? (int) $id : null;
    }

    /** @return list<array<string, mixed>> */
    private function cachedAiFlags(string $workspace, ?int $companyId): array
    {
        try {
            $ai       = new \App\Services\AiModel($this->db);
            $cacheKey = 'fm_ai_scan_' . $workspace . '_' . (int) $companyId;
            if (! cache()->get($cacheKey)) {
                $ai->runAnalysis($companyId);
                cache()->save($cacheKey, 1, 120);
            }

            return $ai->flagsForWorkspace($workspace, 6);
        } catch (\Throwable $e) {
            log_message('error', strtoupper($workspace) . ' dashboard AI: ' . $e->getMessage());

            return [];
        }
    }

    private function facilityManagementDashboard(): string
    {
        return $this->facilityManagerDashboard();
    }

    // =========================================================
    // SUPER ADMIN — FIX CQ-01: single GROUP BY queries replace 36-query loop
    // =========================================================
    private function superAdminDashboard(): string
    {
        $currency = $this->settings['currency'] ?? 'QAR';
        $dash     = new DashboardService($this->db);
        $overdue  = $dash->invoiceOverdueStats($this->companyScope()->facilityIds());

        // Core KPIs (company-scoped)
        $totalFacilities = $this->scopeCompany(
            $this->db->table('facilities')->where('status', 'active')
        )->countAllResults();
        $woTotals        = $dash->workOrderTotals($this->companyScope()->facilityIds());
        $totalWO         = $woTotals['total'];
        $openWO          = $woTotals['open'];
        $criticalWO      = $woTotals['critical'];
        $breachedWO      = $woTotals['breached'];
        $slaCompliance   = $totalWO > 0 ? round((($totalWO - $breachedWO) / $totalWO) * 100, 1) : 100;
        $activeContracts = $this->db->table('contracts')->where('status','active')->countAllResults();
        $pendingInvoices = $this->db->table('invoices')->whereIn('status',['sent','overdue'])->countAllResults();
        $lowStock        = $this->db->table('inventory_items')->where('quantity <= min_quantity', null, false)->countAllResults();
        $openIncidents   = $this->db->table('incidents')->whereIn('status',['open','investigating'])->countAllResults();
        $expiringDocs    = $this->db->table('compliance_documents')
            ->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
            ->where('expiry_date >=', date('Y-m-d'))->countAllResults();

        // Asset health
        $assetHealth = 100;
        try {
            $ah = $this->db->table('assets')->where('status','active')->selectAvg('health_score','avg')->get()->getRowArray();
            $assetHealth = (int)($ah['avg'] ?? 100);
        } catch (\Throwable $e) {}

        $finTotals = (new \App\Services\FinanceTotalsService($this->db))->invoiceTotals($this->companyScope()->facilityIds());
        $revenue   = $finTotals['revenue'];
        $expenses  = (float)($this->db->table('expenses')->where('status','approved')->selectSum('amount','t')->get()->getRowArray()['t'] ?? 0);

        $revTrend     = $dash->revenueExpenseTrend(12);
        $trendData    = array_map(fn ($r) => [
            'month'    => $r['mon'],
            'revenue'  => $r['revenue'],
            'expenses' => $r['expenses'],
        ], $revTrend);
        $woTrend      = $dash->workOrderMonthlyTrend(12);
        $woPriority   = $dash->workOrderPriorityBreakdown();
        $facilityRows = $dash->facilityStatsWithOccupancy($this->companyScope()->facilityIds());

        // Energy costs
        $energyCosts = ['electricity' => 0, 'water' => 0, 'other' => 0];
        try {
            $energyRaw = $this->db->table('utility_readings')
                ->select('type, SUM(cost) AS total')
                ->where('reading_date >=', date('Y-m-01'))
                ->groupBy('type')->get()->getResultArray();
            foreach ($energyRaw as $e) {
                $t = in_array($e['type'], ['electricity','water']) ? $e['type'] : 'other';
                $energyCosts[$t] += (float)$e['total'];
            }
        } catch (\Throwable $e) {}

        // Recent WOs for live feed
        $recentWO = $this->db->table('work_orders w')
            ->select('w.wo_number, w.id, w.title, w.priority, w.status, w.sla_breached, w.updated_at, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=w.assigned_to','left')
            ->whereIn('w.status',['new','assigned','in_progress'])
            ->orderBy('w.updated_at','DESC')->limit(10)->get()->getResultArray();

        // SLA alerts
        $slaAlerts = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.sla_due, f.name AS facility_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->where('w.sla_breached',0)
            ->where('w.sla_due IS NOT NULL')
            ->where('w.sla_due <=', date('Y-m-d H:i:s', strtotime('+4 hours')))
            ->whereNotIn('w.status',['completed','closed','cancelled'])
            ->limit(5)->get()->getResultArray();

        $expiringContracts = $this->db->table('contracts c')
            ->select('c.id, c.contract_number, c.client_name, c.end_date, f.name AS facility_name')
            ->join('facilities f','f.id=c.facility_id','left')
            ->where('c.status','active')
            ->where('c.end_date <=', date('Y-m-d', strtotime('+30 days')))
            ->where('c.end_date >=', date('Y-m-d'))
            ->orderBy('c.end_date','ASC')->limit(5)->get()->getResultArray();

        // Pending approvals
        $pendingApprovals = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, f.name AS facility_name, u.name AS created_by_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=w.created_by','left')
            ->where('w.approval_status','pending')
            ->orderBy('w.created_at','ASC')->get()->getResultArray();

        // Technician performance (30 days)
        $techPerf = $this->db->query("
            SELECT u.name,
                   COUNT(wo.id) AS total,
                   SUM(CASE WHEN wo.status='completed' THEN 1 ELSE 0 END) AS completed,
                   SUM(CASE WHEN wo.sla_breached=1 THEN 1 ELSE 0 END) AS breached,
                   ROUND(AVG(TIMESTAMPDIFF(HOUR, wo.created_at, wo.completed_at)),1) AS avg_hours
            FROM users u
            JOIN roles r ON r.id=u.role_id AND r.name='technician'
            LEFT JOIN work_orders wo ON wo.assigned_to=u.id
              AND wo.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            WHERE u.status='active'
            GROUP BY u.id, u.name
            ORDER BY completed DESC
            LIMIT 5
        ")->getResultArray();

        // VIEW: dashboard/super_admin (existing file — correct name)
        return view('dashboard/super_admin', $this->viewData([
            'title'             => 'CEO Dashboard',
            'totalFacilities'   => $totalFacilities,
            'totalWO'           => $totalWO,
            'openWO'            => $openWO,
            'criticalWO'        => $criticalWO,
            'breachedWO'        => $breachedWO,
            'slaCompliance'     => $slaCompliance,
            'activeContracts'   => $activeContracts,
            'pendingInvoices'   => $pendingInvoices,
            'assetHealth'       => $assetHealth,
            'lowStock'          => $lowStock,
            'openIncidents'     => $openIncidents,
            'expiringDocs'      => $expiringDocs,
            'revenue'           => $revenue,
            'expenses'          => $expenses,
            'trendData'         => $trendData,
            'revTrend'          => $revTrend,
            'woTrend'           => $woTrend,
            'woPriority'        => $woPriority,
            'overdueAmount'     => $overdue['overdue_amount'],
            'overdueCount'      => $overdue['overdue_count'],
            'facilityStats'     => $facilityRows,
            'energyCosts'       => $energyCosts,
            'recentWO'          => $recentWO,
            'recentWOs'         => $recentWO,
            'slaAlerts'         => $slaAlerts,
            'expiringContracts' => $expiringContracts,
            'pendingApprovals'  => $pendingApprovals,
            'techPerf'          => $techPerf,
        ]));
    }

    // =========================================================
    // FACILITY MANAGER
    // =========================================================
    private function facilityManagerDashboard(): string
    {
        $woTotals     = (new DashboardService($this->db))->workOrderTotals($this->companyScope()->facilityIds());
        $openWO       = $woTotals['open'];
        $preventiveWO = $this->db->table('work_orders')->where('type','preventive')->whereIn('status',['new','assigned'])->countAllResults();
        $slaBreaches  = $woTotals['breached'];
        $totalStaff   = $this->db->table('employees')->where('status','active')->countAllResults();
        $checkedIn    = $this->db->table('attendance')->where('date',date('Y-m-d'))->where('status','present')->countAllResults();
        $openVendorWO = $this->db->table('work_orders')->where('vendor_id IS NOT NULL', null, false)->whereIn('status',['assigned','in_progress'])->countAllResults();

        $urgentWO = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.sla_due, w.sla_breached, w.updated_at, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=w.assigned_to','left')
            ->whereIn('w.priority',['critical','high'])
            ->whereIn('w.status',['new','assigned','in_progress'])
            ->orderBy('CASE w.priority WHEN \'critical\' THEN 1 WHEN \'high\' THEN 2 ELSE 3 END', '')
            ->limit(8)->get()->getResultArray();

        $slaAlerts = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.sla_due, f.name AS facility_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->where('w.sla_due <=', date('Y-m-d H:i:s', strtotime('+2 hours')))
            ->where('w.sla_due >=', date('Y-m-d H:i:s'))
            ->whereIn('w.status',['new','assigned','in_progress'])
            ->orderBy('w.sla_due','ASC')->limit(5)->get()->getResultArray();

        $pendingApprovals = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, f.name AS facility_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->where('w.approval_status','pending')
            ->orderBy('w.created_at','ASC')->get()->getResultArray();

        $expiringContracts = $this->db->table('contracts c')
            ->select('c.id, c.contract_number, c.client_name, c.end_date, f.name AS facility_name')
            ->join('facilities f','f.id=c.facility_id','left')
            ->where('c.status','active')
            ->where('c.end_date <=', date('Y-m-d', strtotime('+30 days')))
            ->where('c.end_date >=', date('Y-m-d'))
            ->orderBy('c.end_date','ASC')->get()->getResultArray();

        // Fetch workOrders for Kanban (what existing view expects as $workOrders)
        $workOrders = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.sla_breached, w.updated_at, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=w.assigned_to','left')
            ->whereNotIn('w.status',['completed','closed','cancelled'])
            ->orderBy('CASE w.priority WHEN \'critical\' THEN 1 WHEN \'high\' THEN 2 WHEN \'medium\' THEN 3 ELSE 4 END', '')
            ->limit(40)->get()->getResultArray();

        $pendingReq = $this->db->table('maintenance_requests')->where('status','pending')->countAllResults();
        $pendingPM  = $preventiveWO;
        $assetStats = (new \App\Services\AssetCodeService($this->db))->dashboardStats();

        // FIX: employees table has no 'name' column — name lives in users table
        $technicians = $this->db->table('employees e')
            ->select('u.name, e.designation, e.status')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('r.name', 'technician')
            ->where('e.status', 'active')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        $recentRequests = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.requester_name, mr.category, mr.priority, mr.status, mr.created_at')
            ->where('mr.status', 'pending')
            ->orderBy('mr.created_at', 'DESC')
            ->limit(10)->get()->getResultArray();

        $aiFlags = $this->cachedAiFlags('fm', $this->pmCompanyIdFromSession());

        // VIEW: dashboard/facility_manager
        return view('dashboard/facility_manager', $this->viewData([
            'title'             => 'Facility Management Dashboard',
            'openWO'            => $openWO,
            'preventiveWO'      => $preventiveWO,
            'pendingPM'         => $preventiveWO,
            'slaBreaches'       => $slaBreaches,
            'totalStaff'        => $totalStaff,
            'checkedIn'         => $checkedIn,
            'openVendorWO'      => $openVendorWO,
            'urgentWO'          => $urgentWO,
            'slaAlerts'         => $slaAlerts,
            'pendingApprovals'  => $pendingApprovals,
            'expiringContracts' => $expiringContracts,
            'workOrders'        => $workOrders,
            'pendingReq'        => $pendingReq,
            'technicians'       => $technicians,
            'recentRequests'    => $recentRequests,
            'assetStats'        => $assetStats,
            'aiFlags'           => $aiFlags,
        ]));
    }

    // =========================================================
    // TECHNICIAN
    // =========================================================
    private function technicianDashboard(): string
    {
        $uid = session()->get('user_id');

        $myWO = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.sla_due, w.sla_breached, w.updated_at, f.name AS facility_name, a.name AS asset_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('assets a','a.id=w.asset_id','left')
            ->where('w.assigned_to', $uid)
            ->whereIn('w.status',['assigned','in_progress'])
            ->orderBy('CASE w.priority WHEN \'critical\' THEN 1 WHEN \'high\' THEN 2 WHEN \'medium\' THEN 3 ELSE 4 END', '')
            ->orderBy('w.sla_due','ASC')
            ->get()->getResultArray();

        $completedToday = $this->db->table('work_orders')
            ->where('assigned_to', $uid)
            ->where('status','completed')
            ->where('DATE(completed_at)', date('Y-m-d'))
            ->countAllResults();

        $myJobCards = $this->db->table('job_cards jc')
            ->select('jc.id, jc.jc_number, jc.status, jc.labor_hours, wo.wo_number, wo.title AS wo_title')
            ->join('work_orders wo','wo.id=jc.wo_id','left')
            ->where('jc.assigned_to', $uid)
            ->whereIn('jc.status',['draft','in_progress'])
            ->orderBy('jc.created_at','DESC')->limit(5)->get()->getResultArray();

        $emp = $this->db->table('employees')->where('user_id', $uid)->get()->getRowArray();
        $todayAtt = null;
        if ($emp) {
            $todayAtt = $this->db->table('attendance')
                ->where('employee_id', $emp['id'])
                ->where('date', date('Y-m-d'))
                ->get()->getRowArray();
        }

        $kpi = [
            'assigned'        => count(array_filter($myWO, fn($w) => $w['status'] === 'assigned')),
            'in_progress'     => count(array_filter($myWO, fn($w) => $w['status'] === 'in_progress')),
            'completed_today' => $completedToday,
            'overdue'         => count(array_filter($myWO, fn($w) => (bool)$w['sla_breached'])),
        ];

        // VIEW: dashboard/technician (existing file)
        return view('dashboard/technician', $this->viewData([
            'title'          => 'My Dashboard',
            'myWO'           => $myWO,
            'myJobCards'     => $myJobCards,
            'completedToday' => $completedToday,
            'emp'            => $emp,
            'todayAtt'       => $todayAtt,
            'kpi'            => $kpi,
        ]));
    }

    // =========================================================
    // FINANCE — new view created: dashboard/finance
    // =========================================================
    private function financeDashboard(): string
    {
        $currency = $this->settings['currency'] ?? 'QAR';

        $finTotals = (new \App\Services\FinanceTotalsService($this->db))->invoiceTotals($this->companyScope()->facilityIds());
        $finRow    = [
            'revenue'       => $finTotals['revenue'],
            'overdue'       => $finTotals['overdue'],
            'pending'       => $finTotals['outstanding'],
            'overdue_count' => $finTotals['overdue_count'],
        ];

        $expRow = $this->db->query("
            SELECT
              SUM(CASE WHEN status='approved' THEN amount ELSE 0 END) AS approved,
              SUM(CASE WHEN status='pending'  THEN amount ELSE 0 END) AS pending_amount,
              COUNT(CASE WHEN status='pending' THEN 1 END)            AS pending_count
            FROM expenses
        ")->getRowArray();

        $pettyCashRow = $this->db->query("
            SELECT
              SUM(CASE WHEN status='approved' THEN amount ELSE 0 END) AS approved,
              COUNT(CASE WHEN status='pending' THEN 1 END)            AS pending_count
            FROM petty_cash
        ")->getRowArray();

        $reimbRow = $this->db->query("
            SELECT COUNT(CASE WHEN status='pending' THEN 1 END) AS pending_count,
                   SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) AS pending_amount
            FROM reimbursements
        ")->getRowArray();

        $pendingInvoices = $this->db->table('invoices i')
            ->select('i.id, i.invoice_number, i.total, i.due_date, i.status, f.name AS facility_name')
            ->join('facilities f','f.id=i.facility_id','left')
            ->where('i.status','sent')
            ->orderBy('i.due_date','ASC')->limit(8)->get()->getResultArray();

        $overdueInvoices = $this->db->table('invoices i')
            ->select('i.id, i.invoice_number, i.total, i.due_date, f.name AS facility_name')
            ->join('facilities f','f.id=i.facility_id','left')
            ->where('i.status','overdue')
            ->orderBy('i.due_date','ASC')->limit(8)->get()->getResultArray();

        $pendingExpenses = $this->db->table('expenses e')
            ->select('e.id, e.description, e.amount, e.expense_date, e.category, f.name AS facility_name, u.name AS created_by_name')
            ->join('facilities f','f.id=e.facility_id','left')
            ->join('users u','u.id=e.created_by','left')
            ->where('e.status','pending')
            ->orderBy('e.created_at','ASC')->limit(8)->get()->getResultArray();

        $pendingPettyCash = [];
        try {
            $pendingPettyCash = $this->db->table('petty_cash pc')
                ->select('pc.id, pc.pc_number, pc.amount, pc.purpose, pc.category, u.name AS requested_by_name')
                ->join('users u','u.id=pc.requested_by','left')
                ->where('pc.status','pending')
                ->orderBy('pc.created_at','ASC')->get()->getResultArray();
        } catch (\Throwable $e) {}

        // VIEW: dashboard/finance (NEW — created below)
        return view('dashboard/finance', $this->viewData([
            'title'            => 'Finance Dashboard',
            'currency'         => $currency,
            'finRow'           => $finRow,
            'expRow'           => $expRow,
            'pettyCashRow'     => $pettyCashRow,
            'reimbRow'         => $reimbRow,
            'pendingInvoices'  => $pendingInvoices,
            'overdueInvoices'  => $overdueInvoices,
            'pendingExpenses'  => $pendingExpenses,
            'pendingPettyCash' => $pendingPettyCash,
        ]));
    }

    // =========================================================
    // PROCUREMENT — new view created: dashboard/procurement
    // =========================================================
    private function procurementDashboard(): string
    {
        $kpi = [
            'pending_pr'  => $this->db->table('purchase_requests')->where('status','pending')->countAllResults(),
            'approved_pr' => $this->db->table('purchase_requests')->where('status','approved')->countAllResults(),
            'open_po'     => $this->db->table('purchase_orders')->whereIn('status',['pending','approved'])->countAllResults(),
            'pending_grn' => $this->db->table('purchase_orders')->where('status','delivered')->countAllResults(),
        ];

        $pendingRequests = $this->db->table('purchase_requests pr')
            ->select('pr.id, pr.quantity, pr.priority, pr.reason, pr.created_at, i.name AS item_name, u.name AS requested_by_name')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->join('users u','u.id=pr.requested_by','left')
            ->where('pr.status','pending')
            ->orderBy('CASE pr.priority WHEN \'critical\' THEN 1 WHEN \'high\' THEN 2 WHEN \'medium\' THEN 3 ELSE 4 END', '')
            ->get()->getResultArray();

        $recentOrders = $this->db->table('purchase_orders po')
            ->select('po.id, po.po_number, po.total_amount, po.status, po.delivery_date, v.name AS vendor_name')
            ->join('vendors v','v.id=po.vendor_id','left')
            ->orderBy('po.created_at','DESC')->limit(8)->get()->getResultArray();

        $lowStockItems = $this->db->table('inventory_items')
            ->select('id, name, quantity, min_quantity, unit, item_code')
            ->where('quantity <= min_quantity', null, false)
            ->where('quantity >', 0)
            ->orderBy('quantity','ASC')->limit(8)->get()->getResultArray();

        // VIEW: dashboard/procurement (NEW — created below)
        return view('dashboard/procurement', $this->viewData([
            'title'           => 'Procurement Dashboard',
            'kpi'             => $kpi,
            'pendingRequests' => $pendingRequests,
            'recentOrders'    => $recentOrders,
            'lowStockItems'   => $lowStockItems,
        ]));
    }

    // =========================================================
    // CLIENT — FIX BUG-06: isolated to user's own data
    // =========================================================
    private function clientDashboard(): string
    {
        $uid   = session()->get('user_id');
        $email = session()->get('user_email');

        // FIX BUG-06: use requester_email (existing column) for isolation
        $myTickets = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.description, mr.priority, mr.status, mr.created_at, hf.rating')
            ->join('helpdesk_feedback hf','hf.request_id=mr.id','left')
            ->where('mr.requester_email', $email)
            ->orderBy('mr.created_at','DESC')->limit(10)->get()->getResultArray();

        // FIX BUG-06: filter invoices by client email via contracts
        $myInvoices = $this->db->table('invoices i')
            ->select('i.id, i.invoice_number, i.total, i.status, i.due_date, f.name AS facility_name')
            ->join('facilities f','f.id=i.facility_id','left')
            ->join('contracts c','c.id=i.contract_id','left')
            ->groupStart()
                ->where('c.client_email', $email)
                ->orWhere('i.created_by', $uid)
            ->groupEnd()
            ->orderBy('i.created_at','DESC')->limit(5)->get()->getResultArray();

        $myContracts = $this->db->table('contracts c')
            ->select('c.id, c.contract_number, c.client_name, c.end_date, c.status, f.name AS facility_name')
            ->join('facilities f','f.id=c.facility_id','left')
            ->where('c.client_email', $email)
            ->where('c.status','active')
            ->get()->getResultArray();

        $kpi = [
            'open_requests'    => count(array_filter($myTickets, fn($t) => in_array($t['status'],['pending','reviewed']))),
            'pending_invoices' => count(array_filter($myInvoices, fn($i) => in_array($i['status'],['sent','overdue']))),
            'active_contracts' => count($myContracts),
        ];

        // VIEW: dashboard/client (existing file)
        return view('dashboard/client', $this->viewData([
            'title'       => 'Client Portal',
            'myTickets'   => $myTickets,
            'myInvoices'  => $myInvoices,
            'myContracts' => $myContracts,
            'kpi'         => $kpi,
        ]));
    }

    public function kpi()
    {
        return redirect()->to(base_url('reports/kpi'));
    }
}
