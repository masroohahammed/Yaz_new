<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrAttendanceService
{
    private BaseConnection $db;

    private HrShiftService $shifts;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db     = $db ?? \Config\Database::connect();
        $this->shifts = new HrShiftService($this->db);
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('attendance');
    }

    public function rawLogsReady(): bool
    {
        return $this->db->tableExists('hr_attendance_raw_logs');
    }

    public function regularizationsReady(): bool
    {
        return $this->db->tableExists('hr_attendance_regularizations');
    }

    /**
     * Record check-in/out with immutable raw log when M5 tables exist.
     *
     * @param  array<string, mixed>  $meta
     * @return array{status: bool, message: string, hours_worked?: float, overtime?: float}
     */
    public function recordPunch(int $employeeId, string $type, array $meta = []): array
    {
        if (! $this->tablesReady()) {
            return ['status' => false, 'message' => 'Attendance module unavailable.'];
        }

        $emp = $this->db->table('employees')->where('id', $employeeId)->get()->getRowArray();
        if (! $emp) {
            return ['status' => false, 'message' => 'Employee not found.'];
        }

        $today  = date('Y-m-d');
        $now    = date('Y-m-d H:i:s');
        $lat    = isset($meta['latitude']) ? (float) $meta['latitude'] : null;
        $lng    = isset($meta['longitude']) ? (float) $meta['longitude'] : null;
        $source = $meta['source'] ?? 'web';
        $shift  = $this->shifts->currentAssignment($employeeId);
        $shiftId = $shift['shift_id'] ?? null;
        $facilityId = $emp['facility_id'] ?? null;

        $rawLogId = null;
        if ($this->rawLogsReady()) {
            $this->db->table('hr_attendance_raw_logs')->insert([
                'employee_id' => $employeeId,
                'log_type'    => $type === 'check_out' ? 'check_out' : 'check_in',
                'logged_at'   => $now,
                'source'      => $source,
                'facility_id' => $facilityId,
                'latitude'    => $lat ?: null,
                'longitude'   => $lng ?: null,
                'device_id'   => $meta['device_id'] ?? null,
            ]);
            $rawLogId = (int) $this->db->insertID();
        }

        $record = $this->db->table('attendance')
            ->where('employee_id', $employeeId)
            ->where('date', $today)
            ->get()->getRowArray();

        if ($type === 'check_in') {
            if ($record && ! empty($record['check_in'])) {
                return ['status' => false, 'message' => 'Already checked in today.'];
            }

        $payload = [
            'employee_id' => $employeeId,
            'date'        => $today,
            'check_in'    => $now,
            'status'      => $this->resolveCheckInStatus($now, $shift),
            'latitude'    => $lat ?: null,
            'longitude'   => $lng ?: null,
        ];
        if ($this->db->fieldExists('facility_id', 'attendance')) {
            $payload['facility_id'] = $facilityId;
        }
        if ($this->db->fieldExists('shift_id', 'attendance')) {
            $payload['shift_id'] = $shiftId;
        }
        if ($this->db->fieldExists('attendance_source', 'attendance')) {
            $payload['attendance_source'] = $source;
        }
        if ($this->db->fieldExists('raw_log_in_id', 'attendance') && $rawLogId) {
            $payload['raw_log_in_id'] = $rawLogId;
        }

            if ($record) {
                $this->db->table('attendance')->where('id', $record['id'])->update($payload);
            } else {
                $payload['created_at'] = $now;
                $this->db->table('attendance')->insert($payload);
            }

            return ['status' => true, 'message' => 'Checked in at ' . date('H:i')];
        }

        if (! $record || empty($record['check_in'])) {
            return ['status' => false, 'message' => 'No check-in found for today.'];
        }

        $checkIn     = strtotime((string) $record['check_in']);
        $checkOut    = time();
        $hoursWorked = round(($checkOut - $checkIn) / 3600, 2);
        $shiftEnd    = $this->shiftEndTimestamp($today, $emp, $shift);
        $overtime    = max(0, round(($checkOut - $shiftEnd) / 3600, 2));

        $update = [
            'check_out'    => $now,
            'hours_worked' => $hoursWorked,
            'overtime_hrs' => $overtime,
            'early_reason' => trim((string) ($meta['reason'] ?? '')),
        ];
        if ($this->db->fieldExists('raw_log_out_id', 'attendance') && $rawLogId) {
            $update['raw_log_out_id'] = $rawLogId;
        }
        if ($this->db->fieldExists('attendance_source', 'attendance')) {
            $update['attendance_source'] = $source;
        }
        if ($this->db->fieldExists('shift_id', 'attendance') && empty($record['shift_id'])) {
            $update['shift_id'] = $shiftId;
        }

        $this->db->table('attendance')->where('id', $record['id'])->update($update);

        return [
            'status'       => true,
            'message'      => 'Checked out. Hours: ' . $hoursWorked,
            'hours_worked' => $hoursWorked,
            'overtime'     => $overtime,
        ];
    }

    /** @param array<string, mixed> $filters */
    public function listRecords(array $filters = [], int $limit = 200): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('attendance a')
            ->select('a.*, e.emp_code, u.name AS employee_name, f.name AS facility_name')
            ->join('employees e', 'e.id = a.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('facilities f', 'f.id = a.facility_id', 'left');

        if ($this->db->tableExists('hr_shifts') && $this->db->fieldExists('shift_id', 'attendance')) {
            $q->select('s.name AS shift_name', false)->join('hr_shifts s', 's.id = a.shift_id', 'left');
        }

        if (! empty($filters['employee_id'])) {
            $q->where('a.employee_id', (int) $filters['employee_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('a.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['month'])) {
            $q->like('a.date', (string) $filters['month'], 'after');
        }
        if (! empty($filters['company_id']) && $this->db->fieldExists('company_id', 'employees')) {
            $q->where('e.company_id', (int) $filters['company_id']);
        }

        return $q->orderBy('a.date', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /** @param array<string, mixed> $data */
    public function submitRegularization(array $data, int $userId): int
    {
        if (! $this->regularizationsReady()) {
            throw new \RuntimeException('Regularization tables missing.');
        }

        $data['requested_by'] = $userId;
        $data['status']       = 'pending';

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            return fm_insert_row_id($this->db, 'hr_attendance_regularizations', $data);
        }

        $this->db->table('hr_attendance_regularizations')->insert($data);

        return (int) $this->db->insertID();
    }

    /** @return list<array<string, mixed>> */
    public function pendingRegularizations(?int $companyId = null): array
    {
        if (! $this->regularizationsReady()) {
            return [];
        }

        $q = $this->db->table('hr_attendance_regularizations r')
            ->select('r.*, e.emp_code, u.name AS employee_name')
            ->join('employees e', 'e.id = r.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('r.status', 'pending')
            ->orderBy('r.created_at', 'ASC');

        if ($companyId && $this->db->fieldExists('company_id', 'employees')) {
            $q->where('e.company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }

    public function approveRegularization(int $id, int $reviewerId, ?string $notes = null): bool
    {
        $reg = $this->findRegularization($id);
        if (! $reg || $reg['status'] !== 'pending') {
            return false;
        }

        $attendanceId = $this->applyRegularizationToAttendance($reg);

        return $this->db->table('hr_attendance_regularizations')->where('id', $id)->update([
            'status'        => 'approved',
            'attendance_id' => $attendanceId,
            'reviewed_by'   => $reviewerId,
            'reviewed_at'   => date('Y-m-d H:i:s'),
            'review_notes'  => $notes,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function rejectRegularization(int $id, int $reviewerId, ?string $notes = null): bool
    {
        if (! $this->regularizationsReady()) {
            return false;
        }

        return $this->db->table('hr_attendance_regularizations')->where('id', $id)->where('status', 'pending')->update([
            'status'       => 'rejected',
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string, mixed> $data */
    public function manualAdjust(int $attendanceId, array $data, int $userId): bool
    {
        $row = $this->db->table('attendance')->where('id', $attendanceId)->get()->getRowArray();
        if (! $row) {
            return false;
        }

        $checkIn  = $data['check_in'] ?? $row['check_in'];
        $checkOut = $data['check_out'] ?? $row['check_out'];
        $hours    = null;
        if ($checkIn && $checkOut) {
            $hours = round((strtotime((string) $checkOut) - strtotime((string) $checkIn)) / 3600, 2);
        }

        $update = array_filter([
            'check_in'      => $checkIn,
            'check_out'     => $checkOut,
            'status'        => $data['status'] ?? $row['status'],
            'notes'         => $data['notes'] ?? $row['notes'] ?? null,
            'hours_worked'  => $hours,
        ], static fn ($v) => $v !== null);
        if ($this->db->fieldExists('attendance_source', 'attendance')) {
            $update['attendance_source'] = 'manual';
        }
        if ($this->db->fieldExists('supervisor_id', 'attendance')) {
            $update['supervisor_id'] = $userId;
        }

        return $this->db->table('attendance')->where('id', $attendanceId)->update($update);
    }

    public function findRegularization(int $id): ?array
    {
        if (! $this->regularizationsReady()) {
            return null;
        }

        $row = $this->db->table('hr_attendance_regularizations')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }

    /** @param array<string, mixed> $reg */
    private function applyRegularizationToAttendance(array $reg): int
    {
        $employeeId = (int) $reg['employee_id'];
        $date       = (string) $reg['attendance_date'];

        $existing = $this->db->table('attendance')
            ->where('employee_id', $employeeId)
            ->where('date', $date)
            ->get()->getRowArray();

        $payload = [
            'employee_id'       => $employeeId,
            'date'              => $date,
            'check_in'          => $reg['requested_check_in'] ?? ($existing['check_in'] ?? null),
            'check_out'         => $reg['requested_check_out'] ?? ($existing['check_out'] ?? null),
            'status'            => $reg['requested_status'] ?? ($existing['status'] ?? 'present'),
        ];
        if ($this->db->fieldExists('regularization_id', 'attendance')) {
            $payload['regularization_id'] = (int) $reg['id'];
        }
        if ($this->db->fieldExists('attendance_source', 'attendance')) {
            $payload['attendance_source'] = 'regularization';
        }

        if ($payload['check_in'] && $payload['check_out']) {
            $payload['hours_worked'] = round((strtotime((string) $payload['check_out']) - strtotime((string) $payload['check_in'])) / 3600, 2);
        }

        if ($existing) {
            $this->db->table('attendance')->where('id', $existing['id'])->update($payload);

            return (int) $existing['id'];
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('attendance')->insert($payload);

        return (int) $this->db->insertID();
    }

    /** @param array<string, mixed>|null $shift */
    private function resolveCheckInStatus(string $checkIn, ?array $shift): string
    {
        if (! $shift || empty($shift['start_time'])) {
            return 'present';
        }

        $grace = (int) ($shift['grace_in_minutes'] ?? 0);
        $start = strtotime(date('Y-m-d') . ' ' . $shift['start_time']);
        $in    = strtotime($checkIn);

        return $in > ($start + ($grace * 60)) ? 'late' : 'present';
    }

    /** @param array<string, mixed> $emp */
    /** @param array<string, mixed>|null $shift */
    private function shiftEndTimestamp(string $date, array $emp, ?array $shift): int
    {
        $end = $shift['end_time'] ?? ($emp['shift_end'] ?? '17:00:00');

        return strtotime($date . ' ' . substr((string) $end, 0, 8));
    }
}
