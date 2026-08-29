<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class LeaveService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
    }

    /** @return list<array<string, mixed>> */
    public function listRequests(array $filters = []): array
    {
        if (! $this->db->tableExists('hr_leave_requests')) {
            return [];
        }
        $q = $this->db->table('hr_leave_requests lr')
            ->select('lr.*, u.name AS employee_name, lt.name AS leave_type_name')
            ->join('users u', 'u.id = lr.user_id', 'left')
            ->join('hr_leave_types lt', 'lt.id = lr.leave_type_id', 'left')
            ->orderBy('lr.created_at', 'DESC');

        if (! empty($filters['status'])) {
            $q->where('lr.status', $filters['status']);
        }
        if (! empty($filters['user_id'])) {
            $q->where('lr.user_id', (int) $filters['user_id']);
        }

        return $q->limit(300)->get()->getResultArray();
    }

    public function submit(int $userId, array $data): int
    {
        $start = $data['start_date'];
        $end   = $data['end_date'];
        $days  = max(1, (int) ceil((strtotime($end) - strtotime($start)) / 86400) + 1);

        $this->db->table('hr_leave_requests')->insert([
            'user_id'       => $userId,
            'leave_type_id' => (int) $data['leave_type_id'],
            'start_date'    => $start,
            'end_date'      => $end,
            'days'          => $days,
            'reason'        => esc($data['reason'] ?? ''),
            'status'        => 'pending',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    public function approve(int $id, int $approverId): bool
    {
        $row = $this->db->table('hr_leave_requests')->where('id', $id)->get()->getRowArray();
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        $this->db->table('hr_leave_requests')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->tableExists('hr_leave_balances')) {
            $year = (int) date('Y', strtotime($row['start_date']));
            $bal  = $this->db->table('hr_leave_balances')
                ->where('user_id', $row['user_id'])
                ->where('leave_type_id', $row['leave_type_id'])
                ->where('year', $year)
                ->get()->getRowArray();
            if ($bal) {
                $this->db->table('hr_leave_balances')->where('id', $bal['id'])->update([
                    'used' => (float) $bal['used'] + (float) $row['days'],
                ]);
            }
        }

        if ($this->db->tableExists('employee_profiles')) {
            $this->db->table('employee_profiles')->where('user_id', $row['user_id'])->update(['status' => 'on_leave']);
        }

        return true;
    }

    public function reject(int $id, int $rejecterId, string $reason = ''): bool
    {
        $row = $this->db->table('hr_leave_requests')->where('id', $id)->get()->getRowArray();
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        $this->db->table('hr_leave_requests')->where('id', $id)->update([
            'status'           => 'rejected',
            'rejected_by'      => $rejecterId,
            'rejected_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => esc($reason),
        ]);

        return true;
    }
}
