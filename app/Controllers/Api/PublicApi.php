<?php
namespace App\Controllers\Api;
use App\Controllers\BaseController;

class PublicApi extends BaseController
{
    public function requestMaintenance()
    {
        $d = $this->request->getJSON(true) ?? [];
        if (empty($d['requester_name'])||empty($d['description'])) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'requester_name and description required']);
        $year = date('Y');
        $last = $this->db->table('maintenance_requests')->like('ticket_number','MR-'.$year.'-','after')->orderBy('id','DESC')->limit(1)->get()->getRowArray();
        $seq  = $last ? (int)explode('-',$last['ticket_number'])[2]+1 : 1;
        $ticket = 'MR-'.$year.'-'.sprintf('%04d',$seq);
        $this->db->table('maintenance_requests')->insert(['ticket_number'=>$ticket,'facility_id'=>$d['facility_id']??null,'requester_name'=>esc($d['requester_name']),'requester_email'=>esc($d['requester_email']??''),'requester_phone'=>esc($d['requester_phone']??''),'category'=>esc($d['category']??''),'description'=>esc($d['description']),'priority'=>$d['priority']??'medium','status'=>'pending']);
        return $this->response->setStatusCode(201)->setJSON(['status'=>true,'ticket_number'=>$ticket,'message'=>'Request submitted successfully']);
    }

    public function trackRequest(string $ticket)
    {
        $req = $this->db->table('maintenance_requests')->where('ticket_number',strtoupper($ticket))->get()->getRowArray();
        if (!$req) return $this->response->setStatusCode(404)->setJSON(['status'=>false,'message'=>'Ticket not found']);
        return $this->response->setJSON(['status'=>true,'data'=>['ticket_number'=>$req['ticket_number'],'status'=>$req['status'],'priority'=>$req['priority'],'submitted_at'=>$req['created_at']]]);
    }
}
