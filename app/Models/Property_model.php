<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Property records — stored in `facilities` (facility = property).
 */
class Property_model extends Model
{
    protected $table          = 'facilities';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'name', 'code', 'address', 'city', 'country', 'area', 'area_sqm',
        'manager_id', 'caretaker_id', 'floors', 'status', 'company_id',
        'category', 'property_type', 'listing_status', 'for_sale', 'sale_price',
        'price_per_sqm', 'landlord_id', 'owner_name', 'owner_contact', 'owner_email',
        'description', 'total_units',
    ];

    public function listPaginated(array $filters, int $perPage = 20, int $page = 1): array
    {
        $builder = $this->db->table('facilities f')
            ->select('f.*, c.name AS company_name, u.name AS manager_name')
            ->join('companies c', 'c.id = f.company_id', 'left')
            ->join('users u', 'u.id = f.manager_id', 'left')
            ->where('f.deleted_at', null);

        $this->applyListFilters($builder, $filters);

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->orderBy('f.name', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['occupancy'] = $this->occupancyCounts((int) $row['id']);
            $row['primary_photo'] = $this->primaryPhotoUrl((int) $row['id']);
            $row['manager_names'] = $this->assignedNames((int) $row['id'], 'manager');
        }

        return [
            'rows'    => $rows,
            'total'   => $total,
            'perPage' => $perPage,
            'page'    => $page,
        ];
    }

    public function findDetail(int $id): ?array
    {
        $row = $this->db->table('facilities f')
            ->select('f.*, c.name AS company_name, u.name AS manager_name, l.full_name AS landlord_name')
            ->join('companies c', 'c.id = f.company_id', 'left')
            ->join('users u', 'u.id = f.manager_id', 'left')
            ->join('landlords l', 'l.id = f.landlord_id', 'left')
            ->where('f.id', $id)
            ->where('f.deleted_at', null)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** Property 360° aggregates for view page */
    public function get360Data(int $facilityId): array
    {
        $out = [
            'units'            => [],
            'assignedStaff'    => [],
            'plMonthly'        => ['income' => 0, 'expense' => 0],
            'plAnnual'         => ['income' => 0, 'expense' => 0],
            'maintenance'      => ['open' => 0, 'done' => 0, 'emergency' => 0, 'total_cost' => 0],
            'crmVisits'        => [],
            'inspections'      => [],
            'costTransactions' => [],
            'recurringCosts'   => [],
            'landlordPayouts'  => [],
            'chequeSummary'    => [],
            'crmLeads'         => [],
            'leaseContracts'   => [],
            'fmContracts'      => [],
            'documents'        => [],
            'occupancy'        => $this->occupancyCounts($facilityId),
            'primaryPhoto'     => $this->primaryPhotoUrl($facilityId),
        ];

        $out['units'] = $this->db->table('units')
            ->where('facility_id', $facilityId)
            ->where('deleted_at', null)
            ->orderBy('unit_number', 'ASC')
            ->get()->getResultArray();

        if ($this->db->tableExists('user_property_assignments')) {
            $out['assignedStaff'] = $this->db->table('user_property_assignments upa')
                ->select('upa.*, u.name AS user_name')
                ->join('users u', 'u.id = upa.user_id', 'left')
                ->where('upa.facility_id', $facilityId)
                ->get()->getResultArray();
        }

        $year  = (int) date('Y');
        $month = date('Y-m');
        if ($this->db->tableExists('finance_entries')) {
            $out['plMonthly']['income'] = (float) ($this->db->table('finance_entries')->selectSum('amount')
                ->where('facility_id', $facilityId)->where('direction', 'income')
                ->where('entry_date >=', $month . '-01')
                ->where('entry_date <=', date('Y-m-t'))
                ->get()->getRowArray()['amount'] ?? 0);
            $out['plMonthly']['expense'] = (float) ($this->db->table('finance_entries')->selectSum('amount')
                ->where('facility_id', $facilityId)->where('direction', 'expense')
                ->where('entry_date >=', $month . '-01')
                ->where('entry_date <=', date('Y-m-t'))
                ->get()->getRowArray()['amount'] ?? 0);
            $out['plAnnual']['income'] = (float) ($this->db->table('finance_entries')->selectSum('amount')
                ->where('facility_id', $facilityId)->where('direction', 'income')
                ->where('entry_date >=', "{$year}-01-01")
                ->where('entry_date <=', "{$year}-12-31")
                ->get()->getRowArray()['amount'] ?? 0);
            $out['plAnnual']['expense'] = (float) ($this->db->table('finance_entries')->selectSum('amount')
                ->where('facility_id', $facilityId)->where('direction', 'expense')
                ->where('entry_date >=', "{$year}-01-01")
                ->where('entry_date <=', "{$year}-12-31")
                ->get()->getRowArray()['amount'] ?? 0);

            $out['costTransactions'] = $this->db->table('finance_entries')
                ->where('facility_id', $facilityId)
                ->orderBy('entry_date', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('lease_payments')) {
            $rentMonth = (float) ($this->db->table('lease_payments')->selectSum('amount')
                ->where('facility_id', $facilityId)->where('status', 'paid')
                ->where('payment_date >=', $month . '-01')
                ->get()->getRowArray()['amount'] ?? 0);
            $out['plMonthly']['income'] += $rentMonth;
            $rentYear = (float) ($this->db->table('lease_payments')->selectSum('amount')
                ->where('facility_id', $facilityId)->where('status', 'paid')
                ->where('payment_date >=', "{$year}-01-01")
                ->get()->getRowArray()['amount'] ?? 0);
            $out['plAnnual']['income'] += $rentYear;
        }

        if ($this->db->tableExists('work_orders')) {
            $woOpen = $this->db->table('work_orders')
                ->where('facility_id', $facilityId)->where('deleted_at', null)
                ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                ->countAllResults();
            $woDone = $this->db->table('work_orders')
                ->where('facility_id', $facilityId)->whereIn('status', ['completed', 'closed'])
                ->countAllResults();
            $woEmer = $this->db->table('work_orders')
                ->where('facility_id', $facilityId)->where('priority', 'urgent')
                ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                ->countAllResults();
            $out['maintenance'] = [
                'open'       => $woOpen,
                'done'       => $woDone,
                'emergency'  => $woEmer,
                'total_cost' => 0,
            ];
        }

        if ($this->db->tableExists('crm_visits')) {
            $out['crmVisits'] = $this->db->table('crm_visits v')
                ->select('v.*, l.full_name AS lead_name')
                ->join('crm_leads l', 'l.id = v.lead_id', 'left')
                ->where('v.facility_id', $facilityId)
                ->orderBy('v.visit_date', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('unit_checklists')) {
            $unitIds = array_column($out['units'], 'id');
            if ($unitIds) {
                $out['inspections'] = $this->db->table('unit_checklists uc')
                    ->select('uc.*, u.unit_number')
                    ->join('units u', 'u.id = uc.unit_id', 'left')
                    ->whereIn('uc.unit_id', $unitIds)
                    ->orderBy('uc.created_at', 'DESC')
                    ->limit(10)
                    ->get()->getResultArray();
            }
        }

        if ($this->db->tableExists('cost_reminders')) {
            $out['recurringCosts'] = $this->db->table('cost_reminders')
                ->where('facility_id', $facilityId)
                ->where('deleted_at', null)
                ->orderBy('due_date')
                ->limit(20)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('landlord_payouts')) {
            $out['landlordPayouts'] = $this->db->table('landlord_payouts')
                ->where('facility_id', $facilityId)
                ->orderBy('created_at', 'DESC')
                ->limit(6)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('cheques')) {
            $out['chequeSummary'] = $this->db->table('cheques')
                ->select('status, COUNT(*) AS cnt, SUM(amount) AS total')
                ->where('facility_id', $facilityId)
                ->groupBy('status')
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('crm_leads')) {
            $leadIds = [];
            if ($this->db->tableExists('crm_visits')) {
                $leadIds = array_column(
                    $this->db->table('crm_visits')->select('lead_id')
                        ->where('facility_id', $facilityId)->distinct()->get()->getResultArray(),
                    'lead_id'
                );
            }
            $q = $this->db->table('crm_leads')->where('converted', 0)->where('deleted_at', null);
            if ($leadIds) {
                $q->whereIn('id', $leadIds);
            } else {
                $q->where('1 = 0', null, false);
            }
            $out['crmLeads'] = $q->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray();
        }

        if ($this->db->tableExists('lease_contracts')) {
            $out['leaseContracts'] = $this->db->table('lease_contracts lc')
                ->select('lc.*, t.full_name AS tenant_name')
                ->join('tenants t', 't.id = lc.tenant_id', 'left')
                ->where('lc.facility_id', $facilityId)
                ->orderBy('lc.start_date', 'DESC')
                ->get()->getResultArray();
        }

        $out['fmContracts'] = $this->db->table('contracts')
            ->where('facility_id', $facilityId)
            ->orderBy('end_date', 'DESC')
            ->get()->getResultArray();

        if ($this->db->tableExists('documents')) {
            $out['documents'] = $this->db->table('documents')
                ->whereIn('module', ['property', 'facilities', 'facility', 'properties'])
                ->where('ref_id', $facilityId)
                ->orderBy('is_primary', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        return $out;
    }

    public function occupancyCounts(int $facilityId): array
    {
        $total = $this->db->table('units')->where('facility_id', $facilityId)->where('deleted_at', null)->countAllResults();
        $occupied = $this->db->table('units')->where('facility_id', $facilityId)->where('status', 'occupied')->where('deleted_at', null)->countAllResults();
        $vacant = $this->db->table('units')->where('facility_id', $facilityId)->where('status', 'vacant')->where('deleted_at', null)->countAllResults();
        $maintenance = $this->db->table('units')->where('facility_id', $facilityId)->where('status', 'maintenance')->where('deleted_at', null)->countAllResults();
        $reserved = $this->db->table('units')->where('facility_id', $facilityId)->where('status', 'reserved')->where('deleted_at', null)->countAllResults();

        $occupancyPct = $total > 0 ? round(($occupied / $total) * 100) : 0;

        return compact('total', 'occupied', 'vacant', 'maintenance', 'reserved', 'occupancy_pct');
    }

    public function generateCode(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
        if ($base === '') {
            $base = 'PROP';
        }
        $code = $base;
        $n    = 1;
        while ($this->db->table('facilities')->where('code', $code)->countAllResults() > 0) {
            $code = $base . $n++;
        }

        return $code;
    }

    private function applyListFilters($builder, array $filters): void
    {
        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('f.name', $s)
                ->orLike('f.code', $s)
                ->orLike('f.city', $s)
                ->orLike('f.area', $s)
                ->groupEnd();
        }
        if (! empty($filters['category'])) {
            $builder->where('f.category', $filters['category']);
        }
        if (! empty($filters['listing_status'])) {
            $builder->where('f.listing_status', $filters['listing_status']);
        }
        if (! empty($filters['status'])) {
            $builder->where('f.status', $filters['status']);
        }
        if (isset($filters['for_sale']) && $filters['for_sale'] !== '') {
            $builder->where('f.for_sale', (int) $filters['for_sale']);
        }
        if (! empty($filters['company_id'])) {
            $builder->where('f.company_id', (int) $filters['company_id']);
        }
    }

    private function primaryPhotoUrl(int $facilityId): ?string
    {
        if (! $this->db->tableExists('documents')) {
            return null;
        }
        $doc = $this->db->table('documents')
            ->whereIn('module', ['property', 'facilities'])
            ->where('ref_id', $facilityId)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()->getRowArray();

        if (! $doc || empty($doc['file_path'])) {
            return null;
        }

        return base_url('file/documents/' . basename($doc['file_path']));
    }

    private function assignedNames(int $facilityId, string $roleType): string
    {
        if (! $this->db->tableExists('user_property_assignments')) {
            return '';
        }
        $rows = $this->db->table('user_property_assignments upa')
            ->select('u.name')
            ->join('users u', 'u.id = upa.user_id', 'left')
            ->where('upa.facility_id', $facilityId)
            ->where('upa.role_type', $roleType)
            ->get()->getResultArray();

        return implode(', ', array_filter(array_column($rows, 'name')));
    }
}
