<?php
namespace App\Controllers;

use App\Services\Finance\FinanceModuleRegistry;
use App\Services\RbacService;
use App\Services\UserFacilityService;
use App\Services\WorkflowSettingsService;

class Settings extends BaseController
{
    public function index()
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->migrateLegacyLogo();
        return view('settings/index', $this->viewData([
            'title'          => 'Settings',
            'companyLogoUrl' => $this->logoUrl(),
        ]));
    }

    public function switchCompany()
    {
        $this->requireRole('super_admin');
        $companyId = $this->request->getPost('company_id');
        if ($companyId === '' || $companyId === null) {
            session()->remove('company_id');
        } else {
            session()->set('company_id', (int) $companyId);
        }

        return redirect()->back()->with('success', 'Company context updated.');
    }

    public function update()
    {
        $this->requireRole('super_admin');

        $fields = [
            'company_name', 'company_tagline', 'company_address', 'company_phone', 'company_email',
            'company_cr', 'company_po_box', 'company_website',
            'parking_header_title_deed', 'parking_owner_name_ar', 'parking_owner_name_en', 'parking_owner_cr',
            'parking_rep_company_ar', 'parking_rep_company_en', 'parking_rep_cr',
            'parking_poa_no', 'parking_poa_date', 'parking_rep_name_ar', 'parking_rep_name_en',
            'parking_rep_nationality_ar', 'parking_rep_nationality_en', 'parking_rep_qid',
            'parking_collector_account',
            'currency', 'vat_enabled', 'vat_rate',
            'timezone', 'sla_breach_notify', 'smtp_host', 'smtp_user', 'smtp_port',
            'primary_color', 'secondary_color',
            'alert_whatsapp_webhook',
        ];

        foreach ($fields as $k) {
            $val = $this->request->getPost($k);
            if ($k === 'vat_enabled') {
                $val = $val ? '1' : '0';
            }
            if ($val !== null) {
                $this->_saveSetting($k, esc($val));
            }
        }

        foreach (['alert_email_enabled', 'alert_whatsapp_enabled', 'procurement_match_required'] as $toggle) {
            $this->_saveSetting($toggle, $this->request->getPost($toggle) ? '1' : '0');
        }

        $logo = $this->request->getFile('company_logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp'];
            if (in_array($logo->getMimeType(), $allowed)) {
                $oldLogo = $this->settings['company_logo'] ?? '';
                $this->_deleteLogoFile($oldLogo);
                $logoDir = WRITEPATH . 'uploads/logos/';
                if (! is_dir($logoDir)) {
                    mkdir($logoDir, 0755, true);
                }
                $newName = 'logo_' . time() . '.' . $logo->getExtension();
                $logo->move($logoDir, $newName);
                $this->_saveSetting('company_logo', 'logos/' . $newName);
            } else {
                return redirect()->back()->with('error', 'Invalid logo file type. Use PNG, JPG, GIF, SVG, or WebP.');
            }
        }

        cache()->delete('system_settings');
        helper('fm');
        fm_clear_settings_cache();

        return redirect()->to(base_url('settings'))->with('success', 'Settings saved successfully.');
    }

    public function workflow()
    {
        $this->requireRole('super_admin');
        $wf = new WorkflowSettingsService($this->db);

        return view('settings/workflow', $this->viewData([
            'title'    => 'Workflow Configuration',
            'workflow' => $wf->all(),
        ]));
    }

    public function saveWorkflow()
    {
        $this->requireRole('super_admin');
        (new WorkflowSettingsService($this->db))->save($this->request->getPost() ?? []);

        return redirect()->to(base_url('settings/workflow'))->with('success', 'Workflow settings saved.');
    }

    public function financeModule()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $svc = new \App\Services\Finance\GlReportService($this->db);

        return view('settings/finance_module', $this->viewData([
            'title'     => 'Finance Module Setup',
            'glEnabled' => $svc->isEnabled(),
            'modules'   => FinanceModuleRegistry::modules(),
            'crossLinks' => FinanceModuleRegistry::crossModuleLinks(),
        ]));
    }

    public function roles()
    {
        $this->requireRole('super_admin');
        $rbac  = new RbacService($this->db);
        $roles = $this->db->table('roles')->orderBy('id', 'ASC')->get()->getResultArray();

        return view('settings/roles', $this->viewData([
            'title'       => 'Roles & Permissions',
            'roles'       => $roles,
            'permissions' => RbacService::allPermissionKeys(),
            'labels'      => RbacService::PERMISSION_LABELS,
            'permMap'     => $rbac->permissionsMap(),
        ]));
    }

    public function saveRoles()
    {
        $this->requireRole('super_admin');
        $posted = $this->request->getPost('perm') ?? [];
        $map    = [];
        $roles  = $this->db->table('roles')->select('name')->get()->getResultArray();

        foreach ($roles as $r) {
            $name = $r['name'];
            if ($name === 'super_admin') {
                $map[$name] = ['*'];
                continue;
            }
            $selected = $posted[$name] ?? [];
            $map[$name] = is_array($selected) ? array_values(array_unique($selected)) : [];
        }

        (new RbacService($this->db))->saveOverrides($map);

        return redirect()->to(base_url('settings/roles'))->with('success', 'Role permissions updated.');
    }

    public function companies()
    {
        $this->requireRole('super_admin');
        $companies = $this->db->table('companies')->orderBy('name', 'ASC')->get()->getResultArray();

        return view('settings/companies', $this->viewData(['title' => 'Companies', 'companies' => $companies]));
    }

    public function createCompany()
    {
        $this->requireRole('super_admin');

        return view('settings/create_company', $this->viewData(['title' => 'Add Company']));
    }

    public function storeCompany()
    {
        $this->requireRole('super_admin');
        $rules = ['name' => 'required|min_length[2]', 'code' => 'required|max_length[20]|is_unique[companies.code]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table('companies')->insert([
            'name'           => esc($this->request->getPost('name')),
            'code'           => strtoupper(esc($this->request->getPost('code'))),
            'address'        => esc($this->request->getPost('address') ?? ''),
            'contact_person' => esc($this->request->getPost('contact_person') ?? ''),
            'email'          => $this->request->getPost('email') ?? '',
            'phone'          => esc($this->request->getPost('phone') ?? ''),
            'vat_number'     => esc($this->request->getPost('vat_number') ?? ''),
            'status'         => 'active',
        ]);
        $cid = $this->db->insertID();

        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            if (in_array($logo->getMimeType(), $allowed) && $logo->getSize() <= 2 * 1024 * 1024) {
                $dir = WRITEPATH . 'uploads/companies/';
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $name = 'company_' . $cid . '_' . time() . '.' . $logo->getExtension();
                $logo->move($dir, $name);
                $this->db->table('companies')->where('id', $cid)->update(['logo' => 'uploads/companies/' . $name]);
            }
        }

        $this->logActivity('create', 'companies', $cid);

        return redirect()->to(base_url('settings/companies'))->with('success', 'Company added.');
    }

    public function editCompany(int $id)
    {
        $this->requireRole('super_admin');
        $company = $this->db->table('companies')->where('id', $id)->get()->getRowArray();
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('settings/create_company', $this->viewData(['title' => 'Edit Company', 'company' => $company]));
    }

    public function updateCompany(int $id)
    {
        $this->requireRole('super_admin');
        $update = [
            'name'           => esc($this->request->getPost('name')),
            'address'        => esc($this->request->getPost('address') ?? ''),
            'contact_person' => esc($this->request->getPost('contact_person') ?? ''),
            'email'          => $this->request->getPost('email') ?? '',
            'phone'          => esc($this->request->getPost('phone') ?? ''),
            'vat_number'     => esc($this->request->getPost('vat_number') ?? ''),
            'status'         => $this->request->getPost('status') ?? 'active',
        ];
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            if (in_array($logo->getMimeType(), $allowed)) {
                $dir = WRITEPATH . 'uploads/companies/';
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $name = 'company_' . $id . '_' . time() . '.' . $logo->getExtension();
                $logo->move($dir, $name);
                $update['logo'] = 'uploads/companies/' . $name;
            }
        }
        $this->db->table('companies')->where('id', $id)->update($update);
        $this->logActivity('update', 'companies', $id);

        return redirect()->to(base_url('settings/companies'))->with('success', 'Company updated.');
    }

    public function activityLog()
    {
        $q = $this->request->getGet() ?? [];
        return redirect()->to(base_url('reports/activity-log') . ($q ? '?' . http_build_query($q) : ''));
    }

    public function loginHistory()
    {
        $this->requireRole('super_admin');
        $sessions = [];
        if ($this->db->tableExists('user_sessions')) {
            $sessions = $this->db->table('user_sessions s')
                ->select('s.*, u.name as user_name, u.email')
                ->join('users u', 'u.id=s.user_id', 'left')
                ->orderBy('s.logged_in_at', 'DESC')->limit(200)->get()->getResultArray();
        }

        return view('settings/login_history', $this->viewData(['title' => 'Login History', 'sessions' => $sessions]));
    }

    public function users()
    {
        $this->requireRole('super_admin');
        $pg = $this->paginate(25);
        $q  = $this->db->table('users u')
            ->select('u.*, r.display_name as role_display')
            ->join('roles r', 'r.id=u.role_id', 'left');
        $total = (clone $q)->countAllResults(false);
        $users = $q->orderBy('u.name', 'ASC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();

        return view('settings/users', $this->viewData([
            'title'       => 'User Management',
            'users'       => $users,
            'totalCount'  => $total,
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
        ]));
    }

    public function createUser()
    {
        $this->requireRole('super_admin');
        $roles   = $this->db->table('roles')->get()->getResultArray();
        $tenants = [];
        if ($this->db->tableExists('tenants')) {
            $tq = $this->db->table('tenants')->select('id, full_name, phone, email')->orderBy('full_name');
            if ($this->db->fieldExists('deleted_at', 'tenants')) {
                $tq->where('deleted_at', null);
            }
            $tenants = $tq->get()->getResultArray();
        }

        $companies = [];
        if ($this->db->tableExists('companies')) {
            $companies = $this->db->table('companies')->orderBy('name', 'ASC')->get()->getResultArray();
        }

        return view('settings/create_user', $this->viewData(array_merge(
            ['title' => 'Add User', 'roles' => $roles, 'tenants' => $tenants, 'companies' => $companies],
            $this->userAccessFormExtras(null, $roles)
        )));
    }

    public function storeUser()
    {
        $this->requireRole('super_admin');
        $rules = [
            'name'             => 'required',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
            'company_id'       => 'required|integer|greater_than[0]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $row = [
            'role_id'    => (int) $this->request->getPost('role_id'),
            'name'       => esc($this->request->getPost('name')),
            'email'      => $this->request->getPost('email'),
            'phone'      => esc($this->request->getPost('phone')),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'status'     => 'active',
            'company_id' => (int) $this->request->getPost('company_id'),
        ];
        if ($this->db->fieldExists('tenant_id', 'users')) {
            $row['tenant_id'] = (int) $this->request->getPost('tenant_id') ?: null;
        }
        $this->db->table('users')->insert($row);

        $userId   = (int) $this->db->insertID();
        $tenantId = (int) $this->request->getPost('tenant_id');
        if ($userId && $tenantId && $this->db->tableExists('tenants') && $this->db->fieldExists('user_id', 'tenants')) {
            $this->db->table('tenants')->where('id', $tenantId)->update(['user_id' => $userId]);
        }
        if ($userId) {
            $this->syncUserAccessFields($userId, (int) $this->request->getPost('role_id'));
        }

        return redirect()->to(base_url('settings/users'))->with('success', 'User created.');
    }

    public function editUser(int $id)
    {
        $this->requireRole('super_admin');
        $user    = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        $roles   = $this->db->table('roles')->get()->getResultArray();
        $tenants = [];
        if ($this->db->tableExists('tenants')) {
            $tq = $this->db->table('tenants')->select('id, full_name, phone, email')->orderBy('full_name');
            if ($this->db->fieldExists('deleted_at', 'tenants')) {
                $tq->where('deleted_at', null);
            }
            $tenants = $tq->get()->getResultArray();
        }

        $companies = [];
        if ($this->db->tableExists('companies')) {
            $companies = $this->db->table('companies')->orderBy('name', 'ASC')->get()->getResultArray();
        }

        return view('settings/edit_user', $this->viewData(array_merge(
            ['title' => 'Edit User', 'user' => $user, 'roles' => $roles, 'tenants' => $tenants, 'companies' => $companies],
            $this->userAccessFormExtras($user, $roles)
        )));
    }

    public function updateUser(int $id)
    {
        $this->requireRole('super_admin');
        $companyId = (int) $this->request->getPost('company_id');
        if ($companyId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please select a company.');
        }

        $update = [
            'name'       => esc($this->request->getPost('name')),
            'phone'      => esc($this->request->getPost('phone')),
            'role_id'    => (int) $this->request->getPost('role_id'),
            'status'     => $this->request->getPost('status'),
            'company_id' => $companyId,
        ];
        if ($this->db->fieldExists('tenant_id', 'users')) {
            $update['tenant_id'] = (int) $this->request->getPost('tenant_id') ?: null;
        }
        $pwd = $this->request->getPost('password');
        if ($pwd) {
            $update['password'] = password_hash($pwd, PASSWORD_BCRYPT);
        }
        $this->db->table('users')->where('id', $id)->update($update);

        $tenantId = (int) $this->request->getPost('tenant_id');
        if ($tenantId && $this->db->tableExists('tenants') && $this->db->fieldExists('user_id', 'tenants')) {
            $this->db->table('tenants')->where('user_id', $id)->update(['user_id' => null]);
            $this->db->table('tenants')->where('id', $tenantId)->update(['user_id' => $id]);
        }
        $this->syncUserAccessFields($id, (int) $this->request->getPost('role_id'));

        return redirect()->to(base_url('settings/users'))->with('success', 'User updated.');
    }

    public function deleteUser(int $id)
    {
        $this->requireRole('super_admin');
        if ($id == session()->get('user_id')) {
            return redirect()->back()->with('error', 'Cannot deactivate your own account.');
        }
        $this->db->table('users')->where('id', $id)->update(['status' => 'inactive']);

        return redirect()->to(base_url('settings/users'))->with('success', 'User deactivated.');
    }

    public function workspaces()
    {
        $this->requireRole('super_admin');
        $roles = $this->db->table('roles')->orderBy('id', 'ASC')->get()->getResultArray();
        $workspaceSvc = new \App\Services\WorkspaceService($this->db);

        foreach ($roles as &$role) {
            if (empty($role['workspace'])) {
                $role['workspace'] = $workspaceSvc->workspaceForRole((string) $role['name']);
            }
        }
        unset($role);

        return view('settings/workspaces', $this->viewData([
            'title'      => 'Role Workspaces',
            'roles'      => $roles,
            'workspaces' => ['pm' => 'Property Management', 'fm' => 'Facility Management', 'both' => 'Both', 'portal' => 'Portal'],
        ]));
    }

    public function saveWorkspaces()
    {
        $this->requireRole('super_admin');
        $posted = $this->request->getPost('workspace') ?? [];
        $allowed = ['pm', 'fm', 'both', 'portal', 'collector'];

        if (! $this->db->fieldExists('workspace', 'roles')) {
            return redirect()->back()->with('error', 'Workspace column not available. Run migrations first.');
        }

        $roles = $this->db->table('roles')->select('id, name')->get()->getResultArray();
        foreach ($roles as $role) {
            $ws = $posted[$role['name']] ?? null;
            if ($ws && in_array($ws, $allowed, true)) {
                $this->db->table('roles')->where('id', $role['id'])->update(['workspace' => $ws]);
            }
        }

        return redirect()->to(base_url('settings/workspaces'))->with('success', 'Workspace assignments saved.');
    }

    public function contractTemplates()
    {
        $this->requireRole('super_admin', 'property_manager', 'real_estate_manager');
        $templates = [];
        if ($this->db->tableExists('contract_templates')) {
            $templates = $this->db->table('contract_templates')
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('settings/contract_templates', $this->viewData([
            'title'     => 'Contract Templates',
            'templates' => $templates,
        ]));
    }

    public function saveContractTemplate()
    {
        $this->requireRole('super_admin', 'property_manager', 'real_estate_manager');
        if (! $this->db->tableExists('contract_templates')) {
            return redirect()->back()->with('error', 'Contract templates table not available. Run migrations first.');
        }

        $id   = (int) ($this->request->getPost('id') ?? 0);
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Template name is required.');
        }

        $data = [
            'name'       => esc($name),
            'content_en' => $this->request->getPost('content_en') ?? '',
            'content_ar' => $this->request->getPost('content_ar') ?? '',
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $this->db->table('contract_templates')->where('id', $id)->update($data);
            $this->logActivity('update', 'contract_templates', $id, $name);

            return redirect()->to(base_url('settings/contract-templates'))->with('success', 'Template updated.');
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('contract_templates')->insert($data);
        $newId = $this->db->insertID();
        $this->logActivity('create', 'contract_templates', $newId, $name);

        return redirect()->to(base_url('settings/contract-templates'))->with('success', 'Template created.');
    }


    public function permissionsMatrix()
    {
        $this->requireRole('super_admin');

        if (!$this->db->tableExists('role_permissions')) {
            return view('settings/permissions', $this->viewData([
                'title'    => 'Permissions Matrix',
                'roles'    => [],
                'modules'  => [],
                'matrix'   => [],
                'migrationRequired' => true,
            ]));
        }

        $modules = [
            'dashboard', 'facilities', 'units', 'tenants', 'landlords', 'leases', 'cheques',
            'payments', 'invoices', 'crm', 'sales', 'helpdesk', 'workorders', 'assets',
            'compliance', 'employees', 'vendors', 'inventory', 'procurement', 'estimations',
            'reports', 'quotations', 'media', 'ai_insights', 'settings',
        ];

        $roles = $this->db->table('roles')->orderBy('id')->get()->getResultArray();

        // Seed default rows if empty
        $existing = $this->db->table('role_permissions')->countAllResults();
        if ($existing === 0) {
            $this->seedDefaultPermissions($roles, $modules);
        }

        $rawPerms = $this->db->table('role_permissions')->get()->getResultArray();
        $matrix   = [];
        foreach ($rawPerms as $p) {
            $matrix[$p['role_id']][$p['module']] = $p;
        }

        return view('settings/permissions', $this->viewData([
            'title'   => 'Permissions Matrix',
            'roles'   => $roles,
            'modules' => $modules,
            'matrix'  => $matrix,
        ]));
    }

    public function savePermissionsMatrix()
    {
        $this->requireRole('super_admin');

        if (!$this->db->tableExists('role_permissions')) {
            return redirect()->back()->with('error', 'Permissions table not available.');
        }

        $posted = $this->request->getPost('perm') ?? [];
        $roles  = $this->db->table('roles')->get()->getResultArray();

        foreach ($roles as $role) {
            $rId = $role['id'];
            $rolePerms = $posted[$rId] ?? [];
            foreach ($rolePerms as $module => $flags) {
                $existing = $this->db->table('role_permissions')
                    ->where('role_id', $rId)->where('module', $module)->get()->getRowArray();

                $data = [
                    'can_view'   => !empty($flags['view'])   ? 1 : 0,
                    'can_create' => !empty($flags['create']) ? 1 : 0,
                    'can_edit'   => !empty($flags['edit'])   ? 1 : 0,
                    'can_delete' => !empty($flags['delete']) ? 1 : 0,
                ];

                if ($existing) {
                    $this->db->table('role_permissions')->where('id', $existing['id'])->update($data);
                } else {
                    $this->db->table('role_permissions')->insert(array_merge($data, [
                        'role_id' => $rId,
                        'module'  => $module,
                    ]));
                }
            }
        }

        return redirect()->to(base_url('settings/permissions'))->with('success', 'Permissions matrix saved.');
    }

    private function seedDefaultPermissions(array $roles, array $modules): void
    {
        $pmRoles = [
            'property_manager', 'real_estate_manager', 'salesman', 'finance_manager', 'finance_user',
            'supervisor', 'landlord', 'leasing_agent', 'crm_agent', 'accountant',
        ];
        $pmEditRoles = ['property_manager', 'real_estate_manager', 'leasing_agent'];
        $pmModules = ['units', 'leases', 'facilities', 'tenants'];

        foreach ($roles as $role) {
            $name    = (string) ($role['name'] ?? '');
            $isAdmin = $name === 'super_admin';
            $isPm     = in_array($name, $pmRoles, true);
            $canEdit  = $isAdmin || in_array($name, $pmEditRoles, true);

            foreach ($modules as $module) {
                $canView = $isAdmin || $isPm && in_array($module, $pmModules, true) ? 1 : 1;
                $this->db->table('role_permissions')->insert([
                    'role_id'    => $role['id'],
                    'module'     => $module,
                    'can_view'   => $canView,
                    'can_create' => $isAdmin || ($isPm && $canEdit && in_array($module, $pmModules, true)) ? 1 : 0,
                    'can_edit'   => $isAdmin || ($isPm && $canEdit && in_array($module, ['units', 'facilities', 'leases'], true)) ? 1 : 0,
                    'can_delete' => $isAdmin ? 1 : 0,
                ]);
            }
        }
    }

    private function _deleteLogoFile(string $stored): void
    {
        if ($stored === '') {
            return;
        }
        if (str_contains($stored, '/')) {
            $path = WRITEPATH . 'uploads/' . $stored;
            if (is_file($path)) {
                @unlink($path);
            }
            return;
        }
        foreach ([WRITEPATH . 'uploads/logos/' . $stored, WRITEPATH . 'uploads/' . $stored] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function migrateLegacyLogo(): void
    {
        $logo = $this->settings['company_logo'] ?? '';
        if ($logo === '' || str_contains($logo, '/')) {
            return;
        }
        $legacy = WRITEPATH . 'uploads/' . $logo;
        if (! is_file($legacy)) {
            return;
        }
        $dir = WRITEPATH . 'uploads/logos/';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir . basename($logo);
        if (! is_file($dest) && @rename($legacy, $dest)) {
            $this->_saveSetting('company_logo', 'logos/' . basename($logo));
            cache()->delete('system_settings');
        helper('fm');
        fm_clear_settings_cache();
        }
    }

    private function _saveSetting(string $key, string $value): void
    {
        $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
        if ($exists) {
            $this->db->table('system_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
        } else {
            $this->db->table('system_settings')->insert(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    /** @return array<string, mixed> */
    private function userAccessFormExtras(?array $user = null, ?array $roles = null): array
    {
        $facilities = [];
        if ($this->db->tableExists('facilities')) {
            $q = $this->db->table('facilities')->select('id, name, code')->where('status', 'active');
            if ($this->db->fieldExists('deleted_at', 'facilities')) {
                $q->where('deleted_at', null);
            }
            $facilities = $q->orderBy('name', 'ASC')->get()->getResultArray();
        }

        $landlords = [];
        if ($this->db->tableExists('landlords')) {
            $lq = $this->db->table('landlords')->select('id, full_name, email')->orderBy('full_name', 'ASC');
            if ($this->db->fieldExists('deleted_at', 'landlords')) {
                $lq->where('deleted_at', null);
            }
            $landlords = $lq->get()->getResultArray();
        }

        $assignedFacilityIds = [];
        if ($user && ! empty($user['id'])) {
            $assignedFacilityIds = UserFacilityService::facilityIdsForUser($this->db, (int) $user['id']);
        }

        return [
            'facilities'           => $facilities,
            'landlordsList'        => $landlords,
            'assignedFacilityIds'  => $assignedFacilityIds,
            'userLandlordId'       => (int) ($user['landlord_id'] ?? 0),
            'hasLandlordUserCol'   => $this->db->fieldExists('landlord_id', 'users'),
            'roles'                => $roles ?? $this->db->table('roles')->get()->getResultArray(),
        ];
    }

    private function syncUserAccessFields(int $userId, int $roleId): void
    {
        $roleRow  = $this->db->table('roles')->select('name')->where('id', $roleId)->get()->getRowArray();
        $roleName = (string) ($roleRow['name'] ?? '');

        if (UserFacilityService::usesAssignedFacilities($roleName)) {
            $facilityIds = array_values(array_filter(array_map('intval', (array) $this->request->getPost('facility_ids'))));
            UserFacilityService::syncUserFacilities($this->db, $userId, $facilityIds);
            UserFacilityService::syncUserPropertyAssignments($this->db, $userId, $roleName, $facilityIds);
        } else {
            UserFacilityService::syncUserFacilities($this->db, $userId, []);
        }

        if ($this->db->fieldExists('landlord_id', 'users')) {
            $landlordId = (int) $this->request->getPost('landlord_id') ?: null;
            if ($roleName === 'landlord') {
                $this->db->table('users')->where('id', $userId)->update(['landlord_id' => $landlordId]);
            } else {
                $this->db->table('users')->where('id', $userId)->update(['landlord_id' => null]);
            }
        }
    }
}
