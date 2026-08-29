<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class LandlordPayoutService
{
  public function __construct(private BaseConnection $db)
  {
  }

  /**
   * Mark payout paid and create finance ledger entry (expense).
   *
   * @param array{paid_date?: string, payment_method?: ?string, reference_no?: ?string} $opts
   */
  public function markPaidAndJournal(int $payoutId, int $userId, array $opts = []): void
  {
    if (! $this->db->tableExists('landlord_payouts')) {
      return;
    }

    $p = $this->db->table('landlord_payouts')->where('id', $payoutId)->get()->getRowArray();
    if (! $p) {
      return;
    }

    if (($p['status'] ?? '') === 'paid') {
      return;
    }

    $paidDate = trim((string) ($opts['paid_date'] ?? '')) ?: date('Y-m-d');

    $this->db->table('landlord_payouts')->where('id', $payoutId)->update([
      'status'         => 'paid',
      'paid_date'      => $paidDate,
      'payment_method' => $opts['payment_method'] ?? $p['payment_method'] ?? null,
      'reference_no'   => $opts['reference_no'] ?? $p['reference_no'] ?? null,
      'updated_at'     => date('Y-m-d H:i:s'),
    ]);

    if (! $this->db->tableExists('finance_entries')) {
      return;
    }

    $exists = $this->db->table('finance_entries')
      ->where('ref_module', 'landlord_payouts')
      ->where('ref_id', $payoutId)
      ->countAllResults();

    if ($exists > 0) {
      return;
    }

    $facilityId = (int) ($p['facility_id'] ?? 0) ?: null;
    $desc       = 'Landlord payout';
    if ($facilityId && $this->db->tableExists('facilities')) {
      $fac = $this->db->table('facilities')->select('name')->where('id', $facilityId)->get()->getRowArray();
      if ($fac) {
        $desc .= ' — ' . $fac['name'];
      }
    }
    if (! empty($p['period_from']) && ! empty($p['period_to'])) {
      $desc .= ' (' . $p['period_from'] . ' to ' . $p['period_to'] . ')';
    }

    $this->db->table('finance_entries')->insert([
      'company_id'  => $p['company_id'],
      'entry_type'  => 'landlord_payout',
      'direction'   => 'expense',
      'amount'      => $p['net_amount'] ?? 0,
      'facility_id' => $facilityId,
      'landlord_id' => $p['landlord_id'],
      'ref_module'  => 'landlord_payouts',
      'ref_id'      => $payoutId,
      'description' => $desc,
      'entry_date'  => $paidDate,
      'created_by'  => $userId,
      'created_at'  => date('Y-m-d H:i:s'),
    ]);
  }
}
