<?php

namespace App\Services;

/**
 * Role-based financial data visibility.
 * Clients see selling prices only; internal roles see full cost/profit data.
 */
class FinancialVisibilityService
{
  /** @var list<string> */
  private const INTERNAL_ROLES = [
    'super_admin',
    'facility_manager',
    'finance_manager',
    'finance_user',
    'property_manager',
  ];

  /** Internal-only field keys stripped from client-facing payloads */
  private const INTERNAL_FIELDS = [
    'estimated_cost',
    'estimated_unit_cost',
    'estimated_total',
    'actual_cost',
    'actual_unit_cost',
    'actual_total',
    'actual_labor_cost',
    'actual_material_cost',
    'actual_transport_cost',
    'actual_equipment_cost',
    'actual_misc_cost',
    'actual_other_cost',
    'actual_total_cost',
    'actual_subtotal',
    'estimated_subtotal',
    'unit_cost',
    'unit_cost_internal',
    'total_cost',
    'profit',
    'margin_percent',
    'total_profit',
    'total_margin',
    'cost_variance',
    'variance',
    'wastage_qty',
    'wastage_cost',
    'labor_cost',
    'material_cost',
    'other_cost',
    'internal_notes',
    'notes_internal',
  ];

  public function canViewInternalFinancials(?string $role, bool $supervisorOverride = false): bool
  {
    if ($role === null || $role === 'client') {
      return false;
    }

    if (in_array($role, self::INTERNAL_ROLES, true)) {
      return true;
    }

    if ($role === 'supervisor' && $supervisorOverride) {
      return true;
    }

    return false;
  }

  public function canViewInternalFinancialsForUser(?array $user): bool
  {
    if (! $user) {
      return false;
    }

    $role = (string) ($user['role_name'] ?? session()->get('user_role') ?? '');
    $perm = (bool) ($user['can_view_costs'] ?? session()->get('can_view_costs') ?? false);

    return $this->canViewInternalFinancials($role, $role === 'supervisor' && $perm);
  }

  /**
   * @param  array<string, mixed>  $row
   * @return array<string, mixed>
   */
  public function filterRowForClient(array $row): array
  {
    foreach (self::INTERNAL_FIELDS as $field) {
      unset($row[$field]);
    }

    return $row;
  }

  /**
   * @param  list<array<string, mixed>>  $rows
   * @return list<array<string, mixed>>
   */
  public function filterRowsForClient(array $rows): array
  {
    return array_map(fn (array $row) => $this->filterRowForClient($row), $rows);
  }

  /**
   * @param  list<array<string, mixed>>  $items
   * @return list<array<string, mixed>>
   */
  public function filterEstimationItems(array $items, ?string $role): array
  {
    if ($this->canViewInternalFinancials($role)) {
      return $items;
    }

    $clientSafe = [];
    foreach ($items as $item) {
      $clientSafe[] = [
        'item_name'   => $item['item_name'] ?? $item['description'] ?? '',
        'description' => $item['description'] ?? '',
        'quantity'    => $item['quantity'] ?? 0,
        'unit'        => $item['unit'] ?? 'unit',
        'unit_price'  => $item['unit_price'] ?? $item['line_total'] ?? 0,
        'line_total'  => $item['line_total'] ?? $item['total_cost'] ?? 0,
      ];
    }

    return $clientSafe;
  }
}
