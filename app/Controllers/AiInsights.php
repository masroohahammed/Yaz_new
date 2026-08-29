<?php
namespace App\Controllers;

/**
 * AI Insights — Predictive Maintenance, Risk Analytics, Cost Optimization
 * Uses internal rule-based analysis — no external API required.
 */
class AiInsights extends BaseController
{
    public function index()
    {
        $assetQ = $this->db->table('assets')->where('status', 'active');
        $this->scopeFacilities($assetQ, 'facility_id');
        $assets = $assetQ->orderBy('health_score', 'ASC')->limit(10)->get()->getResultArray();

        $woQ = $this->db->table('work_orders')->where('sla_breached', 1)->whereIn('status', ['new', 'assigned', 'in_progress']);
        $this->scopeFacilities($woQ, 'facility_id');
        $breachedWO = $woQ->countAllResults();

        $invQ = $this->db->table('inventory_items')->where('quantity <= min_quantity', null, false);
        if ($this->db->fieldExists('company_id', 'inventory_items')) {
            $this->scopeCompany($invQ, 'company_id');
        }
        $lowStock = $invQ->countAllResults();
        $expiringDocs = $this->db->table('compliance_documents')->where('expiry_date <=',date('Y-m-d',strtotime('+30 days')))->where('expiry_date >=',date('Y-m-d'))->countAllResults();

        return view('ai/index', $this->viewData([
            'title'        => 'AI Insights',
            'assets'       => $assets,
            'breachedWO'   => $breachedWO,
            'lowStock'     => $lowStock,
            'expiringDocs' => $expiringDocs,
        ]));
    }

    public function predictive()
    {
        if ($this->request->isAJAX() || $this->request->getGet('format') === 'json') {
            return $this->predictiveMaintenance();
        }
        $json = json_decode($this->predictiveMaintenance()->getBody(), true);

        return view('ai/report', $this->viewData([
            'title'    => 'Predictive Maintenance',
            'subtitle' => 'AI rule-based asset health analysis',
            'icon'     => 'bi-graph-up-arrow',
            'content'  => $json['analysis'] ?? 'No analysis available.',
        ]));
    }

    public function risk()
    {
        if ($this->request->isAJAX() || $this->request->getGet('format') === 'json') {
            return $this->riskAnalysis();
        }
        $json = json_decode($this->riskAnalysis()->getBody(), true);

        return view('ai/report', $this->viewData([
            'title'    => 'Facility Risk Analysis',
            'subtitle' => 'Risk scoring by facility',
            'icon'     => 'bi-shield-exclamation',
            'content'  => $json['analysis'] ?? 'No analysis available.',
        ]));
    }

    public function cost()
    {
        if ($this->request->isAJAX() || $this->request->getGet('format') === 'json') {
            return $this->costOptimization();
        }
        $json = json_decode($this->costOptimization()->getBody(), true);

        return view('ai/report', $this->viewData([
            'title'    => 'Cost Optimization',
            'subtitle' => 'Spend analysis and recommendations',
            'icon'     => 'bi-piggy-bank',
            'content'  => $json['suggestions'] ?? $json['analysis'] ?? 'No suggestions available.',
        ]));
    }

    public function predictiveMaintenance()
    {
        $assets = $this->db->table('assets')->where('status','active')->orderBy('health_score','ASC')->limit(20)->get()->getResultArray();
        $lines  = [];
        foreach ($assets as $a) {
            $score  = (int)$a['health_score'];
            $name   = $a['name'] . ' (' . $a['asset_code'] . ')';
            $daysSinceMaint = $a['last_maintenance'] ? floor((time() - strtotime($a['last_maintenance'])) / 86400) : 999;
            $daysUntilNext  = $a['next_maintenance'] ? floor((strtotime($a['next_maintenance']) - time()) / 86400) : null;

            if ($score < 50) {
                $risk   = '🔴 CRITICAL';
                $action = 'Immediate inspection required. High failure probability within 7–14 days.';
            } elseif ($score < 70) {
                $risk   = '🟠 HIGH';
                $action = 'Schedule maintenance within 30 days. Degradation trend detected.';
            } elseif ($score < 85) {
                $risk   = '🟡 MEDIUM';
                $action = 'Monitor closely. Routine maintenance recommended within 60 days.';
            } else {
                continue; // healthy assets — skip
            }

            $nextInfo = $daysUntilNext !== null
                ? ($daysUntilNext < 0 ? "⚠️ Overdue by " . abs($daysUntilNext) . " days" : "Next maintenance in {$daysUntilNext} days")
                : "No maintenance scheduled";

            $lines[] = "{$risk} — {$name}\n  Health: {$score}%  |  Last maintenance: {$daysSinceMaint} days ago  |  {$nextInfo}\n  ➜ {$action}";
        }

        $analysis = empty($lines)
            ? "✅ All monitored assets have a health score above 85%. No immediate maintenance concerns detected."
            : "Predictive Maintenance Report (" . date('d M Y') . ")\n" . str_repeat('─', 50) . "\n\n" . implode("\n\n", $lines);

        return $this->response->setJSON(['status'=>true,'analysis'=>$analysis]);
    }

