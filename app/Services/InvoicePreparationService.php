<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Build invoice lines from WO job card data (internal cost vs client selling price).
 */
class InvoicePreparationService
{
    public function __construct(
        private BaseConnection $db,
        private array $settings = []
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildLinesFromWorkOrder(int $woId): array
    {
        $wo     = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        $markup = 1 + ((float) ($this->settings['invoice_markup_percent'] ?? 0) / 100);
        $lines  = [];
        $sort   = 0;

        foreach ($this->db->table('wo_labor')->where('wo_id', $woId)->get()->getResultArray() as $l) {
            $cost = (float) ($l['labor_cost'] ?? 0);
            if ($cost <= 0) {
                continue;
            }
            $qty   = (float) ($l['hours_worked'] ?? 1);
            $sell  = round($cost * $markup, 2);
            $lines[] = $this->lineRow('labor', 'Labor — ' . ($l['notes'] ?? 'Technician labor'), $qty, $cost / max($qty, 0.01), $sell / max($qty, 0.01), $sell, $cost, $woId, $sort++);
        }

        foreach ($this->db->table('wo_materials')->where('wo_id', $woId)->get()->getResultArray() as $m) {
            $cost = (float) ($m['total_cost'] ?? 0);
            if ($cost <= 0) {
                continue;
            }
            $qty  = (float) ($m['quantity'] ?? 1);
            $sell = round($cost * $markup, 2);
            $lines[] = $this->lineRow(
                'material',
                'Material — ' . ($m['item_name'] ?? 'Parts'),
                $qty,
                (float) ($m['unit_cost'] ?? ($cost / max($qty, 0.01))),
                $sell / max($qty, 0.01),
                $sell,
                $cost,
                $woId,
                $sort++
            );
        }

        if ($lines === [] && $wo) {
            $cost = (float) ($wo['actual_cost'] ?? $wo['estimated_cost'] ?? 0);
            if ($cost > 0) {
                $sell = round($cost * $markup, 2);
                $lines[] = $this->lineRow(
                    'service',
                    'Facility management services — WO #' . ($wo['wo_number'] ?? $woId),
                    1,
                    $cost,
                    $sell,
                    $sell,
                    $cost,
                    $woId,
                    0
                );
            }
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $postedLines  from form (description, qty, unit_price, unit_cost_internal)
     * @return array{invoice_id: int, invoice_number: string, total: float, profit: float}
     */
    public function createInvoiceFromPreparation(int $woId, array $postedLines, int $userId): array
    {
        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (! $wo) {
            throw new \RuntimeException('Work order not found.');
        }
        if (! empty($wo['invoice_id'])) {
            $existing = $this->db->table('invoices')->where('id', $wo['invoice_id'])->get()->getRowArray();
            if ($existing) {
                return [
                    'invoice_id'     => (int) $existing['id'],
                    'invoice_number' => $existing['invoice_number'],
                    'total'          => (float) $existing['total'],
                    'profit'         => 0,
                ];
            }
        }

        $subtotal = 0.0;
        $costSum  = 0.0;
        $normalized = [];
        foreach ($postedLines as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $qty      = max(0.01, (float) ($row['quantity'] ?? 1));
            $unitSell = round((float) ($row['unit_price'] ?? 0), 2);
            $amount   = round($qty * $unitSell, 2);
            $unitCost = round((float) ($row['unit_cost_internal'] ?? 0), 2);
            $costLine = round($qty * $unitCost, 2);
            $subtotal += $amount;
            $costSum  += $costLine;
            $normalized[] = [
                'line_type'          => in_array($row['line_type'] ?? '', ['labor', 'material', 'service', 'other'], true) ? $row['line_type'] : 'service',
                'description'        => $desc,
                'quantity'           => $qty,
                'unit_price'         => $unitSell,
                'amount'             => $amount,
                'unit_cost_internal' => $unitCost,
                'work_order_id'      => $woId,
                'sort_order'         => (int) ($row['sort_order'] ?? $i),
            ];
        }

        if ($subtotal <= 0) {
            throw new \RuntimeException('Add at least one invoice line with a selling price.');
        }

        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float) ($this->settings['vat_rate'] ?? 5) : 0;
        $vatAmount  = $vatEnabled ? round($subtotal * $vatRate / 100, 2) : 0;
        $total      = $subtotal + $vatAmount;

        $invNumber = $this->nextInvoiceNumber();
        $contract  = null;
        if (! empty($wo['facility_id'])) {
            $contract = $this->db->table('contracts')
                ->where('facility_id', $wo['facility_id'])
                ->where('status', 'active')
                ->orderBy('end_date', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        $insert = [
            'invoice_number' => $invNumber,
            'facility_id'    => ! empty($wo['facility_id']) ? (int) $wo['facility_id'] : null,
            'contract_id'    => $contract['id'] ?? null,
            'work_order_id'  => $woId,
            'invoice_type'   => 'work_order',
            'issue_date'     => date('Y-m-d'),
            'due_date'       => date('Y-m-d', strtotime('+30 days')),
            'subtotal'       => $subtotal,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmount,
            'total'          => $total,
            'currency'       => $this->settings['currency'] ?? 'QAR',
            'status'         => 'draft',
            'notes'          => 'Prepared from WO ' . ($wo['wo_number'] ?? $woId) . ' after QC (selling prices set by accounts).',
            'created_by'     => $userId,
        ];
        if ($this->db->fieldExists('company_id', 'invoices') && ! empty($wo['facility_id'])) {
            $fac = $this->db->table('facilities')->select('company_id')->where('id', $wo['facility_id'])->get()->getRowArray();
            $insert['company_id'] = $fac['company_id'] ?? null;
        }
        $this->db->table('invoices')->insert($insert);
        $invoiceId = (int) $this->db->insertID();

        if ($this->db->tableExists('invoice_items')) {
            $this->db->table('invoice_items')->where('invoice_id', $invoiceId)->delete();
            foreach ($normalized as $line) {
                $insert = [
                    'invoice_id'    => $invoiceId,
                    'line_type'     => $line['line_type'],
                    'description'   => $line['description'],
                    'quantity'      => $line['quantity'],
                    'unit_price'    => $line['unit_price'],
                    'amount'        => $line['amount'],
                    'work_order_id' => $woId,
                    'sort_order'    => $line['sort_order'],
                ];
                if ($this->db->fieldExists('unit_cost_internal', 'invoice_items')) {
                    $insert['unit_cost_internal'] = $line['unit_cost_internal'];
                }
                $this->db->table('invoice_items')->insert($insert);
            }
        }

        $this->recordCosting($woId, $invoiceId, $costSum, $subtotal, $total - $costSum);

        $this->db->table('work_orders')->where('id', $woId)->update([
            'invoice_id'     => $invoiceId,
            'workflow_stage' => 'wo_closed',
            'status'         => 'closed',
        ]);

        if ($this->db->tableExists('finance_integration_log')) {
            (new \App\Services\Finance\FinanceIntegrationService($this->db))->log(
                'workorders',
                'wo_invoice_prepared',
                'work_order',
                $woId,
                'invoice',
                $invoiceId,
                ['subtotal' => $subtotal, 'cost' => $costSum]
            );
        }

        return [
            'invoice_id'     => $invoiceId,
            'invoice_number' => $invNumber,
            'total'          => $total,
            'profit'         => round($subtotal - $costSum, 2),
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

    /** @return array<string, mixed> */
    private function lineRow(
        string $type,
        string $desc,
        float $qty,
        float $unitCost,
        float $unitSell,
        float $amountSell,
        float $lineCost,
        int $woId,
        int $sort
    ): array {
        return [
            'line_type'          => $type,
            'description'        => $desc,
            'quantity'           => $qty,
            'unit_cost_internal' => round($unitCost, 2),
            'unit_price'         => round($unitSell, 2),
            'amount'             => round($amountSell, 2),
            'line_cost'          => round($lineCost, 2),
            'profit'             => round($amountSell - $lineCost, 2),
            'work_order_id'      => $woId,
            'sort_order'         => $sort,
        ];
    }

    private function recordCosting(int $woId, int $invoiceId, float $cost, float $revenue, float $profit): void
    {
        if (! $this->db->tableExists('maintenance_costing')) {
            return;
        }
        $exists = $this->db->table('maintenance_costing')->where('wo_id', $woId)->countAllResults();
        $row    = [
            'wo_id'        => $woId,
            'invoice_id'   => $invoiceId,
            'labor_cost'   => 0,
            'material_cost'=> 0,
            'total_cost'   => $cost,
            'revenue'      => $revenue,
            'profit'       => $profit,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        if ($exists) {
            $this->db->table('maintenance_costing')->where('wo_id', $woId)->update($row);
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('maintenance_costing')->insert($row);
        }
    }
}
