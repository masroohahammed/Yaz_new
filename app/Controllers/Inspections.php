<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Database\AutoIncrementRepair;
use App\Services\InspectionAreaService;
use App\Services\InspectionPhotoService;

/**
 * PM Inspections — property, unit, and asset checklists.
 */
class Inspections extends BaseController
{
    use PmModuleTrait;

    /** Allow FM staff to inspect via QR scan flows. */
    protected ?string $workspaceRequired = null;
    private const TABLE = 'unit_checklists';

    public function index()
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return $this->migrationView();
        }

        $filters = [
            'property_id' => (int) ($this->request->getGet('property_id') ?? 0),
            'type'        => $this->request->getGet('inspection_type') ?? '',
            'status'      => $this->request->getGet('status') ?? '',
            'scope'       => $this->request->getGet('scope_type') ?? '',
        ];

        $totalCount = $this->countInspections($filters);
        $draftCount = $this->countInspections($filters, 'draft', true);
        $completedCount = $this->countInspections($filters, 'completed', true);

        $pg = $this->paginate(25);
        $rows = $this->inspectionListQuery($filters)
            ->select('ic.*, u.unit_number, u.facility_id AS unit_facility_id, f.name AS property_name, fa.name AS asset_name')
            ->orderBy('ic.created_at', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('inspections/index', $this->viewData([
            'title'          => 'Inspections',
            'inspections'    => $rows,
            'filters'        => $filters,
            'facilities'     => $this->scopedFacilitiesList('id, name'),
            'currentPage'    => $pg['page'],
            'perPage'        => $pg['perPage'],
            'totalCount'     => $totalCount,
            'draftCount'     => $draftCount,
            'completedCount' => $completedCount,
        ]));
    }

    public function view(int $id)
    {
        $row = $this->findInspection($id);
        if (! $row) {
            return redirect()->to(base_url('pm-inspections'))->with('error', 'Inspection not found.');
        }

        return view('inspections/view', $this->viewData([
            'title'      => 'Inspection',
            'inspection' => $row,
            'items'      => $this->decodeItems($row),
        ]));
    }

    public function create()
    {
        $scopeType = $this->resolveScopeType(
            (string) ($this->request->getGet('scope') ?? ''),
            (int) ($this->request->getGet('asset_id') ?? 0),
            (int) ($this->request->getGet('unit_id') ?? 0)
        );

        $propertyId = (int) old('property_id', $this->request->getGet('property_id') ?? $this->request->getGet('facility_id') ?? 0);
        $unitId     = (int) old('unit_id', $this->request->getGet('unit_id') ?? 0);
        $assetId    = (int) old('asset_id', $this->request->getGet('asset_id') ?? 0);
        $floorLabel = (string) old('floor_label', $this->request->getGet('floor_label') ?? '');

        $propertyFloors = 1;
        $assetRow       = null;

        if ($assetId > 0) {
            $assetRow = $this->db->table('assets a')
                ->select('a.*, f.name AS facility_name, f.id AS facility_id')
                ->join('facilities f', 'f.id = a.facility_id', 'left')
                ->where('a.id', $assetId)
                ->where('a.deleted_at', null)
                ->get()->getRowArray();
            if ($assetRow) {
                $scopeType  = 'asset';
                $propertyId = (int) ($assetRow['facility_id'] ?? $propertyId);
            }
        }

        if ($propertyId > 0) {
            $facility = $this->db->table('facilities')->select('id, name, floors')->where('id', $propertyId)->get()->getRowArray();
            if ($facility) {
                $propertyFloors = max(1, (int) ($facility['floors'] ?? 1));
            }
        }

        if ($unitId > 0 && $scopeType !== 'asset') {
            $unitRow = $this->db->table('units')->select('id, facility_id')->where('id', $unitId)->get()->getRowArray();
            if ($unitRow) {
                $scopeType  = 'unit';
                $propertyId = (int) ($unitRow['facility_id'] ?? $propertyId);
            }
        }

        return view('inspections/form', $this->viewData([
            'title'             => 'New Inspection',
            'inspection'        => null,
            'facilities'        => $this->scopedFacilitiesList('id, name, floors'),
            'scopeType'         => $scopeType,
            'preselectProperty' => $propertyId,
            'preselectUnit'     => $unitId,
            'preselectAsset'    => $assetId,
            'preselectFloor'    => $floorLabel,
            'propertyFloors'    => $propertyFloors,
            'assetRow'          => $assetRow,
            'preselectType'     => (string) ($this->request->getGet('inspection_type') ?? ''),
        ]));
    }

    public function store()
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $scopeType  = $this->resolveScopeType(
            (string) ($this->request->getPost('scope_type') ?? ''),
            (int) ($this->request->getPost('asset_id') ?? 0),
            (int) ($this->request->getPost('unit_id') ?? 0)
        );
        $propertyId = (int) $this->request->getPost('property_id');
        $unitId     = (int) $this->request->getPost('unit_id');
        $assetId    = (int) $this->request->getPost('asset_id');
        $floorLabel = trim((string) $this->request->getPost('floor_label'));

        if ($scopeType === 'asset') {
            if ($assetId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Asset is required for asset inspections.');
            }
            $assetRow = $this->db->table('assets')->select('id, facility_id')->where('id', $assetId)->where('deleted_at', null)->get()->getRowArray();
            if (! $assetRow) {
                return redirect()->back()->withInput()->with('error', 'Asset not found.');
            }
            $propertyId = (int) ($assetRow['facility_id'] ?? $propertyId);
            $unitId     = 0;
        } elseif ($scopeType === 'property') {
            if ($propertyId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Property is required for property inspections.');
            }
            if ($unitId > 0) {
                $unitRow = $this->db->table('units')->select('id, facility_id')->where('id', $unitId)->get()->getRowArray();
                if (! $unitRow || (int) ($unitRow['facility_id'] ?? 0) !== $propertyId) {
                    return redirect()->back()->withInput()->with('error', 'Selected unit does not belong to the chosen property.');
                }
            } else {
                $unitId = 0;
            }
            $assetId = 0;
        } else {
            if ($unitId > 0) {
                $unitRow = $this->db->table('units')->select('id, facility_id')->where('id', $unitId)->get()->getRowArray();
                if ($unitRow) {
                    if ($propertyId <= 0) {
                        $propertyId = (int) ($unitRow['facility_id'] ?? 0);
                    } elseif ((int) ($unitRow['facility_id'] ?? 0) !== $propertyId) {
                        return redirect()->back()->withInput()->with('error', 'Selected unit does not belong to the chosen property.');
                    }
                }
            }
            if ($propertyId <= 0 || $unitId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Property and unit are required. Please wait for units to load and select a unit.');
            }
            $scopeType = 'unit';
            $assetId   = 0;
            $floorLabel = '';
        }

        $type = (string) ($this->request->getPost('inspection_type') ?: 'routine');
        if (! in_array($type, ['move_in', 'move_out', 'routine'], true)) {
            $type = 'routine';
        }

        AutoIncrementRepair::ensure($this->db, self::TABLE);

        $insert = [
            'type'            => $type,
            'inspection_date' => $this->request->getPost('inspection_date') ?: date('Y-m-d'),
            'inspector_name'  => trim((string) $this->request->getPost('inspector')),
            'status'          => 'draft',
            'created_by'      => session()->get('user_id'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('facility_id', self::TABLE)) {
            $insert['facility_id'] = $propertyId > 0 ? $propertyId : null;
        }
        if ($this->db->fieldExists('asset_id', self::TABLE)) {
            $insert['asset_id'] = $assetId > 0 ? $assetId : null;
        }
        if ($this->db->fieldExists('scope_type', self::TABLE)) {
            $insert['scope_type'] = $scopeType;
        }
        if ($this->db->fieldExists('floor_label', self::TABLE)) {
            $insert['floor_label'] = $floorLabel !== '' ? $floorLabel : null;
        }

        $insert['unit_id'] = $unitId > 0 ? $unitId : null;

        $defaultAreas = InspectionAreaService::defaultAreasForScope($scopeType);
        $payload      = [
            'areas'      => $defaultAreas,
            'ratings'    => array_fill(0, count($defaultAreas), 'good'),
            'notes'      => array_fill(0, count($defaultAreas), ''),
            'photos'     => array_fill(0, count($defaultAreas), []),
            'priorities' => array_fill(0, count($defaultAreas), 'medium'),
            'statuses'   => array_fill(0, count($defaultAreas), 'open'),
        ];
        $insert['areas_json'] = json_encode($payload);
        $insert['items_json'] = json_encode($payload);

        $this->db->table(self::TABLE)->insert($insert);

        $id = (int) $this->db->insertID();
        $this->logActivity('create', self::TABLE, $id, 'Inspection created');

        return redirect()->to(base_url('pm-inspections/checklist/' . $id));
    }

    public function checklist(int $id)
    {
        $row = $this->findInspection($id);
        if (! $row) {
            return redirect()->to(base_url('pm-inspections'))->with('error', 'Inspection not found.');
        }

        if ($this->request->is('post')) {
            return $this->saveChecklist($id, $row);
        }

        return view('inspections/checklist', $this->viewData([
            'title'      => 'Inspection Checklist',
            'inspection' => $row,
            'areas'      => $this->decodeAreas($row),
            'savedData'  => $this->decodeItems($row),
            'priorities' => InspectionAreaService::priorities(),
            'statuses'   => InspectionAreaService::issueStatuses(),
            'conditions' => InspectionAreaService::conditionRatings(),
        ]));
    }

    private function saveChecklist(int $id, array $row): \CodeIgniter\HTTP\RedirectResponse
    {
        $areas = $this->request->getPost('areas') ?? [];
        $ratings = $this->request->getPost('condition_rating') ?? [];
        $notes = $this->request->getPost('item_notes') ?? [];
        $priorities = $this->request->getPost('item_priority') ?? [];
        $statuses = $this->request->getPost('item_status') ?? [];
        $existingPhotos = $this->request->getPost('existing_photos') ?? [];

        $validPriorities = InspectionAreaService::priorities();
        $validStatuses   = InspectionAreaService::issueStatuses();

        $photos = [];
        $normalizedPriorities = [];
        $normalizedStatuses   = [];

        foreach ($areas as $idx => $area) {
            $kept = InspectionPhotoService::normalizePhotoEntry($existingPhotos[$idx] ?? []);
            $newPaths = InspectionPhotoService::storeAreaUploads((int) $idx);
            $photos[] = array_values(array_unique(array_merge($kept, $newPaths)));

            $prio = strtolower(trim((string) ($priorities[$idx] ?? 'medium')));
            $normalizedPriorities[] = in_array($prio, $validPriorities, true) ? $prio : 'medium';

            $stat = strtolower(trim((string) ($statuses[$idx] ?? 'open')));
            $normalizedStatuses[] = in_array($stat, $validStatuses, true) ? $stat : 'open';
        }

        $payload = [
            'areas'      => $areas,
            'ratings'    => $ratings,
            'notes'      => $notes,
            'photos'     => $photos,
            'priorities' => $normalizedPriorities,
            'statuses'   => $normalizedStatuses,
        ];
        $update = [
            'areas_json'        => json_encode($payload),
            'items_json'        => json_encode($payload),
            'overall_condition' => $this->request->getPost('overall_condition'),
            'notes'             => trim((string) $this->request->getPost('overall_notes')),
            'status'            => $this->request->getPost('submit_action') === 'complete' ? 'completed' : 'draft',
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table(self::TABLE)->where('id', $id)->update($update);
        $this->logActivity('update', self::TABLE, $id, 'Checklist saved');

        return redirect()->to(base_url('pm-inspections/view/' . $id))->with('success', 'Checklist saved.');
    }

    public function compare(int $id1, int $id2)
    {
        $a = $this->findInspection($id1);
        $b = $this->findInspection($id2);
        if (! $a || ! $b) {
            return redirect()->to(base_url('pm-inspections'))->with('error', 'Inspections not found.');
        }

        return view('inspections/compare', $this->viewData([
            'title' => 'Compare Inspections',
            'left'  => $a,
            'right' => $b,
            'diff'  => $this->compareItems($a, $b),
        ]));
    }

    public function link(int $id)
    {
        $row = $this->findInspection($id);
        if (! $row) {
            return redirect()->to(base_url('pm-inspections'))->with('error', 'Inspection not found.');
        }

        if ($this->request->is('post')) {
            $linkTo = $this->request->getPost('link_to');
            $refId = (int) $this->request->getPost('ref_id');
            $this->db->table(self::TABLE)->where('id', $id)->update([
                'link_to' => $linkTo,
                'ref_id'  => $refId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to(base_url('pm-inspections/view/' . $id))->with('success', 'Inspection linked.');
        }

        $workOrders = $this->db->tableExists('work_orders')
            ? $this->db->table('work_orders')->select('id, wo_number, title')->orderBy('id', 'DESC')->limit(50)->get()->getResultArray()
            : [];
        $contracts = $this->db->tableExists('lease_contracts')
            ? $this->db->table('lease_contracts')->select('id, contract_number')->orderBy('id', 'DESC')->limit(50)->get()->getResultArray()
            : [];

        return view('inspections/link', $this->viewData([
            'title'      => 'Link Inspection',
            'inspection' => $row,
            'workOrders' => $workOrders,
            'contracts'  => $contracts,
        ]));
    }

    public function print_report(int $id)
    {
        $row = $this->findInspection($id);
        if (! $row) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('inspections/print', $this->viewData([
            'title'      => 'Inspection Report',
            'inspection' => $row,
            'items'      => $this->decodeItems($row),
            'usePdf'     => true,
        ]));
    }

    public function delete(int $id)
    {
        if (! $this->findInspection($id)) {
            return redirect()->to(base_url('pm-inspections'))->with('error', 'Inspection not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->delete();
        $this->logActivity('delete', self::TABLE, $id, 'Inspection deleted');

        return redirect()->to(base_url('pm-inspections'))->with('success', 'Inspection removed.');
    }

    private function inspectionListQuery(array $filters, ?string $status = null, bool $ignoreStatusFilter = false)
    {
        $q = $this->db->table(self::TABLE . ' ic')
            ->join('units u', 'u.id = ic.unit_id', 'left')
            ->join('facilities f', 'f.id = COALESCE(ic.facility_id, u.facility_id)', 'left', false)
            ->join('assets fa', 'fa.id = ic.asset_id', 'left');
        $this->scopeFacilities($q, 'COALESCE(ic.facility_id, u.facility_id)');

        if ($filters['property_id'] > 0) {
            $q->where('COALESCE(ic.facility_id, u.facility_id)', $filters['property_id'], false);
        }
        if (($filters['scope'] ?? '') !== '') {
            if ($this->db->fieldExists('scope_type', self::TABLE)) {
                $q->where('ic.scope_type', $filters['scope']);
            }
        }
        if ($filters['type'] !== '') {
            $q->where('ic.type', $filters['type']);
        }
        if ($status !== null && $status !== '') {
            $q->where('ic.status', $status);
        } elseif (! $ignoreStatusFilter && ($filters['status'] ?? '') !== '') {
            $q->where('ic.status', $filters['status']);
        }

        return $q;
    }

    private function countInspections(array $filters, ?string $status = null, bool $ignoreStatusFilter = false): int
    {
        return (int) $this->inspectionListQuery($filters, $status, $ignoreStatusFilter)->countAllResults();
    }

    private function findInspection(int $id): ?array
    {
        if (! $this->db->tableExists(self::TABLE)) {
            return null;
        }

        $q = $this->db->table(self::TABLE . ' ic')
            ->select('ic.*, u.unit_number, u.facility_id AS unit_facility_id, f.name AS property_name, fa.name AS asset_name, fa.asset_code')
            ->join('units u', 'u.id = ic.unit_id', 'left')
            ->join('facilities f', 'f.id = COALESCE(ic.facility_id, u.facility_id)', 'left', false)
            ->join('assets fa', 'fa.id = ic.asset_id', 'left')
            ->where('ic.id', $id);
        $this->scopeFacilities($q, 'COALESCE(ic.facility_id, u.facility_id)');

        return $q->get()->getRowArray() ?: null;
    }

    private function decodeAreas(array $row): array
    {
        $data = json_decode($row['areas_json'] ?? $row['items_json'] ?? '{}', true) ?: [];
        if (! empty($data['areas'])) {
            return $data['areas'];
        }

        $scope = (string) ($row['scope_type'] ?? 'unit');

        return InspectionAreaService::defaultAreasForScope($scope);
    }

    private function decodeItems(array $row): array
    {
        $data = json_decode($row['areas_json'] ?? $row['items_json'] ?? '{}', true) ?: [];
        if (empty($data['areas'])) {
            $data['areas'] = InspectionAreaService::defaultAreasForScope((string) ($row['scope_type'] ?? 'unit'));
        }
        foreach (['ratings', 'notes', 'photos', 'priorities', 'statuses'] as $key) {
            if (! isset($data[$key])) {
                $default = match ($key) {
                    'priorities' => 'medium',
                    'statuses'   => 'open',
                    'ratings'    => 'good',
                    'photos'     => [],
                    default      => '',
                };
                $data[$key] = array_fill(0, count($data['areas']), $default);
            }
        }

        if (isset($data['photos']) && is_array($data['photos'])) {
            $data['photos'] = array_map(
                static fn ($entry) => InspectionPhotoService::normalizePhotoEntry($entry),
                $data['photos']
            );
        }

        return $data;
    }

    private function compareItems(array $a, array $b): array
    {
        $itemsA = $this->decodeItems($a);
        $itemsB = $this->decodeItems($b);
        $ratingsA = $itemsA['ratings'] ?? [];
        $ratingsB = $itemsB['ratings'] ?? [];
        $areas = $itemsA['areas'] ?? [];
        $diff = [];

        foreach ($areas as $i => $area) {
            $ra = $ratingsA[$i] ?? '';
            $rb = $ratingsB[$i] ?? '';
            if ($ra !== $rb && ($ra === 'damaged' || $rb === 'damaged' || $ra === 'poor' || $rb === 'poor')) {
                $diff[] = ['area' => $area, 'before' => $rb, 'after' => $ra];
            }
        }

        return $diff;
    }

    private function resolveScopeType(string $explicit, int $assetId, int $unitId): string
    {
        if (in_array($explicit, ['property', 'unit', 'asset'], true)) {
            return $explicit;
        }
        if ($assetId > 0) {
            return 'asset';
        }
        if ($unitId > 0) {
            return 'unit';
        }

        return 'property';
    }

    private function migrationView()
    {
        return view('pm/migration_required', $this->viewData([
            'title' => 'Inspections',
        ]));
    }
}
