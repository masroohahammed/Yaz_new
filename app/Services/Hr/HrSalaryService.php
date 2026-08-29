<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrSalaryService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_salary_structures');
    }

    /** @return list<array<string, mixed>> */
    public function components(?int $companyId = null): array
    {
        if (! $this->db->tableExists('hr_salary_components')) {
            return [];
        }

        $q = $this->db->table('hr_salary_components')->where('is_active', 1)->orderBy('sort_order');
        if ($companyId) {
            $q->groupStart()->where('company_id', $companyId)->orWhere('company_id', null)->groupEnd();
        }

        return $q->get()->getResultArray();
    }

    public function currentStructure(int $employeeId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $struct = $this->db->table('hr_salary_structures')
            ->where('employee_id', $employeeId)
            ->where('is_current', 1)
            ->orderBy('effective_from', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (! $struct) {
            return null;
        }

        $struct['lines'] = $this->structureLines((int) $struct['id']);

        return $struct;
    }

    /** @return list<array<string, mixed>> */
    public function structureHistory(int $employeeId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('hr_salary_structures')
            ->where('employee_id', $employeeId)
            ->orderBy('effective_from', 'DESC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function revisions(int $employeeId): array
    {
        if (! $this->db->tableExists('hr_salary_revisions')) {
            return [];
        }

        return $this->db->table('hr_salary_revisions')
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** @param list<array{component_id: int, amount: float, percentage?: float|null}> $lines */
    public function saveStructure(int $employeeId, array $header, array $lines, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Salary tables missing.');
        }

        $old = $this->currentStructure($employeeId);
        if ($old) {
            $this->db->table('hr_salary_structures')->where('id', $old['id'])->update([
                'is_current' => 0,
                'status'     => 'superseded',
                'effective_to' => $header['effective_from'] ?? date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $totals = $this->calculateTotals($lines);
        $payload = [
            'employee_id'    => $employeeId,
            'company_id'     => $header['company_id'] ?? null,
            'effective_from' => $header['effective_from'] ?? date('Y-m-d'),
            'gross_salary'   => $totals['gross'],
            'net_salary'     => $totals['net'],
            'currency'       => $header['currency'] ?? 'QAR',
            'status'         => 'active',
            'is_current'     => 1,
            'remarks'        => $header['remarks'] ?? null,
            'created_by'     => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $structureId = fm_insert_row_id($this->db, 'hr_salary_structures', $payload);
        } else {
            $this->db->table('hr_salary_structures')->insert($payload);
            $structureId = (int) $this->db->insertID();
        }

        foreach ($lines as $i => $line) {
            $this->db->table('hr_salary_structure_lines')->insert([
                'structure_id' => $structureId,
                'component_id' => (int) $line['component_id'],
                'amount'       => (float) ($line['amount'] ?? 0),
                'percentage'   => isset($line['percentage']) ? (float) $line['percentage'] : null,
                'sort_order'   => $i + 1,
            ]);
        }

        if ($this->db->tableExists('hr_salary_revisions')) {
            $this->db->table('hr_salary_revisions')->insert([
                'employee_id'    => $employeeId,
                'structure_id'   => $structureId,
                'revision_type'  => $old ? 'revision' : 'initial',
                'old_gross'      => $old['gross_salary'] ?? null,
                'new_gross'      => $totals['gross'],
                'effective_date' => $payload['effective_from'],
                'reason'         => $header['remarks'] ?? null,
                'approved_by'    => $userId,
                'approved_at'    => date('Y-m-d H:i:s'),
                'snapshot'       => json_encode(['header' => $payload, 'lines' => $lines]),
            ]);
        }

        return $structureId;
    }

    /** @param list<array{component_id: int, amount: float, percentage?: float|null}> $lines */
    /** @return array{gross: float, net: float} */
    public function calculateTotals(array $lines): array
    {
        $components = [];
        foreach ($this->db->table('hr_salary_components')->get()->getResultArray() as $c) {
            $components[(int) $c['id']] = $c;
        }

        $gross = 0.0;
        $deductions = 0.0;
        foreach ($lines as $line) {
            $comp = $components[(int) $line['component_id']] ?? null;
            if (! $comp) {
                continue;
            }
            $amount = (float) ($line['amount'] ?? 0);
            if (($comp['component_type'] ?? '') === 'deduction') {
                $deductions += $amount;
            } else {
                $gross += $amount;
            }
        }

        return ['gross' => round($gross, 2), 'net' => round(max(0, $gross - $deductions), 2)];
    }

    /** @return list<array<string, mixed>> */
    private function structureLines(int $structureId): array
    {
        return $this->db->table('hr_salary_structure_lines l')
            ->select('l.*, c.code, c.name, c.component_type')
            ->join('hr_salary_components c', 'c.id = l.component_id', 'left')
            ->where('l.structure_id', $structureId)
            ->orderBy('l.sort_order')
            ->get()->getResultArray();
    }
}
