<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class Payments extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'lease_payments';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $filters = [
            'search'   => trim((string) ($this->request->getGet('search') ?? '')),
            'status'   => $this->request->getGet('status') ?? '',
            'contract' => (int) ($this->request->getGet('contract') ?? 0),
            'from'     => $this->request->getGet('from') ?? '',
            'to'       => $this->request->getGet('to') ?? '',
        ];

        $q = $this->db->table(self::TABLE . ' lp')
            ->select('lp.*, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name')
            ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left');
        $this->scopeCompany($q, 'lp.company_id');
        $this->scopeFacilities($q, 'lp.facility_id');

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('lp.payment_number', $filters['search'])
                ->orLike('t.full_name', $filters['search'])
                ->orLike('lc.contract_number', $filters['search'])
                ->groupEnd();
        }
        if ($filters['status'] !== '') {
            $q->where('lp.status', $filters['status']);
        }
        if ($filters['contract'] > 0) {
            $q->where('lp.contract_id', $filters['contract']);
        }
        if ($filters['from'] !== '') {
            $q->where('lp.due_date >=', $filters['from']);
        }
        if ($filters['to'] !== '') {
            $q->where('lp.due_date <=', $filters['to']);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('lp.due_date', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('payments/index', $this->viewData([
            'title'       => 'Lease Payments',
            'payments'    => $rows,
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

        return view('payments/form', $this->viewData([
            'title'     => 'Record Payment',
            'payment'   => null,
            'contracts' => $this->contractOptions(),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module is not available. Run database migration first.');
        }

        $rules = [
            'contract_id'    => 'permit_empty|integer',
            'amount'         => 'required|decimal',
            'payment_method' => 'required|max_length[50]',
            'due_date'       => 'permit_empty|valid_date[Y-m-d]',
            'status'         => 'required|in_list[pending,paid,partial,overdue,cancelled,postponed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->paymentPayload();
        $data['payment_number'] = $this->generateNumber('PAY', self::TABLE, 'payment_number');
        $data['created_by']     = $this->currentUser()['id'] ?: null;
        $data['created_at']     = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('create', 'lease_payments', $id, 'Payment created: ' . $data['payment_number']);

        return redirect()->to(base_url('payments'))->with('success', 'Payment recorded.');
    }

    public function show(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $partials = [];
        if ($this->pmTableExists('payment_partials')) {
            $partials = $this->db->table('payment_partials')
                ->where('payment_id', $id)
                ->orderBy('paid_date', 'DESC')
                ->get()->getResultArray();
        }

        return view('payments/show', $this->viewData([
            'title'    => 'Payment ' . ($payment['payment_number'] ?? $id),
            'payment'  => $payment,
            'partials' => $partials,
        ]));
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        return view('payments/form', $this->viewData([
            'title'     => 'Edit Payment',
            'payment'   => $payment,
            'contracts' => $this->contractOptions(),
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module is not available. Run database migration first.');
        }

        if (! $this->scopedPayment($id)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $rules = [
            'contract_id'    => 'permit_empty|integer',
            'amount'         => 'required|decimal',
            'payment_method' => 'required|max_length[50]',
            'due_date'       => 'permit_empty|valid_date[Y-m-d]',
            'status'         => 'required|in_list[pending,paid,partial,overdue,cancelled,postponed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->paymentPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->logActivity('update', 'lease_payments', $id, 'Payment updated');

        return redirect()->to(base_url('payments'))->with('success', 'Payment updated.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module is not available. Run database migration first.');
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->delete();
        $this->logActivity('delete', 'lease_payments', $id, 'Payment deleted: ' . $payment['payment_number']);

        return redirect()->to(base_url('payments'))->with('success', 'Payment removed.');
    }

    // ── Collect ──────────────────────────────────────────────────────────────

    public function collect(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module not available.');
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $amount      = $this->request->getPost('amount') ?: $payment['amount'];
        $paymentDate = $this->request->getPost('payment_date') ?: date('Y-m-d');

        $this->db->table(self::TABLE)->where('id', $id)->update([
            'status'       => 'paid',
            'amount'       => $amount,
            'payment_date' => $paymentDate,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        if (($payment['payment_method'] ?? '') === 'cheque') {
            $autoCreate = $this->settings['auto_create_cheque_record'] ?? '0';
            if ($autoCreate && $this->pmTableExists('cheques') && ! empty($payment['cheque_no'])) {
                $exists = $this->db->table('cheques')
                    ->where('cheque_no', $payment['cheque_no'])
                    ->where('contract_id', $payment['contract_id'])
                    ->countAllResults();
                if (! $exists) {
                    $this->db->table('cheques')->insert([
                        'company_id'   => $payment['company_id'],
                        'contract_id'  => $payment['contract_id'],
                        'tenant_id'    => $payment['tenant_id'],
                        'facility_id'  => $payment['facility_id'],
                        'cheque_no'    => $payment['cheque_no'],
                        'amount'       => $amount,
                        'status'       => 'cleared',
                        'cheque_date'  => $paymentDate,
                        'received_date'=> $paymentDate,
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $this->logActivity('collect', 'lease_payments', $id, 'Payment collected: ' . $payment['payment_number']);

        return redirect()->back()->with('success', 'Payment marked as paid.');
    }

    // ── Partial ──────────────────────────────────────────────────────────────

    public function partial(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module not available.');
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $partialAmount = $this->request->getPost('partial_amount');
        if (! $partialAmount || (float) $partialAmount <= 0) {
            return redirect()->back()->with('error', 'Partial amount is required.');
        }

        if ($this->pmTableExists('payment_partials')) {
            $this->db->table('payment_partials')->insert([
                'payment_id' => $id,
                'amount'     => $partialAmount,
                'paid_date'  => $this->request->getPost('paid_date') ?: date('Y-m-d'),
                'method'     => esc($this->request->getPost('method')) ?: $payment['payment_method'],
                'notes'      => esc($this->request->getPost('notes')) ?: null,
                'created_by' => $this->currentUser()['id'] ?: null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $paid    = $this->totalPartials($id) + (float) $partialAmount;
        $remaining = (float) $payment['amount'] - $paid;
        $notes   = 'Partial paid: ' . $paid . ' / ' . $payment['amount'] . ' — Remaining: ' . max(0, $remaining);

        $this->db->table(self::TABLE)->where('id', $id)->update([
            'status'     => 'partial',
            'notes'      => $notes,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('partial', 'lease_payments', $id, 'Partial payment: ' . $partialAmount);

        return redirect()->back()->with('success', 'Partial payment recorded.');
    }

    // ── Postpone ─────────────────────────────────────────────────────────────

    public function postpone(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module not available.');
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $postponedTo = $this->request->getPost('postponed_to');
        $note        = esc(trim((string) $this->request->getPost('postpone_note')));

        if (! $postponedTo) {
            return redirect()->back()->with('error', 'New due date is required.');
        }
        if ($note === '') {
            return redirect()->back()->with('error', 'Postpone note is required.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update([
            'status'     => 'postponed',
            'due_date'   => $postponedTo,
            'notes'      => 'Postponed to ' . $postponedTo . ': ' . $note,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('postpone', 'lease_payments', $id, 'Payment postponed to ' . $postponedTo);

        return redirect()->back()->with('success', 'Payment postponed.');
    }

    // ── Refund ────────────────────────────────────────────────────────────────

    public function refund(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module not available.');
        }

        $payment = $this->scopedPayment($id);
        if (! $payment) {
            return redirect()->to(base_url('payments'))->with('error', 'Payment not found.');
        }

        $refundType = esc($this->request->getPost('refund_type'));
        if (! $refundType) {
            return redirect()->back()->with('error', 'Refund type is required.');
        }

        if ($this->pmTableExists('refunds')) {
            $this->db->table('refunds')->insert([
                'payment_id'    => $id,
                'contract_id'   => $payment['contract_id'],
                'refund_type'   => $refundType,
                'refund_amount' => $this->request->getPost('refund_amount') ?: null,
                'refund_date'   => $this->request->getPost('refund_date') ?: date('Y-m-d'),
                'reference_no'  => esc($this->request->getPost('reference')) ?: null,
                'notes'         => esc($this->request->getPost('notes')) ?: null,
                'created_by'    => $this->currentUser()['id'] ?: null,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $this->logActivity('refund', 'lease_payments', $id, 'Refund recorded: ' . $refundType);

        return redirect()->back()->with('success', 'Refund recorded.');
    }

    // ── Export CSV ────────────────────────────────────────────────────────────

    public function exportCsv()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('payments'))->with('error', 'Payments module not available.');
        }

        $q = $this->db->table(self::TABLE . ' lp')
            ->select('lp.payment_number, lc.contract_number, t.full_name AS tenant_name, lp.payment_type, lp.payment_method, lp.amount, lp.status, lp.due_date, lp.payment_date, lp.period_from, lp.period_to')
            ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
            ->join('tenants t', 't.id = lp.tenant_id', 'left');
        $this->scopeCompany($q, 'lp.company_id');
        $this->scopeFacilities($q, 'lp.facility_id');

        $rows = $q->orderBy('lp.due_date', 'DESC')->get()->getResultArray();

        $headers = ['Payment #', 'Contract', 'Tenant', 'Type', 'Method', 'Amount', 'Status', 'Due Date', 'Paid Date', 'Period From', 'Period To'];

        return $this->csvResponse('payments_' . date('Ymd') . '.csv', $headers, $rows);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /** @return array<string,mixed>|null */
    private function scopedPayment(int $id): ?array
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return null;
        }

        $q = $this->db->table(self::TABLE . ' lp')
            ->select('lp.*, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number')
            ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->join('units u', 'u.id = lp.unit_id', 'left')
            ->where('lp.id', $id);
        $this->scopeCompany($q, 'lp.company_id');
        $this->scopeFacilities($q, 'lp.facility_id');

        return $q->get()->getRowArray() ?: null;
    }

    private function totalPartials(int $paymentId): float
    {
        if (! $this->pmTableExists('payment_partials')) {
            return 0.0;
        }

        $row = $this->db->table('payment_partials')
            ->selectSum('amount')
            ->where('payment_id', $paymentId)
            ->get()->getRowArray();

        return (float) ($row['amount'] ?? 0);
    }

    /** @return list<array<string,mixed>> */
    private function contractOptions(): array
    {
        if (! $this->pmTableExists('lease_contracts')) {
            return [];
        }

        $q = $this->db->table('lease_contracts lc')
            ->select('lc.id, lc.contract_number, t.full_name AS tenant_name')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.contract_number', 'DESC');
        $this->scopeCompany($q, 'lc.company_id');
        $this->scopeFacilities($q, 'lc.facility_id');

        return $q->get()->getResultArray();
    }

    /** @return array<string,mixed> */
    private function paymentPayload(): array
    {
        $contractId = (int) $this->request->getPost('contract_id') ?: null;
        $tenantId   = null;
        $facilityId = null;
        $unitId     = null;

        if ($contractId && $this->pmTableExists('lease_contracts')) {
            $contract = $this->db->table('lease_contracts')->where('id', $contractId)->get()->getRowArray();
            if ($contract) {
                $tenantId   = (int) ($contract['tenant_id'] ?? 0) ?: null;
                $facilityId = (int) ($contract['facility_id'] ?? 0) ?: null;
                $unitId     = (int) ($contract['unit_id'] ?? 0) ?: null;
            }
        }

        return [
            'company_id'         => $this->pmCompanyId(),
            'contract_id'        => $contractId,
            'tenant_id'          => $tenantId,
            'facility_id'        => $facilityId,
            'unit_id'            => $unitId,
            'payment_type'       => $this->request->getPost('payment_type') ?: 'rent',
            'payment_method'     => $this->request->getPost('payment_method'),
            'amount'             => $this->request->getPost('amount'),
            'status'             => $this->request->getPost('status'),
            'bank_name'          => esc($this->request->getPost('bank_name')) ?: null,
            'transfer_reference' => esc($this->request->getPost('transfer_reference')) ?: null,
            'cheque_no'          => esc($this->request->getPost('cheque_no')) ?: null,
            'payment_date'       => $this->request->getPost('payment_date') ?: null,
            'due_date'           => $this->request->getPost('due_date') ?: null,
            'period_from'        => $this->request->getPost('period_from') ?: null,
            'period_to'          => $this->request->getPost('period_to') ?: null,
            'reference_no'       => esc($this->request->getPost('reference_no')) ?: null,
            'notes'              => esc($this->request->getPost('notes')) ?: null,
        ];
    }

    /**
     * @param list<string>              $headers
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
        return view('payments/index', $this->viewData([
            'title'             => 'Lease Payments',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'payments'          => [],
            'filters'           => [],
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
