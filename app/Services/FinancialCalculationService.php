<?php

namespace App\Services;

/**
 * Central financial calculations: line totals, profit, margin, variance.
 */
class FinancialCalculationService
{
  /**
   * @return array{
   *   line_total: float,
   *   estimated_total: float,
   *   actual_total: float,
   *   profit: float,
   *   margin_percent: float,
   *   variance: float
   * }
   */
  public function calculateLine(
    float $quantity,
    float $unitPrice,
    float $estimatedUnitCost,
    float $actualUnitCost
  ): array {
    $qty           = max(0, $quantity);
    $lineTotal     = round($qty * max(0, $unitPrice), 2);
    $estimatedTotal = round($qty * max(0, $estimatedUnitCost), 2);
    $actualTotal   = round($qty * max(0, $actualUnitCost), 2);

    return array_merge(
      [
        'line_total'       => $lineTotal,
        'estimated_total'  => $estimatedTotal,
        'actual_total'     => $actualTotal,
      ],
      $this->calculateProfitMetrics($lineTotal, $actualTotal, $estimatedTotal)
    );
  }

  /**
   * @return array{profit: float, margin_percent: float, variance: float}
   */
  public function calculateProfitMetrics(float $sellingPrice, float $actualCost, float $estimatedCost = 0): array
  {
    $profit = round($sellingPrice - $actualCost, 2);
    $margin = $sellingPrice > 0
      ? round((($sellingPrice - $actualCost) / $sellingPrice) * 100, 2)
      : 0.0;
    $variance = round($actualCost - $estimatedCost, 2);

    return [
      'profit'         => $profit,
      'margin_percent' => $margin,
      'variance'       => $variance,
    ];
  }

  /**
   * @param  list<array<string, mixed>>  $items
   * @return array{
   *   selling_subtotal: float,
   *   estimated_subtotal: float,
   *   actual_subtotal: float,
   *   total_profit: float,
   *   total_margin: float,
   *   cost_variance: float
   * }
   */
  public function summarizeItems(array $items): array
  {
    $selling   = 0.0;
    $estimated = 0.0;
    $actual    = 0.0;

    foreach ($items as $item) {
      $selling   += (float) ($item['line_total'] ?? 0);
      $estimated += (float) ($item['estimated_total'] ?? 0);
      $actual    += (float) ($item['actual_total'] ?? 0);
    }

    $metrics = $this->calculateProfitMetrics($selling, $actual, $estimated);

    return [
      'selling_subtotal'   => round($selling, 2),
      'estimated_subtotal' => round($estimated, 2),
      'actual_subtotal'    => round($actual, 2),
      'total_profit'       => $metrics['profit'],
      'total_margin'       => $metrics['margin_percent'],
      'cost_variance'      => $metrics['variance'],
    ];
  }

  /**
   * @return array{actual_total_cost: float}
   */
  public function summarizeActualBreakdown(array $breakdown): array
  {
    $total = 0.0;
    foreach (['actual_labor_cost', 'actual_material_cost', 'actual_transport_cost', 'actual_equipment_cost', 'actual_misc_cost', 'actual_other_cost'] as $key) {
      $total += (float) ($breakdown[$key] ?? 0);
    }

    return ['actual_total_cost' => round($total, 2)];
  }

  /**
   * @return array{paid_amount: float, due_amount: float, pending_amount: float, status: string}
   */
  public function invoicePaymentState(float $invoiceTotal, float $paidSoFar): array
  {
    $paid    = round(min($paidSoFar, $invoiceTotal), 2);
    $due     = round(max(0, $invoiceTotal - $paid), 2);
    $pending = $due;

    if ($paid <= 0.01) {
      $status = 'sent';
    } elseif ($due <= 0.01) {
      $status = 'paid';
    } else {
      $status = 'partial';
    }

    return [
      'paid_amount'    => $paid,
      'due_amount'     => $due,
      'pending_amount' => $pending,
      'status'         => $status,
    ];
  }
}
