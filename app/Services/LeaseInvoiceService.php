<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Lease contract billing: auto-generate lease_payments schedule.
 */
class LeaseInvoiceService
{
  public function __construct(private BaseConnection $db)
  {
  }

  public function computeBillingStartDate(array $contract): string
  {
    $start = $contract['start_date'];
    if (empty($contract['has_free_period']) || empty($contract['free_period_months'])) {
      return $contract['billing_start_date'] ?? $start;
    }
    if (($contract['free_period_position'] ?? 'beginning') === 'beginning') {
      $days = (int) $contract['free_period_months'] * 30;

      return date('Y-m-d', strtotime($start . " +{$days} days"));
    }

    return $start;
  }

  /**
   * Remove pending/overdue/postponed payments then rebuild schedule.
   *
   * @return int Number of new payments created
   */
  public function regenerateSchedule(int $contractId, callable $generateNumber): int
  {
    if ($this->db->tableExists('lease_payments')) {
      $this->db->table('lease_payments')
        ->where('contract_id', $contractId)
        ->whereIn('status', ['pending', 'postponed', 'overdue'])
        ->delete();
    }

    return $this->generateSchedule($contractId, $generateNumber);
  }

  /**
   * Generate recurring lease_payments for contract term (skips existing due dates).
   *
   * @return int Number of payments created
   */
  public function generateSchedule(int $contractId, callable $generateNumber): int
  {
    if (! $this->db->tableExists('lease_payments') || ! $this->db->tableExists('lease_contracts')) {
      return 0;
    }

    $c = $this->db->table('lease_contracts')->where('id', $contractId)->get()->getRowArray();
    if (! $c || empty($c['auto_generate_invoices'])) {
      return 0;
    }

    $billingStart = $this->computeBillingStartDate($c);
    $this->db->table('lease_contracts')->where('id', $contractId)->update([
      'billing_start_date' => $billingStart,
    ]);
    $c['billing_start_date'] = $billingStart;

    $freq   = $c['payment_frequency'] ?? 'monthly';
    $months = match ($freq) {
      'quarterly' => 3,
      'semi-annual', 'semi_annual' => 6,
      'yearly', 'annual' => 12,
      default => 1,
    };

    $discount = (float) ($c['discount_pct'] ?? 0);

    $start = strtotime($billingStart);
    $end   = strtotime($c['end_date']);
    $count = 0;
    $cur   = $start;

    while ($cur <= $end) {
      $dueDate    = date('Y-m-d', $cur);
      $periodFrom = $dueDate;
      $periodTo   = date('Y-m-d', strtotime("+{$months} months -1 day", $cur));

      if ($this->dueDateExists($contractId, $dueDate)) {
        $cur = strtotime("+{$months} months", $cur);
        continue;
      }

      $rentBase = $this->rentAmountForPeriod($c, $dueDate);
      $amount   = round($rentBase * (1 - $discount / 100), 2);
      $amount   = $this->applyComplimentaryOffers($amount, $contractId, $periodFrom, $periodTo);

      if (! empty($c['vat_applicable']) && ! empty($c['vat_rate']) && $amount > 0) {
        $amount = round($amount * (1 + (float) $c['vat_rate'] / 100), 2);
      }

      $num = $generateNumber('PAY', 'lease_payments', 'payment_number');
      $this->db->table('lease_payments')->insert([
        'company_id'      => $c['company_id'],
        'payment_number'  => $num,
        'contract_id'     => $contractId,
        'tenant_id'       => $c['tenant_id'],
        'facility_id'     => $c['facility_id'],
        'unit_id'         => $c['unit_id'],
        'payment_type'    => 'rent',
        'payment_method'  => $c['payment_type'] ?? 'cheque',
        'amount'          => $amount,
        'status'          => $amount <= 0 ? 'paid' : 'pending',
        'due_date'        => $dueDate,
        'period_from'     => $periodFrom,
        'period_to'       => $periodTo,
        'notes'           => $amount <= 0 ? 'Complimentary / free period' : null,
        'created_by'      => $c['created_by'],
        'created_at'      => date('Y-m-d H:i:s'),
      ]);
      $count++;
      $cur = strtotime("+{$months} months", $cur);
    }

    return $count;
  }

