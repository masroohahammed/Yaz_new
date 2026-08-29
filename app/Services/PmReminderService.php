<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Auto-create system reminders + in-app notifications for PM events */
class PmReminderService
{
  private ?AlertDispatchService $alerts = null;

  public function __construct(private BaseConnection $db)
  {
  }

  public function syncAll(int $companyId, int $createdBy): array
  {
    return [
      'leases'  => $this->syncLeaseExpiry($companyId, $createdBy, 60),
      'cheques' => $this->syncChequeDue($companyId, $createdBy, 14),
      'crm'     => $this->syncCrmFollowUps($companyId, $createdBy),
    ];
  }

  public function syncLeaseExpiry(int $companyId, int $createdBy, int $daysAhead = 60): int
  {
    if (! $this->db->tableExists('lease_contracts') || ! $this->db->tableExists('reminders')) {
      return 0;
    }

    $until = date('Y-m-d', strtotime("+{$daysAhead} days"));
    $today = date('Y-m-d');
    $rows  = $this->db->table('lease_contracts lc')
      ->select('lc.id, lc.contract_number, lc.end_date, lc.facility_id, t.full_name AS tenant_name')
      ->join('tenants t', 't.id = lc.tenant_id', 'left')
      ->where('lc.status', 'active')
      ->where('lc.end_date >=', $today)
      ->where('lc.end_date <=', $until)
      ->get()->getResultArray();

    $count = 0;
    foreach ($rows as $c) {
      $msg = 'Contract ' . ($c['contract_number'] ?? $c['id']) . ' expires ' . $c['end_date']
        . ($c['tenant_name'] ? ' — ' . $c['tenant_name'] : '');
      if ($this->insertReminder($companyId, 'lease_contracts', (int) $c['id'], $msg, $c['end_date'] . ' 09:00:00', $createdBy)) {
        $count++;
        $this->notifyLeaseExpiry((int) $c['facility_id'], $msg, (int) $c['id']);
      }
    }

    return $count;
  }

  public function syncChequeDue(int $companyId, int $createdBy, int $daysAhead = 14): int
  {
    if (! $this->db->tableExists('cheques') || ! $this->db->tableExists('reminders')) {
      return 0;
    }

    $until = date('Y-m-d', strtotime("+{$daysAhead} days"));
    $today = date('Y-m-d');
    $rows  = $this->db->table('cheques')
      ->whereIn('status', ['pending', 'deposited'])
      ->where('cheque_date >=', $today)
      ->where('cheque_date <=', $until)
      ->get()->getResultArray();

    $count = 0;
    foreach ($rows as $ch) {
      $msg = 'Cheque ' . ($ch['cheque_no'] ?? '') . ' due ' . $ch['cheque_date'] . ' — ' . number_format((float) $ch['amount'], 2);
      $userId = null;
      if ($this->db->tableExists('user_property_assignments') && ! empty($ch['facility_id'])) {
        $assign = $this->db->table('user_property_assignments')
          ->where('facility_id', (int) $ch['facility_id'])
          ->whereIn('role_type', ['property_manager', 'manager', 'caretaker'])
          ->orderBy('id')
          ->get()->getRowArray();
        $userId = (int) ($assign['user_id'] ?? 0) ?: null;
      }
      if ($this->insertReminder($companyId, 'cheques', (int) $ch['id'], $msg, $ch['cheque_date'] . ' 09:00:00', $createdBy, $userId)) {
        $count++;
        if ($userId) {
          $this->pushNotification($userId, 'Cheque due', $msg, (int) $ch['id']);
        }
      }
    }

    return $count;
  }

  public function syncCrmFollowUps(int $companyId, int $createdBy): int
  {
    if (! $this->db->tableExists('crm_leads') || ! $this->db->tableExists('reminders')) {
      return 0;
    }

    if (! $this->db->fieldExists('follow_up_date', 'crm_leads')) {
      return 0;
    }

    $today = date('Y-m-d');
    $until = date('Y-m-d', strtotime('+7 days'));
    $rows  = $this->db->table('crm_leads')
      ->where('converted', 0)
      ->where('follow_up_date >=', $today)
      ->where('follow_up_date <=', $until)
      ->get()->getResultArray();

    $count = 0;
    foreach ($rows as $l) {
      $msg = 'CRM follow-up: ' . ($l['full_name'] ?? $l['lead_number'] ?? $l['id']);
      $dt  = $l['follow_up_date'];
      if (! empty($l['follow_up_time'])) {
        $dt .= ' ' . $l['follow_up_time'];
      } else {
        $dt .= ' 10:00:00';
      }
      $assignee = (int) ($l['assigned_to'] ?? 0) ?: null;
      if ($this->insertReminder($companyId, 'crm_leads', (int) $l['id'], $msg, $dt, $createdBy, $assignee)) {
        $count++;
        if ($assignee) {
          $this->pushNotification($assignee, 'CRM follow-up', $msg, (int) $l['id']);
        }
      }
    }

    return $count;
  }

  private function notifyLeaseExpiry(int $facilityId, string $message, int $contractId): void
  {
    $userIds = [];
    if ($facilityId > 0 && $this->db->tableExists('user_property_assignments')) {
      foreach ($this->db->table('user_property_assignments')
        ->where('facility_id', $facilityId)
        ->whereIn('role_type', ['property_manager', 'manager'])
        ->get()->getResultArray() as $a) {
        $userIds[] = (int) $a['user_id'];
      }
    }

    if (empty($userIds) && $this->db->tableExists('users')) {
      $role = $this->db->table('roles')->where('name', 'property_manager')->get()->getRowArray();
      if ($role) {
        foreach ($this->db->table('users')->where('role_id', $role['id'])->where('status', 'active')->limit(5)->get()->getResultArray() as $u) {
          $userIds[] = (int) $u['id'];
        }
      }
    }

    foreach (array_unique($userIds) as $uid) {
      $this->pushNotification($uid, 'Lease expiry', $message, $contractId);
    }
  }

  private function pushNotification(int $userId, string $title, string $message, int $refId): void
  {
    if ($userId <= 0 || ! $this->db->tableExists('notifications')) {
      return;
    }

    $dup = $this->db->table('notifications')
      ->where('user_id', $userId)
      ->where('title', $title)
      ->where('reference_id', $refId)
      ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 day')))
      ->countAllResults();

    if ($dup > 0) {
      return;
    }

    $this->alertService()->notifyUser($userId, $title, $message, 'general', $refId);
  }

  private function alertService(): AlertDispatchService
  {
    if ($this->alerts === null) {
      $this->alerts = new AlertDispatchService($this->db);
    }

    return $this->alerts;
  }

  private function insertReminder(
    int $companyId,
    string $module,
    int $refId,
    string $message,
    string $datetime,
    int $createdBy,
    ?int $userId = null
  ): bool {
    $exists = $this->db->table('reminders')
      ->where('module', $module)
      ->where('ref_id', $refId)
      ->where('status', 'pending')
      ->where('message', $message)
      ->countAllResults();

    if ($exists > 0) {
      return false;
    }

    $this->db->table('reminders')->insert([
      'company_id'        => $companyId,
      'module'            => $module,
      'ref_id'            => $refId,
      'user_id'           => $userId,
      'reminder_datetime' => $datetime,
      'message'           => $message,
      'status'            => 'pending',
      'created_by'        => $createdBy,
      'created_at'        => date('Y-m-d H:i:s'),
    ]);

    return true;
  }
}
