<?php
namespace App\Controllers;

use App\Controllers\Traits\ParkingContractTrait;
use App\Database\AutoIncrementRepair;
use App\Services\ContractSignatureService;
use App\Services\EntityQrService;
use App\Services\ParkingContractService;
use App\Services\UnitLeaseSyncService;

class Units extends BaseController
{
    use ParkingContractTrait;
    public function index(int $facilityId)
    {
        $facility = $this->db->table('facilities')->where('id',$facilityId)->get()->getRowArray();
        if (!$facility) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $this->assertFacilityAccess($facilityId);

        $statusFilter = $this->request->getGet('status') ?? '';
        $viewMode     = $this->request->getGet('view') === 'list' ? 'list' : 'grid';
        $q = $this->db->table('units')->where('facility_id',$facilityId);
        if ($statusFilter) $q->where('status',$statusFilter);
        $units = $q->orderBy('unit_number','ASC')->get()->getResultArray();

        $hasParkingUnits = (bool) array_filter(
            $units,
            static fn ($u) => strtolower((string) ($u['unit_type'] ?? '')) === 'parking'
        );

        $kpi = [
            'total'       => $this->db->table('units')->where('facility_id',$facilityId)->countAllResults(),
            'occupied'    => $this->db->table('units')->where('facility_id',$facilityId)->where('status','occupied')->countAllResults(),
            'vacant'      => $this->db->table('units')->where('facility_id',$facilityId)->where('status','vacant')->countAllResults(),
            'maintenance' => $this->db->table('units')->where('facility_id',$facilityId)->where('status','maintenance')->countAllResults(),
        ];
        $kpi['occupancy_pct'] = $kpi['total'] > 0 ? round(($kpi['occupied']/$kpi['total'])*100) : 0;

        return view('units/index', $this->viewData([
            'title'        => $facility['name'].' — Units',
            'facility'     => $facility,
            'units'        => $units,
            'kpi'          => $kpi,
            'statusFilter' => $statusFilter,
            'viewMode'     => $viewMode,
            'hasParkingUnits'=> $hasParkingUnits,
        ]));
    }

    /**
     * Company-scoped unit directory (sidebar "Units" with no facility id).
     */
    public function all()
    {
        $statusFilter = (string) ($this->request->getGet('status') ?? '');
        $search       = trim((string) ($this->request->getGet('search') ?? ''));

        $q = $this->db->table('units u')
            ->select('u.*, f.name as facility_name, f.id as facility_id')
            ->join('facilities f', 'f.id = u.facility_id', 'left');
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('u.deleted_at', null);
        }
        $this->scopeFacilities($q, 'u.facility_id');
        if ($this->db->fieldExists('company_id', 'facilities')) {
            $this->scopeCompany($q, 'f.company_id');
        }
        if ($statusFilter !== '') {
            $q->where('u.status', $statusFilter);
        }
        if ($search !== '') {
            $q->groupStart()
                ->like('u.unit_number', $search)
                ->orLike('f.name', $search)
                ->orLike('u.tenant_name', $search)
                ->groupEnd();
        }

        $units = $q->orderBy('f.name', 'ASC')->orderBy('u.unit_number', 'ASC')->limit(500)->get()->getResultArray();

        $kpi = ['total' => count($units), 'occupied' => 0, 'vacant' => 0, 'maintenance' => 0];
        foreach ($units as $u) {
            $st = (string) ($u['status'] ?? '');
            if (isset($kpi[$st])) {
                $kpi[$st]++;
            }
        }
        $kpi['occupancy_pct'] = $kpi['total'] > 0 ? round(($kpi['occupied'] / $kpi['total']) * 100) : 0;

