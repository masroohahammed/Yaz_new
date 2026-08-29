<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Controllers\Traits\ParkingContractTrait;
use App\Services\ParkingContractService;
use App\Services\UtilityAccountService;

class Leases extends BaseController
{
    use PmModuleTrait;
    use ParkingContractTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'lease_contracts';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $filters = [
            'search'   => trim((string) ($this->request->getGet('search') ?? '')),
            'status'   => $this->request->getGet('status') ?? '',
            'facility' => (int) ($this->request->getGet('facility') ?? 0),
        ];

        $q = $this->db->table(self::TABLE . ' lc')
            ->select('lc.*, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number, u.unit_type, u.plate_number AS unit_plate_number')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->join('facilities f', $this->leaseFacilityJoinSql(), 'left')
            ->where('lc.deleted_at', null);
        $this->scopeCompany($q, 'lc.company_id');
        $this->applyLeaseFacilityScope($q);

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('lc.contract_number', $filters['search'])
                ->orLike('t.full_name', $filters['search'])
                ->orLike('f.name', $filters['search'])
                ->orLike('u.unit_number', $filters['search'])
                ->groupEnd();
        }
        if ($filters['status'] !== '') {
            $q->where('lc.status', $filters['status']);
        }
        if ($filters['facility'] > 0) {
            $this->whereLeaseFacility($q, $filters['facility']);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('lc.id', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        $facilities = $this->scopedFacilitiesList('id, name');

        return view('leases/index', $this->viewData([
            'title'       => 'Lease Contracts',
            'contracts'   => $rows,
            'filters'     => $filters,
            'facilities'  => $facilities,
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

        $preUnitId = (int) ($this->request->getGet('unit_id') ?? 0);
        $units     = [];
        $preUnit   = null;
        if ($preUnitId > 0 && $this->db->tableExists('units')) {
            $preUnit = $this->db->table('units')->where('id', $preUnitId)->get()->getRowArray();
            if ($preUnit) {
                $units = $this->unitsForFacility((int) $preUnit['facility_id']);
            }
        }

        return view('leases/form', $this->viewData([
            'title'      => 'New Lease Contract',
            'contract'   => null,
            'tenants'    => $this->tenantOptions(),
            'facilities' => $this->facilityOptions(),
            'units'      => $units,
            'preUnit'    => $preUnit,
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module is not available. Run database migration first.');
        }

        $rules = [
            'tenant_id'   => 'required|integer',
            'facility_id' => 'required|integer',
            'unit_id'     => 'required|integer',
            'start_date'  => 'required|valid_date[Y-m-d]',
            'end_date'    => 'required|valid_date[Y-m-d]',
            'rent_amount' => 'required|decimal',
            'status'      => 'required|in_list[draft,active,expired,terminated,renewed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->assertFacilityAccess((int) $this->request->getPost('facility_id'));

        $data = $this->contractPayload();
        $data['contract_number'] = $this->generateNumber('LC', self::TABLE, 'contract_number');
        $data['created_by']      = $this->currentUser()['id'] ?: null;
        $data['created_at']      = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->syncParkingUnitPlate((int) $data['unit_id'], $data);

        $this->logActivity('create', 'lease_contracts', $id, 'Contract created: ' . $data['contract_number']);

        if (($data['status'] ?? '') === 'active' && ! empty($data['auto_generate_invoices'])) {
            $this->doGenerateInvoices($id);
        }

        if (($data['status'] ?? '') === 'active') {
            (new UtilityAccountService($this->db))->transferToTenantForUnit(
                (int) $data['unit_id'],
                (int) $data['tenant_id'],
                $data['start_date'] ?? date('Y-m-d')
            );
        }

        return redirect()->to(base_url('contracts/' . $id))->with('success', 'Lease contract created.');
    }

    public function show(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $contract = $this->contractDetail($id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $payments = [];
        if ($this->pmTableExists('lease_payments')) {
            $payments = $this->db->table('lease_payments')
                ->where('contract_id', $id)
                ->orderBy('due_date', 'DESC')
                ->limit(20)
                ->get()->getResultArray();
        }

        $amendments = [];
        if ($this->pmTableExists('lease_amendments')) {
            $amendments = $this->db->table('lease_amendments')
                ->where('contract_id', $id)
                ->orderBy('effective_date', 'DESC')
                ->get()->getResultArray();
        }

        $offers = [];
        if ($this->pmTableExists('complimentary_offers')) {
            $offers = $this->db->table('complimentary_offers')
                ->where('contract_id', $id)
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();
        }

        $documents = [];
        if ($this->pmTableExists('documents') && $this->db->fieldExists('ref_id', 'documents')) {
            $documents = $this->db->table('documents')
                ->where('module', 'lease_contracts')
                ->where('ref_id', $id)
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();
        }

        return view('leases/show', $this->viewData([
            'title'      => 'Contract ' . $contract['contract_number'],
            'contract'   => $contract,
            'payments'   => $payments,
            'amendments' => $amendments,
            'offers'     => $offers,
            'documents'  => $documents,
        ]));
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $contract = $this->pmFind(self::TABLE, $id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $units = $this->unitsForFacility((int) $contract['facility_id']);

        return view('leases/form', $this->viewData([
            'title'      => 'Edit Contract',
            'contract'   => $contract,
            'tenants'    => $this->tenantOptions(),
            'facilities' => $this->facilityOptions(),
            'units'      => $units,
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module is not available. Run database migration first.');
        }

        if (! $this->pmFind(self::TABLE, $id)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $rules = [
            'tenant_id'   => 'required|integer',
            'facility_id' => 'required|integer',
            'unit_id'     => 'required|integer',
            'start_date'  => 'required|valid_date[Y-m-d]',
            'end_date'    => 'required|valid_date[Y-m-d]',
            'rent_amount' => 'required|decimal',
            'status'      => 'required|in_list[draft,active,expired,terminated,renewed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->assertFacilityAccess((int) $this->request->getPost('facility_id'));

        $data = $this->contractPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->syncParkingUnitPlate((int) $data['unit_id'], $data);

        $this->logActivity('update', 'lease_contracts', $id, 'Contract updated');

        return redirect()->to(base_url('contracts/' . $id))->with('success', 'Contract updated.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module is not available. Run database migration first.');
        }

        $contract = $this->pmFind(self::TABLE, $id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        if ($this->db->fieldExists('deleted_at', self::TABLE)) {
            $this->db->table(self::TABLE)->where('id', $id)->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->table(self::TABLE)->where('id', $id)->delete();
        }

        $this->logActivity('delete', 'lease_contracts', $id, 'Contract deleted: ' . ($contract['contract_number'] ?? $id));

        return redirect()->to(base_url('contracts'))->with('success', 'Contract removed.');
    }

    // ── Renew ────────────────────────────────────────────────────────────────

    public function renew(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $old = $this->contractDetail($id);
        if (! $old) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $rules = [
            'new_start_date' => 'required|valid_date[Y-m-d]',
            'new_end_date'   => 'required|valid_date[Y-m-d]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newRent      = $this->request->getPost('new_rent') ?: $old['rent_amount'];
        $newFrequency = $this->request->getPost('payment_frequency') ?: $old['payment_frequency'];

        $newData = [
            'company_id'             => $old['company_id'],
            'tenant_id'              => $old['tenant_id'],
            'facility_id'            => $old['facility_id'],
            'unit_id'                => $old['unit_id'],
            'status'                 => 'active',
            'start_date'             => $this->request->getPost('new_start_date'),
            'end_date'               => $this->request->getPost('new_end_date'),
            'rent_amount'            => $newRent,
            'payment_frequency'      => $newFrequency,
            'payment_type'           => $old['payment_type'],
            'payment_day'            => $old['payment_day'],
            'late_penalty_pct'       => $old['late_penalty_pct'],
            'grace_period_days'      => $old['grace_period_days'],
            'vat_applicable'         => $old['vat_applicable'],
            'vat_rate'               => $old['vat_rate'],
            'auto_renew'             => $old['auto_renew'],
            'auto_generate_invoices' => $old['auto_generate_invoices'],
            'contract_terms'         => $old['contract_terms'],
            'parent_contract_id'     => $id,
            'contract_number'        => $this->generateNumber('LC', self::TABLE, 'contract_number'),
            'created_by'             => $this->currentUser()['id'] ?: null,
            'created_at'             => date('Y-m-d H:i:s'),
        ];

        $this->db->transStart();
        try {
            $this->db->table(self::TABLE)->where('id', $id)->update([
                'status'     => 'renewed',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->table(self::TABLE)->insert($newData);
            $newId = (int) $this->db->insertID();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Renewal failed. No records were saved.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Contract renew failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Renewal failed. Please try again.');
        }

        $this->logActivity('renew', 'lease_contracts', $id, 'Contract renewed → #' . $newId);

        return redirect()->to(base_url('contracts/' . $newId))->with('success', 'Contract renewed. New contract created.');
    }

    public function renewForm(int $id)
    {
        $contract = $this->workflowContract($id);
        if ($contract instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $contract;
        }

        return view('contracts/renew', $this->viewData([
            'title'    => 'Renew Contract',
            'contract' => $contract,
        ]));
    }

    // ── Terminate ────────────────────────────────────────────────────────────

    public function terminate(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $contract = $this->contractDetail($id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $reason = esc(trim((string) ($this->request->getPost('termination_reason') ?: $this->request->getPost('reason'))));
        if ($reason === '') {
            return redirect()->back()->with('error', 'Termination reason is required.');
        }

        $update = [
            'status'             => 'terminated',
            'termination_reason' => $reason,
            'updated_at'         => date('Y-m-d H:i:s'),
        ];
        $termDate = trim((string) $this->request->getPost('termination_date'));
        if ($termDate !== '' && $this->db->fieldExists('notes', self::TABLE)) {
            $update['notes'] = trim((string) ($contract['notes'] ?? '') . "\nTerminated on {$termDate}: {$reason}");
        }

        $this->db->table(self::TABLE)->where('id', $id)->update($update);

        if ($this->db->tableExists('units') && $this->db->fieldExists('status', 'units')) {
            $this->db->table('units')->where('id', $contract['unit_id'])->update(['status' => 'vacant']);
        }

        (new UtilityAccountService($this->db))->transferBackForUnit(
            (int) $contract['unit_id'],
            date('Y-m-d'),
            $reason
        );

        $this->logActivity('terminate', 'lease_contracts', $id, 'Contract terminated: ' . $reason);

        return redirect()->to(base_url('contracts/' . $id))->with('success', 'Contract terminated.');
    }

    public function terminateForm(int $id)
    {
        $contract = $this->workflowContract($id);
        if ($contract instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $contract;
        }

        return view('contracts/terminate', $this->viewData([
            'title'    => 'Terminate Contract',
            'contract' => $contract,
        ]));
    }

    // ── Amendment ────────────────────────────────────────────────────────────

    public function amendment(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $contract = $this->pmFind(self::TABLE, $id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $effectiveDate = $this->request->getPost('effective_date');
        $description   = esc(trim((string) $this->request->getPost('description')));
        if (! $effectiveDate || $description === '') {
            return redirect()->back()->with('error', 'Effective date and description are required.');
        }

        $newRent    = $this->request->getPost('new_rent') ?: null;
        $newEndDate = $this->request->getPost('new_end_date') ?: null;

        if ($this->pmTableExists('lease_amendments')) {
            $this->db->table('lease_amendments')->insert([
                'contract_id'   => $id,
                'new_rent'      => $newRent,
                'new_end_date'  => $newEndDate,
                'effective_date'=> $effectiveDate,
                'description'   => $description,
                'created_by'    => $this->currentUser()['id'] ?: null,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];
        if ($newRent !== null) {
            $updateData['rent_amount'] = $newRent;
        }
        if ($newEndDate !== null) {
            $updateData['end_date'] = $newEndDate;
        }
        $this->db->table(self::TABLE)->where('id', $id)->update($updateData);

        $this->logActivity('amendment', 'lease_contracts', $id, 'Amendment: ' . $description);

        return redirect()->to(base_url('contracts/' . $id))->with('success', 'Amendment recorded.');
    }

    public function amendmentForm(int $id)
    {
        $contract = $this->workflowContract($id);
        if ($contract instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $contract;
        }

        return view('contracts/amendment', $this->viewData([
            'title'    => 'Amend Contract',
            'contract' => $contract,
        ]));
    }

    // ── Penalties ────────────────────────────────────────────────────────────

    public function applyPenalties(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('contracts/' . $id));
        }

        $contract = $this->workflowContract($id);
        if ($contract instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $contract;
        }

        if (! $this->pmTableExists('lease_payments')) {
            return redirect()->to(base_url('contracts/' . $id))->with('error', 'Payments table is not available.');
        }

        $pct   = (float) ($contract['late_penalty_pct'] ?? 0);
        $grace = (int) ($contract['grace_period_days'] ?? 0);
        if ($pct <= 0) {
            return redirect()->to(base_url('contracts/' . $id))
                ->with('error', 'No late-penalty rate is configured on this contract. Set Late Penalty % on the contract before applying penalties.');
        }

        $cutoff = date('Y-m-d', strtotime("-{$grace} days"));
        $overdue = $this->db->table('lease_payments')
            ->where('contract_id', $id)
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_date <', $cutoff)
            ->where('payment_type !=', 'penalty')
            ->get()->getResultArray();

        $existingNotes = [];
        $existing = $this->db->table('lease_payments')
            ->select('notes')
            ->where('contract_id', $id)
            ->where('payment_type', 'penalty')
            ->get()->getResultArray();
        foreach ($existing as $row) {
            $existingNotes[(string) ($row['notes'] ?? '')] = true;
        }

        $count = 0;
        $this->db->transStart();
        try {
            foreach ($overdue as $p) {
                $ref   = (string) ($p['payment_number'] ?? $p['id']);
                $notes = 'Late penalty for ' . $ref;
                if (isset($existingNotes[$notes])) {
                    continue;
                }
                $penalty = round((float) $p['amount'] * $pct / 100, 2);
                if ($penalty <= 0) {
                    continue;
                }
                $this->db->table('lease_payments')->insert([
                    'company_id'     => $contract['company_id'] ?? $this->pmCompanyId(),
                    'payment_number' => $this->generateNumber('PAY', 'lease_payments', 'payment_number'),
                    'contract_id'    => $id,
                    'tenant_id'      => $contract['tenant_id'],
                    'facility_id'    => $contract['facility_id'],
                    'unit_id'        => $contract['unit_id'],
                    'payment_type'   => 'penalty',
                    'payment_method' => 'other',
                    'amount'         => $penalty,
                    'status'         => 'pending',
                    'due_date'       => date('Y-m-d'),
                    'notes'          => $notes,
                    'created_by'     => $this->currentUser()['id'] ?: null,
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->to(base_url('contracts/' . $id))->with('error', 'Penalty application failed. No records were saved.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Apply penalties failed: ' . $e->getMessage());

            return redirect()->to(base_url('contracts/' . $id))->with('error', 'Penalty application failed. Please try again.');
        }

        $this->logActivity('penalty', 'lease_contracts', $id, "Applied {$count} penalty invoice(s)");

        if ($count === 0) {
            return redirect()->to(base_url('contracts/' . $id))
                ->with('warning', 'No new penalties applied. Overdue invoices already have a penalty, or none are past the grace period.');
        }

        return redirect()->to(base_url('contracts/' . $id))->with('success', "Applied {$count} penalty invoice(s).");
    }

    public function savePrint(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('contracts/' . $id . '/print'));
        }

        $contract = $this->workflowContract($id);
        if ($contract instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $contract;
        }

        $en = (string) $this->request->getPost('custom_content_en');
        $ar = (string) $this->request->getPost('custom_content_ar');

        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('custom_content_en', self::TABLE)) {
            $data['custom_content_en'] = $en;
        }
        if ($this->db->fieldExists('custom_content_ar', self::TABLE)) {
            $data['custom_content_ar'] = $ar;
        }
        if ($this->db->fieldExists('edited_at', self::TABLE)) {
            $data['edited_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->fieldExists('edited_by', self::TABLE)) {
            $data['edited_by'] = $this->currentUser()['id'] ?: null;
        }

        try {
            $this->db->table(self::TABLE)->where('id', $id)->update($data);
        } catch (\Throwable $e) {
            log_message('error', 'Save & print failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Could not save print content. Please try again.');
        }

        $this->logActivity('save_print', 'lease_contracts', $id, 'Print content saved');

        return redirect()->to(base_url('contracts/' . $id . '/print'))->with('success', 'Print content saved.');
    }

    // ── Print ─────────────────────────────────────────────────────────────────

    public function printView(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $contract = $this->contractDetail($id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        if ($this->isParkingContractRow($contract)) {
            return redirect()->to(base_url('contracts/' . $id . '/parking-print'));
        }

        $templateEn = $contract['custom_content_en'] ?? '';
        $templateAr = $contract['custom_content_ar'] ?? '';

        if ($templateEn === '' && $this->pmTableExists('contract_templates')) {
            $tplId = $contract['template_id'] ?? null;
            $q     = $this->db->table('contract_templates')->where('is_active', 1);
            if ($tplId) {
                $q->where('id', $tplId);
            }
            $tpl = $q->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($tpl) {
                $templateEn = $tpl['content_en'] ?? '';
                $templateAr = $tpl['content_ar'] ?? '';
            }
        }

        $vars = [
            '{{unit_number}}'       => esc($contract['unit_number'] ?? ''),
            '{{property_name}}'     => esc($contract['facility_name'] ?? ''),
            '{{tenant_name}}'       => esc($contract['tenant_name'] ?? ''),
            '{{rent_amount}}'       => number_format((float) ($contract['rent_amount'] ?? 0), 2),
            '{{currency}}'          => $this->settings['currency'] ?? 'QAR',
            '{{payment_frequency}}' => esc($contract['payment_frequency'] ?? ''),
            '{{start_date}}'        => esc($contract['start_date'] ?? ''),
            '{{end_date}}'          => esc($contract['end_date'] ?? ''),
            '{{contract_number}}'   => esc($contract['contract_number'] ?? ''),
        ];
        $templateEn = strtr($templateEn, $vars);
        $templateAr = strtr($templateAr, $vars);

        return view('leases/print', $this->viewData([
            'title'       => 'Contract ' . $contract['contract_number'],
            'contract'    => $contract,
            'templateEn'  => $templateEn,
            'templateAr'  => $templateAr,
            'usePdf'      => true,
        ]));
    }

    public function printParkingContract(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $contract = $this->contractDetail($id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        if (! $this->isParkingContractRow($contract)) {
            return redirect()->to(base_url('contracts/' . $id . '/print'))->with('error', 'Standard print applies to non-parking contracts.');
        }

        $unitId = (int) ($contract['unit_id'] ?? 0);
        if ($this->request->getMethod() === 'GET' && ! $this->request->getGet('preview')) {
            helper('fm');

            return view('leases/parking_contract_form', $this->viewData([
                'title'    => 'Parking Contract — ' . $contract['contract_number'],
                'unit'     => [
                    'id'           => $unitId,
                    'unit_number'  => $contract['unit_number'],
                    'facility_name'=> $contract['facility_name'],
                    'unit_type'    => 'parking',
                ],
                'd'        => (new ParkingContractService($this->db))->buildDefaults($unitId, $id),
                'backUrl'  => base_url('contracts/' . $id),
                'printUrl' => base_url('contracts/' . $id . '/parking-print'),
            ]));
        }

        $svc      = new ParkingContractService($this->db);
        $defaults = $svc->buildDefaults($unitId, $id);
        $d        = $svc->mergeFormInput($defaults, array_merge(
            $this->request->getPost() ?? [],
            $this->request->getGet() ?? []
        ));
        $d['lease_contract_id'] = $id;

        $this->persistParkingContractFields($unitId, $id, $d);

        $wantPdf = $this->request->getPost('pdf') || $this->request->getGet('pdf');

        return $this->renderParkingContractDocument($d, (bool) $wantPdf);
    }

    public function ajaxUnits(int $facilityId)
    {
        if (! $this->db->tableExists('units')) {
            return $this->response->setJSON([]);
        }

        $q = $this->db->table('units')
            ->select('id, unit_number, unit_type, plate_number')
            ->where('facility_id', $facilityId)
            ->orderBy('unit_number');
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }

        return $this->response->setJSON($q->get()->getResultArray());
    }

    // ── Generate Invoices ────────────────────────────────────────────────────

    public function generateInvoices(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE) || ! $this->pmTableExists('lease_payments')) {
            return redirect()->to(base_url('contracts'))->with('error', 'Required tables not available.');
        }

        $contract = $this->contractDetail($id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        $this->db->transStart();
        try {
            $count = $this->doGenerateInvoices($id);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->to(base_url('contracts/' . $id))->with('error', 'Invoice generation failed. No records were saved.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Lease invoice generation failed: ' . $e->getMessage());

            return redirect()->to(base_url('contracts/' . $id))->with('error', 'Invoice generation failed. Please try again.');
        }

        if ($count === 0) {
            return redirect()->to(base_url('contracts/' . $id))
                ->with('warning', 'No new invoices generated. Existing billing periods were skipped to prevent duplicates.');
        }

        return redirect()->to(base_url('contracts/' . $id))->with('success', $count . ' invoice(s) generated.');
    }

    // ── Sync unit contracts → lease module ───────────────────────────────────

    public function syncFromUnits()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('contracts'));
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $facilityIds = $this->companyScope()->facilityIds();
        $stats       = (new \App\Services\UnitLeaseSyncService($this->db))->syncAll(
            $this->pmCompanyId(),
            $facilityIds,
            $this->currentUser()['id'] ?: null
        );

        $msg = sprintf(
            'Sync complete: %d created, %d updated, %d skipped.',
            $stats['created'],
            $stats['updated'],
            $stats['skipped']
        );
        if (! empty($stats['errors'])) {
            $msg .= ' ' . count($stats['errors']) . ' error(s).';
        }

        $this->logActivity('sync', 'lease_contracts', 0, $msg);

        $redirect = redirect()->to(base_url('contracts'))->with('success', $msg);
        if (! empty($stats['errors'])) {
            $redirect->with('sync_errors', $stats['errors']);
        }

        return $redirect;
    }

    // ── Export CSV ───────────────────────────────────────────────────────────

    public function exportCsv()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $q = $this->db->table(self::TABLE . ' lc')
            ->select('lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number, lc.status, lc.start_date, lc.end_date, lc.rent_amount, lc.payment_frequency')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->join('facilities f', $this->leaseFacilityJoinSql(), 'left')
            ->where('lc.deleted_at', null);
        $this->scopeCompany($q, 'lc.company_id');
        $this->applyLeaseFacilityScope($q);

        $rows = $q->orderBy('lc.id', 'DESC')->get()->getResultArray();

        $headers = ['Contract #', 'Tenant', 'Property', 'Unit', 'Status', 'Start', 'End', 'Rent', 'Frequency'];

        return $this->csvResponse('contracts_' . date('Ymd') . '.csv', $headers, $rows);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|\CodeIgniter\HTTP\RedirectResponse
     */
    private function workflowContract(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('contracts'))->with('error', 'Leases module not available.');
        }

        $contract = $this->contractDetail($id);
        if (! $contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Contract not found.');
        }

        return $contract;
    }


    private function doGenerateInvoices(int $contractId): int
    {
        $contract = $this->contractDetail($contractId);
        if (! $contract || empty($contract['auto_generate_invoices'])) {
            return 0;
        }

        $startDate = $contract['billing_start_date'] ?: $contract['start_date'];
        $endDate   = $contract['end_date'];
        $frequency = $contract['payment_frequency'] ?? 'monthly';

        if (! $startDate || ! $endDate) {
            return 0;
        }

        $offers = [];
        if ($this->pmTableExists('complimentary_offers')) {
            $offers = $this->db->table('complimentary_offers')
                ->where('contract_id', $contractId)
                ->where('status', 'active')
                ->get()->getResultArray();
        }

        $existingPeriods = [];
        $existing = $this->db->table('lease_payments')
            ->select('period_from, period_to')
            ->where('contract_id', $contractId)
            ->get()->getResultArray();
        foreach ($existing as $e) {
            $existingPeriods[] = $e['period_from'] . '_' . $e['period_to'];
        }

        $current = strtotime($startDate);
        $end     = strtotime($endDate);
        $count   = 0;

        while ($current <= $end) {
            switch ($frequency) {
                case 'quarterly':
                    $next = strtotime('+3 months', $current);
                    break;
                case 'yearly':
                    $next = strtotime('+1 year', $current);
                    break;
                default:
                    $next = strtotime('+1 month', $current);
            }

            $periodFrom = date('Y-m-d', $current);
            $periodTo   = date('Y-m-d', min($next - 86400, $end));
            $key        = $periodFrom . '_' . $periodTo;

            if (in_array($key, $existingPeriods, true)) {
                $current = $next;
                continue;
            }

            $inOffer = false;
            foreach ($offers as $offer) {
                if (! empty($offer['start_date']) && ! empty($offer['end_date'])) {
                    if ($periodFrom >= $offer['start_date'] && $periodFrom <= $offer['end_date']) {
                        $inOffer = true;
                        break;
                    }
                }
            }

            if (! $inOffer) {
                $payNo = $this->generateNumber('PAY', 'lease_payments', 'payment_number');
                $this->db->table('lease_payments')->insert([
                    'company_id'      => $contract['company_id'],
                    'payment_number'  => $payNo,
                    'contract_id'     => $contractId,
                    'tenant_id'       => $contract['tenant_id'],
                    'facility_id'     => $contract['facility_id'],
                    'unit_id'         => $contract['unit_id'],
                    'payment_type'    => 'rent',
                    'payment_method'  => $contract['payment_type'] ?? 'cash',
                    'amount'          => $contract['rent_amount'],
                    'status'          => 'pending',
                    'due_date'        => $periodFrom,
                    'period_from'     => $periodFrom,
                    'period_to'       => $periodTo,
                    'created_by'      => $this->currentUser()['id'] ?: null,
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }

            $current = $next;
        }

        return $count;
    }

    /** @return array<string,mixed>|null */
    private function contractDetail(int $id): ?array
    {
        $q = $this->db->table(self::TABLE . ' lc')
            ->select('lc.*, t.full_name AS tenant_name, t.phone AS tenant_phone, t.qid_no, t.passport_no, t.nationality, f.name AS facility_name, f.city AS facility_city, f.address AS facility_address, u.unit_number, u.unit_type, u.plate_number AS unit_plate_number')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->join('facilities f', $this->leaseFacilityJoinSql(), 'left')
            ->where('lc.id', $id)
            ->where('lc.deleted_at', null);
        $this->applyLeaseFacilityScope($q);

        return $q->get()->getRowArray() ?: null;
    }

    /** @return list<array<string,mixed>> */
    private function tenantOptions(): array
    {
        if (! $this->pmTableExists('tenants')) {
            return [];
        }

        $q = $this->db->table('tenants')->select('id, full_name, phone, qid_no, passport_no')
            ->where('deleted_at', null)->where('status', 'active')->orderBy('full_name');
        $this->scopeCompany($q);

        return $q->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function facilityOptions(): array
    {
        return $this->scopedFacilitiesList('id, name');
    }

    private function leaseFacilityJoinSql(): string
    {
        return $this->db->fieldExists('facility_id', self::TABLE)
            ? 'f.id = lc.facility_id'
            : 'f.id = u.facility_id';
    }

    private function leaseFacilityScopeColumn(): string
    {
        return $this->db->fieldExists('facility_id', self::TABLE)
            ? 'lc.facility_id'
            : 'u.facility_id';
    }

    private function applyLeaseFacilityScope(object $q): void
    {
        if ($this->db->fieldExists('facility_id', self::TABLE)
            || ($this->db->tableExists('units') && $this->db->fieldExists('facility_id', 'units'))) {
            $this->scopeFacilities($q, $this->leaseFacilityScopeColumn());
        }
    }

    private function whereLeaseFacility(object $q, int $facilityId): void
    {
        if ($facilityId < 1) {
            return;
        }
        if ($this->db->fieldExists('facility_id', self::TABLE)) {
            $q->where('lc.facility_id', $facilityId);

            return;
        }
        if ($this->db->tableExists('units') && $this->db->fieldExists('facility_id', 'units')) {
            $q->where('u.facility_id', $facilityId);
        }
    }

    /** @return list<array<string,mixed>> */
    private function unitsForFacility(int $facilityId): array
    {
        if ($facilityId < 1 || ! $this->db->tableExists('units')) {
            return [];
        }

        $q = $this->db->table('units')->select('id, unit_number, unit_type, plate_number')
            ->where('facility_id', $facilityId)->orderBy('unit_number');
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }

        return $q->get()->getResultArray();
    }

    private function contractPayload(): array
    {
        $unitId = (int) $this->request->getPost('unit_id');
        $unit   = $unitId > 0 && $this->db->tableExists('units')
            ? $this->db->table('units')->where('id', $unitId)->get()->getRowArray()
            : null;
        $isParking = $unit && strtolower((string) ($unit['unit_type'] ?? '')) === 'parking';

        $data = [
            'company_id'             => $this->pmCompanyId(),
            'tenant_id'              => (int) $this->request->getPost('tenant_id'),
            'facility_id'            => (int) $this->request->getPost('facility_id'),
            'unit_id'                => $unitId,
            'status'                 => $this->request->getPost('status'),
            'signed_date'            => $this->request->getPost('signed_date') ?: null,
            'billing_start_date'     => $this->request->getPost('billing_start_date') ?: null,
            'start_date'             => $this->request->getPost('start_date'),
            'end_date'               => $this->request->getPost('end_date'),
            'rent_amount'            => $this->request->getPost('rent_amount'),
            'security_deposit'       => $this->request->getPost('security_deposit') ?: null,
            'payment_frequency'      => $this->request->getPost('payment_frequency') ?: 'monthly',
            'payment_type'           => $this->request->getPost('payment_type') ?: 'cheque',
            'payment_day'            => $this->request->getPost('payment_day') ?: null,
            'late_penalty_pct'       => $this->request->getPost('late_penalty_pct') ?: null,
            'grace_period_days'      => $this->request->getPost('grace_period_days') ?: null,
            'discount_pct'           => $this->request->getPost('discount_pct') ?: null,
            'vat_applicable'         => $this->request->getPost('vat_applicable') ? 1 : 0,
            'vat_rate'               => $this->request->getPost('vat_rate') ?: null,
            'auto_renew'             => $this->request->getPost('auto_renew') ? 1 : 0,
            'auto_generate_invoices' => $this->request->getPost('auto_generate_invoices') ? 1 : 0,
            'contract_terms'         => esc($this->request->getPost('contract_terms')) ?: null,
            'notes'                  => esc($this->request->getPost('notes')) ?: null,
        ];

        if ($isParking && $this->db->fieldExists('contract_kind', self::TABLE)) {
            $data['contract_kind'] = 'parking';
        }
        if ($isParking && $this->db->fieldExists('plate_number', self::TABLE)) {
            $plate = trim((string) $this->request->getPost('plate_number'));
            $data['plate_number'] = $plate !== '' ? esc($plate) : ($unit['plate_number'] ?? null);
        }
        if ($isParking && $this->db->fieldExists('vehicle_type', self::TABLE)) {
            $data['vehicle_type'] = esc(trim((string) $this->request->getPost('vehicle_type') ?? '')) ?: null;
        }
        if ($isParking && $this->db->fieldExists('vehicle_description', self::TABLE)) {
            $data['vehicle_description'] = esc(trim((string) $this->request->getPost('vehicle_description') ?? '')) ?: null;
        }
        if ($isParking && $this->db->fieldExists('title_deed_no', self::TABLE)) {
            $data['title_deed_no'] = esc(trim((string) $this->request->getPost('title_deed_no') ?? '')) ?: null;
        }
        if ($isParking && $this->db->fieldExists('zone_no', self::TABLE)) {
            $data['zone_no'] = esc(trim((string) $this->request->getPost('zone_no') ?? '')) ?: null;
        }
        if ($isParking && $this->db->fieldExists('street_no', self::TABLE)) {
            $data['street_no'] = esc(trim((string) $this->request->getPost('street_no') ?? '')) ?: null;
        }
        if ($isParking && $this->db->fieldExists('building_no', self::TABLE)) {
            $data['building_no'] = esc(trim((string) $this->request->getPost('building_no') ?? '')) ?: null;
        }

        $this->applyTenantQidField($data);

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function applyTenantQidField(array &$data): void
    {
        if (! $this->db->fieldExists('tenant_qid', self::TABLE)) {
            return;
        }

        $qid = trim((string) $this->request->getPost('tenant_qid'));
        if ($qid === '' && ! empty($data['tenant_id'])) {
            $tenant = $this->db->table('tenants')
                ->select('qid_no, passport_no')
                ->where('id', (int) $data['tenant_id'])
                ->get()->getRowArray();
            $qid = trim((string) ($tenant['qid_no'] ?? $tenant['passport_no'] ?? ''));
        }

        $data['tenant_qid'] = $qid !== '' ? esc($qid) : null;

        if ($qid !== '' && ! empty($data['tenant_id']) && $this->db->fieldExists('qid_no', 'tenants')) {
            $this->db->table('tenants')->where('id', (int) $data['tenant_id'])->update([
                'qid_no'     => esc($qid),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /** @param array<string, mixed> $contract */
    private function isParkingContractRow(array $contract): bool
    {
        if (($contract['contract_kind'] ?? '') === 'parking') {
            return true;
        }

        return strtolower((string) ($contract['unit_type'] ?? '')) === 'parking';
    }

    /** @param array<string, mixed> $data */
    private function syncParkingUnitPlate(int $unitId, array $data): void
    {
        if ($unitId < 1 || ! $this->db->fieldExists('plate_number', 'units')) {
            return;
        }
        if (empty($data['plate_number'])) {
            return;
        }
        $unit = $this->db->table('units')->where('id', $unitId)->get()->getRowArray();
        if (! $unit || strtolower((string) ($unit['unit_type'] ?? '')) !== 'parking') {
            return;
        }
        $this->db->table('units')->where('id', $unitId)->update([
            'plate_number' => $data['plate_number'],
        ]);
    }

    /**
     * @param list<string>           $headers
     * @param list<array<string,mixed>> $rows
     */
    private function csvResponse(string $filename, array $headers, array $rows): \CodeIgniter\HTTP\Response
    {
        $output = implode(',', array_map(fn($h) => '"' . $h . '"', $headers)) . "\n";
        foreach ($rows as $row) {
            $output .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string) $v) . '"', array_values($row))) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($output);
    }

    private function migrationView()
    {
        return view('leases/index', $this->viewData([
            'title'             => 'Lease Contracts',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'contracts'         => [],
            'filters'           => [],
            'facilities'        => [],
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
