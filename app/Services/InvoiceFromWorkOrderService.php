<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Auto-generate draft invoice from completed work order (labor + materials + VAT).
 */
class InvoiceFromWorkOrderService
{
    public function __construct(
        private BaseConnection $db,
        private array $settings = []
    ) {
    }

    /**
     * @return array{invoice_id: int, invoice_number: string, subtotal: float, total: float}
     */
    public function createDraftFromWorkOrder(int $woId, int $createdBy): array
    {
        $wo = $this->db->table('work_orders w')
            ->select('w.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = w.facility_id', 'left')
            ->where('w.id', $woId)
            ->get()
            ->getRowArray();

        if (!$wo) {
            throw new \RuntimeException('Work order not found.');
        }

        if (!empty($wo['invoice_id'])) {
            $existing = $this->db->table('invoices')->where('id', $wo['invoice_id'])->get()->getRowArray();
            if ($existing) {
                return [
                    'invoice_id'     => (int) $existing['id'],
                    'invoice_number' => $existing['invoice_number'],
                    'subtotal'       => (float) $existing['subtotal'],
                    'total'          => (float) $existing['total'],
                ];
            }
        }

        $laborTotal = (float) ($this->db->table('wo_labor')
            ->selectSum('labor_cost', 't')
            ->where('wo_id', $woId)
            ->get()
            ->getRowArray()['t'] ?? 0);

        $materialTotal = (float) ($this->db->table('wo_materials')
            ->selectSum('total_cost', 't')
            ->where('wo_id', $woId)
            ->get()
            ->getRowArray()['t'] ?? 0);

        $subtotal = round($laborTotal + $materialTotal, 2);
        if ($subtotal <= 0 && !empty($wo['actual_cost'])) {
            $subtotal = round((float) $wo['actual_cost'], 2);
        }
        if ($subtotal <= 0 && !empty($wo['estimated_cost'])) {
            $subtotal = round((float) $wo['estimated_cost'], 2);
        }
        if ($subtotal <= 0) {
            throw new \RuntimeException('Cannot invoice work order with zero cost. Add labor or materials first.');
        }

        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float) ($this->settings['vat_rate'] ?? 5) : 0;
        $vatAmount  = $vatEnabled ? round($subtotal * $vatRate / 100, 2) : 0;
        $total      = $subtotal + $vatAmount;

        $contract = $this->db->table('contracts')
            ->where('facility_id', $wo['facility_id'])
            ->where('status', 'active')
            ->orderBy('end_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $invNumber = $this->nextInvoiceNumber();
        $issueDate = date('Y-m-d');
        $dueDate   = date('Y-m-d', strtotime('+30 days'));

        $insert = [
            'invoice_number' => $invNumber,
            'facility_id'    => (int) $wo['facility_id'],
            'contract_id'    => $contract['id'] ?? null,
            'work_order_id'  => $woId,
            'invoice_type'   => 'work_order',
            'issue_date'     => $issueDate,
            'due_date'       => $dueDate,
            'subtotal'       => $subtotal,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmount,
            'total'          => $total,
            'currency'       => $this->settings['currency'] ?? 'QAR',
            'status'         => 'draft',
            'notes'          => 'Auto-generated from ' . $wo['wo_number'] . ' — Labor: ' . number_format($laborTotal, 2) . ', Materials: ' . number_format($materialTotal, 2),
            'created_by'     => $createdBy,
        ];
        if ($this->db->fieldExists('company_id', 'invoices')) {
            $fac = $this->db->table('facilities')->select('company_id')->where('id', $wo['facility_id'])->get()->getRowArray();
            $insert['company_id'] = $fac['company_id'] ?? null;
        }
        $this->db->table('invoices')->insert($insert);

        $invoiceId = (int) $this->db->insertID();

        if ($this->db->fieldExists('invoice_id', 'work_orders')) {
            $this->db->table('work_orders')->where('id', $woId)->update(['invoice_id' => $invoiceId]);
        }

        $integration = new \App\Services\Finance\FinanceIntegrationService($this->db);
        $integration->syncInvoiceItemsFromWorkOrder($invoiceId, $woId);
        $integration->log('workorders', 'wo_to_invoice', 'work_order', $woId, 'invoice', $invoiceId, [
            'labor' => $laborTotal, 'materials' => $materialTotal,
        ]);

        return [
            'invoice_id'     => $invoiceId,
            'invoice_number' => $invNumber,
            'subtotal'       => $subtotal,
            'total'          => $total,
        ];
    }

    private function nextInvoiceNumber(): string
    {
        $year = date('Y');
        $like = 'INV-' . $year . '-';
        $last = $this->db->table('invoices')
            ->like('invoice_number', $like, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        $seq = 1;
        if ($last && ! empty($last['invoice_number'])) {
            helper('fm');
            $seq = fm_sequence_from_code($last['invoice_number']) + 1;
        }

        return $like . sprintf('%04d', $seq);
    }
}
