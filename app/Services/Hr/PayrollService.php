<?php

namespace App\Services\Hr;

use App\Services\Finance\GlPostingService;
use CodeIgniter\Database\BaseConnection;

class PayrollService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
    }

    /** @return list<array<string, mixed>> */
    public function listRuns(?string $month = null): array
    {
        if (! $this->db->tableExists('pm_salary_runs')) {
            return [];
        }
        $q = $this->db->table('pm_salary_runs sr')
            ->select('sr.*, ep.employee_code, u.name AS employee_name')
            ->join('employee_profiles ep', 'ep.id = sr.profile_id', 'left')
            ->join('users u', 'u.id = sr.user_id', 'left')
            ->orderBy('sr.month', 'DESC')
            ->orderBy('u.name', 'ASC');
        if ($month) {
            $q->where('sr.month', $month);
        }

        return $q->limit(500)->get()->getResultArray();
    }

    public function generateMonth(string $month, int $createdBy): int
    {
        if (! $this->db->tableExists('pm_salary_runs') || ! $this->db->tableExists('employee_profiles')) {
            return 0;
        }

        $profiles = $this->db->table('employee_profiles ep')
            ->select('ep.id, ep.user_id, ep.basic_salary, ep.allowances')
            ->where('ep.status', 'active')
            ->where('ep.deleted_at', null)
            ->get()->getResultArray();

        $count = 0;
        foreach ($profiles as $p) {
            $profileId = (int) $p['id'];
            $exists = $this->db->table('pm_salary_runs')
                ->where('profile_id', $profileId)
                ->where('month', $month)
                ->countAllResults();
            if ($exists) {
                continue;
            }

            $basic       = (float) ($p['basic_salary'] ?? 0);
            $allowances  = (float) ($p['allowances'] ?? 0);
            $gross       = round($basic + $allowances, 2);
            $deductions  = 0;
            $net         = round($gross - $deductions, 2);

            $this->db->table('pm_salary_runs')->insert([
                'company_id'  => session()->get('company_id') ?: null,
                'employee_id' => $profileId,
                'profile_id'  => $profileId,
                'user_id'     => (int) $p['user_id'],
                'month'       => $month,
                'hours'       => 0,
                'rate'        => $basic,
                'gross'       => $gross,
                'allowances'  => $allowances,
                'deductions'  => $deductions,
                'net'         => $net,
                'status'      => 'draft',
                'created_by'  => $createdBy,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        return $count;
    }

    public function approve(int $runId, int $approverId): bool
    {
        $run = $this->db->table('pm_salary_runs')->where('id', $runId)->get()->getRowArray();
        if (! $run || $run['status'] !== 'draft') {
            return false;
        }

        $jid = (new GlPostingService($this->db))->postPayrollAccrual(
            $runId,
            (float) $run['gross'],
            (float) $run['deductions'],
            (float) $run['net'],
            $approverId
        );

        $this->db->table('pm_salary_runs')->where('id', $runId)->update([
            'status'              => 'approved',
            'approved_by'         => $approverId,
            'approved_at'         => date('Y-m-d H:i:s'),
            'accrual_journal_id'  => $jid,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function markPaid(int $runId, int $userId): bool
    {
        $run = $this->db->table('pm_salary_runs')->where('id', $runId)->get()->getRowArray();
        if (! $run || $run['status'] !== 'approved') {
            return false;
        }

        $jid = (new GlPostingService($this->db))->postPayrollPayment($runId, (float) $run['net'], $userId);

        $this->db->table('pm_salary_runs')->where('id', $runId)->update([
            'status'               => 'paid',
            'payment_journal_id'   => $jid,
            'paid_at'              => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