    public function smartAssign()
    {
        $woId  = (int)$this->request->getPost('wo_id');
        $wo    = $this->db->table('work_orders')->where('id',$woId)->get()->getRowArray();
        if (!$wo) return $this->response->setJSON(['status'=>false,'message'=>'Work order not found']);

        $techs = $this->db->table('employees e')
            ->select('e.id, e.user_id, e.designation, u.name, (SELECT COUNT(*) FROM work_orders WHERE assigned_to=e.user_id AND status IN ("assigned","in_progress")) as active_jobs')
            ->join('users u','u.id=e.user_id','left')
            ->where('e.status','active')
            ->get()->getResultArray();

        if (empty($techs)) return $this->response->setJSON(['status'=>true,'recommendation'=>'No active technicians found.']);

        usort($techs, fn($a,$b) => (int)$a['active_jobs'] - (int)$b['active_jobs']);
        $best = $techs[0];

        $rec = json_encode([
            'recommended_id' => $best['user_id'],
            'name'           => $best['name'],
            'reason'         => "Least loaded technician with {$best['active_jobs']} active job(s). Priority: {$wo['priority']}.",
        ]);

        return $this->response->setJSON(['status'=>true,'recommendation'=>$rec]);
    }

    public function riskAnalysis()
    {
        $facQ = $this->db->table('facilities f')
            ->select('f.id, f.name, AVG(a.health_score) as avg_health, COUNT(DISTINCT a.id) as total_assets, COUNT(DISTINCT w.id) as open_wo, SUM(COALESCE(w.sla_breached,0)) as sla_breaches')
            ->join('assets a', 'a.facility_id=f.id AND a.status="active"', 'left')
            ->join('work_orders w', 'w.facility_id=f.id AND w.status NOT IN ("completed","closed","cancelled")', 'left')
            ->where('f.status', 'active');
        $this->scopeFacilities($facQ, 'f.id');
        $facilities = $facQ->groupBy('f.id')->get()->getResultArray();

        $lines = [];
        foreach ($facilities as $f) {
            $health  = round((float)($f['avg_health'] ?? 100));
            $breaches= (int)($f['sla_breaches'] ?? 0);
            $openWO  = (int)($f['open_wo'] ?? 0);

            $riskScore = 0;
            if ($health < 60)  $riskScore += 3;
            elseif ($health < 80) $riskScore += 1;
            if ($breaches > 5) $riskScore += 3;
            elseif ($breaches > 0) $riskScore += 1;
            if ($openWO > 10)  $riskScore += 2;

            $level = match(true) {
                $riskScore >= 5 => '🔴 HIGH RISK',
                $riskScore >= 3 => '🟠 MEDIUM RISK',
                default         => '🟢 LOW RISK',
            };

            $recs = [];
            if ($health < 70)  $recs[] = "Audit assets with health score < 70%";
            if ($breaches > 0) $recs[] = "Review SLA breach causes ({$breaches} breach(es))";
            if ($openWO > 5)   $recs[] = "Clear backlog of {$openWO} open work orders";
            if (empty($recs))  $recs[] = "Continue routine maintenance schedule";

            $lines[] = "{$level} — {$f['name']}\n  Avg Asset Health: {$health}%  |  Open WO: {$openWO}  |  SLA Breaches: {$breaches}\n  ➜ " . implode(' | ', $recs);
        }

        $analysis = "Facility Risk Analysis (" . date('d M Y') . ")\n" . str_repeat('─', 50) . "\n\n" . implode("\n\n", $lines);
        return $this->response->setJSON(['status'=>true,'analysis'=>$analysis]);
    }

