<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

class FinanceIntegrationService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function log(
        string $module,
        string $event,
        string $sourceType,
        int $sourceId,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $payload = null
    ): void {
        if (! $this->db->tableExists('finance_integration_log')) {
            return;
        }
        $this->db->table('finance_integration_log')->insert([
            'module'      => $module,
            'event'       => $event,
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'payload'     => $payload ? json_encode($payload) : null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** Create AP vendor bill from approved purchase order. */
    public function createVendorBillFromPo(int $poId, int $userId): ?int
    {
        if (! $this->db->tableExists('finance_vendor_bills')) {
            return null;
        }

        $existing = $this->db->table('finance_vendor_bills')->where('purchase_order_id', $poId)->get()->getRowArray();
        if ($existing) {
            return (int) $existing['id'];
        }

        $po = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->where('po.id', $poId)
            ->get()->getRowArray();
        if (! $po) {
            return null;
        }

        $vatEnabled = ($this->getSetting('vat_enabled') ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float) ($this->getSetting('vat_rate') ?? 5) : 0;
        $subtotal   = (float) ($po['total_amount'] ?? 0);
        $vatAmt     = round($subtotal * $vatRate / 100, 2);
        $n          = (int) $this->db->table('finance_vendor_bills')->countAllResults() + 1;
        $billNo     = 'VB-' . date('Y') . '-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);

        $this->db->table('finance_vendor_bills')->insert([
            'bill_number'        => $billNo,
            'vendor_id'          => (int) $po['vendor_id'],
            'purchase_order_id'  => $poId,
            'bill_date'          => date('Y-m-d'),
            'due_date'           => date('Y-m-d', strtotime('+30 days')),
            'subtotal'           => $subtotal,
            'vat_rate'           => $vatRate,
            'vat_amount'         => $vatAmt,
            'total'              => $subtotal + $vatAmt,
            'status'             => 'pending',
            'created_by'         => $userId,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);
        $billId = (int) $this->db->insertID();

        $this->log('procurement', 'po_to_vendor_bill', 'purchase_order', $poId, 'vendor_bill', $billId);
        (new GlPostingService($this->db))->postVendorBill($billId, $subtotal + $vatAmt, $userId);

        return $billId;
    }

    /** Sync invoice line items from work order labor + materials. */
    public function syncInvoiceItemsFromWorkOrder(int $invoiceId, int $workOrderId): void
    {
        if (! $this->db->tableExists('invoice_items')) {
            return;
        }

        $this->db->table('invoice_items')->where('invoice_id', $invoiceId)->delete();
        $sort  = 0;
        $batch = [];

        $labors = $this->db->table('wo_labor')->where('wo_id', $workOrderId)->get()->getResultArray();
        foreach ($labors as $l) {
            $amt = (float) ($l['labor_cost'] ?? 0);
            if ($amt <= 0) {
                continue;
            }
            $batch[] = [
                'invoice_id'    => $invoiceId,
                'line_type'     => 'labor',
                'description'   => 'Labor — ' . ($l['notes'] ?? 'Technician labor'),
                'quantity'      => (float) ($l['hours_worked'] ?? 1),
                'unit_price'    => (float) ($l['hourly_rate'] ?? $amt),
                'amount'        => $amt,
                'work_order_id' => $workOrderId,
                'sort_order'    => $sort++,
            ];
        }

        $materials = $this->db->table('wo_materials')->where('wo_id', $workOrderId)->get()->getResultArray();
        foreach ($materials as $m) {
            $amt = (float) ($m['total_cost'] ?? 0);
            if ($amt <= 0) {
                continue;
            }
            $batch[] = [
                'invoice_id'    => $invoiceId,
                'line_type'     => 'material',
                'description'   => 'Material — ' . ($m['item_name'] ?? 'Parts'),
                'quantity'      => (float) ($m['quantity'] ?? 1),
                'unit_price'    => (float) ($m['unit_cost'] ?? $amt),
                'amount'        => $amt,
                'work_order_id' => $workOrderId,
                'sort_order'    => $sort++,
            ];
        }

        if ($batch !== []) {
            $this->db->table('invoice_items')->insertBatch($batch);
        }

        if ($sort === 0) {
            $wo = $this->db->table('work_orders')->where('id', $workOrderId)->get()->getRowArray();
            $amt = (float) ($wo['actual_cost'] ?? $wo['estimated_cost'] ?? 0);
            if ($amt > 0) {
                $this->db->table('invoice_items')->insert([
                    'invoice_id'    => $invoiceId,
                    'line_type'     => 'service',
                    'description'   => 'Facility management services — WO #' . ($wo['wo_number'] ?? $workOrderId),
                    'quantity'      => 1,
                    'unit_price'    => $amt,
                    'amount'        => $amt,
                    'work_order_id' => $workOrderId,
                    'sort_order'    => 0,
                ]);
            }
        }
    }

    private function getSetting(string $key): ?string
    {
        $row = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRowArray();

        return $row['setting_value'] ?? null;
    }
}
