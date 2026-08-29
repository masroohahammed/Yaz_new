<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class Landlords extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'landlords';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $status = $this->request->getGet('status') ?? '';

        $q = $this->db->table(self::TABLE . ' l')->where('l.deleted_at', null);
        $this->scopeCompany($q, 'l.company_id');

        if ($search !== '') {
            $q->groupStart()
                ->like('l.full_name', $search)
                ->orLike('l.phone', $search)
                ->orLike('l.email', $search)
                ->orLike('l.id_number', $search)
                ->groupEnd();
        }
        if ($status !== '') {
            $q->where('l.status', $status);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('l.full_name', 'ASC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('landlords/index', $this->viewData([
            'title'       => 'Landlords',
            'landlords'   => $rows,
            'search'      => $search,
            'status'      => $status,
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

        return view('landlords/form', $this->viewData([
            'title'    => 'Add Landlord',
            'landlord' => null,
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlords module is not available. Run database migration first.');
        }

        $rules = [
            'full_name' => 'required|min_length[2]|max_length[200]',
            'phone'     => 'permit_empty|max_length[30]',
            'email'     => 'permit_empty|valid_email|max_length[150]',
            'status'    => 'required|in_list[active,inactive]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->landlordPayload();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('create', 'landlords', $id, 'Landlord created: ' . $data['full_name']);

        return redirect()->to(base_url('landlords'))->with('success', 'Landlord created successfully.');
    }

    public function show(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $data360 = [];
        try {
            $data360 = (new \App\Models\Landlord_model())->get360Data($id);
        } catch (\Throwable $e) {
            log_message('error', 'Landlord 360 load failed: ' . $e->getMessage());
        }

        return view('landlords/view', $this->viewData([
            'title'    => 'Landlord — ' . $landlord['full_name'],
            'landlord' => $landlord,
            'data360'  => $data360,
            'payouts'  => $data360['payouts'] ?? [],
        ]));
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        return view('landlords/form', $this->viewData([
            'title'    => 'Edit Landlord',
            'landlord' => $landlord,
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlords module is not available. Run database migration first.');
        }

        if (! $this->pmFind(self::TABLE, $id)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $rules = [
            'full_name' => 'required|min_length[2]|max_length[200]',
            'phone'     => 'permit_empty|max_length[30]',
            'email'     => 'permit_empty|valid_email|max_length[150]',
            'status'    => 'required|in_list[active,inactive]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->landlordPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->logActivity('update', 'landlords', $id, 'Landlord updated: ' . $data['full_name']);

        return redirect()->to(base_url('landlords'))->with('success', 'Landlord updated successfully.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlords module is not available. Run database migration first.');
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete', 'landlords', $id, 'Landlord deleted: ' . $landlord['full_name']);

        return redirect()->to(base_url('landlords'))->with('success', 'Landlord removed.');
    }

    // ── Payout ────────────────────────────────────────────────────────────────

    public function payout(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlords module not available.');
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        if ($this->request->is('get')) {
            return view('landlords/payout', $this->viewData([
                'title'    => 'Create Payout — ' . $landlord['full_name'],
                'landlord' => $landlord,
            ]));
        }

        if (! $this->pmTableExists('landlord_payouts')) {
            return redirect()->back()->with('error', 'Run migration 2026-07-23-140000_PmWorkflowExtras first.');
        }

        $grossRent  = $this->request->getPost('gross_rent') ?: null;
        $commission = $this->request->getPost('commission') ?: null;
        $deductions = $this->request->getPost('deductions') ?: 0;
        $netAmount  = $this->request->getPost('net_amount') ?: null;

        if ($netAmount === null && $grossRent !== null) {
            $netAmount = (float) $grossRent - (float) $commission - (float) $deductions;
        }

        $this->db->table('landlord_payouts')->insert([
            'company_id'     => $this->pmCompanyId(),
            'landlord_id'    => $id,
            'period_from'    => $this->request->getPost('period_from') ?: null,
            'period_to'      => $this->request->getPost('period_to') ?: null,
            'gross_rent'     => $grossRent,
            'commission'     => $commission,
            'deductions'     => $deductions,
            'net_amount'     => $netAmount,
            'status'         => 'pending',
            'payment_method' => esc($this->request->getPost('payment_method')) ?: null,
            'notes'          => esc($this->request->getPost('notes')) ?: null,
            'created_by'     => $this->currentUser()['id'] ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $payoutId = (int) $this->db->insertID();
        $this->logActivity('payout', 'landlords', $id, 'Payout created for landlord: ' . $landlord['full_name']);

        return redirect()->to(base_url('landlords/' . $id))->with('success', 'Payout created.');
    }

    public function payouts(int $id)
    {
        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $payouts = [];
        if ($this->pmTableExists('landlord_payouts')) {
            $payouts = $this->db->table('landlord_payouts')
                ->where('landlord_id', $id)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        return view('landlords/payouts', $this->viewData([
            'title'    => 'Payouts — ' . $landlord['full_name'],
            'landlord' => $landlord,
            'payouts'  => $payouts,
        ]));
    }

    // ── Mark Payout Paid ──────────────────────────────────────────────────────

    public function markPaid(int $payoutId)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists('landlord_payouts')) {
            return redirect()->back()->with('error', 'Payouts table not available.');
        }

        $q = $this->db->table('landlord_payouts lp')
            ->select('lp.*, l.company_id AS landlord_company_id, l.id AS scoped_landlord_id')
            ->join('landlords l', 'l.id = lp.landlord_id', 'left')
            ->where('lp.id', $payoutId);
        $this->scopeCompany($q, 'l.company_id');
        $payout = $q->get()->getRowArray();
        if (! $payout) {
            return redirect()->back()->with('error', 'Payout not found.');
        }

        if (($payout['status'] ?? '') === 'paid') {
            return redirect()->to(base_url('landlords/' . (int) $payout['landlord_id']))
                ->with('warning', 'This payout is already marked paid.');
        }

        $this->db->transStart();
        try {
            $this->db->table('landlord_payouts')->where('id', $payoutId)->update([
                'status'       => 'paid',
                'paid_date'    => $this->request->getPost('paid_date') ?: date('Y-m-d'),
                'reference_no' => esc($this->request->getPost('reference_no')) ?: null,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Could not mark payout paid.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Mark payout paid failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Could not mark payout paid. Please try again.');
        }

        $this->logActivity('mark_paid', 'landlord_payouts', $payoutId, 'Payout marked paid');

        return redirect()->to(base_url('landlords/' . (int) $payout['landlord_id']))
            ->with('success', 'Payout marked as paid.');
    }

    public function uploadDoc(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('landlords/' . $id));
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        if (! $this->db->tableExists('documents')) {
            return redirect()->back()->with('error', 'Documents module not available.');
        }

        $file = $this->request->getFile('doc_file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Please upload a valid document file.');
        }

        $maxBytes = 10 * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return redirect()->back()->with('error', 'File exceeds the 10 MB size limit.');
        }

        $allowedExt  = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $allowedMime = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $ext  = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        $mime = strtolower((string) $file->getMimeType());
        if (! in_array($ext, $allowedExt, true) || ! in_array($mime, $allowedMime, true)) {
            return redirect()->back()->with('error', 'File type is not allowed. Use PDF, image, Word, or Excel.');
        }

        $path = WRITEPATH . 'uploads/documents/';
        if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
            return redirect()->back()->with('error', 'Could not prepare the upload directory.');
        }

        $newName = $file->getRandomName();
        if (str_contains($newName, '..') || str_contains($newName, '/') || str_contains($newName, '\\')) {
            return redirect()->back()->with('error', 'Unsafe filename generated. Please retry.');
        }

        try {
            $file->move($path, $newName);
        } catch (\Throwable $e) {
            log_message('error', 'Landlord document move failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Could not store the uploaded file.');
        }

        $docType = preg_replace('/[^a-z0-9_\-]/i', '', (string) $this->request->getPost('doc_type')) ?: 'other';
        $notes   = trim((string) $this->request->getPost('notes'));
        $title   = $file->getClientName();
        $title   = preg_replace('/[^\w.\- ()\[\]]+/', '_', $title) ?: 'document';

        $this->db->table('documents')->insert([
            'module'      => 'landlords',
            'ref_id'      => $id,
            'title'       => $title,
            'doc_type'    => $docType,
            'description' => $notes !== '' ? $notes : null,
            'file_path'   => 'uploads/documents/' . $newName,
            'uploaded_by' => $this->currentUser()['id'] ?: null,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('create', 'documents', $id, 'Landlord document uploaded');

        return redirect()->to(base_url('landlords/' . $id))->with('success', 'Document uploaded.');
    }

    public function deleteDoc(int $docId)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->db->tableExists('documents')) {
            return redirect()->back()->with('error', 'Documents module not available.');
        }

        $doc = $this->db->table('documents')->where('id', $docId)->where('module', 'landlords')->get()->getRowArray();
        if (! $doc) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $landlordId = (int) $doc['ref_id'];
        if (! $this->pmFind(self::TABLE, $landlordId)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $stored = (string) ($doc['file_path'] ?? '');
        $base   = realpath(WRITEPATH . 'uploads/documents') ?: (WRITEPATH . 'uploads/documents');
        if ($stored !== '') {
            $full = WRITEPATH . ltrim($stored, '/');
            $real = realpath($full);
            if ($real && str_starts_with($real, $base) && is_file($real)) {
                @unlink($real);
            }
        }

        $this->db->table('documents')->where('id', $docId)->delete();
        $this->logActivity('delete', 'documents', $docId, 'Landlord document deleted');

        return redirect()->to(base_url('landlords/' . $landlordId))->with('success', 'Document deleted.');
    }

    public function dismissReminder(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('landlords/' . $id));
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $key        = trim((string) $this->request->getPost('reminder_key'));
        $reminderId = (int) $this->request->getPost('reminder_id');
        $userId     = (int) ($this->currentUser()['id'] ?? 0);

        if ($key === '' && $reminderId <= 0) {
            return redirect()->back()->with('error', 'Reminder reference missing.');
        }

        (new \App\Models\Landlord_model())->dismissReminder($id, $key, $userId, $reminderId > 0 ? $reminderId : null);

        if ($this->db->tableExists('reminders')) {
            $upd = ['status' => 'dismissed'];
            if ($this->db->fieldExists('dismissed_by', 'reminders')) {
                $upd['dismissed_by'] = $userId ?: null;
            }
            if ($this->db->fieldExists('dismissed_at', 'reminders')) {
                $upd['dismissed_at'] = date('Y-m-d H:i:s');
            }
            if ($reminderId > 0) {
                $this->db->table('reminders')
                    ->where('id', $reminderId)
                    ->where('module', 'landlords')
                    ->where('ref_id', $id)
                    ->update($upd);
            }
        }

        $this->logActivity('update', 'landlords', $id, 'Reminder dismissed: ' . ($key !== '' ? $key : '#' . $reminderId));

        return redirect()->to(base_url('landlords/' . $id))->with('success', 'Reminder dismissed.');
    }

    // ── Revenue Report ────────────────────────────────────────────────────────

    public function revenue(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlords module not available.');
        }

        $landlord = $this->pmFind(self::TABLE, $id);
        if (! $landlord) {
            return redirect()->to(base_url('landlords'))->with('error', 'Landlord not found.');
        }

        $contracts = [];
        $payments  = [];
        $payouts   = [];

        if ($this->pmTableExists('lease_contracts')) {
            $q = $this->db->table('facilities f')
                ->select('lc.id, lc.contract_number, lc.status, lc.start_date, lc.end_date, lc.rent_amount, lc.payment_frequency, f.name AS facility_name, u.unit_number, t.full_name AS tenant_name')
                ->join('lease_contracts lc', 'lc.facility_id = f.id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->join('tenants t', 't.id = lc.tenant_id', 'left')
                ->where('f.landlord_id', $id)
                ->where('lc.deleted_at', null)
                ->orderBy('lc.id', 'DESC');
            $this->scopeCompany($q, 'f.company_id');
            $contracts = $q->get()->getResultArray();

            $contractIds = array_column($contracts, 'id');
            if (! empty($contractIds) && $this->pmTableExists('lease_payments')) {
                $payments = $this->db->table('lease_payments lp')
                    ->select('lp.payment_number, lp.amount, lp.status, lp.payment_date, lp.due_date, lp.period_from, lp.period_to, lc.contract_number')
                    ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
                    ->whereIn('lp.contract_id', $contractIds)
                    ->orderBy('lp.due_date', 'DESC')
                    ->get()->getResultArray();
            }
        }

        if ($this->pmTableExists('landlord_payouts')) {
            $payouts = $this->db->table('landlord_payouts')
                ->where('landlord_id', $id)
                ->orderBy('period_from', 'DESC')
                ->get()->getResultArray();
        }

        $totalRevenue = array_sum(array_column(
            array_filter($payments, fn($p) => $p['status'] === 'paid'),
            'amount'
        ));
        $totalPayouts = array_sum(array_column($payouts, 'net_amount'));

        return view('landlords/revenue', $this->viewData([
            'title'         => 'Revenue — ' . $landlord['full_name'],
            'landlord'      => $landlord,
            'contracts'     => $contracts,
            'payments'      => $payments,
            'payouts'       => $payouts,
            'totalRevenue'  => $totalRevenue,
            'totalPayouts'  => $totalPayouts,
        ]));
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /** @return array<string,mixed> */
    private function landlordPayload(): array
    {
        return [
            'company_id'     => $this->pmCompanyId(),
            'full_name'      => esc($this->request->getPost('full_name')),
            'full_name_ar'   => esc($this->request->getPost('full_name_ar')) ?: null,
            'phone'          => esc($this->request->getPost('phone')) ?: null,
            'phone2'         => esc($this->request->getPost('phone2')) ?: null,
            'email'          => esc($this->request->getPost('email')) ?: null,
            'nationality'    => esc($this->request->getPost('nationality')) ?: null,
            'id_type'        => esc($this->request->getPost('id_type')) ?: null,
            'id_number'      => esc($this->request->getPost('id_number')) ?: null,
            'id_expiry'      => $this->request->getPost('id_expiry') ?: null,
            'address'        => esc($this->request->getPost('address')) ?: null,
            'bank_name'      => esc($this->request->getPost('bank_name')) ?: null,
            'bank_account'   => esc($this->request->getPost('bank_account')) ?: null,
            'bank_iban'      => esc($this->request->getPost('bank_iban')) ?: null,
            'commission_pct' => $this->request->getPost('commission_pct') !== '' ? $this->request->getPost('commission_pct') : null,
            'status'         => $this->request->getPost('status'),
            'notes'          => esc($this->request->getPost('notes')) ?: null,
        ];
    }

    private function migrationView()
    {
        return view('landlords/index', $this->viewData([
            'title'             => 'Landlords',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'landlords'         => [],
            'search'            => '',
            'status'            => '',
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
