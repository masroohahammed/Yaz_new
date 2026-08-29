<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class OutgoingCheques extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'outgoing_cheques';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $status = $this->request->getGet('status') ?? '';

        $q = $this->db->table(self::TABLE . ' oc')
            ->select('oc.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = oc.facility_id', 'left');
        $this->scopeCompany($q, 'oc.company_id');
        $this->scopeFacilities($q, 'oc.facility_id');

        if ($search !== '') {
            $q->groupStart()
                ->like('oc.cheque_no', $search)
                ->orLike('oc.payee_name', $search)
                ->orLike('oc.purpose', $search)
                ->groupEnd();
        }
        if ($status !== '') {
            $q->where('oc.status', $status);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('oc.cheque_date', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('outgoing_cheques/index', $this->viewData([
            'title'       => 'Outgoing Cheques',
            'cheques'     => $rows,
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

        return view('outgoing_cheques/form', $this->viewData([
            'title'      => 'Issue Outgoing Cheque',
            'cheque'     => null,
            'facilities' => $this->facilityOptions(),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('outgoing-cheques'))->with('error', 'Outgoing cheques module is not available. Run database migration first.');
        }

        $rules = [
            'cheque_no'   => 'required|max_length[50]',
            'bank_name'   => 'required|max_length[120]',
            'amount'      => 'required|decimal',
            'cheque_date' => 'required|valid_date[Y-m-d]',
            'payee_name'  => 'required|max_length[200]',
            'purpose'     => 'required|max_length[80]',
            'status'      => 'required|in_list[pending,issued,cleared,cancelled]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->outgoingPayload();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('create', 'outgoing_cheques', $id, 'Outgoing cheque: ' . $data['cheque_no']);

        return redirect()->to(base_url('outgoing-cheques'))->with('success', 'Outgoing cheque recorded.');
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $cheque = $this->db->table(self::TABLE)->where('id', $id)->get()->getRowArray();
        if (! $cheque) {
            return redirect()->to(base_url('outgoing-cheques'))->with('error', 'Cheque not found.');
        }

        return view('outgoing_cheques/form', $this->viewData([
            'title'      => 'Edit Outgoing Cheque',
            'cheque'     => $cheque,
            'facilities' => $this->facilityOptions(),
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('outgoing-cheques'))->with('error', 'Outgoing cheques module is not available. Run database migration first.');
        }

        if (! $this->db->table(self::TABLE)->where('id', $id)->countAllResults()) {
            return redirect()->to(base_url('outgoing-cheques'))->with('error', 'Cheque not found.');
        }

        $rules = [
            'cheque_no'   => 'required|max_length[50]',
            'bank_name'   => 'required|max_length[120]',
            'amount'      => 'required|decimal',
            'cheque_date' => 'required|valid_date[Y-m-d]',
            'payee_name'  => 'required|max_length[200]',
            'purpose'     => 'required|max_length[80]',
            'status'      => 'required|in_list[pending,issued,cleared,cancelled]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->outgoingPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->logActivity('update', 'outgoing_cheques', $id, 'Outgoing cheque updated');

        return redirect()->to(base_url('outgoing-cheques'))->with('success', 'Outgoing cheque updated.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('outgoing-cheques'))->with('error', 'Outgoing cheques module is not available. Run database migration first.');
        }

        $cheque = $this->db->table(self::TABLE)->where('id', $id)->get()->getRowArray();
        if (! $cheque) {
            return redirect()->to(base_url('outgoing-cheques'))->with('error', 'Cheque not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->delete();
        $this->logActivity('delete', 'outgoing_cheques', $id, 'Outgoing cheque deleted: ' . $cheque['cheque_no']);

        return redirect()->to(base_url('outgoing-cheques'))->with('success', 'Outgoing cheque removed.');
    }

    /** @return list<array<string,mixed>> */
    private function facilityOptions(): array
    {
        return $this->scopeFacilities(
            $this->db->table('facilities')->select('id, name')->where('status', 'active')->orderBy('name')
        )->get()->getResultArray();
    }

    /** @return array<string,mixed> */
    private function outgoingPayload(): array
    {
        $facilityId = (int) $this->request->getPost('facility_id') ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        return [
            'company_id'  => $this->pmCompanyId(),
            'cheque_no'   => esc($this->request->getPost('cheque_no')),
            'bank_name'   => esc($this->request->getPost('bank_name')),
            'amount'      => $this->request->getPost('amount'),
            'cheque_date' => $this->request->getPost('cheque_date'),
            'payee_name'  => esc($this->request->getPost('payee_name')),
            'payee_type'  => esc($this->request->getPost('payee_type')) ?: null,
            'purpose'     => esc($this->request->getPost('purpose')),
            'facility_id' => $facilityId,
            'description' => esc($this->request->getPost('description')) ?: null,
            'status'      => $this->request->getPost('status'),
        ];
    }

    private function migrationView()
    {
        return view('outgoing_cheques/index', $this->viewData([
            'title'             => 'Outgoing Cheques',
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'cheques'           => [],
            'search'            => '',
            'status'            => '',
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