  public function dueDateExists(int $contractId, string $dueDate): bool
  {
    return $this->db->table('lease_payments')
      ->where('contract_id', $contractId)
      ->where('due_date', $dueDate)
      ->whereNotIn('status', ['cancelled'])
      ->countAllResults() > 0;
  }

  /** Apply active complimentary_offers overlapping the billing period */
  public function applyComplimentaryOffers(float $amount, int $contractId, string $periodFrom, string $periodTo): float
  {
    if ($amount <= 0 || ! $this->db->tableExists('complimentary_offers')) {
      return $amount;
    }

    $offers = $this->db->table('complimentary_offers')
      ->where('contract_id', $contractId)
      ->where('status', 'active')
      ->get()->getResultArray();

    foreach ($offers as $o) {
      $oStart = $o['start_date'] ?? null;
      $oEnd   = $o['end_date'] ?? null;
      if ($oStart && $oEnd && ! $this->periodsOverlap($periodFrom, $periodTo, $oStart, $oEnd)) {
        continue;
      }

      $type = strtolower((string) ($o['offer_type'] ?? ''));
      if (str_contains($type, 'complimentary') || str_contains($type, 'free')) {
        if (! empty($o['free_period_value']) && $oStart) {
          $freeEnd = date('Y-m-d', strtotime($oStart . ' +' . (int) $o['free_period_value'] . ' months -1 day'));
          if ($periodFrom <= $freeEnd && $periodTo >= $oStart) {
            return 0.0;
          }
        }
        if ((float) ($o['discount_percent'] ?? 0) >= 100) {
          return 0.0;
        }
      }

      $pct = (float) ($o['discount_percent'] ?? 0);
      if ($pct > 0) {
        $amount = round($amount * (1 - $pct / 100), 2);
      }
    }

    return max(0, $amount);
  }

  private function periodsOverlap(string $aFrom, string $aTo, string $bFrom, string $bTo): bool
  {
    return $aFrom <= $bTo && $aTo >= $bFrom;
  }

  /** Rent for billing period using multi-year schedule when present */
  public function rentAmountForPeriod(array $contract, string $dueDate): float
  {
    $base       = (float) ($contract['rent_amount'] ?? 0);
    $contractId = (int) ($contract['id'] ?? 0);

    if ($contractId <= 0 || ! $this->db->tableExists('contract_rent_schedule')) {
      return $base;
    }

    $billingStart = $this->computeBillingStartDate($contract);
    $startTs      = strtotime($billingStart);
    $dueTs        = strtotime($dueDate);

    if ($dueTs < $startTs) {
      return $base;
    }

    $monthsDiff = ((int) date('Y', $dueTs) - (int) date('Y', $startTs)) * 12
      + ((int) date('m', $dueTs) - (int) date('m', $startTs));
    $yearNumber = max(1, (int) floor($monthsDiff / 12) + 1);

    $row = $this->db->table('contract_rent_schedule')
      ->where('contract_id', $contractId)
      ->where('year_number', $yearNumber)
      ->get()->getRowArray();

    if ($row) {
      return (float) $row['rent_amount'];
    }

    $last = $this->db->table('contract_rent_schedule')
      ->where('contract_id', $contractId)
      ->orderBy('year_number', 'DESC')
      ->get()->getRowArray();

    return $last ? (float) $last['rent_amount'] : $base;
  }

  /** Create cheque row when payment method is cheque */
  public function linkChequeFromPayment(int $paymentId): void
  {
    if (! $this->db->tableExists('cheques')) {
      return;
    }

    $p = $this->db->table('lease_payments')->where('id', $paymentId)->get()->getRowArray();
    if (! $p || ($p['payment_method'] ?? '') !== 'cheque' || (float) ($p['amount'] ?? 0) <= 0) {
      return;
    }

    $this->db->table('cheques')->insert([
      'company_id'   => $p['company_id'],
      'contract_id'  => $p['contract_id'],
      'tenant_id'    => $p['tenant_id'],
      'facility_id'  => $p['facility_id'],
      'cheque_no'    => $p['cheque_no'] ?? 'PENDING',
      'amount'       => $p['amount'],
      'status'       => 'pending',
      'cheque_date'  => $p['due_date'],
      'period_from'  => $p['period_from'],
      'period_to'    => $p['period_to'],
      'created_at'   => date('Y-m-d H:i:s'),
    ]);
  }
}
