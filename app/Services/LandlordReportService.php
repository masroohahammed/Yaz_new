<?php

namespace App\Services;

use App\Support\PmExpenseCategories;
use CodeIgniter\Database\BaseConnection;

/**
 * Landlord-scoped reporting on existing PM tables.
 *
 * Hierarchy: landlords → facilities (properties) → units → leases / payments / cheques.
 * There is no separate buildings table — facilities are properties.
 *
 * Rent/collection numbers come from lease_payments (same source as Payments + PmReports).
 * Cheques come from cheques. Expenses come from expenses. Occupancy from units.status.
 * Maintenance cost comes from work_orders.actual_cost via converted_to_wo — requests have no cost column.
 */
class LandlordReportService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * @param list<int>|null $companyFacilityIds null = all companies
     * @return list<int>
     */
    public function facilityIdsForLandlord(int $landlordId, ?int $companyId, ?array $companyFacilityIds, ?int $facilityId = null): array
    {
        if ($landlordId < 1 || ! $this->db->tableExists('facilities')) {
            return [];
        }

        $q = $this->db->table('facilities')->select('id')->where('landlord_id', $landlordId);
        if ($this->db->fieldExists('deleted_at', 'facilities')) {
            $q->where('deleted_at', null);
        }
        if ($companyId && $this->db->fieldExists('company_id', 'facilities')) {
            $q->where('company_id', $companyId);
        }
        if ($companyFacilityIds !== null) {
            if ($companyFacilityIds === []) {
                return [];
            }
            $q->whereIn('id', $companyFacilityIds);
        }
        if ($facilityId && $facilityId > 0) {
            $q->where('id', $facilityId);
        }

        return array_map(static fn ($r) => (int) $r['id'], $q->get()->getResultArray());
    }

    /** @return list<array<string, mixed>> */
    public function landlords(?int $companyId): array
    {
        if (! $this->db->tableExists('landlords')) {
            return [];
        }
        $q = $this->db->table('landlords')->select('id, full_name, status')->where('deleted_at', null)->orderBy('full_name');
        if ($companyId && $this->db->fieldExists('company_id', 'landlords')) {
            $q->where('company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function properties(array $facilityIds): array
    {
        if ($facilityIds === []) {
            return [];
        }

        return $this->db->table('facilities')
            ->select('id, name, code, status, city, property_type')
            ->whereIn('id', $facilityIds)
            ->orderBy('name')
            ->get()->getResultArray();
    }

    /**
     * @param list<int> $facilityIds
     * @return array<string, float|int>
     */
    public function overview(array $facilityIds, string $from, string $to): array
    {
        $empty = [
            'properties' => 0, 'buildings' => 0, 'units' => 0, 'occupied' => 0, 'vacant' => 0, 'maintenance_units' => 0,
            'rent_due' => 0.0, 'rent_collected' => 0.0, 'rent_pending' => 0.0, 'rent_overdue' => 0.0,
            'collection_pct' => 0.0, 'expenses' => 0.0, 'maintenance_cost' => 0.0,
            'maint_open' => 0, 'maint_total' => 0,
            'cheques_pending' => 0, 'cheques_cleared' => 0, 'cheques_bounced' => 0, 'cheques_upcoming' => 0, 'cheques_overdue' => 0,
            'revenue' => 0.0, 'net_income' => 0.0,
        ];
        if ($facilityIds === []) {
            return $empty;
        }

        $unitRow = $this->db->table('units')
            ->select("COUNT(*) AS total,
                SUM(CASE WHEN status='occupied' THEN 1 ELSE 0 END) AS occupied,
                SUM(CASE WHEN status='vacant' THEN 1 ELSE 0 END) AS vacant,
                SUM(CASE WHEN status='maintenance' THEN 1 ELSE 0 END) AS maint", false)
            ->whereIn('facility_id', $facilityIds);
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $unitRow->where('deleted_at', null);
        }
        $u = $unitRow->get()->getRowArray() ?: [];

        $pay = ['due' => 0.0, 'collected' => 0.0, 'pending' => 0.0, 'overdue' => 0.0];
        if ($this->db->tableExists('lease_payments')) {
            $payRow = $this->db->query("
                SELECT
                  COALESCE(SUM(CASE WHEN status NOT IN ('cancelled') THEN amount ELSE 0 END), 0) AS due,
                  COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS collected,
                  COALESCE(SUM(CASE WHEN status IN ('pending','partial','postponed') THEN amount ELSE 0 END), 0) AS pending,
                  COALESCE(SUM(CASE WHEN status = 'overdue' OR (status IN ('pending','partial') AND due_date < CURDATE()) THEN amount ELSE 0 END), 0) AS overdue
                FROM lease_payments
                WHERE facility_id IN (" . $this->inList($facilityIds) . ")
                  AND due_date >= ? AND due_date <= ?
            ", array_merge($facilityIds, [$from, $to]))->getRowArray() ?: [];
            $pay = [
                'due'       => (float) ($payRow['due'] ?? 0),
                'collected' => (float) ($payRow['collected'] ?? 0),
                'pending'   => (float) ($payRow['pending'] ?? 0),
                'overdue'   => (float) ($payRow['overdue'] ?? 0),
            ];
        }

        $expenses = 0.0;
        if ($this->db->tableExists('expenses')) {
            $expenses = (float) ($this->db->table('expenses')
                ->selectSum('amount', 't')
                ->whereIn('facility_id', $facilityIds)
                ->where('status', 'approved')
                ->where('expense_date >=', $from)
                ->where('expense_date <=', $to)
                ->get()->getRowArray()['t'] ?? 0);
        }

        $maintCost = 0.0;
        $maintOpen = 0;
        $maintTotal = 0;
        if ($this->db->tableExists('maintenance_requests')) {
            $m = $this->db->query("
                SELECT COUNT(*) AS total,
                       SUM(CASE WHEN status IN ('pending','reviewed') THEN 1 ELSE 0 END) AS open_cnt
                FROM maintenance_requests
                WHERE facility_id IN (" . $this->inList($facilityIds) . ")
                  AND DATE(created_at) >= ? AND DATE(created_at) <= ?
            ", array_merge($facilityIds, [$from, $to]))->getRowArray() ?: [];
            $maintTotal = (int) ($m['total'] ?? 0);
            $maintOpen  = (int) ($m['open_cnt'] ?? 0);
            if ($this->db->tableExists('work_orders') && $this->db->fieldExists('converted_to_wo', 'maintenance_requests')) {
                $maintCost = (float) ($this->db->query("
                    SELECT COALESCE(SUM(w.actual_cost), 0) AS t
                    FROM maintenance_requests mr
                    JOIN work_orders w ON w.id = mr.converted_to_wo
                    WHERE mr.facility_id IN (" . $this->inList($facilityIds) . ")
                      AND DATE(mr.created_at) >= ? AND DATE(mr.created_at) <= ?
                ", array_merge($facilityIds, [$from, $to]))->getRowArray()['t'] ?? 0);
            }
        }

        $cheques = ['pending' => 0, 'cleared' => 0, 'bounced' => 0, 'upcoming' => 0, 'overdue' => 0];
        if ($this->db->tableExists('cheques')) {
            $c = $this->db->query("
                SELECT
                  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                  SUM(CASE WHEN status = 'cleared' THEN 1 ELSE 0 END) AS cleared,
                  SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) AS bounced,
                  SUM(CASE WHEN status = 'pending' AND cheque_date >= CURDATE() THEN 1 ELSE 0 END) AS upcoming,
                  SUM(CASE WHEN status = 'pending' AND cheque_date < CURDATE() THEN 1 ELSE 0 END) AS overdue
                FROM cheques
                WHERE facility_id IN (" . $this->inList($facilityIds) . ")
                  AND cheque_date >= ? AND cheque_date <= ?
            ", array_merge($facilityIds, [$from, $to]))->getRowArray() ?: [];
            $cheques = [
                'pending'  => (int) ($c['pending'] ?? 0),
                'cleared'  => (int) ($c['cleared'] ?? 0),
                'bounced'  => (int) ($c['bounced'] ?? 0),
                'upcoming' => (int) ($c['upcoming'] ?? 0),
                'overdue'  => (int) ($c['overdue'] ?? 0),
            ];
        }

        $due = $pay['due'];
        $pct = $due > 0 ? round($pay['collected'] / $due * 100, 1) : 0.0;

        return [
            'properties'         => count($facilityIds),
            'buildings'          => count($facilityIds),
            'units'              => (int) ($u['total'] ?? 0),
            'occupied'           => (int) ($u['occupied'] ?? 0),
            'vacant'             => (int) ($u['vacant'] ?? 0),
            'maintenance_units'  => (int) ($u['maint'] ?? 0),
            'rent_due'           => $pay['due'],
            'rent_collected'     => $pay['collected'],
            'rent_pending'       => $pay['pending'],
            'rent_overdue'       => $pay['overdue'],
            'collection_pct'     => $pct,
            'expenses'           => $expenses,
            'maintenance_cost'   => $maintCost,
            'maint_open'         => $maintOpen,
            'maint_total'        => $maintTotal,
            'cheques_pending'    => $cheques['pending'],
            'cheques_cleared'    => $cheques['cleared'],
            'cheques_bounced'    => $cheques['bounced'],
            'cheques_upcoming'   => $cheques['upcoming'],
            'cheques_overdue'    => $cheques['overdue'],
            'revenue'            => $pay['collected'],
            'net_income'         => $pay['collected'] - $expenses,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function units(array $facilityIds, ?int $unitId = null, string $status = ''): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('units')) {
            return [];
        }
        $q = $this->db->table('units u')
            ->select('u.id, u.unit_number, u.unit_type, u.floor, u.area_sqft, u.status, u.rent_amount, u.contract_start, u.contract_end, u.tenant_name, f.name AS facility_name, f.code AS facility_code, t.full_name AS lease_tenant, lc.start_date AS lease_start, lc.end_date AS lease_end, lc.rent_amount AS lease_rent, lc.contract_number')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->join('lease_contracts lc', 'lc.unit_id = u.id AND lc.status = \'active\' AND lc.deleted_at IS NULL', 'left')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->whereIn('u.facility_id', $facilityIds);
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('u.deleted_at', null);
        }
        if ($unitId) {
            $q->where('u.id', $unitId);
        }
        if ($status !== '') {
            $q->where('u.status', $status);
        }

        return $q->orderBy('f.name')->orderBy('u.unit_number')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function tenants(array $facilityIds): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('tenants') || ! $this->db->tableExists('lease_contracts')) {
            return [];
        }

        return $this->db->table('lease_contracts lc')
            ->select('t.id AS tenant_id, t.full_name, t.phone, t.email, t.status AS tenant_status, f.name AS facility_name, u.unit_number, lc.id AS contract_id, lc.contract_number, lc.status AS lease_status, lc.start_date, lc.end_date, lc.rent_amount, lc.security_deposit')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->whereIn('lc.facility_id', $facilityIds)
            ->where('lc.deleted_at', null)
            ->orderBy('t.full_name')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function collections(array $facilityIds, string $from, string $to, string $status = '', ?int $tenantId = null): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('lease_payments')) {
            return [];
        }
        $q = $this->db->table('lease_payments lp')
            ->select('lp.*, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number')
            ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->join('units u', 'u.id = lp.unit_id', 'left')
            ->whereIn('lp.facility_id', $facilityIds)
            ->where('lp.due_date >=', $from)
            ->where('lp.due_date <=', $to);
        if ($status !== '') {
            $q->where('lp.status', $status);
        }
        if ($tenantId) {
            $q->where('lp.tenant_id', $tenantId);
        }

        return $q->orderBy('lp.due_date', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function pendingCollections(array $facilityIds): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('lease_payments')) {
            return [];
        }

        $rows = $this->db->table('lease_payments lp')
            ->select('lp.*, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number')
            ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->join('units u', 'u.id = lp.unit_id', 'left')
            ->whereIn('lp.facility_id', $facilityIds)
            ->whereIn('lp.status', ['pending', 'partial', 'overdue', 'postponed'])
            ->orderBy('lp.due_date', 'ASC')
            ->get()->getResultArray();

        $today = strtotime(date('Y-m-d'));
        foreach ($rows as &$r) {
            $due = strtotime((string) ($r['due_date'] ?? ''));
            $r['days_overdue'] = ($due && $due < $today) ? (int) floor(($today - $due) / 86400) : 0;
            $r['outstanding']  = (float) ($r['amount'] ?? 0);
            $r['amount_paid']  = (($r['status'] ?? '') === 'partial') ? 0.0 : 0.0;
        }
        unset($r);

        return $rows;
    }

    /** @return list<array<string, mixed>> */
    public function cheques(array $facilityIds, string $from, string $to, string $status = ''): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('cheques')) {
            return [];
        }
        $q = $this->db->table('cheques c')
            ->select('c.*, t.full_name AS tenant_name, lc.contract_number, f.name AS facility_name, u.unit_number')
            ->join('tenants t', 't.id = c.tenant_id', 'left')
            ->join('lease_contracts lc', 'lc.id = c.contract_id', 'left')
            ->join('facilities f', 'f.id = c.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->whereIn('c.facility_id', $facilityIds)
            ->where('c.cheque_date >=', $from)
            ->where('c.cheque_date <=', $to);
        if ($status !== '') {
            $q->where('c.status', $status);
        }

        return $q->orderBy('c.cheque_date', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function maintenance(array $facilityIds, string $from, string $to, string $status = ''): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('maintenance_requests')) {
            return [];
        }
        $q = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.category, mr.description, mr.priority, mr.status, mr.created_at, mr.requester_name, f.name AS facility_name, u.unit_number, w.actual_cost, w.status AS wo_status, w.completed_at, tech.name AS technician_name')
            ->join('facilities f', 'f.id = mr.facility_id', 'left')
            ->join('units u', 'u.id = mr.unit_id', 'left')
            ->join('work_orders w', 'w.id = mr.converted_to_wo', 'left')
            ->join('users tech', 'tech.id = w.assigned_to', 'left')
            ->whereIn('mr.facility_id', $facilityIds)
            ->where('DATE(mr.created_at) >=', $from)
            ->where('DATE(mr.created_at) <=', $to);
        if ($status !== '') {
            $q->where('mr.status', $status);
        }

        return $q->orderBy('mr.created_at', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function expenses(array $facilityIds, string $from, string $to, string $category = ''): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('expenses')) {
            return [];
        }
        $q = $this->db->table('expenses e')
            ->select('e.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = e.facility_id', 'left')
            ->whereIn('e.facility_id', $facilityIds)
            ->where('e.expense_date >=', $from)
            ->where('e.expense_date <=', $to);
        if ($category !== '') {
            $q->where('e.category', $category);
        }

        return $q->orderBy('e.expense_date', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function contracts(array $facilityIds, string $status = '', ?int $expiryDays = null): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('lease_contracts')) {
            return [];
        }
        $q = $this->db->table('lease_contracts lc')
            ->select('lc.*, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->whereIn('lc.facility_id', $facilityIds)
            ->where('lc.deleted_at', null);
        if ($status !== '') {
            $q->where('lc.status', $status);
        }
        if ($expiryDays !== null && $expiryDays > 0) {
            $q->where('lc.status', 'active')
                ->where('lc.end_date >=', date('Y-m-d'))
                ->where('lc.end_date <=', date('Y-m-d', strtotime('+' . $expiryDays . ' days')));
        }

        return $q->orderBy('lc.end_date', 'ASC')->get()->getResultArray();
    }

    /**
     * @return array{rows: list<array<string,mixed>>, occupancy_pct: float, vacancy_pct: float, trend: list<array<string,mixed>>}
     */
    public function occupancy(array $facilityIds): array
    {
        if ($facilityIds === []) {
            return ['rows' => [], 'occupancy_pct' => 0.0, 'vacancy_pct' => 0.0, 'trend' => []];
        }

        $rows = $this->db->query("
            SELECT f.id, f.name, f.code,
              COUNT(u.id) AS total_units,
              SUM(CASE WHEN u.status='occupied' THEN 1 ELSE 0 END) AS occupied,
              SUM(CASE WHEN u.status='vacant' THEN 1 ELSE 0 END) AS vacant,
              SUM(CASE WHEN u.status='maintenance' THEN 1 ELSE 0 END) AS maintenance,
              ROUND(SUM(CASE WHEN u.status='occupied' THEN 1 ELSE 0 END)/NULLIF(COUNT(u.id),0)*100,1) AS occupancy_pct
            FROM facilities f
            LEFT JOIN units u ON u.facility_id=f.id AND (u.deleted_at IS NULL)
            WHERE f.id IN (" . $this->inList($facilityIds) . ")
            GROUP BY f.id, f.name, f.code
            ORDER BY f.name
        ", $facilityIds)->getResultArray();

        $tot = 0;
        $occ = 0;
        foreach ($rows as $r) {
            $tot += (int) $r['total_units'];
            $occ += (int) $r['occupied'];
        }
        $pct = $tot > 0 ? round($occ / $tot * 100, 1) : 0.0;

        $trend = [];
        if ($this->db->tableExists('lease_contracts')) {
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = date('Y-m-01', strtotime('-' . $i . ' months'));
                $monthEnd   = date('Y-m-t', strtotime($monthStart));
                $occupied   = (int) ($this->db->query("
                    SELECT COUNT(DISTINCT unit_id) AS occupied
                    FROM lease_contracts
                    WHERE facility_id IN (" . $this->inList($facilityIds) . ")
                      AND deleted_at IS NULL
                      AND start_date <= ?
                      AND (end_date IS NULL OR end_date >= ?)
                      AND status NOT IN ('terminated','cancelled','draft')
                ", array_merge($facilityIds, [$monthEnd, $monthStart]))->getRowArray()['occupied'] ?? 0);
                $active = (int) $this->db->table('lease_contracts')
                    ->whereIn('facility_id', $facilityIds)
                    ->where('deleted_at', null)
                    ->where('start_date <=', $monthEnd)
                    ->groupStart()
                        ->where('end_date >=', $monthStart)
                        ->orWhere('end_date', null)
                    ->groupEnd()
                    ->whereNotIn('status', ['terminated', 'cancelled', 'draft'])
                    ->countAllResults();
                $trend[] = [
                    'month'          => date('M Y', strtotime($monthStart)),
                    'occupied_units' => $occupied,
                    'total_units'    => $tot,
                    'active_leases'  => $active,
                    'occupancy_pct'  => $tot > 0 ? round($occupied / $tot * 100, 1) : 0.0,
                ];
            }
        }

        return [
            'rows'          => $rows,
            'occupancy_pct' => $pct,
            'vacancy_pct'   => $tot > 0 ? round(100 - $pct, 1) : 0.0,
            'trend'         => $trend,
        ];
    }

    /**
     * Line-item statement from operational records (payments, expenses, payouts).
     * Does not invent a second ledger — finance_entries remain on finance/pm/owner-statement.
     *
     * @return list<array<string, mixed>>
     */
    public function statement(array $facilityIds, int $landlordId, string $from, string $to): array
    {
        $lines = [];
        foreach ($this->collections($facilityIds, $from, $to, 'paid') as $p) {
            $lines[] = [
                'entry_date'  => $p['payment_date'] ?: $p['due_date'],
                'facility'    => $p['facility_name'] ?? '',
                'unit'        => $p['unit_number'] ?? '',
                'description' => 'Rent collected ' . ($p['payment_number'] ?? '') . ' ' . ($p['tenant_name'] ?? ''),
                'income'      => (float) ($p['amount'] ?? 0),
                'expense'     => 0.0,
                'payment'     => (float) ($p['amount'] ?? 0),
                'adjustment'  => 0.0,
                'type'        => 'income',
            ];
        }
        foreach ($this->expenses($facilityIds, $from, $to) as $e) {
            if (($e['status'] ?? '') !== 'approved') {
                continue;
            }
            $lines[] = [
                'entry_date'  => $e['expense_date'] ?? '',
                'facility'    => $e['facility_name'] ?? '',
                'unit'        => '',
                'description' => ($e['category'] ?? 'expense') . ' — ' . ($e['description'] ?? ''),
                'income'      => 0.0,
                'expense'     => (float) ($e['amount'] ?? 0),
                'payment'     => 0.0,
                'adjustment'  => 0.0,
                'type'        => 'expense',
            ];
        }
        if ($this->db->tableExists('landlord_payouts')) {
            $payouts = $this->db->table('landlord_payouts lp')
                ->select('lp.*, f.name AS facility_name')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->where('lp.landlord_id', $landlordId)
                ->where('lp.period_from >=', $from)
                ->where('lp.period_to <=', $to)
                ->get()->getResultArray();
            foreach ($payouts as $po) {
                $lines[] = [
                    'entry_date'  => $po['paid_date'] ?? $po['period_to'] ?? '',
                    'facility'    => $po['facility_name'] ?? '',
                    'unit'        => '',
                    'description' => 'Landlord payout ' . ($po['reference_no'] ?? '') . ' (' . ($po['status'] ?? '') . ')',
                    'income'      => 0.0,
                    'expense'     => 0.0,
                    'payment'     => (float) ($po['net_amount'] ?? 0),
                    'adjustment'  => 0.0,
                    'type'        => 'payout',
                ];
            }
        }
        usort($lines, static fn ($a, $b) => strcmp((string) $a['entry_date'], (string) $b['entry_date']));
        $balance = 0.0;
        foreach ($lines as &$line) {
            $balance += (float) $line['income'] - (float) $line['expense'];
            $line['balance'] = $balance;
        }
        unset($line);

        return $lines;
    }

    /**
     * @return array{rental: float, other: float, parking: float, service: float, utility: float, late: float, gross: float, collected: float, pending: float, expenses: float, maintenance: float, net: float, margin: float, by_category: array<string,float>, by_group: array<string,float>}
     */
    public function pnl(array $facilityIds, string $from, string $to): array
    {
        $empty = [
            'rental' => 0.0, 'other' => 0.0, 'parking' => 0.0, 'service' => 0.0, 'utility' => 0.0, 'late' => 0.0,
            'gross' => 0.0, 'collected' => 0.0, 'pending' => 0.0, 'expenses' => 0.0, 'maintenance' => 0.0,
            'net' => 0.0, 'margin' => 0.0, 'by_category' => [], 'by_group' => [],
        ];
        if ($facilityIds === [] || ! $this->db->tableExists('lease_payments')) {
            return $empty;
        }

        $rows = $this->db->table('lease_payments')
            ->select('payment_type, status, amount')
            ->whereIn('facility_id', $facilityIds)
            ->where('due_date >=', $from)
            ->where('due_date <=', $to)
            ->get()->getResultArray();

        $out = $empty;
        foreach ($rows as $r) {
            $amt  = (float) ($r['amount'] ?? 0);
            $type = strtolower((string) ($r['payment_type'] ?? 'rent'));
            $st   = (string) ($r['status'] ?? '');
            if ($st === 'cancelled') {
                continue;
            }
            if ($st === 'paid') {
                $out['collected'] += $amt;
                if (str_contains($type, 'park')) {
                    $out['parking'] += $amt;
                } elseif (str_contains($type, 'service')) {
                    $out['service'] += $amt;
                } elseif (str_contains($type, 'util')) {
                    $out['utility'] += $amt;
                } elseif (str_contains($type, 'late') || str_contains($type, 'penal')) {
                    $out['late'] += $amt;
                } elseif ($type === 'rent' || $type === '') {
                    $out['rental'] += $amt;
                } else {
                    $out['other'] += $amt;
                }
            } elseif (in_array($st, ['pending', 'partial', 'overdue', 'postponed'], true)) {
                $out['pending'] += $amt;
            }
        }
        $out['gross'] = $out['rental'] + $out['other'] + $out['parking'] + $out['service'] + $out['utility'] + $out['late'];

        $expRows = $this->expenses($facilityIds, $from, $to);
        $byCat   = [];
        $byGroup = [];
        foreach ($expRows as $e) {
            if (($e['status'] ?? '') !== 'approved') {
                continue;
            }
            $cat   = (string) ($e['category'] ?? 'other');
            $amt   = (float) ($e['amount'] ?? 0);
            $group = PmExpenseCategories::groupKey($cat);
            $out['expenses'] += $amt;
            $byCat[$cat]     = ($byCat[$cat] ?? 0) + $amt;
            $byGroup[$group] = ($byGroup[$group] ?? 0) + $amt;
            if ($group === 'maintenance') {
                $out['maintenance'] += $amt;
            }
        }
        $out['by_category'] = $byCat;
        $out['by_group']    = $byGroup;
        $out['net']         = $out['collected'] - $out['expenses'];
        $out['margin']      = $out['collected'] > 0 ? round($out['net'] / $out['collected'] * 100, 1) : 0.0;

        return $out;
    }

    /** @param list<int> $ids */
    private function inList(array $ids): string
    {
        return implode(',', array_fill(0, count($ids), '?'));
    }
}
