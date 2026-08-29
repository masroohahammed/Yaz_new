<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class Crm extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'crm_leads';

    /** @var list<string> */
    private array $stages = ['new', 'contacted', 'qualified', 'viewing', 'negotiation', 'won', 'lost'];

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $filters = [
            'search' => trim((string) ($this->request->getGet('search') ?? '')),
            'stage'  => $this->request->getGet('stage') ?? '',
            'view'   => $this->request->getGet('view') ?? 'list',
        ];

        $q = $this->db->table(self::TABLE . ' l')
            ->select('l.*, u.name AS assigned_name')
            ->join('users u', 'u.id = l.assigned_to', 'left')
            ->where('l.deleted_at', null);
        $this->scopeCompany($q, 'l.company_id');

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('l.full_name', $filters['search'])
                ->orLike('l.phone', $filters['search'])
                ->orLike('l.email', $filters['search'])
                ->orLike('l.lead_number', $filters['search'])
                ->groupEnd();
        }
        if ($filters['stage'] !== '') {
            $q->where('l.stage', $filters['stage']);
        }

        $pg    = $this->paginate(50);
        $total = (clone $q)->countAllResults(false);
        $leads = $q->orderBy('l.updated_at', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        $kanban = [];
        if ($filters['view'] === 'kanban') {
            foreach ($this->stages as $stage) {
                $kanban[$stage] = array_values(array_filter($leads, static fn ($r) => ($r['stage'] ?? 'new') === $stage));
            }
        }

        return view('crm/index', $this->viewData([
            'title'       => 'CRM Leads',
            'leads'       => $leads,
            'kanban'      => $kanban,
            'stages'      => $this->stages,
            'filters'     => $filters,
            'total'       => $total,
            'currentPage' => $pg['page'],
            'perPage'     => $pg['perPage'],
        ]));
    }

    public function create()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        return view('crm/form', $this->viewData([
            'title' => 'New Lead',
            'lead'  => null,
            'users' => $this->userOptions(),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('crm'))->with('error', 'CRM module is not available. Run database migration first.');
        }

        $rules = [
            'full_name' => 'required|min_length[2]|max_length[200]',
            'phone'     => 'permit_empty|max_length[30]',
            'email'     => 'permit_empty|valid_email|max_length[150]',
            'stage'     => 'required|max_length[50]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->leadPayload();
        $data['lead_number'] = $this->generateNumber('LD', self::TABLE, 'lead_number');
        $data['created_by']  = $this->currentUser()['id'] ?: null;
        $data['created_at']  = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('create', 'crm_leads', $id, 'Lead created: ' . $data['lead_number']);

        return redirect()->to(base_url('crm/' . $id))->with('success', 'Lead created.');
    }

    public function show(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $lead = $this->db->table(self::TABLE . ' l')
            ->select('l.*, u.name AS assigned_name, cu.name AS created_by_name')
            ->join('users u', 'u.id = l.assigned_to', 'left')
            ->join('users cu', 'cu.id = l.created_by', 'left')
            ->where('l.id', $id)
            ->where('l.deleted_at', null)
            ->get()->getRowArray();

        if (! $lead) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $activities = [];
        if ($this->pmTableExists('crm_activities')) {
            $activities = $this->db->table('crm_activities a')
                ->select('a.*, u.name AS created_by_name')
                ->join('users u', 'u.id = a.created_by', 'left')
                ->where('a.lead_id', $id)
                ->orderBy('a.created_at', 'DESC')
                ->get()->getResultArray();
        }

        $visits = [];
        if ($this->pmTableExists('crm_visits')) {
            $visits = $this->db->table('crm_visits v')
                ->select('v.*, f.name AS facility_name, u.unit_number')
                ->join('facilities f', 'f.id = v.facility_id', 'left')
                ->join('units u', 'u.id = v.unit_id', 'left')
                ->where('v.lead_id', $id)
                ->orderBy('v.visit_date', 'DESC')
                ->get()->getResultArray();
        }

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('crm/show', $this->viewData([
            'title'      => 'Lead ' . $lead['lead_number'],
            'lead'       => $lead,
            'activities' => $activities,
            'visits'     => $visits,
            'facilities' => $facilities,
            'users'      => $this->userOptions(),
            'stages'     => $this->stages,
        ]));
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $lead = $this->pmFind(self::TABLE, $id);
        if (! $lead) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        return view('crm/form', $this->viewData([
            'title' => 'Edit Lead',
            'lead'  => $lead,
            'users' => $this->userOptions(),
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('crm'))->with('error', 'CRM module is not available. Run database migration first.');
        }

        if (! $this->pmFind(self::TABLE, $id)) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $rules = [
            'full_name' => 'required|min_length[2]|max_length[200]',
            'phone'     => 'permit_empty|max_length[30]',
            'email'     => 'permit_empty|valid_email|max_length[150]',
            'stage'     => 'required|max_length[50]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->leadPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->logActivity('update', 'crm_leads', $id, 'Lead updated');

        return redirect()->to(base_url('crm/' . $id))->with('success', 'Lead updated.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('crm'))->with('error', 'CRM module is not available. Run database migration first.');
        }

        $lead = $this->pmFind(self::TABLE, $id);
        if (! $lead) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete', 'crm_leads', $id, 'Lead deleted: ' . $lead['lead_number']);

        return redirect()->to(base_url('crm'))->with('success', 'Lead removed.');
    }

    public function addVisit(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE) || ! $this->pmTableExists('crm_visits')) {
            return redirect()->to(base_url('crm/' . $id))->with('error', 'CRM visits table not available. Run migration first.');
        }

        $lead = $this->pmFind(self::TABLE, $id);
        if (! $lead) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $rules = ['visit_date' => 'required|valid_date'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table('crm_visits')->insert([
            'lead_id'           => $id,
            'facility_id'       => (int) $this->request->getPost('facility_id') ?: null,
            'unit_id'           => (int) $this->request->getPost('unit_id') ?: null,
            'visit_date'        => $this->request->getPost('visit_date'),
            'visit_time'        => $this->request->getPost('visit_time') ?: null,
            'visit_type'        => esc($this->request->getPost('visit_type')) ?: null,
            'agent_id'          => (int) $this->request->getPost('agent_id') ?: null,
            'rating'            => $this->request->getPost('rating') ?: null,
            'customer_feedback' => esc($this->request->getPost('customer_feedback')) ?: null,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->db->table(self::TABLE)->where('id', $id)->update(['stage' => 'viewing', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('visit', 'crm_leads', $id, 'Visit scheduled for ' . $this->request->getPost('visit_date'));

        return redirect()->to(base_url('crm/' . $id))->with('success', 'Visit scheduled.');
    }

    public function updateStage(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('crm/' . $id))->with('error', 'CRM module not available.');
        }

        $lead = $this->pmFind(self::TABLE, $id);
        if (! $lead) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $stage = $this->request->getPost('stage');
        if (! in_array($stage, $this->stages, true)) {
            return redirect()->back()->with('error', 'Invalid stage.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update([
            'stage'       => $stage,
            'lost_reason' => $stage === 'lost' ? (esc($this->request->getPost('lost_reason')) ?: null) : $lead['lost_reason'],
            'notes'       => esc($this->request->getPost('notes')) ?: $lead['notes'],
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('stage_change', 'crm_leads', $id, 'Stage changed to ' . $stage);

        return redirect()->to(base_url('crm/' . $id))->with('success', 'Stage updated to ' . ucfirst($stage) . '.');
    }

    public function convert(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('crm/' . $id))->with('error', 'CRM module not available.');
        }

        $lead = $this->pmFind(self::TABLE, $id);
        if (! $lead) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $convertTo = $this->request->getPost('convert_to');

        if ($convertTo === 'tenant' && $this->pmTableExists('tenants')) {
            $this->db->table('tenants')->insert([
                'company_id'  => $this->pmCompanyId(),
                'full_name'   => $lead['full_name'],
                'phone'       => $lead['phone'] ?? '',
                'email'       => $lead['email'] ?? null,
                'nationality' => $lead['nationality'] ?? null,
                'status'      => 'active',
                'notes'       => 'Converted from lead ' . ($lead['lead_number'] ?? ''),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $tenantId = (int) $this->db->insertID();

            $this->db->table(self::TABLE)->where('id', $id)->update(['stage' => 'won', 'updated_at' => date('Y-m-d H:i:s')]);
            $this->logActivity('convert', 'crm_leads', $id, 'Lead converted to tenant #' . $tenantId);

            return redirect()->to(base_url('tenants/' . $tenantId))->with('success', 'Lead converted to tenant.');
        }

        if ($convertTo === 'deal' && $this->pmTableExists('sales_deals')) {
            $dealNum = $this->generateNumber('DEAL', 'sales_deals', 'deal_number');
            $this->db->table('sales_deals')->insert([
                'company_id'  => $this->pmCompanyId(),
                'deal_number' => $dealNum,
                'deal_type'   => $lead['interest_type'] === 'Buy' ? 'Sale' : 'Lease',
                'lead_id'     => $id,
                'buyer_name'  => $lead['full_name'],
                'buyer_phone' => $lead['phone'] ?? null,
                'buyer_email' => $lead['email'] ?? null,
                'stage'       => 'prospect',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $dealId = (int) $this->db->insertID();

            $this->db->table(self::TABLE)->where('id', $id)->update(['stage' => 'won', 'updated_at' => date('Y-m-d H:i:s')]);
            $this->logActivity('convert', 'crm_leads', $id, 'Lead converted to deal ' . $dealNum);

            return redirect()->to(base_url('sales'))->with('success', 'Lead converted to sales deal ' . $dealNum . '.');
        }

        if ($convertTo === 'contract' && $this->pmTableExists('lease_contracts')) {
            $this->db->table(self::TABLE)->where('id', $id)->update(['stage' => 'won', 'updated_at' => date('Y-m-d H:i:s')]);
            $this->logActivity('convert', 'crm_leads', $id, 'Lead marked won → redirect to contracts');

            return redirect()->to(base_url('contracts/create'))->with('success', 'Lead won. Create a lease contract below.');
        }

        return redirect()->to(base_url('crm/' . $id))->with('error', 'Conversion target not recognised or required table missing.');
    }

    public function reports()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return view('crm/reports', $this->viewData([
                'title'   => 'CRM Reports',
                'funnel'  => [],
                'sources' => [],
                'topLocs' => [],
            ]));
        }

        $baseQ = $this->db->table(self::TABLE)->where('deleted_at', null);
        $this->scopeCompany($baseQ);

        $funnel = [];
        foreach ($this->stages as $stage) {
            $cnt = (clone $baseQ)->where('stage', $stage)->countAllResults(false);
            $funnel[$stage] = (int) $cnt;
        }

        $sourcesRaw = (clone $baseQ)
            ->select('source, COUNT(*) AS cnt')
            ->groupBy('source')
            ->orderBy('cnt', 'DESC')
            ->get()->getResultArray();

        $sources = [];
        foreach ($sourcesRaw as $row) {
            $sources[$row['source'] ?: 'Unknown'] = (int) $row['cnt'];
        }

        $topLocsRaw = (clone $baseQ)
            ->select('preferred_location, COUNT(*) AS cnt')
            ->where('preferred_location IS NOT NULL', null, false)
            ->groupBy('preferred_location')
            ->orderBy('cnt', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $topLocs = [];
        foreach ($topLocsRaw as $row) {
            $topLocs[$row['preferred_location']] = (int) $row['cnt'];
        }

        return view('crm/reports', $this->viewData([
            'title'   => 'CRM Reports',
            'funnel'  => $funnel,
            'sources' => $sources,
            'topLocs' => $topLocs,
        ]));
    }

    public function addActivity(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE) || ! $this->pmTableExists('crm_activities')) {
            return redirect()->to(base_url('crm/' . $id))->with('error', 'CRM activities are not available. Run database migration first.');
        }

        if (! $this->pmFind(self::TABLE, $id)) {
            return redirect()->to(base_url('crm'))->with('error', 'Lead not found.');
        }

        $rules = [
            'activity_type' => 'required|max_length[50]',
            'subject'       => 'permit_empty|max_length[200]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table('crm_activities')->insert([
            'lead_id'          => $id,
            'activity_type'    => esc($this->request->getPost('activity_type')),
            'outcome'          => esc($this->request->getPost('outcome')) ?: null,
            'subject'          => esc($this->request->getPost('subject')) ?: null,
            'description'      => esc($this->request->getPost('description')) ?: null,
            'duration_minutes' => $this->request->getPost('duration_minutes') ?: null,
            'next_follow_up'   => $this->request->getPost('next_follow_up') ?: null,
            'created_by'       => $this->currentUser()['id'] ?: null,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->db->table(self::TABLE)->where('id', $id)->update(['updated_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('activity', 'crm_leads', $id, 'Activity logged: ' . $this->request->getPost('activity_type'));

        return redirect()->to(base_url('crm/' . $id))->with('success', 'Activity added.');
    }

    public function exportCsv()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('crm'))->with('error', 'CRM module is not available.');
        }

        $q = $this->db->table(self::TABLE . ' l')
            ->select('l.*, u.name AS assigned_name')
            ->join('users u', 'u.id = l.assigned_to', 'left')
            ->where('l.deleted_at', null);
        $this->scopeCompany($q, 'l.company_id');
        $rows = $q->orderBy('l.updated_at', 'DESC')->get()->getResultArray();

        $filename = 'crm_leads_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Lead #', 'Full Name', 'Phone', 'Email', 'Nationality', 'Source', 'Interest', 'Stage', 'Temperature', 'Budget Min', 'Budget Max', 'Assigned To', 'Follow Up', 'Created At']);
        foreach ($rows as $r) {
            fputcsv($fp, [
                $r['lead_number'],
                $r['full_name'],
                $r['phone'] ?? '',
                $r['email'] ?? '',
                $r['nationality'] ?? '',
                $r['source'] ?? '',
                $r['interest_type'] ?? '',
                $r['stage'],
                $r['temperature'],
                $r['budget_min'] ?? '',
                $r['budget_max'] ?? '',
                $r['assigned_name'] ?? '',
                $r['follow_up_date'] ?? '',
                $r['created_at'] ?? '',
            ]);
        }
        fclose($fp);
        exit;
    }

    /** @return list<array<string,mixed>> */
    private function userOptions(): array
    {
        return $this->db->table('users')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();
    }

    /** @return array<string,mixed> */
    private function leadPayload(): array
    {
        return [
            'company_id'         => $this->pmCompanyId(),
            'full_name'          => esc($this->request->getPost('full_name')),
            'phone'              => esc($this->request->getPost('phone')) ?: null,
            'email'              => esc($this->request->getPost('email')) ?: null,
            'nationality'        => esc($this->request->getPost('nationality')) ?: null,
            'source'             => esc($this->request->getPost('source')) ?: null,
            'interest_type'      => $this->request->getPost('interest_type') ?: 'Rent',
            'preferred_location' => esc($this->request->getPost('preferred_location')) ?: null,
            'budget_min'         => $this->request->getPost('budget_min') ?: null,
            'budget_max'         => $this->request->getPost('budget_max') ?: null,
            'bedrooms'           => $this->request->getPost('bedrooms') ?: null,
            'temperature'        => $this->request->getPost('temperature') ?: 'Warm',
            'stage'              => $this->request->getPost('stage') ?: 'new',
            'assigned_to'        => (int) $this->request->getPost('assigned_to') ?: null,
            'follow_up_date'     => $this->request->getPost('follow_up_date') ?: null,
            'follow_up_time'     => $this->request->getPost('follow_up_time') ?: null,
            'lost_reason'        => esc($this->request->getPost('lost_reason')) ?: null,
            'notes'              => esc($this->request->getPost('notes')) ?: null,
        ];
    }

    private function migrationView()
    {
        return view('crm/index', $this->viewData([
            'title'             => 'CRM Leads',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'leads'             => [],
            'kanban'            => [],
            'stages'            => $this->stages,
            'filters'           => ['view' => 'list'],
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 50,
        ]));
    }
}
