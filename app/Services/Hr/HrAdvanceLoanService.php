<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrAdvanceLoanService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function advancesReady(): bool
    {
        return $this->db->tableExists('hr_salary_advances');
    }

    public function loansReady(): bool
    {
        return $this->db->tableExists('hr_employee_loans');
    }

    /** @param array<string, mixed> $data */
    public function submitAdvance(array $data, int $userId): int
    {
        if (! $this->advancesReady()) {
            throw new \RuntimeException('Advance tables missing.');
        }

        $amount       = (float) $data['amount'];
        $installments = max(1, (int) ($data['installments'] ?? 1));
        $payload      = [
            'employee_id'         => (int) $data['employee_id'],
            'amount'              => $amount,
            'request_date'        => $data['request_date'] ?? date('Y-m-d'),
            'recovery_start_date' => $data['recovery_start_date'] ?? null,
            'installments'        => $installments,
            'installment_amount'  => round($amount / $installments, 2),
            'balance'             => $amount,
            'reason'              => $data['reason'] ?? null,
            'status'              => 'pending',
            'requested_by'        => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            return fm_insert_row_id($this->db, 'hr_salary_advances', $payload);
        }

        $this->db->table('hr_salary_advances')->insert($payload);

        return (int) $this->db->insertID();
    }

    public function approveAdvance(int $id, int $reviewerId, ?string $notes = null): bool
    {
        $row = $this->findAdvance($id);
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        return $this->db->table('hr_salary_advances')->where('id', $id)->update([
            'status'       => 'active',
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function rejectAdvance(int $id, int $reviewerId, ?string $notes = null): bool
    {
        return $this->db->table('hr_salary_advances')->where('id', $id)->where('status', 'pending')->update([
            'status'       => 'rejected',
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string, mixed> $data */
    public function submitLoan(array $data, int $userId): int
    {
        if (! $this->loansReady()) {
            throw new \RuntimeException('Loan tables missing.');
        }

        $principal = (float) $data['principal'];
        $tenure    = max(1, (int) ($data['tenure_months'] ?? 12));
        $rate      = (float) ($data['interest_rate'] ?? 0);
        $monthly   = round($principal / $tenure * (1 + ($rate / 100)), 2);

        $payload = [
            'employee_id'         => (int) $data['employee_id'],
            'loan_number'         => $data['loan_number'] ?? ('LN-' . date('Y') . '-' . random_int(1000, 9999)),
            'principal'           => $principal,
            'interest_rate'       => $rate,
            'tenure_months'       => $tenure,
            'monthly_installment' => $monthly,
            'start_date'          => $data['start_date'] ?? null,
            'balance'             => $principal,
            'reason'              => $data['reason'] ?? null,
            'status'              => 'pending',
            'requested_by'        => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $loanId = fm_insert_row_id($this->db, 'hr_employee_loans', $payload);
        } else {
            $this->db->table('hr_employee_loans')->insert($payload);
            $loanId = (int) $this->db->insertID();
        }

        return $loanId;
    }

    public function approveLoan(int $id, int $reviewerId, ?string $notes = null): bool
    {
        $row = $this->findLoan($id);
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        $this->db->table('hr_employee_loans')->where('id', $id)->update([
            'status'       => 'active',
            'start_date'   => $row['start_date'] ?: date('Y-m-d'),
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->generateInstallments($id);

        return true;
    }

    public function rejectLoan(int $id, int $reviewerId, ?string $notes = null): bool
    {
        return $this->db->table('hr_employee_loans')->where('id', $id)->where('status', 'pending')->update([
            'status'       => 'rejected',
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function advancesForEmployee(int $employeeId): array
    {
        if (! $this->advancesReady()) {
            return [];
        }

        return $this->db->table('hr_salary_advances')->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function loansForEmployee(int $employeeId): array
    {
        if (! $this->loansReady()) {
            return [];
        }

        return $this->db->table('hr_employee_loans')->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function pendingAdvances(?int $companyId = null): array
    {
        if (! $this->advancesReady()) {
            return [];
        }

        $q = $this->db->table('hr_salary_advances a')
            ->select('a.*, e.emp_code, u.name AS employee_name')
            ->join('employees e', 'e.id = a.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('a.status', 'pending');

        if ($companyId) {
            $q->where('e.company_id', $companyId);
        }

        return $q->orderBy('a.created_at', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function pendingLoans(?int $companyId = null): array
    {
        if (! $this->loansReady()) {
            return [];
        }

        $q = $this->db->table('hr_employee_loans l')
            ->select('l.*, e.emp_code, u.name AS employee_name')
            ->join('employees e', 'e.id = l.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('l.status', 'pending');

        if ($companyId) {
            $q->where('e.company_id', $companyId);
        }

        return $q->orderBy('l.created_at', 'ASC')->get()->getResultArray();
    }

    public function findAdvance(int $id): ?array
    {
        $row = $this->db->table('hr_salary_advances')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }

    public function findLoan(int $id): ?array
    {
        $row = $this->db->table('hr_employee_loans')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }

    private function generateInstallments(int $loanId): void
    {
        if (! $this->db->tableExists('hr_loan_installments')) {
            return;
        }

        $loan = $this->findLoan($loanId);
        if (! $loan) {
            return;
        }

        $monthly = (float) ($loan['monthly_installment'] ?? 0);
        $start   = strtotime((string) ($loan['start_date'] ?: date('Y-m-d')));

        for ($i = 1; $i <= (int) $loan['tenure_months']; $i++) {
            $due = date('Y-m-d', strtotime("+{$i} month", $start));
            $this->db->table('hr_loan_installments')->insert([
                'loan_id'          => $loanId,
                'installment_no'   => $i,
                'due_date'         => $due,
                'principal_amount' => round((float) $loan['principal'] / (int) $loan['tenure_months'], 2),
                'interest_amount'  => round($monthly - ((float) $loan['principal'] / (int) $loan['tenure_months']), 2),
                'total_amount'     => $monthly,
                'status'           => 'pending',
            ]);
        }
    }
}
