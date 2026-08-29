<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Services\UtilityAccountService;

class UtilityBilling extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const ACC_TABLE  = 'utility_accounts';
    private const BILL_TABLE = 'utility_bills';

    // ── Accounts ─────────────────────────────────────────────────────────────

    public function index()
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return view('utilities/index', $this->viewData([
                'title'             => 'Utility Accounts',
                'migrationRequired' => true,
                'accounts'          => [],
                'total'             => 0,
                'currentPage'       => 1,
                'perPage'           => 25,
            ]));
        }

        $filters = [
            'facility_id'   => (int) ($this->request->getGet('facility_id') ?? 0),
            'billing_mode'  => $this->request->getGet('billing_mode') ?? '',
            'status'        => $this->request->getGet('status') ?? 'active',
        ];

        $q = $this->db->table(self::ACC_TABLE . ' ua')
            ->select('ua.*, f.name AS facility_name, u.unit_number')
            ->join('facilities f', 'f.id = ua.facility_id', 'left')
            ->join('units u', 'u.id = ua.unit_id', 'left')
            ->where('ua.deleted_at', null);
        $this->scopeCompany($q, 'ua.company_id');
        $this->scopeFacilities($q, 'ua.facility_id');

        if ($filters['facility_id'] > 0) {
            $q->where('ua.facility_id', $filters['facility_id']);
        }
        if ($filters['billing_mode'] !== '') {
            $q->where('ua.billing_mode', $filters['billing_mode']);
        }
        if ($filters['status'] !== '') {
            $q->where('ua.status', $filters['status']);
        }

        $pg       = $this->paginate(25);
        $total    = (clone $q)->countAllResults(false);
        $accounts = $q->orderBy('ua.facility_id')->orderBy('ua.utility_name')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('utilities/index', $this->viewData([
            'title'       => 'Utility Accounts',
            'accounts'    => $accounts,
            'facilities'  => $facilities,
            'filters'     => $filters,
            'total'       => $total,
            'currentPage' => $pg['page'],
            'perPage'     => $pg['perPage'],
        ]));
    }

    public function create()
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('utilities/form', $this->viewData([
            'title'      => 'New Utility Account',
            'account'    => null,
            'facilities' => $facilities,
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $rules = [
            'utility_name' => 'required|max_length[80]',
            'facility_id'  => 'permit_empty|is_natural_no_zero',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facilityId = (int) $this->request->getPost('facility_id') ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        $this->db->table(self::ACC_TABLE)->insert([
            'company_id'     => $this->pmCompanyId(),
            'facility_id'    => $facilityId,
            'unit_id'        => (int) $this->request->getPost('unit_id') ?: null,
            'utility_name'   => esc($this->request->getPost('utility_name')),
            'provider_name'  => esc($this->request->getPost('provider_name')) ?: null,
            'account_number' => esc($this->request->getPost('account_number')) ?: null,
            'meter_number'   => esc($this->request->getPost('meter_number')) ?: null,
            'managed_by'     => esc($this->request->getPost('managed_by')) ?: null,
            'billing_mode'   => $this->request->getPost('billing_mode') ?: 'included',
            'monthly_charge' => $this->request->getPost('monthly_charge') ?: null,
            'status'         => 'active',
            'notes'          => esc($this->request->getPost('notes')) ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->db->insertID();
        $this->logActivity('create', self::ACC_TABLE, $id, 'Utility account created');

        return redirect()->to(base_url('utilities'))->with('success', 'Utility account created.');
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('utilities/form', $this->viewData([
            'title'      => 'Edit Utility Account',
            'account'    => $account,
            'facilities' => $facilities,
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $rules = ['utility_name' => 'required|max_length[80]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table(self::ACC_TABLE)->where('id', $id)->update([
            'facility_id'    => (int) $this->request->getPost('facility_id') ?: null,
            'unit_id'        => (int) $this->request->getPost('unit_id') ?: null,
            'utility_name'   => esc($this->request->getPost('utility_name')),
            'provider_name'  => esc($this->request->getPost('provider_name')) ?: null,
            'account_number' => esc($this->request->getPost('account_number')) ?: null,
            'meter_number'   => esc($this->request->getPost('meter_number')) ?: null,
            'managed_by'     => esc($this->request->getPost('managed_by')) ?: null,
            'billing_mode'   => $this->request->getPost('billing_mode') ?: 'included',
            'monthly_charge' => $this->request->getPost('monthly_charge') ?: null,
            'notes'          => esc($this->request->getPost('notes')) ?: null,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('update', self::ACC_TABLE, $id, 'Utility account updated');

        return redirect()->to(base_url('utilities'))->with('success', 'Account updated.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $this->db->table(self::ACC_TABLE)->where('id', $id)->update([
            'deleted_at' => date('Y-m-d H:i:s'),
            'status'     => 'inactive',
        ]);

        $this->logActivity('delete', self::ACC_TABLE, $id, 'Utility account deleted');

        return redirect()->to(base_url('utilities'))->with('success', 'Account removed.');
    }

    public function view(int $id)
    {
        if (! $this->pmTableExists(self::ACC_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $bills = $this->pmTableExists(self::BILL_TABLE)
            ? $this->db->table(self::BILL_TABLE)->where('account_id', $id)->orderBy('bill_date', 'DESC')->get()->getResultArray()
            : [];

        return view('utilities/view', $this->viewData([
            'title'   => $account['utility_name'],
            'account' => $account,
            'bills'   => $bills,
        ]));
    }

    public function by_unit(int $unitId)
    {
        $svc = new UtilityAccountService($this->db);
        $accounts = $svc->byUnit($unitId);
        $unit = $this->db->table('units u')
            ->select('u.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.id', $unitId)->get()->getRowArray();

        return view('utilities/by_unit', $this->viewData([
            'title'    => 'Unit Utilities',
            'unit'     => $unit,
            'accounts' => $accounts,
        ]));
    }

    public function transfer_to_tenant(int $id)
    {
        if (! $this->request->is('post')) {
            return $this->transferToTenantForm($id);
        }

        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $tenantId = (int) $this->request->getPost('tenant_id');
        $date = $this->request->getPost('transfer_date') ?: date('Y-m-d');
        if ($tenantId <= 0 || ! $date) {
            return redirect()->back()->with('error', 'Tenant and transfer date required.');
        }

        (new UtilityAccountService($this->db))->transferAccountToTenant($id, $tenantId, $date);
        $this->logActivity('transfer', self::ACC_TABLE, $id, 'Utility transferred to tenant');

        return redirect()->to(base_url('utilities/view/' . $id))->with('success', 'Utility responsibility transferred to tenant.');
    }

    private function transferToTenantForm(int $id)
    {
        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $tenants = $this->db->tableExists('tenants')
            ? $this->db->table('tenants')->where('status', 'active')->orderBy('full_name')->get()->getResultArray()
            : [];

        return view('utilities/transfer_tenant', $this->viewData([
            'title'   => 'Transfer to Tenant',
            'account' => $account,
            'tenants' => $tenants,
        ]));
    }

    public function transfer_back(int $id)
    {
        if (! $this->request->is('post')) {
            return $this->transferBackForm($id);
        }

        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $date = $this->request->getPost('transfer_date') ?: date('Y-m-d');
        (new UtilityAccountService($this->db))->transferAccountBack($id, $date, $this->request->getPost('reason'));
        $this->logActivity('transfer', self::ACC_TABLE, $id, 'Utility transferred back to company');

        return redirect()->to(base_url('utilities/view/' . $id))->with('success', 'Utility responsibility returned to company.');
    }

    private function transferBackForm(int $id)
    {
        $account = $this->pmFind(self::ACC_TABLE, $id);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        return view('utilities/transfer_back', $this->viewData([
            'title'   => 'Transfer Back',
            'account' => $account,
        ]));
    }

    // ── Bills ─────────────────────────────────────────────────────────────────

    public function bills(int $accountId)
    {
        if (! $this->pmTableExists(self::BILL_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $account = $this->pmFind(self::ACC_TABLE, $accountId);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $bills = $this->db->table(self::BILL_TABLE)
            ->where('account_id', $accountId)
            ->orderBy('bill_date', 'DESC')
            ->get()->getResultArray();

        $totalPending = array_sum(array_column(
            array_filter($bills, static fn ($b) => $b['status'] === 'pending'), 'amount'
        ));

        return view('utilities/bills', $this->viewData([
            'title'        => 'Bills — ' . $account['utility_name'],
            'account'      => $account,
            'bills'        => $bills,
            'totalPending' => $totalPending,
        ]));
    }

    public function addBill(int $accountId)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::BILL_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $account = $this->pmFind(self::ACC_TABLE, $accountId);
        if (! $account) {
            return redirect()->to(base_url('utilities'))->with('error', 'Account not found.');
        }

        $rules = ['amount' => 'required|decimal|greater_than[0]', 'due_date' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table(self::BILL_TABLE)->insert([
            'account_id'       => $accountId,
            'bill_no'          => esc($this->request->getPost('bill_no')) ?: null,
            'bill_date'        => $this->request->getPost('bill_date') ?: date('Y-m-d'),
            'period_from'      => $this->request->getPost('period_from') ?: null,
            'period_to'        => $this->request->getPost('period_to') ?: null,
            'reading_prev'     => $this->request->getPost('reading_prev') ?: null,
            'reading_curr'     => $this->request->getPost('reading_curr') ?: null,
            'amount'           => (float) $this->request->getPost('amount'),
            'charge_to_tenant' => $this->request->getPost('charge_to_tenant') ? 1 : 0,
            'due_date'         => $this->request->getPost('due_date') ?: null,
            'status'           => 'pending',
            'notes'            => esc($this->request->getPost('notes')) ?: null,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        $billId = (int) $this->db->insertID();
        $this->logActivity('create', self::BILL_TABLE, $billId, 'Bill added for account #' . $accountId);

        return redirect()->to(base_url('utilities/' . $accountId . '/bills'))->with('success', 'Bill added.');
    }

    public function transferToTenant(int $billId)
    {
        if (! $this->pmTableExists(self::BILL_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $this->db->table(self::BILL_TABLE)->where('id', $billId)->update([
            'charge_to_tenant' => 1,
            'paid_by'          => 'tenant',
            'status'           => 'transferred',
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('transfer', self::BILL_TABLE, $billId, 'Bill transferred to tenant');

        return redirect()->back()->with('success', 'Bill transferred to tenant.');
    }

    public function transferToOwner(int $billId)
    {
        if (! $this->pmTableExists(self::BILL_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        $this->db->table(self::BILL_TABLE)->where('id', $billId)->update([
            'charge_to_tenant' => 0,
            'paid_by'          => 'owner',
            'status'           => 'transferred',
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('transfer', self::BILL_TABLE, $billId, 'Bill charged to owner');

        return redirect()->back()->with('success', 'Bill assigned to owner.');
    }

    public function markBillPaid(int $billId)
    {
        if (! $this->pmTableExists(self::BILL_TABLE)) {
            return redirect()->to(base_url('utilities'))->with('error', 'Run migration first.');
        }

        if ($this->request->is('post')) {
            $paymentDate = $this->request->getPost('payment_date') ?: date('Y-m-d');
            $this->db->table(self::BILL_TABLE)->where('id', $billId)->update([
                'status'         => 'paid',
                'payment_date'   => $paymentDate,
                'payment_method' => $this->request->getPost('payment_method'),
                'paid_by'        => $this->request->getPost('paid_by'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            $this->logActivity('paid', self::BILL_TABLE, $billId, 'Bill marked paid');

            return redirect()->back()->with('success', 'Bill marked as paid.');
        }

        $bill = $this->db->table(self::BILL_TABLE)->where('id', $billId)->get()->getRowArray();

        return view('utilities/pay_bill', $this->viewData([
            'title' => 'Pay Bill',
            'bill'  => $bill,
        ]));
    }
}
