<?php

namespace App\Controllers\Api\V1;

use App\Services\ApiOperationsService;
use App\Services\FinanceTotalsService;

class Invoices extends BaseApiController
{
    public function index()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $ops = new ApiOperationsService($this->db);
        $q   = $this->db->table('invoices i')
            ->select('i.id, i.invoice_number, i.status, i.subtotal, i.vat_amount, i.total, i.issue_date, i.due_date, i.facility_id, f.name as facility_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left');
        $ops->applyFacilityScope($q, 'i.facility_id', $user);
        $invoices = $q->orderBy('i.created_at', 'DESC')->limit(50)->get()->getResultArray();

        $totals = (new FinanceTotalsService($this->db))->invoiceTotals($ops->facilityIds($user));

        return $this->response->setJSON([
            'status' => true,
            'data'   => $invoices,
            'count'  => count($invoices),
            'totals' => $totals,
        ]);
    }

    public function create()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $d = $this->request->getJSON(true) ?? [];
        if (empty($d['facility_id']) || empty($d['subtotal'])) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'facility_id and subtotal required']);
        }

        $ops        = new ApiOperationsService($this->db);
        $facilityId = (int) $d['facility_id'];
        if (! $ops->canAccessFacility($user, $facilityId)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Facility not in your company']);
        }

        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float) ($this->settings['vat_rate'] ?? 5) : 0;
        $subtotal   = (float) $d['subtotal'];
        $vatAmt     = $vatEnabled ? round($subtotal * $vatRate / 100, 2) : 0;
        $total      = $subtotal + $vatAmt;
        $invNum     = $this->generateNumber('INV', 'invoices', 'invoice_number');

        $this->db->transStart();
        $this->db->table('invoices')->insert([
            'invoice_number' => $invNum,
            'facility_id'    => $facilityId,
            'subtotal'       => $subtotal,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmt,
            'total'          => $total,
            'currency'       => $this->settings['currency'] ?? 'QAR',
            'issue_date'     => $d['issue_date'] ?? date('Y-m-d'),
            'due_date'       => $d['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'status'         => 'draft',
            'created_by'     => (int) $user['id'],
        ]);
        $this->db->transComplete();

        return $this->response->setStatusCode(201)->setJSON(['status' => true, 'invoice_number' => $invNum, 'total' => $total]);
    }
}
