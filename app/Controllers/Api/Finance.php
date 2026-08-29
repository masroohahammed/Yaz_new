<?php
namespace App\Controllers\Api;
use App\Controllers\BaseController;

class Finance extends BaseController
{
    public function invoices()
    {
        $q = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left');
        $this->scopeFacilities($q, 'i.facility_id');
        $invoices = $q->orderBy('i.created_at', 'DESC')->limit(50)->get()->getResultArray();
        return $this->response->setJSON(['status'=>true,'data'=>$invoices,'count'=>count($invoices)]);
    }

    public function createInvoice()
    {
        $d = $this->request->getJSON(true) ?? [];
        if (empty($d['facility_id'])||empty($d['subtotal'])) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'facility_id and subtotal required']);
        $vatEnabled = ($this->settings['vat_enabled']??'0')==='1';
        $vatRate    = $vatEnabled ? (float)($this->settings['vat_rate']??5) : 0;
        $subtotal   = (float)$d['subtotal'];
        $vatAmt     = $vatEnabled ? round($subtotal*$vatRate/100,2) : 0;
        $total      = $subtotal + $vatAmt;
        $uid        = $this->request->jwt_user_id ?? 1;
        $invNum     = $this->generateNumber('INV','invoices','invoice_number');
        $this->db->table('invoices')->insert(['invoice_number'=>$invNum,'facility_id'=>$d['facility_id'],'subtotal'=>$subtotal,'vat_rate'=>$vatRate,'vat_amount'=>$vatAmt,'total'=>$total,'currency'=>$this->settings['currency']??'QAR','issue_date'=>$d['issue_date']??date('Y-m-d'),'due_date'=>$d['due_date']??date('Y-m-d',strtotime('+30 days')),'status'=>'draft','created_by'=>$uid]);
        return $this->response->setStatusCode(201)->setJSON(['status'=>true,'invoice_number'=>$invNum,'total'=>$total]);
    }
}
