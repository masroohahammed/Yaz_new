<?php

namespace App\Controllers;

use App\Services\PmCrudService;
use Config\PmFormFields;
use Config\PmModules as PmModuleConfig;

/**
 * Generic PM registry powered by PmCrudService.
 *
 * Dedicated controllers remain the live path for landlords, tenants, leases,
 * payments, cheques, CRM, sales (including commission-rules), utilities,
 * budgets, and offers. This controller serves leftover slugs that have no
 * company-wide dedicated list (landlord-payouts) and redirects known slugs.
 */
class PmModules extends BaseController
{
    protected ?string $workspaceRequired = 'pm';

    /** @var array<string, string> */
    private const DEDICATED = [
        'landlords'             => 'landlords',
        'tenants'               => 'tenants',
        'leases'                => 'contracts',
        'rent-payments'         => 'payments',
        'cheques'               => 'cheques',
        'outgoing-cheques'      => 'outgoing-cheques',
        'crm'                   => 'crm',
        'sales'                 => 'sales',
        'pm-utilities'          => 'utilities',
        'budgeting'             => 'budgets',
        'complimentary-offers'  => 'complimentary-offers',
        'commission-rules'      => 'sales/commission-rules',
    ];

    private function crud(): PmCrudService
    {
        return new PmCrudService($this->db);
    }

    public function index(string $slug)
    {
        if ($redirect = $this->dedicatedRedirect($slug)) {
            return $redirect;
        }

        $module = $this->requireModule($slug);
        if ($module instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $module;
        }

        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $result = $this->crud()->list($slug, $this->companyId(), $search, 100, 0);

        return view('pm_modules/index', $this->viewData([
            'title'  => $module['title'],
            'slug'   => $slug,
            'module' => $module,
            'rows'   => $result['rows'],
            'search' => $search,
            'total'  => $result['total'],
        ]));
    }

    public function create(string $slug)
    {
        if ($redirect = $this->dedicatedRedirect($slug, 'create')) {
            return $redirect;
        }

        return $this->form($slug, null);
    }

    public function store(string $slug)
    {
        if ($redirect = $this->dedicatedRedirect($slug)) {
            return $redirect;
        }
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('pm/' . $slug . '/create'));
        }

        $module = $this->requireModule($slug);
        if ($module instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $module;
        }

        $built = $this->crud()->validateAndBuild($slug, $this->request->getPost() ?? [], $this->currentUser(), false);
        if ($built['errors'] !== []) {
            return redirect()->back()->withInput()->with('errors', $built['errors']);
        }

        $number = $this->crud()->autoNumber($slug, fn ($prefix, $table, $field) => $this->generateNumber($prefix, $table, $field));
        if ($number && ! empty($module['number']['field'])) {
            $built['data'][$module['number']['field']] = $number;
        }

        $this->db->transStart();
        $this->db->table($module['table'])->insert($built['data']);
        $id = (int) $this->db->insertID();
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Could not save the record.');
        }

        $this->logActivity('create', $module['table'], $id, $module['title'] . ' created');

        return redirect()->to(base_url('pm/' . $slug . '/' . $id))->with('success', $module['title'] . ' created.');
    }

    public function show(string $slug, int $id)
    {
        if ($redirect = $this->dedicatedRedirect($slug, (string) $id)) {
            return $redirect;
        }

        $module = $this->requireModule($slug);
        if ($module instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $module;
        }

        $row = $this->crud()->row($slug, $id, $this->companyId());
        if (! $row) {
            return redirect()->to(base_url('pm/' . $slug))->with('error', 'Record not found.');
        }

        return view('pm_modules/show', $this->viewData([
            'title'  => $module['title'],
            'slug'   => $slug,
            'module' => $module,
            'row'    => $row,
        ]));
    }

    public function edit(string $slug, int $id)
    {
        if ($redirect = $this->dedicatedRedirect($slug, $id . '/edit')) {
            return $redirect;
        }

        return $this->form($slug, $id);
    }

    public function update(string $slug, int $id)
    {
        if ($redirect = $this->dedicatedRedirect($slug, (string) $id)) {
            return $redirect;
        }
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('pm/' . $slug . '/' . $id . '/edit'));
        }

        $module = $this->requireModule($slug);
        if ($module instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $module;
        }

        $existing = $this->crud()->row($slug, $id, $this->companyId());
        if (! $existing) {
            return redirect()->to(base_url('pm/' . $slug))->with('error', 'Record not found.');
        }

        $built = $this->crud()->validateAndBuild($slug, $this->request->getPost() ?? [], $this->currentUser(), true);
        if ($built['errors'] !== []) {
            return redirect()->back()->withInput()->with('errors', $built['errors']);
        }

        $this->db->transStart();
        $this->db->table($module['table'])->where('id', $id)->update($built['data']);
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Could not update the record.');
        }

        $this->logActivity('update', $module['table'], $id, $module['title'] . ' updated');

        return redirect()->to(base_url('pm/' . $slug . '/' . $id))->with('success', $module['title'] . ' updated.');
    }

    private function form(string $slug, ?int $id)
    {
        $module = $this->requireModule($slug);
        if ($module instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $module;
        }

        $row = [];
        if ($id !== null) {
            $row = $this->crud()->row($slug, $id, $this->companyId()) ?? [];
            if ($row === []) {
                return redirect()->to(base_url('pm/' . $slug))->with('error', 'Record not found.');
            }
        }

        $companyId = $this->companyId();
        $options   = [];
        foreach (PmFormFields::sections($slug) as $section) {
            foreach ($section['fields'] as $field) {
                if (in_array($field['type'] ?? '', ['fk', 'fk_user'], true)) {
                    $options[$field['name']] = $this->crud()->fkOptions($field, $companyId, function ($q) {
                        $this->scopeCompany($q, 'company_id');
                    });
                }
            }
        }

        return view('pm_modules/form', $this->viewData([
            'title'   => ($id ? 'Edit ' : 'Add ') . $module['title'],
            'slug'    => $slug,
            'module'  => $module,
            'row'     => $row,
            'options' => $options,
            'action'  => $id ? base_url('pm/' . $slug . '/' . $id . '/update') : base_url('pm/' . $slug),
        ]));
    }

    private function dedicatedRedirect(string $slug, string $suffix = ''): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (! isset(self::DEDICATED[$slug])) {
            return null;
        }
        $target = self::DEDICATED[$slug];
        if ($suffix !== '') {
            $target .= '/' . ltrim($suffix, '/');
        }

        return redirect()->to(base_url($target));
    }

    /** @return array<string, mixed>|\CodeIgniter\HTTP\RedirectResponse */
    private function requireModule(string $slug)
    {
        $module = PmModuleConfig::get($slug);
        if (! $module) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (! $this->db->tableExists($module['table'])) {
            return redirect()->to(base_url('dashboard'))->with('error', $module['title'] . ' is not available. Run migrations first.');
        }

        return $module;
    }

    private function companyId(): ?int
    {
        $id = session()->get('company_id');

        return $id ? (int) $id : null;
    }
}
