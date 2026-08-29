<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class Tenants extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'tenants';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView('Tenants');
        }

        $filters = [
            'search' => trim((string) ($this->request->getGet('search') ?? '')),
            'status' => $this->request->getGet('status') ?? '',
            'type'   => $this->request->getGet('type') ?? '',
        ];

        $q = $this->db->table(self::TABLE . ' t')->where('t.deleted_at', null);
        $this->scopeCompany($q, 't.company_id');

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('t.full_name', $filters['search'])
                ->orLike('t.phone', $filters['search'])
                ->orLike('t.email', $filters['search'])
                ->orLike('t.qid_no', $filters['search'])
                ->orLike('t.passport_no', $filters['search'])
                ->orLike('t.company_name', $filters['search'])
                ->groupEnd();
        }
        if ($filters['status'] !== '') {
            $q->where('t.status', $filters['status']);
        }
        if ($filters['type'] !== '') {
            $q->where('t.tenant_type', $filters['type']);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('t.full_name', 'ASC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('tenants/index', $this->viewData([
            'title'       => 'Tenants',
            'tenants'     => $rows,
            'filters'     => $filters,
            'total'       => $total,
            'currentPage' => $pg['page'],
            'perPage'     => $pg['perPage'],
        ]));
    }

    public function create()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView('Tenants');
        }

        return view('tenants/form', $this->viewData([
            'title'  => 'Add Tenant',
            'tenant' => null,
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenants module is not available. Run database migration first.');
        }

        $rules = [
            'tenant_type' => 'required|in_list[Personal,Corporate]',
            'full_name'   => 'required|min_length[2]|max_length[200]',
            'phone'       => 'required|max_length[30]',
            'email'       => 'permit_empty|valid_email|max_length[150]',
            'status'      => 'required|in_list[active,inactive,blacklisted]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->tenantPayload();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('create', 'tenants', $id, 'Tenant created: ' . $data['full_name']);

        try {
            (new \App\Services\TenantPortalUserService($this->db))->linkOrCreateForTenant($id);
        } catch (\Throwable $e) {
            log_message('error', 'Portal user link on tenant create failed: ' . $e->getMessage());
        }

        return redirect()->to(base_url('tenants/' . $id))->with('success', 'Tenant created successfully.');
    }

    public function show(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView('Tenants');
        }

        $tenant = $this->pmCrud()->row('tenants', $id, $this->pmCompanyId()) ?: $this->pmFind(self::TABLE, $id);
        if (! $tenant) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenant not found.');
        }

        $tenantModel = new \App\Models\Tenant_model();
        $detail      = $tenantModel->findDetail($id) ?: $tenant;

        return view('tenants/view', $this->viewData([
            'title'   => 'Tenant — ' . $tenant['full_name'],
            'tenant'  => $detail,
            'data360' => $tenantModel->get360Data($id),
        ]));
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return $this->migrationView('Tenants');
        }

        $tenant = $this->pmFind(self::TABLE, $id);
        if (! $tenant) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenant not found.');
        }

        return view('tenants/form', $this->viewData([
            'title'  => 'Edit Tenant',
            'tenant' => $tenant,
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenants module is not available. Run database migration first.');
        }

        $tenant = $this->pmFind(self::TABLE, $id);
        if (! $tenant) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenant not found.');
        }

        $rules = [
            'tenant_type' => 'required|in_list[Personal,Corporate]',
            'full_name'   => 'required|min_length[2]|max_length[200]',
            'phone'       => 'required|max_length[30]',
            'email'       => 'permit_empty|valid_email|max_length[150]',
            'status'      => 'required|in_list[active,inactive,blacklisted]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->tenantPayload();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table(self::TABLE)->where('id', $id)->update($data);

        $this->logActivity('update', 'tenants', $id, 'Tenant updated: ' . $data['full_name']);

        try {
            (new \App\Services\TenantPortalUserService($this->db))->linkOrCreateForTenant($id);
        } catch (\Throwable $e) {
            log_message('error', 'Portal user link on tenant update failed: ' . $e->getMessage());
        }

        return redirect()->to(base_url('tenants/' . $id))->with('success', 'Tenant updated successfully.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenants module is not available. Run database migration first.');
        }

        $tenant = $this->pmFind(self::TABLE, $id);
        if (! $tenant) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenant not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete', 'tenants', $id, 'Tenant deleted: ' . $tenant['full_name']);

        return redirect()->to(base_url('tenants'))->with('success', 'Tenant removed.');
    }

    public function blacklist(int $id)
    {
        return $this->action($id, 'blacklist');
    }

    public function unblacklist(int $id)
    {
        return $this->action($id, 'unblacklist');
    }

    public function action(int $id, string $action)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('tenants/' . $id));
        }

        $tenant = $this->pmFind(self::TABLE, $id);
        if (! $tenant) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenant not found.');
        }

        $action = strtolower($action);
        if (! in_array($action, ['blacklist', 'unblacklist'], true)) {
            return redirect()->back()->with('error', 'Unknown action.');
        }

        $reason = trim((string) $this->request->getPost('reason'));
        $userId = (int) ($this->currentUser()['id'] ?? 0);
        $now    = date('Y-m-d H:i:s');

        if ($action === 'blacklist' && $reason === '') {
            return redirect()->back()->with('error', 'A blacklist reason is required.');
        }

        $already = (($tenant['status'] ?? '') === 'blacklisted') || ! empty($tenant['is_blacklisted']);
        if ($action === 'blacklist' && $already) {
            return redirect()->to(base_url('tenants/' . $id))->with('warning', 'Tenant is already blacklisted.');
        }
        if ($action === 'unblacklist' && ! $already) {
            return redirect()->to(base_url('tenants/' . $id))->with('warning', 'Tenant is not blacklisted.');
        }

        $this->db->transStart();
        try {
            $updates = [
                'status'     => $action === 'blacklist' ? 'blacklisted' : 'active',
                'updated_at' => $now,
            ];
            if ($this->db->fieldExists('is_blacklisted', self::TABLE)) {
                $updates['is_blacklisted'] = $action === 'blacklist' ? 1 : 0;
            }
            if ($action === 'blacklist') {
                if ($this->db->fieldExists('blacklist_reason', self::TABLE)) {
                    $updates['blacklist_reason'] = $reason;
                }
                if ($this->db->fieldExists('blacklisted_at', self::TABLE)) {
                    $updates['blacklisted_at'] = $now;
                }
                if ($this->db->fieldExists('blacklisted_by', self::TABLE)) {
                    $updates['blacklisted_by'] = $userId ?: null;
                }
            } else {
                if ($this->db->fieldExists('unblacklist_reason', self::TABLE)) {
                    $updates['unblacklist_reason'] = $reason !== '' ? $reason : null;
                }
                if ($this->db->fieldExists('unblacklisted_at', self::TABLE)) {
                    $updates['unblacklisted_at'] = $now;
                }
                if ($this->db->fieldExists('unblacklisted_by', self::TABLE)) {
                    $updates['unblacklisted_by'] = $userId ?: null;
                }
            }

            $this->db->table(self::TABLE)->where('id', $id)->update($updates);

            if ($this->db->tableExists('tenant_blacklist_history')) {
                $this->db->table('tenant_blacklist_history')->insert([
                    'tenant_id'    => $id,
                    'company_id'   => $tenant['company_id'] ?? $this->pmCompanyId(),
                    'action'       => $action,
                    'reason'       => $reason !== '' ? $reason : null,
                    'performed_by' => $userId ?: null,
                    'created_at'   => $now,
                ]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Could not update blacklist status.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Tenant blacklist action failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Could not update blacklist status. Please try again.');
        }

        $msg = $action === 'blacklist' ? 'Tenant blacklisted.' : 'Blacklist removed.';
        $this->logActivity($action, 'tenants', $id, $msg . ($reason !== '' ? ' ' . $reason : ''));

        return redirect()->to(base_url('tenants/' . $id))->with('success', $msg);
    }

    /** @return array<string,mixed> */
    public function exportCsv()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('tenants'))->with('error', 'Tenants module is not available.');
        }

        $q = $this->db->table(self::TABLE . ' t')->where('t.deleted_at', null);
        $this->scopeCompany($q, 't.company_id');
        $rows = $q->orderBy('t.full_name', 'ASC')->get()->getResultArray();

        $filename = 'tenants_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['ID', 'Type', 'Full Name', 'Phone', 'WhatsApp', 'Email', 'Nationality', 'QID No', 'Passport No', 'Company Name', 'Status', 'Created At']);
        foreach ($rows as $r) {
            fputcsv($fp, [
                $r['id'],
                $r['tenant_type'],
                $r['full_name'],
                $r['phone'] ?? '',
                $r['whatsapp'] ?? '',
                $r['email'] ?? '',
                $r['nationality'] ?? '',
                $r['qid_no'] ?? '',
                $r['passport_no'] ?? '',
                $r['company_name'] ?? '',
                $r['status'],
                $r['created_at'] ?? '',
            ]);
        }
        fclose($fp);
        exit;
    }

    private function tenantPayload(): array
    {
        $companyId = $this->pmCompanyId();

        return [
            'company_id'          => $companyId,
            'tenant_type'         => $this->request->getPost('tenant_type'),
            'full_name'           => esc($this->request->getPost('full_name')),
            'nationality'         => esc($this->request->getPost('nationality')) ?: null,
            'gender'              => esc($this->request->getPost('gender')) ?: null,
            'dob'                 => $this->request->getPost('dob') ?: null,
            'phone'               => esc($this->request->getPost('phone')),
            'whatsapp'            => esc($this->request->getPost('whatsapp')) ?: null,
            'email'               => esc($this->request->getPost('email')) ?: null,
            'company_name'        => esc($this->request->getPost('company_name')) ?: null,
            'company_cr'          => esc($this->request->getPost('company_cr')) ?: null,
            'qid_no'              => esc($this->request->getPost('qid_no')) ?: null,
            'qid_expiry'          => $this->request->getPost('qid_expiry') ?: null,
            'passport_no'         => esc($this->request->getPost('passport_no')) ?: null,
            'passport_expiry'     => $this->request->getPost('passport_expiry') ?: null,
            'emergency_name'      => esc($this->request->getPost('emergency_name')) ?: null,
            'emergency_phone'     => esc($this->request->getPost('emergency_phone')) ?: null,
            'emergency_relation'  => esc($this->request->getPost('emergency_relation')) ?: null,
            'status'              => $this->request->getPost('status'),
            'notes'               => esc($this->request->getPost('notes')) ?: null,
        ];
    }

    private function migrationView(string $title)
    {
        return view('tenants/index', $this->viewData([
            'title'             => $title,
            'migrationRequired' => true,
            'missingTable'      => self::TABLE,
            'tenants'           => [],
            'filters'           => [],
            'total'             => 0,
            'currentPage'       => 1,
            'perPage'           => 25,
        ]));
    }
}
