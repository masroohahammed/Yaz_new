<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Services\DashboardService;
use App\Services\LandlordReportService;
use App\Support\PmExpenseCategories;

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
        $landlord = (int) ($this->request->getGet('landlord') ?? 0);
        if ($landlord > 0) {
            $facilitySql .= ' AND f.landlord_id = ? ';
            $facilityParams[] = $landlord;
        }

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
        $landlord = (int) ($this->request->getGet('landlord') ?? 0);

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
        if ($landlord > 0) {
            $q->where('f.landlord_id', $landlord);
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
            'filterLandlord' => $landlord,
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
        $landlord = (int) ($this->request->getGet('landlord') ?? 0);

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
        if ($landlord > 0) {
            $q->where('f.landlord_id', $landlord);
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
            'filterLandlord' => $landlord,
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
        $landlord = (int) ($this->request->getGet('landlord') ?? 0);

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
        if ($landlord > 0) {
            $q->where('f.landlord_id', $landlord);
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
            'filterLandlord' => $landlord,
            'totalAmount'    => $totalAmount,
        ]));
    }

    public function expenses()
    {
        $from     = $this->request->getGet('from') ?? date('Y-m-01');
        $to       = $this->request->getGet('to') ?? date('Y-m-d');
        $facility = (int) ($this->request->getGet('facility') ?? 0);
        $status   = $this->request->getGet('status') ?? '';
        $landlord = (int) ($this->request->getGet('landlord') ?? 0);

        $q = $this->db->table('expenses e')
            ->select('e.*, f.name as facility_name')
            ->join('facilities f', 'f.id=e.facility_id', 'left')
            ->where('DATE(e.expense_date) >=', $from)
            ->where('DATE(e.expense_date) <=', $to);
        $this->scopeFacilities($q, 'e.facility_id');

        if ($facility > 0) {
            $q->where('e.facility_id', $facility);
        }
        if ($landlord > 0) {
            $q->where('f.landlord_id', $landlord);
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
            'filterLandlord' => $landlord,
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

    public function landlord()
    {
        $svc      = new LandlordReportService($this->db);
        $companyId = $this->pmCompanyId();
        $forced    = $this->forcedLandlordId();
        $landlordId = $forced ?: (int) ($this->request->getGet('landlord') ?? 0);
        $facilityId = (int) ($this->request->getGet('facility') ?? 0);
        $unitId     = (int) ($this->request->getGet('unit') ?? 0);
        $tenantId   = (int) ($this->request->getGet('tenant') ?? 0);
        $from       = (string) ($this->request->getGet('from') ?: date('Y-m-01'));
        $to         = (string) ($this->request->getGet('to') ?: date('Y-m-d'));
        $payStatus  = (string) ($this->request->getGet('pay_status') ?? '');
        $chqStatus  = (string) ($this->request->getGet('cheque_status') ?? '');
        $mntStatus  = (string) ($this->request->getGet('maint_status') ?? '');
        $expCat     = (string) ($this->request->getGet('expense_category') ?? '');
        $unitStatus = (string) ($this->request->getGet('unit_status') ?? '');
        $leaseStatus = (string) ($this->request->getGet('lease_status') ?? '');
        $expiryDays = (int) ($this->request->getGet('expiry_days') ?? 0);

        $landlords = $svc->landlords($companyId);
        if ($landlordId < 1 && $landlords !== []) {
            $landlordId = (int) $landlords[0]['id'];
        }

        $facilityIds = $landlordId > 0
            ? $svc->facilityIdsForLandlord($landlordId, $companyId, $this->companyScope()->facilityIds(), $facilityId ?: null)
            : [];

        $landlord = null;
        if ($landlordId > 0 && $this->db->tableExists('landlords')) {
            $lq = $this->db->table('landlords')->where('id', $landlordId)->where('deleted_at', null);
            $this->scopeCompany($lq, 'company_id');
            $landlord = $lq->get()->getRowArray();
            if (! $landlord) {
                return redirect()->to(base_url('reports/pm/landlord'))->with('error', 'Landlord not found or not in your company.');
            }
        }

        $overview   = $svc->overview($facilityIds, $from, $to);
        $units      = $svc->units($facilityIds, $unitId ?: null, $unitStatus);
        $tenants    = $svc->tenants($facilityIds);
        $payments   = $svc->collections($facilityIds, $from, $to, $payStatus, $tenantId ?: null);
        $pending    = $svc->pendingCollections($facilityIds);
        $cheques    = $svc->cheques($facilityIds, $from, $to, $chqStatus);
        $maint      = $svc->maintenance($facilityIds, $from, $to, $mntStatus);
        $expenses   = $svc->expenses($facilityIds, $from, $to, $expCat);
        $contracts  = $svc->contracts($facilityIds, $leaseStatus, $expiryDays > 0 ? $expiryDays : null);
        $occupancy  = $svc->occupancy($facilityIds);
        $statement  = $landlordId > 0 ? $svc->statement($facilityIds, $landlordId, $from, $to) : [];
        $pnl        = $svc->pnl($facilityIds, $from, $to);
        $properties = $svc->properties($landlordId > 0
            ? $svc->facilityIdsForLandlord($landlordId, $companyId, $this->companyScope()->facilityIds())
            : []);

        $qs = http_build_query(array_filter([
            'landlord' => $landlordId, 'facility' => $facilityId ?: null, 'from' => $from, 'to' => $to,
            'expense_category' => $expCat !== '' ? $expCat : null,
        ]));

        return view('pm_reports/landlord', $this->viewData([
            'title'        => 'Landlord Reports',
            'landlords'    => $landlords,
            'landlord'     => $landlord,
            'landlordId'   => $landlordId,
            'forcedLandlord' => $forced > 0,
            'properties'   => $properties,
            'facilityId'   => $facilityId,
            'unitId'       => $unitId,
            'tenantId'     => $tenantId,
            'from'         => $from,
            'to'           => $to,
            'payStatus'    => $payStatus,
            'chqStatus'    => $chqStatus,
            'mntStatus'    => $mntStatus,
            'expCat'       => $expCat,
            'unitStatus'   => $unitStatus,
            'leaseStatus'  => $leaseStatus,
            'expiryDays'   => $expiryDays,
            'overview'     => $overview,
            'units'        => $units,
            'tenants'      => $tenants,
            'payments'     => $payments,
            'pending'      => $pending,
            'cheques'      => $cheques,
            'maintenance'  => $maint,
            'expenses'     => $expenses,
            'contracts'    => $contracts,
            'occupancy'    => $occupancy,
            'statement'    => $statement,
            'pnl'          => $pnl,
            'expenseCategories' => PmExpenseCategories::labels(),
            'exportBase'   => base_url('reports/pm/landlord/export') . '?' . $qs,
            'printUrl'     => current_url() . '?' . $qs,
        ]));
    }

    public function landlordExport(string $section = 'overview')
    {
        $svc       = new LandlordReportService($this->db);
        $companyId = $this->pmCompanyId();
        $forced    = $this->forcedLandlordId();
        $landlordId = $forced ?: (int) ($this->request->getGet('landlord') ?? 0);
        $facilityId = (int) ($this->request->getGet('facility') ?? 0);
        $from       = (string) ($this->request->getGet('from') ?: date('Y-m-01'));
        $to         = (string) ($this->request->getGet('to') ?: date('Y-m-d'));
        $expCat     = (string) ($this->request->getGet('expense_category') ?? '');
        $section    = preg_replace('/[^a-z_]/', '', $section) ?: 'overview';
        $format     = strtolower((string) ($this->request->getGet('format') ?? 'csv'));
        if ($format === 'xls') {
            $format = 'excel';
        }
        if (! in_array($format, ['csv', 'pdf', 'excel'], true)) {
            $format = 'csv';
        }

        $facilityIds = $landlordId > 0
            ? $svc->facilityIdsForLandlord($landlordId, $companyId, $this->companyScope()->facilityIds(), $facilityId ?: null)
            : [];

        [$headers, $rows] = $this->landlordExportRows($svc, $section, $facilityIds, $landlordId, $from, $to, $expCat);
        $filename = 'landlord_' . $section . '_' . date('Ymd');

        return $this->tabularExport($filename, 'Landlord ' . str_replace('_', ' ', $section), $headers, $rows, $format);
    }

    /**
     * @param list<int> $facilityIds
     * @return array{0: list<string>, 1: list<array<int|string, mixed>>}
     */
    private function landlordExportRows(LandlordReportService $svc, string $section, array $facilityIds, int $landlordId, string $from, string $to, string $expCat = ''): array
    {
        return match ($section) {
            'units' => [['Property', 'Unit', 'Type', 'Floor', 'Area', 'Tenant', 'Rent', 'Start', 'End', 'Status'],
                array_map(static fn ($r) => [
                    $r['facility_name'] ?? '', $r['unit_number'] ?? '', $r['unit_type'] ?? '', $r['floor'] ?? '',
                    $r['area_sqft'] ?? '', $r['lease_tenant'] ?? $r['tenant_name'] ?? '',
                    $r['lease_rent'] ?? $r['rent_amount'] ?? '', $r['lease_start'] ?? $r['contract_start'] ?? '',
                    $r['lease_end'] ?? $r['contract_end'] ?? '', $r['status'] ?? '',
                ], $svc->units($facilityIds))],
            'tenants' => [['Tenant', 'Phone', 'Email', 'Property', 'Unit', 'Contract', 'Rent', 'Status'],
                array_map(static fn ($r) => [
                    $r['full_name'] ?? '', $r['phone'] ?? '', $r['email'] ?? '', $r['facility_name'] ?? '',
                    $r['unit_number'] ?? '', $r['contract_number'] ?? '', $r['rent_amount'] ?? '', $r['lease_status'] ?? '',
                ], $svc->tenants($facilityIds))],
            'pending' => [['Tenant', 'Property', 'Unit', 'Due', 'Amount', 'Status', 'Days overdue'],
                array_map(static fn ($r) => [
                    $r['tenant_name'] ?? '', $r['facility_name'] ?? '', $r['unit_number'] ?? '',
                    $r['due_date'] ?? '', $r['amount'] ?? '', $r['status'] ?? '', $r['days_overdue'] ?? 0,
                ], $svc->pendingCollections($facilityIds))],
            'cheques' => [['Tenant', 'Property', 'Cheque', 'Bank', 'Cheque date', 'Deposited', 'Cleared', 'Amount', 'Status'],
                array_map(static fn ($r) => [
                    $r['tenant_name'] ?? '', $r['facility_name'] ?? '', $r['cheque_no'] ?? '',
                    $r['bank_name'] ?? '', $r['cheque_date'] ?? '', $r['deposit_date'] ?? '',
                    $r['clearance_date'] ?? '', $r['amount'] ?? '', $r['status'] ?? '',
                ], $svc->cheques($facilityIds, $from, $to))],
            'maintenance' => [['Ticket', 'Property', 'Unit', 'Issue', 'Priority', 'Status', 'Cost'],
                array_map(static fn ($r) => [
                    $r['ticket_number'] ?? '', $r['facility_name'] ?? '', $r['unit_number'] ?? '',
                    $r['description'] ?? '', $r['priority'] ?? '', $r['status'] ?? '', $r['actual_cost'] ?? 0,
                ], $svc->maintenance($facilityIds, $from, $to))],
            'expenses' => [['Date', 'Property', 'Category', 'Description', 'Amount', 'Status'],
                array_map(static fn ($r) => [
                    $r['expense_date'] ?? '', $r['facility_name'] ?? '', $r['category'] ?? '',
                    $r['description'] ?? '', $r['amount'] ?? '', $r['status'] ?? '',
                ], $svc->expenses($facilityIds, $from, $to, $expCat))],
            'contracts' => [['Contract', 'Tenant', 'Property', 'Unit', 'Start', 'End', 'Rent', 'Status'],
                array_map(static fn ($r) => [
                    $r['contract_number'] ?? '', $r['tenant_name'] ?? '', $r['facility_name'] ?? '',
                    $r['unit_number'] ?? '', $r['start_date'] ?? '', $r['end_date'] ?? '',
                    $r['rent_amount'] ?? '', $r['status'] ?? '',
                ], $svc->contracts($facilityIds))],
            'occupancy' => [['Property / month', 'Units', 'Occupied', 'Vacant', 'Maintenance', 'Occupancy %'],
                (static function () use ($svc, $facilityIds) {
                    $occ  = $svc->occupancy($facilityIds);
                    $rows = array_map(static fn ($r) => [
                        $r['name'] ?? '', $r['total_units'] ?? 0, $r['occupied'] ?? 0, $r['vacant'] ?? 0,
                        $r['maintenance'] ?? 0, $r['occupancy_pct'] ?? 0,
                    ], $occ['rows']);
                    foreach ($occ['trend'] as $tr) {
                        $rows[] = [
                            'Trend ' . ($tr['month'] ?? ''),
                            $tr['total_units'] ?? 0,
                            $tr['occupied_units'] ?? $tr['active_leases'] ?? 0,
                            '',
                            '',
                            $tr['occupancy_pct'] ?? '',
                        ];
                    }

                    return $rows;
                })()],
            'statement' => [['Date', 'Property', 'Unit', 'Description', 'Income', 'Expense', 'Payment', 'Balance'],
                array_map(static fn ($r) => [
                    $r['entry_date'] ?? '', $r['facility'] ?? '', $r['unit'] ?? '', $r['description'] ?? '',
                    $r['income'] ?? 0, $r['expense'] ?? 0, $r['payment'] ?? 0, $r['balance'] ?? 0,
                ], $landlordId > 0 ? $svc->statement($facilityIds, $landlordId, $from, $to) : [])],
            'pnl' => [['Metric', 'Amount'], (static function () use ($svc, $facilityIds, $from, $to) {
                $p = $svc->pnl($facilityIds, $from, $to);
                $rows = [
                    ['Rental income', $p['rental']], ['Parking', $p['parking']], ['Service charges', $p['service']],
                    ['Utility recovery', $p['utility']], ['Late fees', $p['late']], ['Other income', $p['other']],
                    ['Collected', $p['collected']], ['Pending', $p['pending']], ['Expenses', $p['expenses']],
                    ['Net income', $p['net']], ['Margin %', $p['margin']],
                ];
                foreach ($p['by_group'] ?? [] as $g => $amt) {
                    $rows[] = ['Expense group: ' . $g, $amt];
                }

                return $rows;
            })()],
            'overview' => [['Metric', 'Value'], (static function () use ($svc, $facilityIds, $from, $to) {
                $o = $svc->overview($facilityIds, $from, $to);

                return [
                    ['Properties', $o['properties']], ['Units', $o['units']], ['Occupied', $o['occupied']],
                    ['Vacant', $o['vacant']], ['Rent due', $o['rent_due']], ['Collected', $o['rent_collected']],
                    ['Pending', $o['rent_pending']], ['Overdue', $o['rent_overdue']],
                    ['Collection %', $o['collection_pct']], ['Expenses', $o['expenses']],
                    ['Net income', $o['net_income']],
                ];
            })()],
            default => [['Payment #', 'Tenant', 'Property', 'Unit', 'Due', 'Paid', 'Amount', 'Method', 'Status', 'Reference'],
                array_map(static fn ($r) => [
                    $r['payment_number'] ?? '', $r['tenant_name'] ?? '', $r['facility_name'] ?? '',
                    $r['unit_number'] ?? '', $r['due_date'] ?? '', $r['payment_date'] ?? '',
                    $r['amount'] ?? '', $r['payment_method'] ?? '', $r['status'] ?? '', $r['reference_no'] ?? '',
                ], $svc->collections($facilityIds, $from, $to))],
        };
    }

    private function forcedLandlordId(): int
    {
        $role = (string) session()->get('user_role');
        if ($role !== 'landlord') {
            return 0;
        }
        $sid = (int) session()->get('landlord_id');
        if ($sid > 0) {
            return $sid;
        }
        $uid = (int) session()->get('user_id');
        if ($uid > 0 && $this->db->fieldExists('landlord_id', 'users')) {
            $row = $this->db->table('users')->select('landlord_id')->where('id', $uid)->get()->getRowArray();

            return (int) ($row['landlord_id'] ?? 0);
        }

        return 0;
    }

    /** @return list<array<string, mixed>> */
    private function scopedActiveFacilities(): array
    {
        $q = $this->db->table('facilities')->select('id, name, code')->where('status', 'active');
        if ($this->db->fieldExists('deleted_at', 'facilities')) {
            $q->where('deleted_at', null);
        }
        $this->scopeCompany($q, 'company_id');
        $this->scopeFacilities($q, 'id');

        return $q->orderBy('name')->get()->getResultArray();
    }

    /**
     * Same CSV / Dompdf PDF / HTML-as-xls stack as Reports::export.
     *
     * @param list<string> $headers
     * @param list<array<int|string, mixed>> $rows
     */
    private function tabularExport(string $filename, string $title, array $headers, array $rows, string $format): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($format === 'pdf' && class_exists(\Dompdf\Dompdf::class)) {
            $html = '<html><head><meta charset="utf-8"><style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:6px;font-size:11px}th{background:#f0f0f0}</style></head><body>';
            $html .= '<h2>' . htmlspecialchars($title) . '</h2><table><thead><tr>';
            foreach ($headers as $h) {
                $html .= '<th>' . htmlspecialchars($h) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach (array_values($row) as $cell) {
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

        if ($format === 'excel' || $format === 'pdf') {
            $html = '<table border="1"><thead><tr>';
            foreach ($headers as $h) {
                $html .= '<th>' . htmlspecialchars($h) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach (array_values($row) as $cell) {
                    $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            return $this->response
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
                ->setBody($html);
        }

        $output = implode(',', array_map(static fn ($h) => '"' . $h . '"', $headers)) . "\n";
        foreach ($rows as $row) {
            $output .= implode(',', array_map(static fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', array_values($row))) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
            ->setBody($output);
    }

    /**
     * @param list<string> $headers
     * @param list<array<int|string, mixed>> $rows
     */
    private function csvResponse(string $filename, array $headers, array $rows): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->tabularExport(basename($filename, '.csv'), 'Export', $headers, $rows, 'csv');
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
