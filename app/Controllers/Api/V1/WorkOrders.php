<?php

namespace App\Controllers\Api\V1;

use App\Services\ApiOperationsService;

class WorkOrders extends BaseApiController
{
    private function ops(): ApiOperationsService
    {
        return new ApiOperationsService($this->db);
    }

    public function index()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $q = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.status, w.priority, w.type, w.facility_id, w.assigned_to, w.estimated_cost, w.actual_cost, w.created_at, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->join('users u', 'u.id=w.assigned_to', 'left');
        $this->ops()->applyFacilityScope($q, 'w.facility_id', $user);
        $wos = $q->orderBy('w.created_at', 'DESC')->limit(50)->get()->getResultArray();

        return $this->response->setJSON(['status' => true, 'data' => $wos, 'count' => count($wos)]);
    }

    public function show(int $id)
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $q = $this->db->table('work_orders w')
            ->select('w.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->join('users u', 'u.id=w.assigned_to', 'left')
            ->where('w.id', $id);
        $this->ops()->applyFacilityScope($q, 'w.facility_id', $user);
        $wo = $q->get()->getRowArray();
        if (! $wo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Work order not found']);
        }

        return $this->response->setJSON(['status' => true, 'data' => $wo]);
    }

    public function create()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $d = $this->request->getJSON(true) ?? [];
        if (empty($d['title']) || empty($d['facility_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'title and facility_id required']);
        }
        $facilityId = (int) $d['facility_id'];
        if (! $this->ops()->canAccessFacility($user, $facilityId)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Facility not in your company']);
        }

        $priority = $d['priority'] ?? 'medium';
        $slaRule  = $this->db->table('sla_rules')->where('priority', $priority)->get()->getRowArray();
        $slaDue   = $slaRule ? date('Y-m-d H:i:s', strtotime('+' . $slaRule['resolution_hours'] . ' hours')) : null;
        $woNum    = $this->generateNumber('WO', 'work_orders', 'wo_number');
        $uid      = (int) $user['id'];

        $this->db->transStart();
        $this->db->table('work_orders')->insert([
            'wo_number'      => $woNum,
            'facility_id'    => $facilityId,
            'title'          => esc($d['title']),
            'description'    => esc($d['description'] ?? ''),
            'type'           => $d['type'] ?? 'corrective',
            'priority'       => $priority,
            'status'         => 'new',
            'created_by'     => $uid,
            'sla_due'        => $slaDue,
            'estimated_cost' => $d['estimated_cost'] ?? null,
        ]);
        $id = (int) $this->db->insertID();
        $this->db->transComplete();

        return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => 'Work order created', 'wo_number' => $woNum, 'id' => $id]);
    }

    public function update(int $id)
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $existing = $this->scopedWorkOrder($id, $user);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Work order not found']);
        }

        $d = $this->request->getJSON(true) ?? [];
        if ($d === []) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'No data provided']);
        }
        $allowed = ['title', 'description', 'priority', 'status', 'assigned_to', 'actual_cost', 'completion_notes'];
        $update  = array_intersect_key($d, array_flip($allowed));
        if (isset($update['status'])) {
            $ok = ['new', 'assigned', 'in_progress', 'completed', 'closed', 'cancelled'];
            if (! in_array((string) $update['status'], $ok, true)) {
                return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Invalid work order status']);
            }
            if ($update['status'] === 'completed') {
                $update['completed_at'] = date('Y-m-d H:i:s');
            }
        }

        $this->db->table('work_orders')->where('id', $id)->update($update);

        return $this->response->setJSON(['status' => true, 'message' => 'Work order updated']);
    }

    public function delete(int $id)
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (! $this->scopedWorkOrder($id, $user)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Work order not found']);
        }

        $this->db->table('work_orders')->where('id', $id)->update(['status' => 'cancelled']);

        return $this->response->setJSON(['status' => true, 'message' => 'Work order cancelled']);
    }

    /** @param array<string, mixed> $user */
    private function scopedWorkOrder(int $id, array $user): ?array
    {
        $q = $this->db->table('work_orders')->where('id', $id);
        $this->ops()->applyFacilityScope($q, 'facility_id', $user);

        return $q->get()->getRowArray() ?: null;
    }
}
