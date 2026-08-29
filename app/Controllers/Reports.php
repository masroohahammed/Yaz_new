<?php
namespace App\Controllers;

use App\Services\DashboardService;
use App\Services\ReportBuilderService;

class Reports extends BaseController
{
    public function index()
    {
        return view('reports/index', $this->viewData(['title'=>'Reports']));
    }

    public function portalHub()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        return view('reports/portal_hub', $this->viewData(['title' => 'Reports Portal']));
    }

    public function kpi()
    {
        $dash = new DashboardService($this->db);
        $totalWO     = $this->scopeFacilities($this->db->table('work_orders'))->countAllResults();
        $breachedWO  = $this->scopeFacilities($this->db->table('work_orders'))->where('sla_breached', 1)->countAllResults();
        $openWO      = $this->scopeFacilities($this->db->table('work_orders'))->whereIn('status', ['new', 'assigned', 'in_progress'])->countAllResults();
        $slaCompliance = $totalWO > 0 ? round((($totalWO - $breachedWO) / $totalWO) * 100, 1) : 100;
        $revenue     = (float) ($this->db->table('invoices')->where('status', 'paid')->selectSum('total', 't')->get()->getRowArray()['t'] ?? 0);

        return view('reports/kpi_analytics', $this->viewData([
            'title'          => 'KPI Analytics',
            'currency'       => $this->settings['currency'] ?? 'QAR',
            'slaCompliance'  => $slaCompliance,
            'openWO'         => $openWO,
            'breachedWO'     => $breachedWO,
            'revenue'        => $revenue,
            'revTrend'       => $dash->revenueExpenseTrend(12),
            'woPriority'     => $dash->workOrderPriorityBreakdown(),
            'facilityStats'  => $dash->facilityStatsWithOccupancy($this->companyScope()->facilityIds()),
        ]));
    }

    public function workorders()
    {
        $from     = $this->request->getGet('from')     ?? date('Y-m-01');
        $to       = $this->request->getGet('to')       ?? date('Y-m-d');
        $facility = (int)($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status')   ?? '';
        $priority = $this->request->getGet('priority') ?? '';

        $q = $this->db->table('work_orders w')
            ->select('w.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=w.assigned_to','left')
            ->where('DATE(w.created_at) >=',$from)->where('DATE(w.created_at) <=',$to);
        $this->scopeFacilities($q, 'w.facility_id');
        if ($facility) $q->where('w.facility_id',$facility);
        if ($status)   $q->where('w.status',$status);
        if ($priority) $q->where('w.priority',$priority);
        $wos = $q->orderBy('w.created_at','DESC')->get()->getResultArray();

        $stats = [
            'total'     => count($wos),
            'completed' => count(array_filter($wos,fn($w)=>$w['status']==='completed')),
            'breached'  => count(array_filter($wos,fn($w)=>$w['sla_breached']==1)),
            'cost'      => array_sum(array_column($wos,'actual_cost')),
            'by_status' => array_count_values(array_column($wos,'status')),
            'by_priority'=> array_count_values(array_column($wos,'priority')),
        ];
        $facilities = $this->scopeCompany(
            $this->db->table('facilities')->where('status', 'active')
        )->get()->getResultArray();

        $activityLogs = $this->db->table('activity_logs al')
            ->select('al.*, u.name as user_name')
            ->join('users u', 'u.id=al.user_id', 'left')
            ->whereIn('al.module', ['work_orders', 'job_cards'])
            ->where('DATE(al.created_at) >=', $from)
            ->where('DATE(al.created_at) <=', $to)
            ->orderBy('al.created_at', 'DESC')
            ->limit(200)
            ->get()->getResultArray();

        return view('reports/workorders', $this->viewData([
            'title'=>'Work Order Report','wos'=>$wos,'stats'=>$stats,
            'from'=>$from,'to'=>$to,'facilities'=>$facilities,'filterFacility'=>$facility,'filterStatus'=>$status,'filterPriority'=>$priority,
            'activityLogs'=>$activityLogs,
        ]));
    }

    public function finance()
    {
        $from     = $this->request->getGet('from') ?? date('Y-01-01');
        $to       = $this->request->getGet('to')   ?? date('Y-m-d');
        $facility = (int)($this->request->getGet('facility') ?? 0);

        $qInv = $this->db->table('invoices i')->select('i.*, f.name as facility_name')->join('facilities f','f.id=i.facility_id','left')->where('DATE(i.issue_date) >=',$from)->where('DATE(i.issue_date) <=',$to);
        $qExp = $this->db->table('expenses e')->select('e.*, f.name as facility_name')->join('facilities f','f.id=e.facility_id','left')->where('DATE(e.expense_date) >=',$from)->where('DATE(e.expense_date) <=',$to);
        $this->scopeFacilities($qInv, 'i.facility_id');
        $this->scopeFacilities($qExp, 'e.facility_id');
        if ($facility) { $qInv->where('i.facility_id',$facility); $qExp->where('e.facility_id',$facility); }
        $invoices = $qInv->orderBy('i.issue_date','DESC')->get()->getResultArray();
        $expenses = $qExp->get()->getResultArray();

        $revenue       = array_sum(array_map(fn($i)=>$i['status']==='paid'?(float)$i['total']:0,$invoices));
        $totalExpenses = array_sum(array_column($expenses,'amount'));
        $outstanding   = array_sum(array_map(fn($i)=>in_array($i['status'],['sent','overdue'])?(float)$i['total']:0,$invoices));
        $profit        = $revenue - $totalExpenses;
        $facilities    = $this->scopeCompany($this->db->table('facilities')->where('status','active'))->get()->getResultArray();

        return view('reports/finance', $this->viewData([
            'title'=>'Finance Report','invoices'=>$invoices,'expenses'=>$expenses,
            'revenue'=>$revenue,'totalExpenses'=>$totalExpenses,'outstanding'=>$outstanding,'profit'=>$profit,
            'from'=>$from,'to'=>$to,'facilities'=>$facilities,'filterFacility'=>$facility,
        ]));
    }

    public function sla()
    {
        $from     = $this->request->getGet('from') ?? date('Y-m-01');
        $to       = $this->request->getGet('to')   ?? date('Y-m-d');
        $facility = (int)($this->request->getGet('facility') ?? 0);

        $q = $this->db->table('work_orders w')->select('w.*, f.name as facility_name, u.name as assigned_name')->join('facilities f','f.id=w.facility_id','left')->join('users u','u.id=w.assigned_to','left')->where('DATE(w.created_at) >=',$from)->where('DATE(w.created_at) <=',$to);
        $this->scopeFacilities($q, 'w.facility_id');
        if ($facility) $q->where('w.facility_id',$facility);
        $wos      = $q->get()->getResultArray();
        $total    = count($wos);
        $breached = count(array_filter($wos,fn($w)=>$w['sla_breached']==1));
        $compliance = $total>0?round((($total-$breached)/$total)*100,1):100;
        $byPriority = [];
        foreach ($wos as $w) { $p=$w['priority']; if(!isset($byPriority[$p]))$byPriority[$p]=['total'=>0,'breached'=>0]; $byPriority[$p]['total']++; if($w['sla_breached'])$byPriority[$p]['breached']++; }
        $facilities = $this->scopeCompany($this->db->table('facilities')->where('status','active'))->get()->getResultArray();

        return view('reports/sla', $this->viewData([
            'title'=>'SLA Report','wos'=>$wos,'total'=>$total,'breached'=>$breached,
            'compliance'=>$compliance,'byPriority'=>$byPriority,'from'=>$from,'to'=>$to,'facilities'=>$facilities,'filterFacility'=>$facility,
        ]));
    }

    public function assets()
    {
        $facility = (int)($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status') ?? '';
        $q = $this->db->table('assets a')->select('a.*, f.name as facility_name')->join('facilities f','f.id=a.facility_id','left');
        $this->scopeFacilities($q, 'a.facility_id');
        if ($facility) $q->where('a.facility_id',$facility);
        if ($status)   $q->where('a.status',$status);
        $assets     = $q->orderBy('a.health_score','ASC')->get()->getResultArray();
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('reports/assets', $this->viewData(['title'=>'Asset Report','assets'=>$assets,'facilities'=>$facilities,'filterFacility'=>$facility,'filterStatus'=>$status]));
    }

    public function occupancy()
    {
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
            WHERE f.status='active'
            GROUP BY f.id, f.name, f.code
            ORDER BY occupancy_pct DESC
        ")->getResultArray();

        $expiringContracts = $this->db->table('units u')
            ->select('u.*, f.name as facility_name')
            ->join('facilities f','f.id=u.facility_id','left')
            ->where('u.status','occupied')
            ->where('u.contract_end >=',date('Y-m-d'))
            ->where('u.contract_end <=',date('Y-m-d',strtotime('+60 days')))
            ->orderBy('u.contract_end','ASC')->get()->getResultArray();

        return view('reports/occupancy', $this->viewData([
            'title'=>'Occupancy Report','facilities'=>$facilities,'expiringContracts'=>$expiringContracts,
        ]));
    }

    public function contracts()
    {
        $status   = $this->request->getGet('status')   ?? '';
        $facility = (int)($this->request->getGet('facility') ?? 0);
        $expiring = $this->request->getGet('expiring')  ?? '';

        $q = $this->db->table('contracts c')
            ->select('c.*, f.name as facility_name, u.unit_number')
            ->join('facilities f','f.id=c.facility_id','left')
            ->join('units u','u.id=c.unit_id','left');
        $this->scopeFacilities($q, 'c.facility_id');
        if ($status)   $q->where('c.status',$status);
        if ($facility) $q->where('c.facility_id',$facility);
        if ($expiring) $q->where('c.end_date <=',date('Y-m-d',strtotime('+60 days')))->where('c.end_date >=',date('Y-m-d'));
        $contracts  = $q->orderBy('c.end_date','ASC')->get()->getResultArray();
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();

        return view('reports/contracts', $this->viewData([
            'title'=>'Contract Expiry Report','contracts'=>$contracts,'facilities'=>$facilities,
            'filterStatus'=>$status,'filterFacility'=>$facility,'filterExpiring'=>$expiring,
        ]));
    }

    public function technicianPerformance()
    {
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $techs = $this->db->query("
            SELECT u.id, u.name, u.email,
              COUNT(wo.id) AS total_assigned,
              SUM(CASE WHEN wo.status='completed' THEN 1 ELSE 0 END) AS completed,
              SUM(CASE WHEN wo.sla_breached=1 THEN 1 ELSE 0 END) AS sla_breached,
              ROUND(AVG(NULLIF(TIMESTAMPDIFF(HOUR,wo.created_at,wo.completed_at),0)),1) AS avg_resolution_hours,
              ROUND(SUM(CASE WHEN wo.status='completed' THEN 1 ELSE 0 END)/NULLIF(COUNT(wo.id),0)*100,1) AS completion_rate,
              COALESCE(SUM(wl.labor_cost),0) AS total_labor_cost
            FROM users u
            JOIN roles r ON r.id=u.role_id AND r.name='technician'
            LEFT JOIN work_orders wo ON wo.assigned_to=u.id AND DATE(wo.created_at) BETWEEN ? AND ?
            LEFT JOIN wo_labor wl ON wl.user_id=u.id AND DATE(wl.work_date) BETWEEN ? AND ?
            WHERE u.status='active'
            GROUP BY u.id, u.name, u.email
            ORDER BY completion_rate DESC
        ", [$from,$to,$from,$to])->getResultArray();

        return view('reports/technician', $this->viewData([
            'title'=>'Technician Performance','techs'=>$techs,'from'=>$from,'to'=>$to,
        ]));
    }

    public function procurement()
    {
        $from = $this->request->getGet('from') ?? date('Y-01-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $orders = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name')
            ->join('vendors v','v.id=po.vendor_id','left')
            ->where('DATE(po.created_at) >=',$from)->where('DATE(po.created_at) <=',$to)
            ->orderBy('po.created_at','DESC')->get()->getResultArray();

        $requests = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name, u.name as requested_by_name')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->join('users u','u.id=pr.requested_by','left')
            ->where('DATE(pr.created_at) >=',$from)->where('DATE(pr.created_at) <=',$to)
            ->get()->getResultArray();

        $totalSpend = array_sum(array_map(fn($o)=>in_array($o['status'],['approved','delivered'])?(float)$o['total_amount']:0,$orders));
        $byVendor   = [];
        foreach ($orders as $o) {
            $v = $o['vendor_name']??'Unknown';
            if (!isset($byVendor[$v])) $byVendor[$v] = ['orders'=>0,'spend'=>0];
            $byVendor[$v]['orders']++;
            $byVendor[$v]['spend'] += (float)$o['total_amount'];
        }
        arsort($byVendor);

        return view('reports/procurement', $this->viewData([
            'title'=>'Procurement Report','orders'=>$orders,'requests'=>$requests,
            'totalSpend'=>$totalSpend,'byVendor'=>$byVendor,'from'=>$from,'to'=>$to,
        ]));
    }

    public function inventory()
    {
        $items = $this->db->table('inventory_items')
            ->orderBy('quantity','ASC')->get()->getResultArray();
        $movements = $this->db->query("
            SELECT i.name as item_name, i.item_code,
              SUM(CASE WHEN m.movement_type='in' THEN m.quantity ELSE 0 END) AS total_in,
              SUM(CASE WHEN m.movement_type='out' THEN m.quantity ELSE 0 END) AS total_out,
              COUNT(m.id) AS move_count
            FROM inventory_items i
            LEFT JOIN stock_movements m ON m.item_id=i.id
              AND DATE(m.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY i.id, i.name, i.item_code
            ORDER BY total_out DESC
            LIMIT 20
        ")->getResultArray();
        $totalValue = array_sum(array_map(fn($i)=>(float)$i['quantity']*(float)$i['unit_cost'],$items));

        return view('reports/inventory', $this->viewData([
            'title'=>'Inventory Report','items'=>$items,'movements'=>$movements,'totalValue'=>$totalValue,
        ]));
    }


    public function activityLog()
    {
        $this->requireRole('super_admin');

        $from    = $this->request->getGet('from') ?? date('Y-m-01');
        $to      = $this->request->getGet('to')   ?? date('Y-m-d');
        $module  = $this->request->getGet('module');
        $action  = $this->request->getGet('action');
        $userId  = (int) ($this->request->getGet('user_id') ?? 0);
        $perPage = 50;
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $builder = $this->db->table('activity_logs al')
            ->select('al.*, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id=al.user_id', 'left')
            ->where('DATE(al.created_at) >=', $from)
            ->where('DATE(al.created_at) <=', $to);

        if ($module) {
            $builder->where('al.module', $module);
        }
        if ($action) {
            $builder->where('al.action', $action);
        }
        if ($userId > 0) {
            $builder->where('al.user_id', $userId);
        }

        $total = (clone $builder)->countAllResults(false);
        $logs  = $builder->orderBy('al.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return view('reports/activity_log', $this->viewData([
            'title'        => 'System Activity Log',
            'logs'         => $logs,
            'total'        => $total,
            'perPage'      => $perPage,
            'currentPage'  => $page,
            'from'         => $from,
            'to'           => $to,
            'filterModule' => $module,
            'filterAction' => $action,
            'filterUser'   => $userId,
            'users'        => $this->db->table('users')->select('id, name')->orderBy('name')->limit(200)->get()->getResultArray(),
        ]));
    }

    public function builder()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        $saved = [];
        if ($this->db->tableExists('report_saved_queries')) {
            $saved = $this->db->table('report_saved_queries')
                ->where('user_id', session()->get('user_id'))
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->get()
                ->getResultArray();
        }

        $prefill = [];
        $loadId  = (int) ($this->request->getGet('load') ?? 0);
        if ($loadId > 0 && $this->db->tableExists('report_saved_queries')) {
            $row = $this->db->table('report_saved_queries')
                ->where('id', $loadId)
                ->where('user_id', session()->get('user_id'))
                ->get()
                ->getRowArray();
            if ($row) {
                $prefill = [
                    'report_type' => $row['report_type'],
                    'columns'     => json_decode($row['columns_json'] ?? '[]', true) ?: [],
                    'filters'     => json_decode($row['filters_json'] ?? '{}', true) ?: [],
                    'show_cost'   => ! empty($row['show_cost']),
                    'name'        => $row['name'],
                ];
            }
        }

        $units = [];
        if ($this->db->tableExists('units')) {
            $units = $this->db->table('units u')
                ->select('u.id, u.unit_number, u.facility_id, f.name AS facility_name')
                ->join('facilities f', 'f.id = u.facility_id', 'left')
                ->orderBy('f.name', 'ASC')
                ->orderBy('u.unit_number', 'ASC')
                ->get()->getResultArray();
        }

        return view('reports/builder', $this->viewData([
            'title'      => 'Custom Report Builder',
            'types'      => ReportBuilderService::TYPES,
            'columns'    => ReportBuilderService::COLUMNS,
            'saved'      => $saved,
            'prefill'    => $prefill,
            'facilities' => $this->db->table('facilities')->where('status', 'active')->get()->getResultArray(),
            'units'      => $units,
        ]));
    }

    public function builderRun()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        $type     = (string) $this->request->getPost('report_type');
        $columns  = (array) ($this->request->getPost('columns') ?? []);
        $showCost = (bool) $this->request->getPost('show_cost');
        $filters  = [
            'from'     => $this->request->getPost('from') ?? date('Y-m-01'),
            'to'       => $this->request->getPost('to') ?? date('Y-m-d'),
            'facility' => (int) ($this->request->getPost('facility') ?? 0),
            'unit'     => (int) ($this->request->getPost('unit') ?? 0),
        ];
        if ($columns === []) {
            $columns = ReportBuilderService::COLUMNS[$type] ?? ['wo_number'];
        }
        $result = (new ReportBuilderService($this->db))->run($type, $columns, $filters, $showCost);

        return view('reports/builder_result', $this->viewData([
            'title'   => ReportBuilderService::TYPES[$type] ?? 'Report',
            'headers' => $result['headers'],
            'rows'    => $result['rows'],
            'type'    => $type,
            'columns' => $columns,
            'filters' => $filters,
            'showCost'=> $showCost,
        ]));
    }

    public function builderSave()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        if (! $this->db->tableExists('report_saved_queries')) {
            return redirect()->to(base_url('reports/builder'))->with('error', 'Run php spark migrate first.');
        }
        $this->db->table('report_saved_queries')->insert([
            'user_id'      => session()->get('user_id'),
            'name'         => $this->request->getPost('save_name') ?: 'Saved report',
            'report_type'  => $this->request->getPost('report_type'),
            'columns_json' => json_encode($this->request->getPost('columns') ?? []),
            'filters_json' => json_encode([
                'from'     => $this->request->getPost('from'),
                'to'       => $this->request->getPost('to'),
                'facility' => $this->request->getPost('facility'),
                'unit'     => $this->request->getPost('unit'),
            ]),
            'show_cost'    => $this->request->getPost('show_cost') ? 1 : 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('reports/builder'))->with('success', 'Report template saved.');
    }

    public function builderDelete(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        if ($this->db->tableExists('report_saved_queries')) {
            $this->db->table('report_saved_queries')
                ->where('id', $id)
                ->where('user_id', session()->get('user_id'))
                ->delete();
        }

        return redirect()->to(base_url('reports/builder'))->with('success', 'Template removed.');
    }

    public function profit()
    {
        return redirect()->to(base_url('reports/pnl'));
    }

    public function pnl()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');

        $from       = $this->request->getGet('from')       ?? date('Y-01-01');
        $to         = $this->request->getGet('to')         ?? date('Y-m-d');
        $groupBy    = $this->request->getGet('group_by')   ?? 'property';
        $period     = $this->request->getGet('period')     ?? 'month';
        $facilityId = (int)($this->request->getGet('facility') ?? 0);
        $currency   = $this->settings['currency'] ?? 'QAR';

        $invQ = $this->db->table('invoices i')
            ->select('i.facility_id, i.work_order_id, i.contract_id,
                      i.total AS revenue, i.issue_date,
                      f.name AS facility_name,
                      c.client_name AS customer_name,
                      w.wo_number,
                      w.estimated_cost AS wo_estimated_cost,
                      w.actual_cost AS wo_actual_cost')
            ->join('facilities f', 'f.id = i.facility_id', 'left')
            ->join('contracts c',  'c.id = i.contract_id', 'left')
            ->join('work_orders w','w.id = i.work_order_id','left')
            ->whereIn('i.status', ['paid', 'partial'])
            ->where('DATE(i.issue_date) >=', $from)
            ->where('DATE(i.issue_date) <=', $to)
            ->where('i.deleted_at', null);
        $this->scopeFacilities($invQ, 'i.facility_id');
        if ($facilityId) $invQ->where('i.facility_id', $facilityId);
        $invoices = $invQ->get()->getResultArray();

        $expQ = $this->db->table('expenses e')
            ->select('e.facility_id, e.amount AS cost, e.expense_date, f.name AS facility_name')
            ->join('facilities f', 'f.id = e.facility_id', 'left')
            ->whereIn('e.status', ['approved', 'paid'])
            ->where('DATE(e.expense_date) >=', $from)
            ->where('DATE(e.expense_date) <=', $to);
        $this->scopeFacilities($expQ, 'e.facility_id');
        if ($facilityId) $expQ->where('e.facility_id', $facilityId);
        $expenses = $expQ->get()->getResultArray();

        $woCosts = [];
        if ($this->db->tableExists('maintenance_costing')) {
            $mcQ = $this->db->table('maintenance_costing mc')
                ->select('mc.wo_id, mc.total_cost, w.wo_number, w.facility_id, f.name AS facility_name')
                ->join('work_orders w', 'w.id = mc.wo_id', 'left')
                ->join('facilities f',  'f.id = w.facility_id', 'left')
                ->where('DATE(mc.created_at) >=', $from)
                ->where('DATE(mc.created_at) <=', $to);
            $this->scopeFacilities($mcQ, 'w.facility_id');
            if ($facilityId) $mcQ->where('w.facility_id', $facilityId);
            $woCosts = $mcQ->get()->getResultArray();
        }

        $periodLabel = function(string $date) use ($period): string {
            return match ($period) {
                'day'   => date('d M Y', strtotime($date)),
                'year'  => date('Y',     strtotime($date)),
                default => date('M Y',   strtotime($date)),
            };
        };

        $rows = []; $totRev = 0.0; $totCst = 0.0; $totEstCst = 0.0;

        if ($groupBy === 'property') {
            $map = [];
            foreach ($invoices as $inv) {
                $key = $inv['facility_name'] ?: 'Unknown';
                $pl  = $periodLabel($inv['issue_date']);
                $map[$key][$pl]['revenue']        = ($map[$key][$pl]['revenue'] ?? 0) + (float)$inv['revenue'];
                $map[$key][$pl]['cost']           = $map[$key][$pl]['cost'] ?? 0;
                $map[$key][$pl]['estimated_cost'] = ($map[$key][$pl]['estimated_cost'] ?? 0) + (float)($inv['wo_estimated_cost'] ?? 0);
                // Use actual_cost from WO if available, else track separately
                $map[$key][$pl]['actual_cost']    = ($map[$key][$pl]['actual_cost'] ?? 0) + (float)($inv['wo_actual_cost'] ?? 0);
            }
            foreach ($expenses as $exp) {
                $key = $exp['facility_name'] ?: 'Unknown';
                $pl  = $periodLabel($exp['expense_date']);
                $map[$key][$pl]['cost']           = ($map[$key][$pl]['cost'] ?? 0) + (float)$exp['cost'];
                $map[$key][$pl]['revenue']        = $map[$key][$pl]['revenue'] ?? 0;
                $map[$key][$pl]['estimated_cost'] = $map[$key][$pl]['estimated_cost'] ?? 0;
                $map[$key][$pl]['actual_cost']    = $map[$key][$pl]['actual_cost'] ?? 0;
            }
            foreach ($map as $prop => $periods) {
                foreach ($periods as $pl => $data) {
                    $rev  = (float)($data['revenue'] ?? 0);
                    $cst  = (float)($data['cost'] ?? 0) ?: (float)($data['actual_cost'] ?? 0);
                    $est  = (float)($data['estimated_cost'] ?? 0);
                    $rows[] = ['group' => $prop, 'period' => $pl, 'revenue' => $rev, 'cost' => $cst, 'estimated_cost' => $est, 'profit' => $rev - $cst];
                    $totRev += $rev; $totCst += $cst; $totEstCst += $est;
                }
            }
        } elseif ($groupBy === 'workorder') {
            $map = [];
            foreach ($invoices as $inv) {
                $key = $inv['wo_number'] ?: ('Facility: ' . ($inv['facility_name'] ?: '?'));
                $pl  = $periodLabel($inv['issue_date']);
                $map[$key][$pl]['revenue']        = ($map[$key][$pl]['revenue'] ?? 0) + (float)$inv['revenue'];
                $map[$key][$pl]['cost']           = $map[$key][$pl]['cost'] ?? 0;
                $map[$key][$pl]['estimated_cost'] = ($map[$key][$pl]['estimated_cost'] ?? 0) + (float)($inv['wo_estimated_cost'] ?? 0);
                $map[$key][$pl]['actual_cost']    = ($map[$key][$pl]['actual_cost'] ?? 0) + (float)($inv['wo_actual_cost'] ?? 0);
            }
            foreach ($woCosts as $wc) {
                $key = $wc['wo_number'] ?: 'Unlinked';
                $pl  = $periodLabel(date('Y-m-d'));
                $map[$key][$pl]['cost']           = ($map[$key][$pl]['cost'] ?? 0) + (float)$wc['total_cost'];
                $map[$key][$pl]['revenue']        = $map[$key][$pl]['revenue'] ?? 0;
                $map[$key][$pl]['estimated_cost'] = $map[$key][$pl]['estimated_cost'] ?? 0;
                $map[$key][$pl]['actual_cost']    = $map[$key][$pl]['actual_cost'] ?? 0;
            }
            foreach ($map as $wo => $periods) {
                foreach ($periods as $pl => $data) {
                    $rev  = (float)($data['revenue'] ?? 0);
                    $cst  = (float)($data['cost'] ?? 0) ?: (float)($data['actual_cost'] ?? 0);
                    $est  = (float)($data['estimated_cost'] ?? 0);
                    $rows[] = ['group' => $wo, 'period' => $pl, 'revenue' => $rev, 'cost' => $cst, 'estimated_cost' => $est, 'profit' => $rev - $cst];
                    $totRev += $rev; $totCst += $cst; $totEstCst += $est;
                }
            }
        } else { // customer
            $map = [];
            foreach ($invoices as $inv) {
                $key = $inv['customer_name'] ?: 'Direct / Ad-hoc';
                $pl  = $periodLabel($inv['issue_date']);
                $map[$key][$pl]['revenue']        = ($map[$key][$pl]['revenue'] ?? 0) + (float)$inv['revenue'];
                $map[$key][$pl]['cost']           = $map[$key][$pl]['cost'] ?? 0;
                $map[$key][$pl]['estimated_cost'] = ($map[$key][$pl]['estimated_cost'] ?? 0) + (float)($inv['wo_estimated_cost'] ?? 0);
                $map[$key][$pl]['actual_cost']    = ($map[$key][$pl]['actual_cost'] ?? 0) + (float)($inv['wo_actual_cost'] ?? 0);
            }
            foreach ($expenses as $exp) {
                $key = $exp['facility_name'] ?: 'Direct / Ad-hoc';
                $pl  = $periodLabel($exp['expense_date']);
                $map[$key][$pl]['cost']           = ($map[$key][$pl]['cost'] ?? 0) + (float)$exp['cost'];
                $map[$key][$pl]['revenue']        = $map[$key][$pl]['revenue'] ?? 0;
                $map[$key][$pl]['estimated_cost'] = $map[$key][$pl]['estimated_cost'] ?? 0;
                $map[$key][$pl]['actual_cost']    = $map[$key][$pl]['actual_cost'] ?? 0;
            }
            foreach ($map as $cust => $periods) {
                foreach ($periods as $pl => $data) {
                    $rev  = (float)($data['revenue'] ?? 0);
                    $cst  = (float)($data['cost'] ?? 0) ?: (float)($data['actual_cost'] ?? 0);
                    $est  = (float)($data['estimated_cost'] ?? 0);
                    $rows[] = ['group' => $cust, 'period' => $pl, 'revenue' => $rev, 'cost' => $cst, 'estimated_cost' => $est, 'profit' => $rev - $cst];
                    $totRev += $rev; $totCst += $cst; $totEstCst += $est;
                }
            }
        }

        usort($rows, fn($a,$b) => strcmp($a['group'].$a['period'], $b['group'].$b['period']));

        $facilities = $this->scopeCompany(
            $this->db->table('facilities')->where('status', 'active')
        )->get()->getResultArray();

        return view('reports/pnl', $this->viewData([
            'title'      => 'P&L Report',
            'rows'       => $rows,
            'totRevenue' => $totRev,
            'totCost'    => $totCst,
            'totEstCost' => $totEstCst,
            'totProfit'  => $totRev - $totCst,
            'from'       => $from,
            'to'         => $to,
            'groupBy'    => $groupBy,
            'period'     => $period,
            'facilityId' => $facilityId,
            'facilities' => $facilities,
            'currency'   => $currency,
        ]));
    }

    public function qc()
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to') ?? date('Y-m-d');
        $rows = $this->db->table('work_orders w')
            ->select('w.wo_number, w.qa_status, w.qa_approved_at, w.client_approval_status, w.workflow_stage, f.name as facility_name')
            ->join('facilities f', 'f.id = w.facility_id', 'left')
            ->whereIn('w.qa_status', ['pending', 'approved', 'rejected'])
            ->where('DATE(w.updated_at) >=', $from)
            ->where('DATE(w.updated_at) <=', $to)
            ->orderBy('w.updated_at', 'DESC')
            ->limit(300)
            ->get()
            ->getResultArray();

        return view('reports/qc', $this->viewData([
            'title' => 'QC / QA Report',
            'rows'  => $rows,
            'from'  => $from,
            'to'    => $to,
        ]));
    }

    public function export(string $type, string $format)
    {
        // Build data based on type
        $data = [];
        $headers = [];
        $filename = $type.'_report_'.date('Y-m-d');

        switch ($type) {
            case 'workorders':
                $headers = ['WO #','Title','Facility','Status','Priority','Assigned','Created','Completed','Actual Cost'];
                $rows    = $this->db->table('work_orders w')->select('w.wo_number,w.title,f.name as facility_name,w.status,w.priority,u.name as assigned_name,w.created_at,w.completed_at,w.actual_cost')->join('facilities f','f.id=w.facility_id','left')->join('users u','u.id=w.assigned_to','left')->orderBy('w.created_at','DESC')->get()->getResultArray();
                foreach ($rows as $r) $data[] = [$r['wo_number'],$r['title'],$r['facility_name'],$r['status'],$r['priority'],$r['assigned_name']??'—',date('d M Y',strtotime($r['created_at'])),($r['completed_at']?date('d M Y',strtotime($r['completed_at'])):'—'),number_format($r['actual_cost']??0,2)];
                break;
            case 'finance':
                $headers = ['Invoice #','Facility','Issue Date','Due Date','Subtotal','VAT','Total','Status'];
                $rows    = $this->db->table('invoices i')->select('i.*,f.name as facility_name')->join('facilities f','f.id=i.facility_id','left')->orderBy('i.issue_date','DESC')->get()->getResultArray();
                foreach ($rows as $r) $data[] = [$r['invoice_number'],$r['facility_name'],date('d M Y',strtotime($r['issue_date'])),date('d M Y',strtotime($r['due_date'])),number_format($r['subtotal'],2),number_format($r['vat_amount'],2),number_format($r['total'],2),$r['status']];
                break;
            case 'inventory':
                $headers = ['Code','Name','Category','Qty','Min Qty','Unit Cost','Total Value','Location'];
                $rows    = $this->db->table('inventory_items')->orderBy('name','ASC')->get()->getResultArray();
                foreach ($rows as $r) $data[] = [$r['item_code'],$r['name'],$r['category'],$r['quantity'],$r['min_quantity'],number_format($r['unit_cost'],2),number_format($r['quantity']*$r['unit_cost'],2),$r['location']??'—'];
                break;
            case 'activity':
                $headers = ['Date/Time','User','Action','Module','Record ID','Description','IP'];
                $rows    = $this->db->table('activity_logs al')->select('al.*,u.name as user_name')->join('users u','u.id=al.user_id','left')->orderBy('al.created_at','DESC')->limit(5000)->get()->getResultArray();
                foreach ($rows as $r) $data[] = [date('Y-m-d H:i:s',strtotime($r['created_at'])),$r['user_name']??'System',$r['action'],$r['module'],$r['record_id']??'',$r['description']??'',$r['ip_address']??''];
                break;
            case 'technician':
                $headers = ['Name','Total Assigned','Completed','Completion %','Avg Hours','SLA Breached'];
                $rows    = $this->db->query("SELECT u.name,COUNT(wo.id) t,SUM(CASE WHEN wo.status='completed' THEN 1 ELSE 0 END) c,SUM(wo.sla_breached) b,ROUND(AVG(TIMESTAMPDIFF(HOUR,wo.created_at,wo.completed_at)),1) h FROM users u JOIN roles r ON r.id=u.role_id AND r.name='technician' LEFT JOIN work_orders wo ON wo.assigned_to=u.id GROUP BY u.id,u.name ORDER BY c DESC")->getResultArray();
                foreach ($rows as $r) $data[] = [$r['name'],$r['t'],$r['c'],($r['t']>0?round($r['c']/$r['t']*100,1):0).'%',$r['h']??0,$r['b']];
                break;
            case 'profit':
                $headers = ['WO #','Facility','Total Cost','Revenue','Profit'];
                if ($this->db->tableExists('maintenance_costing')) {
                    $rows = $this->db->table('maintenance_costing mc')
                        ->select('w.wo_number, f.name as facility_name, mc.total_cost, mc.revenue, mc.profit')
                        ->join('work_orders w', 'w.id = mc.wo_id', 'left')
                        ->join('facilities f', 'f.id = w.facility_id', 'left')
                        ->orderBy('mc.id', 'DESC')->limit(5000)->get()->getResultArray();
                    foreach ($rows as $r) {
                        $data[] = [$r['wo_number'], $r['facility_name'], number_format((float) ($r['total_cost'] ?? 0), 2), number_format((float) ($r['revenue'] ?? 0), 2), number_format((float) ($r['profit'] ?? 0), 2)];
                    }
                }
                break;
            case 'qc':
                $headers = ['WO #','QA Status','QA Date','Client Approval','Facility'];
                $rows = $this->db->table('work_orders w')
                    ->select('w.wo_number, w.qa_status, w.qa_approved_at, w.client_approval_status, f.name as facility_name')
                    ->join('facilities f', 'f.id = w.facility_id', 'left')
                    ->whereIn('w.qa_status', ['pending', 'approved', 'rejected'])
                    ->orderBy('w.updated_at', 'DESC')->limit(5000)->get()->getResultArray();
                foreach ($rows as $r) {
                    $data[] = [$r['wo_number'], $r['qa_status'], $r['qa_approved_at'] ?? '—', $r['client_approval_status'] ?? '—', $r['facility_name']];
                }
                break;
            default:
                return redirect()->to(base_url('reports'))->with('error','Unknown report type.');
        }

        if ($format === 'csv') {
            $this->response->setHeader('Content-Type','text/csv');
            $this->response->setHeader('Content-Disposition','attachment; filename="'.$filename.'.csv"');
            $out = fopen('php://output','w');
            fputcsv($out,$headers);
            foreach ($data as $row) fputcsv($out,$row);
            fclose($out);
            return $this->response;
        }

        if ($format === 'pdf' && class_exists(\Dompdf\Dompdf::class)) {
            $html = '<html><head><meta charset="utf-8"><style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:6px;font-size:11px}th{background:#f0f0f0}</style></head><body>';
            $html .= '<h2>' . htmlspecialchars(ucfirst($type) . ' Report') . '</h2><table><thead><tr>';
            foreach ($headers as $h) {
                $html .= '<th>' . htmlspecialchars($h) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></body></html>';
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.pdf"')
                ->setBody($dompdf->output());
        }

        // HTML table fallback for PDF/Excel
        $this->response->setHeader('Content-Type','application/vnd.ms-excel');
        $this->response->setHeader('Content-Disposition','attachment; filename="'.$filename.'.xls"');
        $html = '<table border="1"><thead><tr>';
        foreach ($headers as $h) $html .= '<th>'.htmlspecialchars($h).'</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($data as $row) { $html .= '<tr>'; foreach ($row as $cell) $html .= '<td>'.htmlspecialchars((string)$cell).'</td>'; $html .= '</tr>'; }
        $html .= '</tbody></table>';
        return $this->response->setBody($html);
    }

    /**
     * Internal financial workflow reports hub.
     */
    public function financialInternal()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');

        $from       = $this->request->getGet('from') ?? date('Y-m-01');
        $to         = $this->request->getGet('to')   ?? date('Y-m-d');
        $report     = $this->request->getGet('report') ?? 'est_vs_act';
        $facilityId = (int) ($this->request->getGet('facility') ?? 0) ?: null;

        $svc = new \App\Services\FinancialReportService($this->db);
        $rows = match ($report) {
            'wo_profit'  => $svc->workOrderProfitability($from, $to, $facilityId),
            'materials'  => $svc->materialVariance($from, $to, $facilityId),
            'monthly'    => $svc->monthlyFinancialSummary(12),
            default      => $svc->estimatedVsActual($from, $to, $facilityId),
        };

        $facilities = $this->scopeCompany($this->db->table('facilities')->where('status', 'active'))->get()->getResultArray();

        return view('reports/financial_internal', $this->viewData([
            'title'          => 'Internal Financial Reports',
            'rows'           => $rows,
            'report'         => $report,
            'from'           => $from,
            'to'             => $to,
            'filterFacility' => $facilityId ?? 0,
            'facilities'     => $facilities,
        ]));
    }
}
