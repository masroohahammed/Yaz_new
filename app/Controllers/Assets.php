<?php

namespace App\Controllers;

use App\Services\AssetCodeService;
use App\Services\AssetScanLogService;

class Assets extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    private AssetCodeService $codeService;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->codeService = new AssetCodeService($this->db);
    }

    public function index()
    {
        $filters = [
            'search'   => $this->request->getGet('search') ?? '',
            'category' => $this->request->getGet('category') ?? '',
            'status'   => $this->request->getGet('status') ?? '',
            'facility' => $this->request->getGet('facility') ?? '',
        ];
        $q = $this->db->table('assets a')
            ->select('a.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->join('users u', 'u.id = a.assigned_to', 'left');
        $this->scopeFacilities($q, 'a.facility_id');
        if ($filters['search']) {
            $q->groupStart()
                ->like('a.name', $filters['search'])
                ->orLike('a.asset_code', $filters['search'])
                ->orLike('a.serial_number', $filters['search'])
                ->groupEnd();
        }
        if ($filters['category']) {
            $q->where('a.category', $filters['category']);
        }
        if ($filters['status']) {
            $q->where('a.status', $filters['status']);
        }
        if ($filters['facility']) {
            $q->where('a.facility_id', $filters['facility']);
        }
        $pg     = $this->paginate(25);
        $total  = (clone $q)->countAllResults(false);
        $assets = $q->orderBy('a.health_score', 'ASC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();
        $facilities = $this->scopeFacilities($this->db->table('facilities')->where('status', 'active'))->get()->getResultArray();
        $stats = $this->codeService->dashboardStats();

        return view('assets/index', $this->viewData([
            'title'       => 'Assets',
            'assets'      => $assets,
            'filters'     => $filters,
            'facilities'  => $facilities,
            'totalCount'  => $total,
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
            'assetStats'  => $stats,
        ]));
    }

    public function create()
    {
        $facilities = $this->db->table('facilities')->where('status', 'active')->get()->getResultArray();
        $users      = $this->db->table('users u')
            ->select('u.id, u.name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.status', 'active')
            ->orderBy('u.name')
            ->get()->getResultArray();

        return view('assets/create', $this->viewData([
            'title'      => 'Add Asset',
            'facilities' => $facilities,
            'users'      => $users,
        ]));
    }

    public function store()
    {
        $rules = ['name' => 'required', 'facility_id' => 'required', 'category' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = strtoupper(trim((string) $this->request->getPost('asset_code')))
            ?: 'AST-' . date('Y') . '-' . sprintf('%04d', random_int(1, 9999));

        $data = $this->buildAssetPayload($code);
        $this->codeService->ensureCodes($data);
        $this->filterExistingFields('assets', $data);
        $this->db->table('assets')->insert($data);
        $id = (int) $this->db->insertID();
        $this->codeService->ensureCodes($data, $id);

        return redirect()->to(base_url('asset-register/view/' . $id))->with('success', 'Asset added with QR label.');
    }

    public function view(int $id)
    {
        $asset = $this->db->table('assets a')
            ->select('a.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->join('users u', 'u.id = a.assigned_to', 'left')
            ->where('a.id', $id)
            ->where('a.deleted_at', null)
            ->get()->getRowArray();
        if (! $asset) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->codeService->ensureCodes($asset, $id);
        $asset = $this->db->table('assets a')
            ->select('a.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->join('users u', 'u.id = a.assigned_to', 'left')
            ->where('a.id', $id)->get()->getRowArray();

        $workOrders = $this->db->table('work_orders')
            ->where('asset_id', $id)->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')->get()->getResultArray();

        $complaints = [];
        if ($this->db->fieldExists('asset_id', 'maintenance_requests')) {
            $complaints = $this->db->table('maintenance_requests')
                ->where('asset_id', $id)
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->get()->getResultArray();
        }

        $documents = [];
        if ($this->db->tableExists('asset_documents')) {
            $documents = $this->db->table('asset_documents d')
                ->select('d.*, u.name AS uploaded_by_name')
                ->join('users u', 'u.id = d.uploaded_by', 'left')
                ->where('d.asset_id', $id)
                ->orderBy('d.created_at', 'DESC')
                ->get()->getResultArray();
        }

        $scanLogs = (new AssetScanLogService($this->db))->forAsset($id);
        $scanUrl  = $this->codeService->scanUrl($asset);

        return view('assets/view', $this->viewData([
            'title'      => $asset['name'],
            'asset'      => $asset,
            'workOrders' => $workOrders,
            'complaints' => $complaints,
            'documents'  => $documents,
            'scanLogs'   => $scanLogs,
            'scanUrl'    => $scanUrl,
            'qrImageUrl' => $this->codeService->qrImageUrl($scanUrl),
        ]));
    }

    public function edit(int $id)
    {
        $asset = $this->db->table('assets')->where('id', $id)->get()->getRowArray();
        if (! $asset) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $facilities = $this->db->table('facilities')->where('status', 'active')->get()->getResultArray();
        $users      = $this->db->table('users u')
            ->select('u.id, u.name')->where('u.status', 'active')->orderBy('u.name')->get()->getResultArray();

        return view('assets/edit', $this->viewData([
            'title'      => 'Edit Asset',
            'asset'      => $asset,
            'facilities' => $facilities,
            'users'      => $users,
        ]));
    }

    public function update(int $id)
    {
        $data = $this->buildAssetPayload(null);
        unset($data['health_score']);
        $data['health_score'] = (int) $this->request->getPost('health_score');
        $this->codeService->ensureCodes($data, $id);
        $data = $this->filterExistingFields('assets', $data);
        $this->db->table('assets')->where('id', $id)->update($data);

        return redirect()->to(base_url('asset-register/view/' . $id))->with('success', 'Asset updated.');
    }

    public function delete(int $id)
    {
        $this->db->table('assets')->where('id', $id)->update(['status' => 'disposed']);

        return redirect()->to(base_url('asset-register'))->with('success', 'Asset marked as disposed.');
    }

    public function deactivate(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager']);
        $this->db->table('assets')->where('id', $id)->update(['status' => 'retired']);

        return redirect()->to(base_url('asset-register/view/' . $id))->with('success', 'Asset deactivated.');
    }

    public function qrcode(int $id)
    {
        $asset = $this->loadAssetOr404($id);
        $this->codeService->ensureCodes($asset, $id);
        $scanUrl = $this->codeService->scanUrl($asset);

        return view('assets/qrcode', $this->viewData([
            'title'      => 'QR Code — ' . $asset['asset_code'],
            'asset'      => $asset,
            'scanUrl'    => $scanUrl,
            'qrImageUrl' => $this->codeService->qrImageUrl($scanUrl, 280),
        ]));
    }

    public function printLabel(int $id)
    {
        $asset = $this->loadAssetOr404($id);
        $size  = $this->request->getGet('size') ?: 'standard';

        return view('assets/print_label', $this->viewData([
            'title'      => 'Print Label',
            'asset'      => $asset,
            'scanUrl'    => $this->codeService->scanUrl($asset),
            'qrImageUrl' => $this->codeService->qrImageUrl($this->codeService->scanUrl($asset), 160),
            'labelSize'  => $size,
            'bulk'       => false,
        ]));
    }

    public function printLabelsBulk()
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $this->request->getGet('ids'))));
        if ($ids === []) {
            return redirect()->to(base_url('asset-register'))->with('error', 'Select assets to print.');
        }

        $assets = $this->db->table('assets')->whereIn('id', $ids)->where('deleted_at', null)->get()->getResultArray();
        foreach ($assets as &$a) {
            $this->codeService->ensureCodes($a, (int) $a['id']);
            $a['scan_url']    = $this->codeService->scanUrl($a);
            $a['qr_image_url'] = $this->codeService->qrImageUrl($a['scan_url'], 140);
        }

        return view('assets/print_label', $this->viewData([
            'title'     => 'Bulk Asset Labels',
            'assets'    => $assets,
            'labelSize' => $this->request->getGet('size') ?: 'standard',
            'bulk'      => true,
        ]));
    }

    public function uploadDocument(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);
        if (! $this->db->tableExists('asset_documents')) {
            return redirect()->back()->with('error', 'Document storage not available. Run migration.');
        }

        $file = $this->request->getFile('document');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Select a file to upload.');
        }

        $name = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads/assets', $name);
        $this->db->table('asset_documents')->insert([
            'asset_id'    => $id,
            'file_name'   => $file->getClientName(),
            'file_path'   => 'uploads/assets/' . $name,
            'doc_type'    => esc($this->request->getPost('doc_type') ?: 'general'),
            'uploaded_by' => session()->get('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('asset-register/view/' . $id . '#tab-docs'))->with('success', 'Document uploaded.');
    }

    public function history(int $id)
    {
        return redirect()->to(base_url('asset-register/view/' . $id . '#tab-maintenance'));
    }

    public function show(int $id)
    {
        return $this->view($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAssetPayload(?string $code): array
    {
        $payload = [
            'facility_id'          => $this->request->getPost('facility_id'),
            'name'                 => esc($this->request->getPost('name')),
            'asset_code'           => $code,
            'tag_number'           => esc($this->request->getPost('tag_number') ?? ''),
            'category'             => $this->request->getPost('category'),
            'asset_type'           => esc($this->request->getPost('asset_type') ?? ''),
            'brand'                => esc($this->request->getPost('brand') ?? ''),
            'manufacturer'         => esc($this->request->getPost('manufacturer') ?? ''),
            'model'                => esc($this->request->getPost('model') ?? ''),
            'serial_number'        => esc($this->request->getPost('serial_number') ?? ''),
            'location_in_facility' => esc($this->request->getPost('location_in_facility') ?? ''),
            'floor_room'           => esc($this->request->getPost('floor_room') ?? ''),
            'department'           => esc($this->request->getPost('department') ?? ''),
            'cost_center'          => esc($this->request->getPost('cost_center') ?? ''),
            'assigned_to'          => $this->request->getPost('assigned_to') ?: null,
            'purchase_date'        => $this->request->getPost('purchase_date') ?: null,
            'warranty_start'       => $this->request->getPost('warranty_start') ?: null,
            'purchase_cost'        => $this->request->getPost('purchase_cost') ?: null,
            'warranty_expiry'      => $this->request->getPost('warranty_expiry') ?: null,
            'amc_expiry'           => $this->request->getPost('amc_expiry') ?: null,
            'next_maintenance'     => $this->request->getPost('next_maintenance') ?: null,
            'criticality'          => $this->request->getPost('criticality') ?: 'medium',
            'health_score'         => 100,
            'status'               => $this->request->getPost('status') ?: 'active',
            'notes'                => esc($this->request->getPost('notes') ?? ''),
        ];

        return $payload;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterExistingFields(string $table, array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($this->db->fieldExists($k, $table)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadAssetOr404(int $id): array
    {
        $asset = $this->db->table('assets')->where('id', $id)->where('deleted_at', null)->get()->getRowArray();
        if (! $asset) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->codeService->ensureCodes($asset, $id);

        return $this->db->table('assets')->where('id', $id)->get()->getRowArray();
    }
}
