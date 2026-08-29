<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Internal financial reports — estimated vs actual, P&amp;L, material variance, WO profitability.
 */
class FinancialReportService
{
  public function __construct(
    private BaseConnection $db,
    private FinancialCalculationService $calc = new FinancialCalculationService(),
  ) {
  }

  /** @return list<array<string, mixed>> */
  public function estimatedVsActual(string $from, string $to, ?int $facilityId = null): array
  {
    $q = $this->db->table('estimations e')
      ->select('e.est_number, e.title, f.name AS facility_name, e.selling_subtotal, e.estimated_subtotal, e.actual_subtotal, e.actual_total_cost, e.cost_variance, e.total_profit, e.total_margin, e.status')
      ->join('facilities f', 'f.id = e.facility_id', 'left')
      ->where('DATE(e.created_at) >=', $from)
      ->where('DATE(e.created_at) <=', $to);

    if ($facilityId) {
      $q->where('e.facility_id', $facilityId);
    }

    return $q->orderBy('e.created_at', 'DESC')->get()->getResultArray();
  }

  /** @return list<array<string, mixed>> */
  public function workOrderProfitability(string $from, string $to, ?int $facilityId = null): array
  {
    $q = $this->db->table('work_orders w')
      ->select('w.wo_number, w.title, f.name AS facility_name, w.selling_total, w.estimated_cost, w.actual_total_cost, w.actual_cost, w.billed_amount, w.pending_billing_amount, w.status')
      ->join('facilities f', 'f.id = w.facility_id', 'left')
      ->where('DATE(w.created_at) >=', $from)
      ->where('DATE(w.created_at) <=', $to);

    if ($facilityId) {
      $q->where('w.facility_id', $facilityId);
    }

    $rows = $q->orderBy('w.created_at', 'DESC')->get()->getResultArray();
    foreach ($rows as &$row) {
      $revenue = (float) ($row['billed_amount'] ?? $row['selling_total'] ?? 0);
      $actual  = (float) ($row['actual_total_cost'] ?? $row['actual_cost'] ?? 0);
      $metrics = $this->calc->calculateProfitMetrics($revenue, $actual, (float) ($row['estimated_cost'] ?? 0));
      $row['revenue']        = $revenue;
      $row['actual_cost']    = $actual;
      $row['profit']         = $metrics['profit'];
      $row['margin_percent'] = $metrics['margin_percent'];
    }

    return $rows;
  }

  /** @return list<array<string, mixed>> */
  public function materialVariance(string $from, string $to, ?int $facilityId = null): array
  {
    $q = $this->db->table('wo_materials wm')
      ->select('wm.*, w.wo_number, f.name AS facility_name')
      ->join('work_orders w', 'w.id = wm.wo_id', 'left')
      ->join('facilities f', 'f.id = w.facility_id', 'left')
      ->where('DATE(wm.created_at) >=', $from)
      ->where('DATE(wm.created_at) <=', $to);

    if ($facilityId) {
      $q->where('w.facility_id', $facilityId);
    }

    $rows = $q->orderBy('wm.created_at', 'DESC')->get()->getResultArray();
    foreach ($rows as &$row) {
      $est = (float) ($row['estimated_cost'] ?? $row['total_cost'] ?? 0);
      $act = (float) ($row['actual_cost'] ?? $row['total_cost'] ?? 0);
      $row['variance']      = round($act - $est, 2);
      $row['wastage_total'] = (float) ($row['wastage_cost'] ?? 0);
    }

    return $rows;
  }

  /** @return list<array<string, mixed>> */
  public function monthlyFinancialSummary(int $months = 12): array
  {
    $rows = [];
    for ($i = $months - 1; $i >= 0; $i--) {
      $start = date('Y-m-01', strtotime("-{$i} months"));
      $end   = date('Y-m-t', strtotime($start));

      $revenue = (float) ($this->db->table('invoices')
        ->selectSum('total', 't')
        ->whereIn('status', ['paid', 'partial', 'sent'])
        ->where('DATE(issue_date) >=', $start)
        ->where('DATE(issue_date) <=', $end)
        ->where('deleted_at', null)
        ->get()->getRowArray()['t'] ?? 0);

      $collected = (float) ($this->db->table('invoice_payments p')
        ->selectSum('p.amount', 't')
        ->join('invoices i', 'i.id = p.invoice_id', 'inner')
        ->where('DATE(p.paid_at) >=', $start)
        ->where('DATE(p.paid_at) <=', $end)
        ->get()->getRowArray()['t'] ?? 0);

      $actualCost = (float) ($this->db->table('work_orders')
        ->selectSum('actual_total_cost', 't')
        ->where('DATE(completed_at) >=', $start)
        ->where('DATE(completed_at) <=', $end)
        ->get()->getRowArray()['t'] ?? 0);

      if ($actualCost <= 0) {
        $actualCost = (float) ($this->db->table('work_orders')
          ->selectSum('actual_cost', 't')
          ->where('DATE(completed_at) >=', $start)
          ->where('DATE(completed_at) <=', $end)
          ->get()->getRowArray()['t'] ?? 0);
      }

      $rows[] = [
        'period'    => date('M Y', strtotime($start)),
        'revenue'   => $revenue,
        'collected' => $collected,
        'cost'      => $actualCost,
        'profit'    => round($collected - $actualCost, 2),
      ];
    }

    return $rows;
  }
}
