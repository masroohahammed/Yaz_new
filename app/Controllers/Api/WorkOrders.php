<?php
namespace App\Controllers\Api;
use App\Controllers\BaseController;

class WorkOrders extends BaseController
{
    public function index()
    {
        $q = $this->db->table('work_orders w')
            ->select('w.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->join('users u', 'u.id=w.assigned_to', 'left');
        $this->scopeFacilities($q, 'w.facility_id');
        $wos = $q->orderBy('w.created_at', 'DESC')->limit(50)->get()->getResultArray();

        return $this->response->setJSON(['status' => true, 'data' => $wos, 'count' => count($wos)]);
    }

    public function show(int $id)
    {
        $q = $this->db->table('work_orders w')
            ->select('w.*, f.name as facility_name, u.name as assigned_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->join('users u', 'u.id=w.assigned_to', 'left')
            ->where('w.id', $id);
        $this->scopeFacilities($q, 'w.facility_id');
        $wo = $q->get()->getRowArray();
        if (!$wo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Work order not found']);
        }

        return $this->response->setJSON(['status' => true, 'data' => $wo]);
    }

    public function create()
    {
        $d = $this->request->getJSON(true) ?? [];
        if (empty($d['title'])||empty($d['facility_id'])) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'title and facility_id required']);
        $priority = $d['priority'] ?? 'medium';
        $slaRule  = $this->db->table('sla_rules')->where('priority',$priority)->get()->getRowArray();
        $slaDue   = $slaRule ? date('Y-m-d H:i:s',strtotime("+{$slaRule['resolution_hours']} hours")) : null;
        $woNum    = $this->generateNumber('WO','work_orders','wo_number');
        $uid      = $this->request->jwt_user_id ?? 1;
        $this->db->table('work_orders')->insert(['wo_number'=>$woNum,'facility_id'=>$d['facility_id'],'title'=>esc($d['title']),'description'=>esc($d['description']??''),'type'=>$d['type']??'corrective','priority'=>$priority,'status'=>'new','created_by'=>$uid,'sla_due'=>$slaDue,'estimated_cost'=>$d['estimated_cost']??null]);
        return $this->response->setStatusCode(201)->setJSON(['status'=>true,'message'=>'Work order created','wo_number'=>$woNum,'id'=>$this->db->insertID()]);
    }

    public function update(int $id)
    {
        $d = $this->request->getJSON(true) ?? [];
        if (empty($d)) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'No data provided']);
        $allowed = ['title','description','priority','status','assigned_to','actual_cost','completion_notes'];
        $update  = array_intersect_key($d, array_flip($allowed));
        if (isset($update['status'])&&$update['status']==='completed') $update['completed_at'] = date('Y-m-d H:i:s');
        $this->db->table('work_orders')->where('id',$id)->update($update);
        return $this->response->setJSON(['status'=>true,'message'=>'Work order updated']);
    }

    public function delete(int $id)
    {
        $this->db->table('work_orders')->where('id',$id)->update(['status'=>'cancelled']);
        return $this->response->setJSON(['status'=>true,'message'=>'Work order cancelled']);
    }
}
