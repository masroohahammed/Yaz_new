<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class CostManagement extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const REM_TABLE = 'cost_reminders';

    public function index()
    {
        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        $facilityId = (int) ($this->request->getGet('facility_id') ?? 0);
        $section    = $this->request->getGet('section') ?? 'expenses';

        $expenses  = [];
        $reminders = [];

        if ($section === 'expenses' && $this->pmTableExists('expenses')) {
            $q = $this->db->table('expenses e')
                ->select('e.*, f.name AS facility_name')
                ->join('facilities f', 'f.id = e.facility_id', 'left')
                ->orderBy('e.id', 'DESC')
                ->limit(50);
            $this->scopeFacilities($q, 'e.facility_id');
            if ($facilityId > 0) {
                $q->where('e.facility_id', $facilityId);
            }
            $expenses = $q->get()->getResultArray();
        }

        if ($section === 'reminders' && $this->pmTableExists(self::REM_TABLE)) {
            $q = $this->db->table(self::REM_TABLE . ' cr')
                ->select('cr.*, f.name AS facility_name')
                ->join('facilities f', 'f.id = cr.facility_id', 'left')
                ->where('cr.deleted_at', null)
                ->orderBy('cr.due_date');
            $this->scopeCompany($q, 'cr.company_id');
            if ($facilityId > 0) {
                $q->where('cr.facility_id', $facilityId);
            }
            $reminders = $q->get()->getResultArray();
        }

        return view('cost_management/index', $this->viewData([
            'title'      => 'Cost Management',
            'section'    => $section,
            'facilities' => $facilities,
            'facilityId' => $facilityId,
            'expenses'   => $expenses,
            'reminders'  => $reminders,
        ]));
    }

    // ── Expenses (wraps Finance expenses with property filter) ────────────────

    public function createExpense()
    {
        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('cost_management/expense_form', $this->viewData([
            'title'      => 'Add Property Expense',
            'expense'    => null,
            'facilities' => $facilities,
        ]));
    }

    public function storeExpense()
    {
        if (! $this->pmTableExists('expenses')) {
            return redirect()->to(base_url('cost-management'))->with('error', 'Expenses table not available.');
        }

        $rules = [
            'title'        => 'required|min_length[2]|max_length[200]',
            'amount'       => 'required|numeric',
            'expense_date' => 'required|valid_date',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facilityId = (int) $this->request->getPost('facility_id') ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        $row = [
            'title'        => esc($this->request->getPost('title')),
            'amount'       => (float) $this->request->getPost('amount'),
            'expense_date' => $this->request->getPost('expense_date'),
            'category'     => esc($this->request->getPost('category')) ?: null,
            'notes'        => esc($this->request->getPost('notes')) ?: null,
            'status'       => 'pending',
            'created_by'   => $this->currentUser()['id'] ?: null,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('facility_id', 'expenses')) {
            $row['facility_id'] = $facilityId;
        }
        if ($this->db->fieldExists('company_id', 'expenses')) {
            $row['company_id'] = $this->pmCompanyId();
        }

        $this->db->table('expenses')->insert($row);
        $id = (int) $this->db->insertID();
        $this->logActivity('create', 'expenses', $id, 'Property expense: ' . $row['title']);

        return redirect()->to(base_url('cost-management?section=expenses'))->with('success', 'Expense added.');
    }

    // ── Reminders ─────────────────────────────────────────────────────────────

    public function createReminder()
    {
        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('cost_management/reminder_form', $this->viewData([
            'title'      => 'New Cost Reminder',
            'reminder'   => null,
            'facilities' => $facilities,
        ]));
    }

    public function storeReminder()
    {
        if (! $this->pmTableExists(self::REM_TABLE)) {
            return redirect()->to(base_url('cost-management'))->with('error', 'Run migration first.');
        }

        $rules = ['title' => 'required|min_length[2]|max_length[200]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facilityId = (int) $this->request->getPost('facility_id') ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        $this->db->table(self::REM_TABLE)->insert([
            'company_id'  => $this->pmCompanyId(),
            'facility_id' => $facilityId,
            'type'        => esc($this->request->getPost('type')) ?: 'general',
            'title'       => esc($this->request->getPost('title')),
            'due_date'    => $this->request->getPost('due_date') ?: null,
            'recurrence'  => $this->request->getPost('recurrence') ?: null,
            'amount'      => $this->request->getPost('amount') ?: null,
            'notes'       => esc($this->request->getPost('notes')) ?: null,
            'status'      => 'pending',
            'created_by'  => $this->currentUser()['id'] ?: null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->db->insertID();
        $this->logActivity('create', self::REM_TABLE, $id, 'Reminder: ' . $this->request->getPost('title'));

        return redirect()->to(base_url('cost-management?section=reminders'))->with('success', 'Reminder created.');
    }

    public function editReminder(int $id)
    {
        if (! $this->pmTableExists(self::REM_TABLE)) {
            return redirect()->to(base_url('cost-management'))->with('error', 'Run migration first.');
        }

        $reminder = $this->pmFind(self::REM_TABLE, $id);
        if (! $reminder) {
            return redirect()->to(base_url('cost-management?section=reminders'))->with('error', 'Reminder not found.');
        }

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('cost_management/reminder_form', $this->viewData([
            'title'      => 'Edit Reminder',
            'reminder'   => $reminder,
            'facilities' => $facilities,
        ]));
    }

    public function updateReminder(int $id)
    {
        if (! $this->pmTableExists(self::REM_TABLE)) {
            return redirect()->to(base_url('cost-management'))->with('error', 'Run migration first.');
        }

        $reminder = $this->pmFind(self::REM_TABLE, $id);
        if (! $reminder) {
            return redirect()->to(base_url('cost-management?section=reminders'))->with('error', 'Reminder not found.');
        }

        $rules = ['title' => 'required|min_length[2]|max_length[200]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table(self::REM_TABLE)->where('id', $id)->update([
            'facility_id' => (int) $this->request->getPost('facility_id') ?: null,
            'type'        => esc($this->request->getPost('type')) ?: 'general',
            'title'       => esc($this->request->getPost('title')),
            'due_date'    => $this->request->getPost('due_date') ?: null,
            'recurrence'  => $this->request->getPost('recurrence') ?: null,
            'amount'      => $this->request->getPost('amount') ?: null,
            'notes'       => esc($this->request->getPost('notes')) ?: null,
            'status'      => $this->request->getPost('status') ?: 'pending',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('cost-management?section=reminders'))->with('success', 'Reminder updated.');
    }

    public function doneReminder(int $id)
    {
        if (! $this->pmTableExists(self::REM_TABLE)) {
            return redirect()->back();
        }

        $this->db->table(self::REM_TABLE)->where('id', $id)->update([
            'status'     => 'done',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Reminder marked as done.');
    }

    public function deleteReminder(int $id)
    {
        if (! $this->pmTableExists(self::REM_TABLE)) {
            return redirect()->back();
        }

        $this->db->table(self::REM_TABLE)->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return redirect()->to(base_url('cost-management?section=reminders'))->with('success', 'Reminder removed.');
    }
}
