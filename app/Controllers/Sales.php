<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class Sales extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE      = 'sales_deals';
    private const RULES_TBL  = 'commission_rules';
    private const COMM_TBL   = 'commission_records';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $filters = [
            'search' => trim((string) ($this->request->getGet('search') ?? '')),
            'stage'  => $this->request->getGet('stage') ?? '',
        ];

        $q = $this->db->table(self::TABLE . ' d')
            ->select('d.*, f.name AS facility_name, u.unit_number, a.name AS agent_name')
            ->join('facilities f', 'f.id = d.facility_id', 'left')
            ->join('units u', 'u.id = d.unit_id', 'left')
            ->join('users a', 'a.id = d.agent_id', 'left');
        $this->scopeCompany($q, 'd.company_id');
        $this->scopeFacilities($q, 'd.facility_id');

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('d.deal_number', $filters['search'])
                ->orLike('d.buyer_name', $filters['search'])
                ->orLike('d.buyer_phone', $filters['search'])
                ->groupEnd();
        }
        if ($filters['stage'] !== '') {
            $q->where('d.stage', $filters['stage']);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $deals = $q->orderBy('d.id', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('sales/index', $this->viewData([
            'title'       => 'Sales Deals',
            'deals'       => $deals,
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

        return view('sales/form', $this->viewData([
            'title'       => 'New Sales Deal',
            'deal'        => null,
            'facilities'  => $this->facilityOptions(),
            'leads'       => $this->leadOptions(),
            'agents'      => $this->agentOptions(),
            'commRules'   => $this->commissionRuleOptions(),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('sales'))->with('error', 'Sales module is not available. Run database migration first.');
        }

        $rules = [
            'buyer_name' => 'required|min_length[2]|max_length[200]',
            'deal_type'  => 'required|in_list[Sale,Lease]',
            'stage'      => 'required|max_length[50]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facilityId = (int) $this->request->getPost('facility_id') ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        $data = $this->dealPayload();
        $data['deal_number'] = $this->generateNumber('DEAL', self::TABLE, 'deal_number');
        $data['created_at']  = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->createCommissionRecord($id, $data);
        $this->logActivity('create', 'sales_deals', $id, 'Deal created: ' . $data['deal_number']);

        return redirect()->to(base_url('sales'))->with('success', 'Sales deal created.');
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $deal = $this->pmFind(self::TABLE, $id);
        if (! $deal) {
            return redirect()->to(base_url('sales'))->with('error', 'Deal not found.');
        }

        return view('sales/form', $this->viewData([
            'title'      => 'Edit Sales Deal',
            'deal'       => $deal,
            'facilities' => $this->facilityOptions(),
            'leads'      => $this->leadOptions(),
            'agents'     => $this->agentOptions(),
            'commRules'  => $this->commissionRuleOptions(),
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('sales'))->with('error', 'Sales module not available.');
        }

        if (! $this->pmFind(self::TABLE, $id)) {
            return redirect()->to(base_url('sales'))->with('error', 'Deal not found.');
        }

        $rules = [
            'buyer_name' => 'required|min_length[2]|max_length[200]',
            'deal_type'  => 'required|in_list[Sale,Lease]',
            'stage'      => 'required|max_length[50]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->dealPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->createCommissionRecord($id, $data, true);
        $this->logActivity('update', 'sales_deals', $id, 'Deal updated');

        return redirect()->to(base_url('sales'))->with('success', 'Deal updated.');
    }

    // ── Commission Rules ─────────────────────────────────────────────────────

    public function commissionRules()
    {
        if (! $this->pmTableExists(self::RULES_TBL)) {
            return redirect()->to(base_url('sales'))->with('error', 'Commission rules table not available. Run migration.');
        }

        $q = $this->db->table(self::RULES_TBL);
        $this->scopeCompany($q);
        $rules = $q->orderBy('id', 'DESC')->get()->getResultArray();

        return view('sales/commission_rules', $this->viewData([
            'title' => 'Commission Rules',
            'rules' => $rules,
        ]));
    }

    public function storeCommissionRule()
    {
        if (! $this->pmTableExists(self::RULES_TBL)) {
            return redirect()->to(base_url('sales/commission-rules'))->with('error', 'Table not available.');
        }

        $validation = [
            'rule_name'  => 'required|max_length[120]',
            'agent_rate' => 'required|numeric',
            'company_rate' => 'required|numeric',
        ];
        if (! $this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table(self::RULES_TBL)->insert([
            'company_id'      => $this->pmCompanyId(),
            'rule_name'       => esc($this->request->getPost('rule_name')),
            'deal_type'       => $this->request->getPost('deal_type') ?: null,
            'commission_type' => $this->request->getPost('commission_type') ?: 'percentage',
            'agent_rate'      => (float) $this->request->getPost('agent_rate'),
            'company_rate'    => (float) $this->request->getPost('company_rate'),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('sales/commission-rules'))->with('success', 'Commission rule created.');
    }

    public function deleteCommissionRule(int $id)
    {
        if (! $this->pmTableExists(self::RULES_TBL)) {
            return redirect()->to(base_url('sales/commission-rules'))->with('error', 'Table not available.');
        }

        $this->db->table(self::RULES_TBL)->where('id', $id)->delete();

        return redirect()->to(base_url('sales/commission-rules'))->with('success', 'Rule deleted.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Create or refresh commission record for a deal when a commission_rule_id is set. */
    private function createCommissionRecord(int $dealId, array $data, bool $update = false): void
    {
        if (! $this->pmTableExists(self::COMM_TBL) || ! $this->pmTableExists(self::RULES_TBL)) {
            return;
        }

        $ruleId = (int) ($data['commission_rule_id'] ?? 0);
        if ($ruleId < 1) {
            return;
        }

        $rule = $this->db->table(self::RULES_TBL)->where('id', $ruleId)->get()->getRowArray();
        if (! $rule) {
            return;
        }

        $baseAmount = (float) ($data['agreed_price'] ?? $data['deal_value'] ?? 0);
        if ($baseAmount <= 0) {
            return;
        }

        $agentAmount   = $rule['commission_type'] === 'flat' ? (float) $rule['agent_rate']   : round($baseAmount * (float) $rule['agent_rate']   / 100, 2);
        $companyAmount = $rule['commission_type'] === 'flat' ? (float) $rule['company_rate'] : round($baseAmount * (float) $rule['company_rate'] / 100, 2);

        if ($update) {
            $existing = $this->db->table(self::COMM_TBL)->where('deal_id', $dealId)->get()->getRowArray();
            if ($existing) {
                $this->db->table(self::COMM_TBL)->where('deal_id', $dealId)->update([
                    'rule_id'        => $ruleId,
                    'agent_id'       => (int) ($data['agent_id'] ?? 0) ?: null,
                    'agent_amount'   => $agentAmount,
                    'company_amount' => $companyAmount,
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
                return;
            }
        }

        $this->db->table(self::COMM_TBL)->insert([
            'deal_id'        => $dealId,
            'rule_id'        => $ruleId,
            'agent_id'       => (int) ($data['agent_id'] ?? 0) ?: null,
            'agent_amount'   => $agentAmount,
            'company_amount' => $companyAmount,
            'status'         => 'pending',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string,mixed>> */
    private function facilityOptions(): array
    {
        return $this->scopeFacilities(
            $this->db->table('facilities')->select('id, name')->where('status', 'active')->orderBy('name')
        )->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function leadOptions(): array
    {
        if (! $this->pmTableExists('crm_leads')) {
            return [];
        }

        $q = $this->db->table('crm_leads')->select('id, lead_number, full_name')
            ->where('deleted_at', null)->orderBy('full_name');
        $this->scopeCompany($q);

        return $q->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function agentOptions(): array
    {
        return $this->db->table('users')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function commissionRuleOptions(): array
    {
        if (! $this->pmTableExists(self::RULES_TBL)) {
            return [];
        }

        $q = $this->db->table(self::RULES_TBL)->select('id, rule_name, agent_rate, company_rate');
        $this->scopeCompany($q);

        return $q->orderBy('rule_name')->get()->getResultArray();
    }

    /** @return array<string,mixed> */
    private function dealPayload(): array
    {
        return [
            'company_id'          => $this->pmCompanyId(),
            'deal_type'           => $this->request->getPost('deal_type'),
            'lead_id'             => (int) $this->request->getPost('lead_id') ?: null,
            'buyer_name'          => esc($this->request->getPost('buyer_name')),
            'buyer_phone'         => esc($this->request->getPost('buyer_phone')) ?: null,
            'buyer_email'         => esc($this->request->getPost('buyer_email')) ?: null,
            'facility_id'         => (int) $this->request->getPost('facility_id') ?: null,
            'unit_id'             => (int) $this->request->getPost('unit_id') ?: null,
            'deal_value'          => $this->request->getPost('deal_value') ?: null,
            'agreed_price'        => $this->request->getPost('agreed_price') ?: null,
            'stage'               => $this->request->getPost('stage') ?: 'prospect',
            'agent_id'            => (int) $this->request->getPost('agent_id') ?: null,
            'commission_rule_id'  => (int) $this->request->getPost('commission_rule_id') ?: null,
            'expected_close_date' => $this->request->getPost('expected_close_date') ?: null,
            'notes'               => esc($this->request->getPost('notes')) ?: null,
        ];
    }

    private function migrationView()
    {
        return view('sales/index', $this->viewData([
            'title'             => 'Sales Deals',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'deals'             => [],
            'filters'           => [],
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
