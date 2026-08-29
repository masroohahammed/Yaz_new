<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;
use App\Database\AutoIncrementRepair;

/**
 * PM Inspections — move-in/move-out/periodic unit checklists.
 */
class Inspections extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';
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
        ];

        $totalCount = $this->countInspections($filters);
        $draftCount = $this->countInspections($filters, 'draft', true);
        $completedCount = $this->countInspections($filters, 'completed', true);

        $pg = $this->paginate(25);
        $rows = $this->inspectionListQuery($filters)
            ->select('ic.*, u.unit_number, u.facility_id, f.name AS property_name')
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
        return view('inspections/form', $this->viewData([
            'title'          => 'New Inspection',
            'inspection'     => null,
            'facilities'     => $this->scopedFacilitiesList('id, name'),
            'preselectProperty' => (int) ($this->request->getGet('property_id') ?? 0),
            'preselectUnit'     => (int) ($this->request->getGet('unit_id') ?? 0),
        ]));
    }

    public function store()
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $propertyId = (int) $this->request->getPost('property_id');
        $unitId = (int) $this->request->getPost('unit_id');
        $type = $this->request->getPost('inspection_type') ?: 'routine';
        $validTypes = ['move_in', 'move_out', 'routine', 'handover', 'periodic'];
        if ($type === 'periodic') {
            $type = 'routine';
        }
        if (! in_array($type, $validTypes, true)) {
            $type = 'routine';
        }

        if ($propertyId <= 0 || $unitId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Property and unit are required.');
        }

        AutoIncrementRepair::ensure($this->db, self::TABLE);

        $this->db->table(self::TABLE)->insert([
            'unit_id'           => $unitId,
            'type'              => $type,
            'inspection_date'   => $this->request->getPost('inspection_date') ?: date('Y-m-d'),
            'inspector_name'    => trim((string) $this->request->getPost('inspector')),
            'status'            => 'draft',
            'created_by'        => session()->get('user_id'),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

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
        ]));
    }

    private function saveChecklist(int $id, array $row): \CodeIgniter\HTTP\RedirectResponse
    {
        $areas = $this->request->getPost('areas') ?? [];
        $ratings = $this->request->getPost('condition_rating') ?? [];
        $notes = $this->request->getPost('item_notes') ?? [];

        $payload = ['areas' => $areas, 'ratings' => $ratings, 'notes' => $notes];
        $update = [
            'areas_json'         => json_encode($payload),
            'items_json'         => json_encode($payload),
            'overall_condition'  => $this->request->getPost('overall_condition'),
            'notes'              => trim((string) $this->request->getPost('overall_notes')),
            'status'             => $this->request->getPost('submit_action') === 'complete' ? 'completed' : 'draft',
            'updated_at'         => date('Y-m-d H:i:s'),
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
            ->join('facilities f', 'f.id = u.facility_id', 'left');
        $this->scopeFacilities($q, 'u.facility_id');

        if ($filters['property_id'] > 0) {
            $q->where('u.facility_id', $filters['property_id']);
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
            ->select('ic.*, u.unit_number, u.facility_id, f.name AS property_name')
            ->join('units u', 'u.id = ic.unit_id', 'left')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('ic.id', $id);
        $this->scopeFacilities($q, 'u.facility_id');

        return $q->get()->getRowArray() ?: null;
    }

    private function decodeAreas(array $row): array
    {
        $data = json_decode($row['areas_json'] ?? $row['items_json'] ?? '{}', true) ?: [];
        return $data['areas'] ?? [];
    }

    private function decodeItems(array $row): array
    {
        $data = json_decode($row['areas_json'] ?? $row['items_json'] ?? '{}', true) ?: [];
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

    private function migrationView()
    {
        return view('pm/migration_required', $this->viewData([
            'title' => 'Inspections',
        ]));
    }
}
