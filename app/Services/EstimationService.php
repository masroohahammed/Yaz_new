<?php

namespace App\Services;

use App\Models\EstimationItemModel;
use App\Models\EstimationModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Estimation persistence, line-item costing, and header rollups.
 */
class EstimationService
{
  public function __construct(
    private BaseConnection $db,
    private FinancialCalculationService $calc = new FinancialCalculationService(),
  ) {
  }

  /**
   * @param  array<string, mixed>  $header
   * @param  list<array<string, mixed>>  $rawItems
   * @return array{header: array<string, mixed>, items: list<array<string, mixed>>}
   */
  public function buildPayload(array $header, array $rawItems, bool $vatEnabled, float $vatRate): array
  {
    $items = [];
    foreach ($rawItems as $i => $row) {
      $name = trim((string) ($row['item_name'] ?? $row['description'] ?? ''));
      if ($name === '') {
        continue;
      }

      $qty       = (float) ($row['quantity'] ?? 1);
      $unitPrice = (float) ($row['unit_price'] ?? 0);
      $estCost   = (float) ($row['estimated_unit_cost'] ?? $row['unit_cost'] ?? 0);
      $actCost   = (float) ($row['actual_unit_cost'] ?? 0);
      $metrics   = $this->calc->calculateLine($qty, $unitPrice, $estCost, $actCost);

      $items[] = array_merge([
        'type'                => in_array($row['type'] ?? '', ['labor', 'material', 'other', 'service'], true) ? $row['type'] : 'material',
        'item_name'           => $name,
        'description'         => trim((string) ($row['description'] ?? $name)),
        'quantity'            => $qty,
        'unit'                => trim((string) ($row['unit'] ?? 'unit')) ?: 'unit',
        'unit_price'          => $unitPrice,
        'estimated_unit_cost' => $estCost,
        'actual_unit_cost'    => $actCost,
        'unit_cost'           => $estCost,
        'total_cost'          => $metrics['estimated_total'],
        'actual_used_qty'     => (float) ($row['actual_used_qty'] ?? 0),
        'wastage_qty'         => (float) ($row['wastage_qty'] ?? 0),
        'wastage_cost'        => (float) ($row['wastage_cost'] ?? 0),
        'sort_order'          => (int) ($row['sort_order'] ?? $i),
      ], $metrics);
    }

    $summary = $this->calc->summarizeItems($items);

    $breakdown = [
      'actual_labor_cost'     => (float) ($header['actual_labor_cost'] ?? 0),
      'actual_material_cost'  => (float) ($header['actual_material_cost'] ?? 0),
      'actual_transport_cost' => (float) ($header['actual_transport_cost'] ?? 0),
      'actual_equipment_cost' => (float) ($header['actual_equipment_cost'] ?? 0),
      'actual_misc_cost'      => (float) ($header['actual_misc_cost'] ?? 0),
      'actual_other_cost'     => (float) ($header['actual_other_cost'] ?? 0),
    ];
    $actualBreakdown = $this->calc->summarizeActualBreakdown($breakdown);

    $sellingSubtotal = $summary['selling_subtotal'];
    $vatAmt          = $vatEnabled ? round($sellingSubtotal * $vatRate / 100, 2) : 0;
    $total           = $sellingSubtotal + $vatAmt;

    $headerOut = array_merge($header, $summary, $breakdown, $actualBreakdown, [
      'subtotal'   => $sellingSubtotal,
      'vat_rate'   => $vatEnabled ? $vatRate : 0,
      'vat_amount' => $vatAmt,
      'total'      => $total,
      'actual_total' => $actualBreakdown['actual_total_cost'] > 0
        ? $actualBreakdown['actual_total_cost']
        : $summary['actual_subtotal'],
      // Legacy header fields kept in sync for reports
      'labor_cost'    => $this->sumByType($items, 'labor', 'estimated_total'),
      'material_cost' => $this->sumByType($items, 'material', 'estimated_total'),
      'other_cost'    => $this->sumByType($items, 'other', 'estimated_total') + $this->sumByType($items, 'service', 'estimated_total'),
    ]);

    return ['header' => $headerOut, 'items' => $items];
  }

  /**
   * @param  list<array<string, mixed>>  $items
   */
  private function sumByType(array $items, string $type, string $field): float
  {
    $sum = 0.0;
    foreach ($items as $item) {
      if (($item['type'] ?? '') === $type) {
        $sum += (float) ($item[$field] ?? 0);
      }
    }

    return round($sum, 2);
  }

  public function saveItems(int $estId, array $items): void
  {
    $itemModel = new EstimationItemModel();
    $itemModel->where('est_id', $estId)->delete();

    foreach ($items as $item) {
      $item['est_id'] = $estId;
      $itemModel->insert($item);
    }
  }

  public function saveEstimation(array $header, ?int $estId = null): int
  {
    $model = new EstimationModel();
    if ($estId) {
      $model->update($estId, $header);

      return $estId;
    }

    $model->insert($header);

    return (int) $model->getInsertID();
  }

  /** @return list<array<string, mixed>> */
  public function getItems(int $estId): array
  {
    return $this->db->table('estimation_items')
      ->where('est_id', $estId)
      ->orderBy('sort_order', 'ASC')
      ->orderBy('id', 'ASC')
      ->get()
      ->getResultArray();
  }
}