        return view('units/all', $this->viewData([
            'title'        => 'Units',
            'units'        => $units,
            'kpi'          => $kpi,
            'statusFilter' => $statusFilter,
            'search'       => $search,
        ]));
    }

    public function create(int $facilityId)
    {
        $this->requirePermission('units.create');
        $facility = $this->db->table('facilities')->where('id',$facilityId)->get()->getRowArray();
        if (!$facility) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('units/create', $this->viewData(['title'=>'Add Unit','facility'=>$facility]));
    }

    public function store(int $facilityId)
    {
        $this->requirePermission('units.create');
        $rules = [
            'unit_number' => 'required|max_length[50]',
            'status'      => 'required|in_list[occupied,vacant,maintenance]',
        ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $contractEnd = $this->request->getPost('contract_end') ?: null;

        $insert = [
            'facility_id'      => $facilityId,
            'unit_number'      => esc($this->request->getPost('unit_number')),
            'floor'            => esc($this->request->getPost('floor')       ?? ''),
            'unit_type'        => esc($this->request->getPost('unit_type')   ?? ''),
            'area_sqft'        => $this->request->getPost('area_sqft')        ?: null,
            // Owner info
            'owner_name'       => esc($this->request->getPost('owner_name')   ?? ''),
            'owner_mobile'     => esc($this->request->getPost('owner_mobile') ?? ''),
            'owner_email'      => esc($this->request->getPost('owner_email')  ?? ''),
            // Tenant info
            'tenant_name'      => esc($this->request->getPost('tenant_name')   ?? ''),
            'tenant_mobile'    => esc($this->request->getPost('tenant_mobile') ?? ''),
            'tenant_email'     => esc($this->request->getPost('tenant_email')  ?? ''),
            // Contract
            'contract_number'  => esc($this->request->getPost('contract_number') ?? ''),
            'contract_start'   => $this->request->getPost('contract_start')  ?: null,
            'contract_end'     => $contractEnd,
            // Financial
            'rent_amount'      => $this->request->getPost('rent_amount')      ?: null,
            'security_deposit' => $this->request->getPost('security_deposit') ?: null,
            'status'           => $this->request->getPost('status'),
            'notes'            => esc($this->request->getPost('notes') ?? ''),
            'created_by'       => session()->get('user_id'),
        ];
        $insert = $this->withPlateNumber($insert, $this->request->getPost('unit_type'), $this->request->getPost('plate_number'));
        $this->db->table('units')->insert($insert);
        $unitId = $this->db->insertID();
        (new EntityQrService($this->db))->ensureToken('unit', (int) $unitId);

        // Handle contract attachment upload
        $file = $this->request->getFile('contract_attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowed = ['application/pdf','image/jpeg','image/png','image/webp'];
            if (in_array($file->getMimeType(),$allowed) && $file->getSize() <= 10*1024*1024) {
                $dir = WRITEPATH.'uploads/contracts/';
                if (!is_dir($dir)) mkdir($dir,0755,true);
                $name = 'unit_'.$unitId.'_'.time().'.'.$file->getExtension();
                $file->move($dir,$name);
                $this->db->table('units')->where('id',$unitId)->update(['contract_attachment'=>'uploads/contracts/'.$name]);
            }
        }

        // Auto-create contract record if tenant present
        if (!empty($this->request->getPost('tenant_name')) && !empty($this->request->getPost('contract_start')) && $contractEnd) {
            $conNum = $this->generateNumber('CON','contracts','contract_number');
            $this->db->table('contracts')->insert([
                'contract_number' => $conNum,
                'facility_id'     => $facilityId,
                'unit_id'         => $unitId,
                'client_name'     => esc($this->request->getPost('tenant_name')),
                'client_email'    => esc($this->request->getPost('tenant_email')  ?? ''),
                'client_mobile'   => esc($this->request->getPost('tenant_mobile') ?? ''),
                'contract_type'   => 'tenancy',
                'start_date'      => $this->request->getPost('contract_start'),
                'end_date'        => $contractEnd,
                'value'           => $this->request->getPost('rent_amount') ?: 0,
                'status'          => 'active',
                'created_by'      => session()->get('user_id'),
            ]);
        }

        $this->logActivity('create','units',$unitId);
        helper('fm');
        return redirect()->to(fm_property_url($facilityId) . '#tab-units')->with('success','Unit added successfully.');
    }

    public function view(int $id)
    {
        $unit = $this->db->table('units u')
            ->select('u.*, f.name as facility_name, f.id as facility_id')
            ->join('facilities f','f.id=u.facility_id','left')
            ->where('u.id',$id)->get()->getRowArray();
        if (!$unit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $this->assertFacilityAccess((int) $unit['facility_id']);

        $qrSvc = new EntityQrService($this->db);
        $qrSvc->ensureToken('unit', $id);
        $unit = $this->db->table('units u')
            ->select('u.*, f.name as facility_name, f.id as facility_id')
            ->join('facilities f','f.id=u.facility_id','left')
            ->where('u.id',$id)->get()->getRowArray() ?? $unit;

        $scanUrl    = $qrSvc->scanUrl('unit', $unit);
        $qrImageUrl = $qrSvc->qrImageUrl($scanUrl, 200);

        $workOrders = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.status, w.priority, w.created_at, u.name as assigned_name')
            ->join('users u','u.id=w.assigned_to','left')
            ->where('w.unit_id',$id)
            ->orderBy('w.created_at','DESC')->limit(10)->get()->getResultArray();

        $contract = $this->db->table('contracts')
            ->where('unit_id',$id)->where('status','active')
            ->orderBy('created_at','DESC')->limit(1)->get()->getRowArray();

        $checklists = $this->db->table('unit_checklists uc')
            ->select('uc.*, u.name as created_by_name')
            ->join('users u','u.id=uc.created_by','left')
            ->where('uc.unit_id',$id)
            ->orderBy('uc.created_at','DESC')->get()->getResultArray();

        $daysToExpiry = null;
        if ($unit['contract_end']) {
            $daysToExpiry = (int)ceil((strtotime($unit['contract_end']) - time()) / 86400);
        }

        $leaseContracts = [];
        $activeLeaseContract = null;
        if ($this->db->tableExists('lease_contracts')) {
            $leaseContracts = $this->db->table('lease_contracts lc')
                ->select('lc.*')
                ->where('lc.unit_id', $id)
                ->where('lc.deleted_at', null)
                ->orderBy('lc.start_date', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
            foreach ($leaseContracts as $lc) {
                if (in_array($lc['status'] ?? '', ['active', 'draft'], true)) {
                    $activeLeaseContract = $lc;
                    break;
                }
            }
        }

        $leasePayments = [];
        if ($this->db->tableExists('lease_payments')) {
            $leasePayments = $this->db->table('lease_payments')
                ->where('unit_id', $id)
                ->orderBy('due_date', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        $assets = $this->db->tableExists('assets')
            ? $this->db->table('assets')->where('facility_id', $unit['facility_id'])->where('deleted_at', null)->limit(20)->get()->getResultArray()
            : [];

        $workspace = $this->currentWorkspace();

        $unitDocuments = [];
        if ($this->db->tableExists('documents') && $this->db->fieldExists('module', 'documents')) {
            $unitDocuments = $this->db->table('documents')
                ->where('module', 'unit')
                ->where('ref_id', $id)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        return view('units/view', $this->viewData([
            'title'         => 'Unit '.$unit['unit_number'],
            'unit'          => $unit,
            'workOrders'    => $workOrders,
            'contract'      => $contract,
            'checklists'    => $checklists,
            'daysToExpiry'  => $daysToExpiry,
            'leaseContracts'=> $leaseContracts,
            'activeLeaseContract' => $activeLeaseContract,
            'leasePayments' => $leasePayments,
            'assets'        => $assets,
            'workspace'     => $workspace,
            'isParkingUnit' => $this->isParkingUnitRow($unit),
            'unitDocuments' => $unitDocuments,
            'scanUrl'       => $scanUrl,
            'qrImageUrl'    => $qrImageUrl,
        ]));
    }

    public function qrcode(int $id)
    {
        $unit = $this->db->table('units u')
            ->select('u.*, f.name as facility_name')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.id', $id)->get()->getRowArray();
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertFacilityAccess((int) $unit['facility_id']);

        $qrSvc = new EntityQrService($this->db);
        $qrSvc->ensureToken('unit', $id);
        $unit = $this->db->table('units')->where('id', $id)->get()->getRowArray() ?? $unit;
        $scanUrl = $qrSvc->scanUrl('unit', $unit);

        return view('units/qrcode', $this->viewData([
            'title'      => 'QR Code — Unit ' . $unit['unit_number'],
            'unit'       => $unit,
            'scanUrl'    => $scanUrl,
            'qrImageUrl' => $qrSvc->qrImageUrl($scanUrl, 280),
        ]));
    }

    public function parkingContract(int $id)
    {
        $unit = $this->db->table('units u')
            ->select('u.*, f.name as facility_name')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.id', $id)->get()->getRowArray();
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertFacilityAccess((int) $unit['facility_id']);
        if (! $this->isParkingUnitRow($unit)) {
            return redirect()->to(base_url('units/view/' . $id))->with('error', 'Parking contract is only available for parking units.');
        }

        $leaseId = (int) ($this->request->getGet('contract_id') ?? 0);
        $svc     = new ParkingContractService($this->db);
        $d       = $svc->buildDefaults($id, $leaseId > 0 ? $leaseId : null);

        if ($leaseId < 1 && ! empty($d['lease_contract_id'])) {
            $leaseId = (int) $d['lease_contract_id'];
        }

        $activeLease = null;
        if ($leaseId > 0 && $this->db->tableExists('lease_contracts')) {
            $activeLease = $this->db->table('lease_contracts')->where('id', $leaseId)->get()->getRowArray();
        }

        helper('fm');

        return view('leases/parking_contract_form', $this->viewData([
            'title'       => 'Parking Contract — Unit ' . $unit['unit_number'],
            'unit'        => $unit,
            'd'           => $d,
            'backUrl'     => fm_unit_view_url($id),
            'printUrl'    => base_url('units/' . $id . '/parking-contract/print'),
            'renewMode'   => (bool) $this->request->getGet('renew'),
            'activeLease' => $activeLease,
            'signLink'    => session()->getFlashdata('sign_link'),
        ]));
    }

    public function parkingContractPrint(int $id)
    {
        $unit = $this->db->table('units')->where('id', $id)->get()->getRowArray();
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertFacilityAccess((int) $unit['facility_id']);
        if (! $this->isParkingUnitRow($unit)) {
            return redirect()->to(base_url('units/view/' . $id))->with('error', 'Not a parking unit.');
        }

        $svc      = new ParkingContractService($this->db);
        $leaseId  = (int) ($this->request->getPost('lease_contract_id') ?? $this->request->getGet('contract_id') ?? 0);
        $defaults = $svc->buildDefaults($id, $leaseId > 0 ? $leaseId : null);
        $d        = $svc->mergeFormInput($defaults, array_merge(
            $this->request->getPost() ?? [],
            $this->request->getGet() ?? []
        ));

        $leaseId = $this->ensureLeaseFromParkingData($id, $d);
        if ($leaseId > 0) {
            $d['lease_contract_id'] = $leaseId;
        }

        $isRenew = (bool) ($this->request->getPost('renew') ?? $this->request->getGet('renew'));
        if ($isRenew && $leaseId > 0) {
            (new ContractSignatureService($this->db))->clearSignature($leaseId, true);
        }

        $this->persistParkingContractFields($id, $leaseId > 0 ? $leaseId : null, $d);

        $wantPdf = $this->request->getPost('pdf') || $this->request->getGet('pdf');
        $sigB64  = '';
        if ($leaseId > 0) {
            $leaseRow = $this->db->table('lease_contracts')->where('id', $leaseId)->get()->getRowArray();
            $sigB64   = (new ContractSignatureService($this->db))->signatureDataUri($leaseRow['tenant_signature_path'] ?? '');
        }

        return $this->renderParkingContractDocument($d, (bool) $wantPdf, $sigB64);
    }

    public function edit(int $id)
    {
        $this->requirePermission('units.edit');
        $unit = $this->db->table('units u')
            ->select('u.*, f.name as facility_name')
            ->join('facilities f','f.id=u.facility_id','left')
            ->where('u.id',$id)->get()->getRowArray();
        if (!$unit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('units/edit', $this->viewData(['title'=>'Edit Unit '.$unit['unit_number'],'unit'=>$unit]));
    }

    public function update(int $id)
    {
        $this->requirePermission('units.edit');
        $unit = $this->db->table('units')->where('id',$id)->get()->getRowArray();
        if (!$unit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $update = [
            'unit_number'      => esc($this->request->getPost('unit_number')),
            'floor'            => esc($this->request->getPost('floor')       ?? ''),
            'unit_type'        => esc($this->request->getPost('unit_type')   ?? ''),
            'area_sqft'        => $this->request->getPost('area_sqft')        ?: null,
            'owner_name'       => esc($this->request->getPost('owner_name')   ?? ''),
            'owner_mobile'     => esc($this->request->getPost('owner_mobile') ?? ''),
            'owner_email'      => esc($this->request->getPost('owner_email')  ?? ''),
            'tenant_name'      => esc($this->request->getPost('tenant_name')   ?? ''),
            'tenant_mobile'    => esc($this->request->getPost('tenant_mobile') ?? ''),
            'tenant_email'     => esc($this->request->getPost('tenant_email')  ?? ''),
            'contract_number'  => esc($this->request->getPost('contract_number') ?? ''),
            'contract_start'   => $this->request->getPost('contract_start')  ?: null,
            'contract_end'     => $this->request->getPost('contract_end')    ?: null,
            'rent_amount'      => $this->request->getPost('rent_amount')      ?: null,
            'security_deposit' => $this->request->getPost('security_deposit') ?: null,
            'status'           => $this->request->getPost('status'),
            'notes'            => esc($this->request->getPost('notes') ?? ''),
        ];
        $update = $this->withPlateNumber($update, $this->request->getPost('unit_type'), $this->request->getPost('plate_number'));
        $this->db->table('units')->where('id',$id)->update($update);

        // Handle new contract attachment
        $file = $this->request->getFile('contract_attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowed = ['application/pdf','image/jpeg','image/png','image/webp'];
            if (in_array($file->getMimeType(),$allowed) && $file->getSize() <= 10*1024*1024) {
                $dir = WRITEPATH.'uploads/contracts/';
                if (!is_dir($dir)) mkdir($dir,0755,true);
                $name = 'unit_'.$id.'_'.time().'.'.$file->getExtension();
                $file->move($dir,$name);
                $this->db->table('units')->where('id',$id)->update(['contract_attachment'=>'uploads/contracts/'.$name]);
            }
        }

        $this->logActivity('update','units',$id);
        return redirect()->to(base_url('units/view/'.$id))->with('success','Unit updated successfully.');
    }

    /** @param array<string, mixed> $row */
    private function withPlateNumber(array $row, ?string $unitType, ?string $plateNumber): array
    {
        if (! $this->db->fieldExists('plate_number', 'units')) {
            return $row;
        }

        $isParking = strtolower(trim((string) $unitType)) === 'parking';
        $row['plate_number'] = $isParking ? esc(trim((string) ($plateNumber ?? ''))) : null;

        return $row;
    }

    public function delete(int $id)
    {
        $this->requireRole('super_admin','facility_manager');
        $unit = $this->db->table('units')->where('id',$id)->get()->getRowArray();
        if (!$unit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $facilityId = $unit['facility_id'];
        $this->db->table('units')->where('id',$id)->update(['deleted_at'=>date('Y-m-d H:i:s'),'status'=>'vacant']);
        $this->logActivity('delete','units',$id);
        return redirect()->to(base_url('facilities/'.$facilityId).'#tab-units')->with('success','Unit removed.');
    }

    public function checklist(int $unitId, string $type)
    {
        $unit = $this->db->table('units u')->select('u.*, f.name as facility_name')->join('facilities f','f.id=u.facility_id','left')->where('u.id',$unitId)->get()->getRowArray();
        if (!$unit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $validTypes = ['move_in','move_out','routine','handover'];
        if (!in_array($type,$validTypes)) $type = 'routine';
        $existing = $this->db->table('unit_checklists')->where('unit_id',$unitId)->where('type',$type)->where('status','draft')->orderBy('created_at','DESC')->limit(1)->get()->getRowArray();
        return view('units/checklist', $this->viewData(['title'=>ucfirst(str_replace('_',' ',$type)).' Checklist','unit'=>$unit,'type'=>$type,'checklist'=>$existing]));
    }

    public function storeChecklist()
    {
        $unitId = (int)$this->request->getPost('unit_id');
        $type   = $this->request->getPost('type');
        $unit   = $this->db->table('units')->where('id',$unitId)->get()->getRowArray();
        if (!$unit) return redirect()->back()->with('error','Unit not found.');

        $items   = $this->request->getPost('items') ?? [];
        $notes   = $this->request->getPost('item_notes') ?? [];
        $encoded = json_encode(['items'=>$items,'notes'=>$notes]);

        AutoIncrementRepair::ensure($this->db, 'unit_checklists');

        $this->db->table('unit_checklists')->insert([
            'unit_id'    => $unitId,
            'type'       => $type,
            'items_json' => $encoded,
            'notes'      => esc($this->request->getPost('general_notes') ?? ''),
            'status'     => $this->request->getPost('submit_action') === 'complete' ? 'completed' : 'draft',
            'created_by' => session()->get('user_id'),
        ]);
        $clId = $this->db->insertID();

        // Update unit status based on checklist type
        if ($type === 'move_in') {
            $this->db->table('units')->where('id',$unitId)->update(['status'=>'occupied']);
        } elseif ($type === 'move_out') {
            $this->db->table('units')->where('id',$unitId)->update(['status'=>'vacant','tenant_name'=>'','tenant_mobile'=>'','tenant_email'=>'']);
        }

        $this->logActivity('create','unit_checklists',$clId,"$type checklist for unit {$unit['unit_number']}");
        return redirect()->to(base_url('units/view/'.$unitId))->with('success',ucfirst(str_replace('_',' ',$type)).' checklist saved.');
    }

    public function printChecklist(int $id)
    {
        $cl = $this->db->table('unit_checklists uc')
            ->select('uc.*, u.unit_number, u.floor, f.name as facility_name')
            ->join('units u', 'u.id=uc.unit_id', 'left')
            ->join('facilities f', 'f.id=u.facility_id', 'left')
            ->where('uc.id', $id)
            ->get()
            ->getRowArray();
        if (!$cl) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $unitRow = $this->db->table('units')->select('facility_id')->where('id', $cl['unit_id'])->get()->getRowArray();
        if ($unitRow) {
            $this->assertFacilityAccess((int) $unitRow['facility_id']);
        }

        $decoded = json_decode($cl['items_json'] ?? '{}', true) ?: [];
        $rawItems = $decoded['items'] ?? [];
        $rawNotes = $decoded['notes'] ?? [];
        $printItems = [];
        foreach ($rawItems as $i => $label) {
            if (! trim((string) $label)) {
                continue;
            }
            $printItems[] = [
                'label'   => $label,
                'checked' => true,
                'notes'   => $rawNotes[$i] ?? '',
            ];
        }

        return view('compliance/checklist_print', $this->viewData([
            'checklistTitle' => ucfirst(str_replace('_', ' ', $cl['type'])) . ' — ' . $cl['unit_number'],
            'checklistType'  => $cl['type'],
            'unitRef'        => $cl['unit_number'] . ($cl['floor'] ? ' / ' . $cl['floor'] : ''),
            'facilityName'   => $cl['facility_name'],
            'inspectionDate' => date('d M Y', strtotime($cl['created_at'])),
            'refNumber'      => 'UC-' . $cl['id'],
            'sections'       => [['title' => 'Checklist Items', 'items' => $printItems]],
            'usePdf'         => true,
        ]));
    }
}
