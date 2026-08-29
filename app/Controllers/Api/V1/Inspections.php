<?php

namespace App\Controllers\Api\V1;

class Inspections extends BaseApiController
{
    /**
     * GET /api/v1/inspections/property — facility-level inspection checklists.
     */
    public function propertyList()
    {
        if (! $this->db->tableExists('inspection_checklists')) {
            return $this->response->setJSON(['status' => true, 'data' => [], 'message' => 'Run compliance_inspections_patch.sql']);
        }

        $facilityId = (int) ($this->request->getGet('facility_id') ?? 0);
        $status     = trim((string) ($this->request->getGet('status') ?? ''));
        $frequency  = trim((string) ($this->request->getGet('frequency') ?? ''));

        $q = $this->db->table('inspection_checklists ic')
            ->select('ic.id, ic.facility_id, ic.title, ic.type, ic.frequency, ic.inspection_date, ic.inspector_name, ic.status, ic.score, ic.created_at, f.name AS facility_name')
            ->join('facilities f', 'f.id = ic.facility_id', 'left');
        $this->scopeFacilitiesForApi($q, 'ic.facility_id');

        if ($facilityId > 0) {
            $q->where('ic.facility_id', $facilityId);
        }
        if ($status !== '') {
            $q->where('ic.status', $status);
        }
        if ($frequency !== '' && $this->db->fieldExists('frequency', 'inspection_checklists')) {
            $q->where('ic.frequency', $frequency);
        }

        $rows = $q->orderBy('ic.inspection_date', 'DESC')->limit(200)->get()->getResultArray();

        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }

    /**
     * GET /api/v1/inspections/property/(:num)
     */
    public function propertyDetail(int $id)
    {
        if (! $this->db->tableExists('inspection_checklists')) {
            return $this->response->setStatusCode(503)->setJSON(['status' => false, 'message' => 'Inspection module not installed']);
        }

        $q = $this->db->table('inspection_checklists ic')
            ->select('ic.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = ic.facility_id', 'left')
            ->where('ic.id', $id);
        $this->scopeFacilitiesForApi($q, 'ic.facility_id');
        $row = $q->get()->getRowArray();

        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Not found']);
        }

        $items = $this->db->tableExists('inspection_items')
            ? $this->db->table('inspection_items')->where('checklist_id', $id)->orderBy('id')->get()->getResultArray()
            : [];

        return $this->response->setJSON(['status' => true, 'data' => $row, 'items' => $items]);
    }

    /**
     * GET /api/v1/inspections/units — unit move-in/out checklists.
     */
    public function unitList()
    {
        if (! $this->db->tableExists('unit_checklists')) {
            return $this->response->setJSON(['status' => true, 'data' => [], 'message' => 'Run compliance_inspections_patch.sql']);
        }

        $facilityId = (int) ($this->request->getGet('facility_id') ?? 0);
        $type       = trim((string) ($this->request->getGet('type') ?? ''));
        $frequency  = trim((string) ($this->request->getGet('frequency') ?? ''));

        $q = $this->db->table('unit_checklists uc')
            ->select('uc.*, u.unit_number, u.facility_id, f.name AS facility_name')
            ->join('units u', 'u.id = uc.unit_id', 'left')
            ->join('facilities f', 'f.id = u.facility_id', 'left');
        $this->scopeFacilitiesForApi($q, 'u.facility_id');

        if ($facilityId > 0) {
            $q->where('u.facility_id', $facilityId);
        }
        if ($type !== '') {
            $q->where('uc.type', $type);
        }
        if ($frequency !== '' && $this->db->fieldExists('frequency', 'unit_checklists')) {
            $q->where('uc.frequency', $frequency);
        }

        $rows = $q->orderBy('uc.created_at', 'DESC')->limit(200)->get()->getResultArray();

        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }

    /**
     * GET /api/v1/inspections/units/(:num)
     */
    public function unitDetail(int $id)
    {
        if (! $this->db->tableExists('unit_checklists')) {
            return $this->response->setStatusCode(503)->setJSON(['status' => false, 'message' => 'Unit checklist module not installed']);
        }

        $q = $this->db->table('unit_checklists uc')
            ->select('uc.*, u.unit_number, u.facility_id, f.name AS facility_name')
            ->join('units u', 'u.id = uc.unit_id', 'left')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('uc.id', $id);
        $this->scopeFacilitiesForApi($q, 'u.facility_id');
        $row = $q->get()->getRowArray();

        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Not found']);
        }

        return $this->response->setJSON(['status' => true, 'data' => $row]);
    }
}
