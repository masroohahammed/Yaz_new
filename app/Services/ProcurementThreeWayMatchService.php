<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Match PO amount vs GRN received value vs vendor bill (AP).
 */
class ProcurementThreeWayMatchService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return array<string, mixed> */
    public function analyze(int $poId): array
    {
        $po = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->where('po.id', $poId)
            ->get()
            ->getRowArray();
        if (! $po) {
            throw new \RuntimeException('Purchase order not found.');
        }

        $poAmount = (float) ($po['total_amount'] ?? 0);

        $grnAmount = 0.0;
        $grnId     = null;
        $grn       = $this->db->table('grn')->where('po_id', $poId)->orderBy('id', 'DESC')->get()->getRowArray();
        if ($grn) {
            $grnId = (int) $grn['id'];
            $items = $this->db->table('grn_items gi')
                ->select('gi.received_qty, pr.quantity, i.unit_cost')
                ->join('purchase_requests pr', 'pr.id = gi.pr_id', 'left')
                ->join('inventory_items i', 'i.id = gi.item_id', 'left')
                ->where('gi.grn_id', $grnId)
                ->get()
                ->getResultArray();
            foreach ($items as $row) {
                $qty  = (float) ($row['received_qty'] ?? 0);
                $cost = (float) ($row['unit_cost'] ?? 0);
                $grnAmount += $qty * $cost;
            }
            if ($grnAmount <= 0) {
                $grnAmount = $poAmount;
            }
        }

        $billAmount = 0.0;
        $billId     = null;
        if ($this->db->tableExists('finance_vendor_bills')) {
            $bill = $this->db->table('finance_vendor_bills')->where('purchase_order_id', $poId)->orderBy('id', 'DESC')->get()->getRowArray();
            if ($bill) {
                $billId     = (int) $bill['id'];
                $billAmount = (float) $bill['total'];
            }
        }

        $tolerance = max(1.0, $poAmount * 0.02);
        $variance  = round($billAmount - $grnAmount, 2);
        $matched   = $billId && $grnId && abs($poAmount - $grnAmount) <= $tolerance && abs($grnAmount - $billAmount) <= $tolerance;

        return [
            'po'           => $po,
            'grn'          => $grn,
            'bill'         => $bill ?? null,
            'po_amount'    => $poAmount,
            'grn_amount'   => round($grnAmount, 2),
            'bill_amount'  => round($billAmount, 2),
            'variance'     => $variance,
            'match_status' => $matched ? 'matched' : ($billId || $grnId ? 'exception' : 'pending'),
            'grn_id'       => $grnId,
            'bill_id'      => $billId,
        ];
    }

    public function recordMatch(int $poId, int $userId, ?string $notes = null): int
    {
        $a = $this->analyze($poId);
        if (! $this->db->tableExists('procurement_three_way_matches')) {
            return 0;
        }
        $data = [
            'po_id'          => $poId,
            'grn_id'         => $a['grn_id'],
            'vendor_bill_id'=> $a['bill_id'],
            'po_amount'      => $a['po_amount'],
            'grn_amount'     => $a['grn_amount'],
            'bill_amount'    => $a['bill_amount'],
            'variance'       => $a['variance'],
            'match_status'   => $a['match_status'],
            'notes'          => $notes,
            'matched_by'     => $userId,
            'matched_at'     => date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
        ];
        $existing = $this->db->table('procurement_three_way_matches')->where('po_id', $poId)->get()->getRowArray();
        if ($existing) {
            $this->db->table('procurement_three_way_matches')->where('id', $existing['id'])->update($data);

            return (int) $existing['id'];
        }
        $this->db->table('procurement_three_way_matches')->insert($data);

        return (int) $this->db->insertID();
    }

    /**
     * Whether AP payment is allowed for a PO-linked vendor bill.
     *
     * @return array{allowed: bool, reason: string, match_status: string}
     */
    public function paymentAllowed(int $poId): array
    {
        $required = ($this->getSetting('procurement_match_required') ?? '1') === '1';
        if (! $required) {
            return ['allowed' => true, 'reason' => '', 'match_status' => 'optional'];
        }

        $a = $this->analyze($poId);
        $status = (string) ($a['match_status'] ?? 'pending');

        if ($status === 'matched') {
            return ['allowed' => true, 'reason' => '', 'match_status' => $status];
        }

        if ($status === 'exception') {
            return [
                'allowed'      => false,
                'reason'       => '3-way match exception — resolve PO/GRN/bill variance before payment.',
                'match_status' => $status,
            ];
        }

        return [
            'allowed'      => false,
            'reason'       => '3-way match pending — complete GRN and vendor bill, then record match.',
            'match_status' => $status,
        ];
    }

    /** Finance override: mark exception as approved for payment. */
    public function approveException(int $poId, int $userId, ?string $notes = null): void
    {
        if (! $this->db->tableExists('procurement_three_way_matches')) {
            return;
        }
        $this->recordMatch($poId, $userId, $notes ?? 'Finance override — exception approved for payment');
        $this->db->table('procurement_three_way_matches')
            ->where('po_id', $poId)
            ->update([
                'match_status' => 'matched',
                'notes'        => trim(($notes ?? '') . ' [finance_approved]'),
                'matched_at'   => date('Y-m-d H:i:s'),
                'matched_by'   => $userId,
            ]);
    }

    private function getSetting(string $key): ?string
    {
        if (! $this->db->tableExists('system_settings')) {
            return null;
        }
        $row = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRowArray();

        return $row['setting_value'] ?? null;
    }
}