    public function reports()
    {
        // Risk summary from ai_flags + ai_tenant_scores
        $riskFlags = [];
        if ($this->db->tableExists('ai_flags')) {
            $q = $this->db->table('ai_flags f')
                ->select('f.*')
                ->where('f.is_resolved', 0)
                ->orderBy('f.severity', 'DESC')
                ->orderBy('f.created_at', 'DESC');
            $this->scopeCompany($q, 'f.company_id');
            $riskFlags = $q->limit(20)->get()->getResultArray();
        }

        $tenantRisks = [];
        if ($this->db->tableExists('ai_tenant_scores')) {
            $q2 = $this->db->table('ai_tenant_scores ats')
                ->select('ats.*, t.full_name, t.phone');
            if ($this->db->tableExists('tenants')) {
                $q2->join('tenants t', 't.id = ats.tenant_id', 'left');
            }
            $tenantRisks = $q2->whereIn('ats.risk_level', ['high', 'medium'])
                ->orderBy('ats.score', 'ASC')
                ->limit(10)
                ->get()->getResultArray();
        }

        // Occupancy analysis from ai_property_scores
        $propertyScores = [];
        if ($this->db->tableExists('ai_property_scores')) {
            $q3 = $this->db->table('ai_property_scores aps')
                ->select('aps.*, f.name AS facility_name');
            if ($this->db->tableExists('facilities')) {
                $q3->join('facilities f', 'f.id = aps.facility_id', 'left');
            }
            $propertyScores = $q3->orderBy('aps.score', 'ASC')->limit(20)->get()->getResultArray();
        }

        // Live occupancy stats
        $occupancyData = [];
        if ($this->db->tableExists('facilities') && $this->db->tableExists('units')) {
            $facQ = $this->db->table('facilities f')
                ->select('f.id, f.name, COUNT(u.id) AS total_units, SUM(u.status = "occupied") AS occupied')
                ->join('units u', 'u.facility_id = f.id AND u.deleted_at IS NULL', 'left')
                ->where('f.status', 'active')
                ->groupBy('f.id');
            $this->scopeFacilities($facQ, 'f.id');
            $occupancyData = $facQ->get()->getResultArray();
        }

        // 6-month revenue forecast from lease_contracts
        $forecast = [];
        if ($this->db->tableExists('lease_contracts')) {
            $months = [];
            for ($i = 0; $i < 6; $i++) {
                $months[] = date('Y-m', strtotime("+{$i} months"));
            }
            foreach ($months as $month) {
                $start = $month . '-01';
                $end   = date('Y-m-t', strtotime($start));
                $q4 = $this->db->table('lease_contracts')
                    ->selectSum('rent_amount')
                    ->where('status', 'active')
                    ->where('start_date <=', $end)
                    ->where('end_date >=', $start);
                $row = $q4->get()->getRowArray();
                $forecast[$month] = (float) ($row['rent_amount'] ?? 0);
            }
        }

        return view('ai/reports', $this->viewData([
            'title'          => 'AI Reports',
            'riskFlags'      => $riskFlags,
            'tenantRisks'    => $tenantRisks,
            'propertyScores' => $propertyScores,
            'occupancyData'  => $occupancyData,
            'forecast'       => $forecast,
        ]));
    }

    public function costOptimization()
    {
        $expQ = $this->db->table('expenses e')
            ->select('e.category, SUM(e.amount) as total, COUNT(*) as count, AVG(e.amount) as avg_amount')
            ->where('e.status', 'approved')
            ->where('e.expense_date >=', date('Y-m-d', strtotime('-6 months')));
        $this->scopeFacilities($expQ, 'e.facility_id');
        $expenses = $expQ->groupBy('e.category')->orderBy('total', 'DESC')->get()->getResultArray();

        $totalSpend = array_sum(array_column($expenses, 'total'));
        $lines = ["Cost Optimization Report — Last 6 Months (" . date('d M Y') . ")\n" . str_repeat('─', 50)];
        $lines[] = sprintf("Total Approved Spend: %s %s\n", $this->settings['currency'] ?? 'QAR', number_format($totalSpend, 2));

        $suggestions = [];
        foreach ($expenses as $e) {
            $pct = $totalSpend > 0 ? round($e['total'] / $totalSpend * 100) : 0;
            $lines[] = sprintf("• %-22s %s %-10s (%d%%, %d transactions, avg %s)", 
                ucfirst($e['category']), $this->settings['currency'] ?? 'QAR', number_format($e['total'],0), $pct, $e['count'], number_format($e['avg_amount'],0));
            if ($pct > 30) $suggestions[] = "⚠️ '{$e['category']}' is " . $pct . "% of total spend — review contracts and negotiate bulk rates";
            if ((float)$e['avg_amount'] > 5000) $suggestions[] = "💡 High avg transaction in '{$e['category']}' ({$this->settings['currency']} " . number_format($e['avg_amount'],0) . ") — consider competitive tendering";
        }
        if (empty($expenses)) $suggestions[] = "✅ No approved expenses in last 6 months.";
        if (empty($suggestions)) $suggestions[] = "✅ Spend distribution looks balanced. Continue monitoring monthly.";

        $lines[] = "\nRecommendations:";
        foreach ($suggestions as $s) $lines[] = $s;

        return $this->response->setJSON(['status'=>true,'suggestions'=>implode("\n", $lines)]);
    }
}
