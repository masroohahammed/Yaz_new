<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Services\AiModel;

class Cheques extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'cheques';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $filters = [
            'search' => trim((string) ($this->request->getGet('search') ?? '')),
            'status' => $this->request->getGet('status') ?? '',
        ];

        $q = $this->db->table(self::TABLE . ' c')
            ->select('c.*, t.full_name AS tenant_name, lc.contract_number, f.name AS facility_name')
            ->join('tenants t', 't.id = c.tenant_id', 'left')
            ->join('lease_contracts lc', 'lc.id = c.contract_id', 'left')
            ->join('facilities f', 'f.id = c.facility_id', 'left');
        $this->scopeCompany($q, 'c.company_id');
        $this->scopeFacilities($q, 'c.facility_id');

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('c.cheque_no', $filters['search'])
                ->orLike('t.full_name', $filters['search'])
                ->orLike('lc.contract_number', $filters['search'])
                ->groupEnd();
        }
        if ($filters['status'] !== '') {
            $q->where('c.status', $filters['status']);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('c.cheque_date', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('cheques/index', $this->viewData([
            'title'       => 'Incoming Cheques (PDC)',
            'cheques'     => $rows,
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

        return view('cheques/form', $this->viewData([
            'title'     => 'Register Cheque',
            'cheque'    => null,
            'contracts' => $this->contractOptions(),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module is not available. Run database migration first.');
        }

        $rules = [
            'cheque_no'   => 'required|max_length[50]',
            'amount'      => 'required|decimal',
            'cheque_date' => 'permit_empty|valid_date[Y-m-d]',
            'status'      => 'required|in_list[pending,deposited,cleared,bounced,cancelled,replaced]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->chequePayload();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('create', 'cheques', $id, 'Cheque registered: ' . $data['cheque_no']);

        return redirect()->to(base_url('cheques/' . $id))->with('success', 'Cheque registered.');
    }

    public function show(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $cheque = $this->db->table(self::TABLE . ' c')
            ->select('c.*, t.full_name AS tenant_name, t.phone AS tenant_phone, lc.contract_number, f.name AS facility_name')
            ->join('tenants t', 't.id = c.tenant_id', 'left')
            ->join('lease_contracts lc', 'lc.id = c.contract_id', 'left')
            ->join('facilities f', 'f.id = c.facility_id', 'left')
            ->where('c.id', $id)
            ->get()->getRowArray();

        if (! $cheque) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheque not found.');
        }

        return view('cheques/form', $this->viewData([
            'title'    => 'Cheque #' . $cheque['cheque_no'],
            'cheque'   => $cheque,
            'readOnly' => true,
        ]));
    }

    // ── Bounce (enhanced) ────────────────────────────────────────────────────

    public function bounce(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module is not available. Run database migration first.');
        }

        $cheque = $this->db->table(self::TABLE)->where('id', $id)->get()->getRowArray();
        if (! $cheque) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheque not found.');
        }

        $reason     = esc($this->request->getPost('bounce_reason') ?? 'Cheque bounced');
        $fileLegal  = $this->request->getPost('file_legal') ? 1 : 0;
        $caseNo     = esc($this->request->getPost('case_no')) ?: null;
        $filedDate  = $this->request->getPost('filed_date') ?: null;
        $caseNotes  = esc($this->request->getPost('case_notes')) ?: null;

        $updateData = [
            'status'       => 'bounced',
            'bounce_reason'=> $reason,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('file_legal', self::TABLE)) {
            $updateData['file_legal'] = $fileLegal;
            $updateData['case_no']    = $caseNo;
            $updateData['filed_date'] = $filedDate;
            $updateData['case_notes'] = $caseNotes;
        }

        $this->db->table(self::TABLE)->where('id', $id)->update($updateData);

        (new AiModel($this->db))->raiseFlag(
            'cheque',
            $id,
            'bounced_cheque',
            'Bounced cheque #' . $cheque['cheque_no'],
            $reason,
            'critical',
            'pm'
        );

        $this->logActivity('bounce', 'cheques', $id, 'Cheque bounced: ' . $cheque['cheque_no']);

        return redirect()->to(base_url('cheques/' . $id))->with('success', 'Cheque marked as bounced.');
    }

    // ── Deposit ────────────────────────────────────────────────────────────────

    public function deposit(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module not available.');
        }

        $cheque = $this->db->table(self::TABLE)->where('id', $id)->get()->getRowArray();
        if (! $cheque) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheque not found.');
        }

        $update = [
            'status'     => 'deposited',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('deposit_date', self::TABLE)) {
            $update['deposit_date'] = date('Y-m-d');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update($update);

        $this->logActivity('deposit', 'cheques', $id, 'Cheque deposited: ' . $cheque['cheque_no']);

        return redirect()->to(base_url('cheques/' . $id))->with('success', 'Cheque marked as deposited.');
    }

    // ── Clear ──────────────────────────────────────────────────────────────────

    public function clear(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module not available.');
        }

        $cheque = $this->db->table(self::TABLE)->where('id', $id)->get()->getRowArray();
        if (! $cheque) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheque not found.');
        }

        $update = [
            'status'     => 'cleared',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('clearance_date', self::TABLE)) {
            $update['clearance_date'] = date('Y-m-d');
        }
        if ($this->db->fieldExists('deposit_date', self::TABLE) && empty($cheque['deposit_date'])) {
            $update['deposit_date'] = date('Y-m-d');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update($update);

        $this->logActivity('clear', 'cheques', $id, 'Cheque cleared: ' . $cheque['cheque_no']);

        return redirect()->to(base_url('cheques/' . $id))->with('success', 'Cheque marked as cleared.');
    }

    // ── Convert to Cash ────────────────────────────────────────────────────────

    public function convertToCash(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module not available.');
        }

        $cheque = $this->db->table(self::TABLE)->where('id', $id)->get()->getRowArray();
        if (! $cheque) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheque not found.');
        }

        $conversionDate   = $this->request->getPost('cash_conversion_date');
        if (! $conversionDate) {
            return redirect()->back()->with('error', 'Conversion date is required.');
        }

        $conversionAmount = $this->request->getPost('conversion_amount') ?: $cheque['amount'];
        $notes            = esc($this->request->getPost('notes')) ?: null;

        $updateData = [
            'status'     => 'cleared',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('cash_conversion_date', self::TABLE)) {
            $updateData['cash_conversion_date'] = $conversionDate;
        }
        if ($this->db->fieldExists('clearance_date', self::TABLE)) {
            $updateData['clearance_date'] = $conversionDate;
        }
        if ($this->db->fieldExists('deposit_date', self::TABLE) && empty($cheque['deposit_date'])) {
            $updateData['deposit_date'] = $conversionDate;
        }

        $this->db->table(self::TABLE)->where('id', $id)->update($updateData);

        if ($this->pmTableExists('lease_payments') && ! empty($cheque['contract_id'])) {
            $payNo = $this->generateNumber('PAY', 'lease_payments', 'payment_number');
            $this->db->table('lease_payments')->insert([
                'company_id'     => $cheque['company_id'],
                'payment_number' => $payNo,
                'contract_id'    => $cheque['contract_id'],
                'tenant_id'      => $cheque['tenant_id'],
                'facility_id'    => $cheque['facility_id'],
                'payment_type'   => 'rent',
                'payment_method' => 'cash',
                'amount'         => $conversionAmount,
                'status'         => 'paid',
                'payment_date'   => $conversionDate,
                'notes'          => 'Cash conversion from cheque #' . $cheque['cheque_no'] . ($notes ? ': ' . $notes : ''),
                'created_by'     => $this->currentUser()['id'] ?: null,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        $this->logActivity('convert_to_cash', 'cheques', $id, 'Cheque converted to cash: ' . $cheque['cheque_no']);

        return redirect()->to(base_url('cheques/' . $id))->with('success', 'Cheque converted to cash payment.');
    }

    // ── Import CSV ────────────────────────────────────────────────────────────

    public function importCsv()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module not available.');
        }

        if ($this->request->is('get')) {
            return view('cheques/import', $this->viewData([
                'title' => 'Import Cheques (CSV)',
            ]));
        }

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $content = file_get_contents($file->getTempName());
        $lines   = explode("\n", str_replace("\r", "", $content));
        $headers = null;
        $count   = 0;
        $errors  = [];

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line);

            if ($headers === null) {
                $headers = array_map('strtolower', array_map('trim', $cols));
                continue;
            }

            $row = array_combine($headers, array_pad($cols, count($headers), ''));
            if ($row === false) {
                $errors[] = 'Line ' . ($lineNum + 1) . ': column count mismatch';
                continue;
            }

            $chequeNo = trim($row['cheque_no'] ?? $row['cheque no'] ?? '');
            $amount   = trim($row['amount'] ?? '');
            if ($chequeNo === '' || $amount === '') {
                $errors[] = 'Line ' . ($lineNum + 1) . ': cheque_no and amount required';
                continue;
            }

            $contractId = null;
            $tenantId   = null;
            $facilityId = null;
            $cid        = (int) ($row['contract_id'] ?? 0);
            if ($cid > 0 && $this->pmTableExists('lease_contracts')) {
                $contract = $this->db->table('lease_contracts')->where('id', $cid)->get()->getRowArray();
                if ($contract) {
                    $contractId = $cid;
                    $tenantId   = (int) ($contract['tenant_id'] ?? 0) ?: null;
                    $facilityId = (int) ($contract['facility_id'] ?? 0) ?: null;
                }
            }

            $this->db->table(self::TABLE)->insert([
                'company_id'    => $this->pmCompanyId(),
                'contract_id'   => $contractId,
                'tenant_id'     => $tenantId,
                'facility_id'   => $facilityId,
                'cheque_no'     => esc($chequeNo),
                'amount'        => $amount,
                'bank_name'     => esc(trim($row['bank_name'] ?? '')) ?: null,
                'cheque_date'   => trim($row['cheque_date'] ?? '') ?: null,
                'status'        => 'pending',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        $msg = $count . ' cheque(s) imported.';
        if ($errors) {
            $msg .= ' Errors on ' . count($errors) . ' rows.';
        }

        $this->logActivity('import_csv', 'cheques', 0, 'Imported ' . $count . ' cheques via CSV');

        return redirect()->to(base_url('cheques'))->with('success', $msg);
    }

    // ── Export CSV ────────────────────────────────────────────────────────────

    public function exportCsv()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('cheques'))->with('error', 'Cheques module not available.');
        }

        $q = $this->db->table(self::TABLE . ' c')
            ->select('c.cheque_no, t.full_name AS tenant_name, lc.contract_number, c.amount, c.status, c.bank_name, c.cheque_date, c.received_date')
            ->join('tenants t', 't.id = c.tenant_id', 'left')
            ->join('lease_contracts lc', 'lc.id = c.contract_id', 'left');
        $this->scopeCompany($q, 'c.company_id');
        $this->scopeFacilities($q, 'c.facility_id');

        $rows    = $q->orderBy('c.cheque_date', 'DESC')->get()->getResultArray();
        $headers = ['Cheque No', 'Tenant', 'Contract', 'Amount', 'Status', 'Bank', 'Cheque Date', 'Received Date'];

        return $this->csvResponse('cheques_' . date('Ymd') . '.csv', $headers, $rows);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /** @return list<array<string,mixed>> */
    private function contractOptions(): array
    {
        if (! $this->pmTableExists('lease_contracts')) {
            return [];
        }

        $q = $this->db->table('lease_contracts lc')
            ->select('lc.id, lc.contract_number, lc.tenant_id, lc.facility_id, t.full_name AS tenant_name')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.contract_number', 'DESC');
        $this->scopeCompany($q, 'lc.company_id');

        return $q->get()->getResultArray();
    }

    /** @return array<string,mixed> */
    private function chequePayload(): array
    {
        $contractId = (int) $this->request->getPost('contract_id') ?: null;
        $tenantId   = (int) $this->request->getPost('tenant_id') ?: null;
        $facilityId = (int) $this->request->getPost('facility_id') ?: null;

        if ($contractId && $this->pmTableExists('lease_contracts')) {
            $contract = $this->db->table('lease_contracts')->where('id', $contractId)->get()->getRowArray();
            if ($contract) {
                $tenantId   = (int) ($contract['tenant_id'] ?? 0) ?: $tenantId;
                $facilityId = (int) ($contract['facility_id'] ?? 0) ?: $facilityId;
            }
        }

        return [
            'company_id'    => $this->pmCompanyId(),
            'contract_id'   => $contractId,
            'tenant_id'     => $tenantId,
            'facility_id'   => $facilityId,
            'cheque_no'     => esc($this->request->getPost('cheque_no')),
            'amount'        => $this->request->getPost('amount'),
            'status'        => $this->request->getPost('status'),
            'bank_name'     => esc($this->request->getPost('bank_name')) ?: null,
            'account_name'  => esc($this->request->getPost('account_name')) ?: null,
            'account_no'    => esc($this->request->getPost('account_no')) ?: null,
            'cheque_date'   => $this->request->getPost('cheque_date') ?: null,
            'received_date' => $this->request->getPost('received_date') ?: null,
            'period_from'   => $this->request->getPost('period_from') ?: null,
            'period_to'     => $this->request->getPost('period_to') ?: null,
            'notes'         => esc($this->request->getPost('notes')) ?: null,
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
        return view('cheques/index', $this->viewData([
            'title'             => 'Incoming Cheques (PDC)',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'cheques'           => [],
            'filters'           => [],
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
