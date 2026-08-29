<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Custom report builder — dynamic columns and filters.
 */
class ReportBuilderService
{
    public const TYPES = [
        'workorders'  => 'Work Orders',
        'helpdesk'    => 'Complaints',
        'jobcards'    => 'Job Cards',
        'invoices'    => 'Invoices',
        'customer'    => 'Customer Invoices',
        'profit'      => 'Profit / Costing',
        'qc'          => 'QC / QA',
        'technician'  => 'Technicians',
        'activity'    => 'Activity Log',
    ];

    public const COLUMNS = [
        'workorders' => ['wo_number', 'title', 'facility_name', 'unit_number', 'status', 'priority', 'assigned_name', 'created_at', 'completed_at', 'actual_cost', 'qa_status'],
        'helpdesk'   => ['ticket_number', 'requester_name', 'facility_name', 'unit_number', 'category', 'priority', 'status', 'created_at'],
        'jobcards'   => ['jc_number', 'wo_number', 'technician_name', 'facility_name', 'unit_number', 'status', 'labor_hours', 'completed_at'],
        'invoices'   => ['invoice_number', 'facility_name', 'unit_number', 'issue_date', 'total', 'status', 'invoice_type'],
        'customer'   => ['invoice_number', 'client_name', 'client_email', 'facility_name', 'contract_number', 'issue_date', 'due_date', 'subtotal', 'vat_amount', 'total', 'status', 'invoice_type'],
        'profit'     => ['wo_number', 'facility_name', 'total_cost', 'revenue', 'profit'],
        'qc'         => ['wo_number', 'qa_status', 'qa_approved_at', 'client_approval_status', 'facility_name'],
        'technician' => ['name', 'total_assigned', 'completed', 'sla_breached'],
        'activity'   => ['created_at', 'user_name', 'action', 'module', 'description'],
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headers: list<string>, rows: list<list<string|float>>}
     */
    public function run(string $type, array $columns, array $filters = [], bool $showCost = false): array
    {
        $from     = $filters['from']     ?? date('Y-m-01');
        $to       = $filters['to']       ?? date('Y-m-d');
        $facility = (int) ($filters['facility'] ?? 0);
        $unit     = (int) ($filters['unit']     ?? 0);          // ← new
        $headers  = [];
        $rows     = [];

        switch ($type) {
            case 'workorders':
                $headers = $this->pickHeaders($columns, self::COLUMNS['workorders']);
                $q = $this->db->table('work_orders w')
                    ->select('w.*, f.name as facility_name, u.name as assigned_name, un.unit_number')
                    ->join('facilities f', 'f.id = w.facility_id', 'left')
                    ->join('users u', 'u.id = w.assigned_to', 'left')
                    ->join('units un', 'un.id = w.unit_id', 'left')
                    ->where('DATE(w.created_at) >=', $from)
                    ->where('DATE(w.created_at) <=', $to);
                if ($facility) { $q->where('w.facility_id', $facility); }
                if ($unit)     { $q->where('w.unit_id', $unit); }
                foreach ($q->get()->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['workorders'], $showCost);
                }
                break;

            case 'profit':
                $headers = $this->pickHeaders($columns, self::COLUMNS['profit']);
                if ($this->db->tableExists('maintenance_costing')) {
                    $q = $this->db->table('maintenance_costing mc')
                        ->select('mc.*, w.wo_number, f.name as facility_name')
                        ->join('work_orders w', 'w.id = mc.wo_id', 'left')
                        ->join('facilities f', 'f.id = w.facility_id', 'left');
                    if ($facility) { $q->where('w.facility_id', $facility); }
                    foreach ($q->get()->getResultArray() as $r) {
                        $r['total_cost'] = $r['total_cost'] ?? 0;
                        $r['profit']     = $r['profit'] ?? 0;
                        $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['profit'], true);
                    }
                }
                break;

            case 'qc':
                $headers = $this->pickHeaders($columns, self::COLUMNS['qc']);
                $q = $this->db->table('work_orders w')
                    ->select('w.wo_number, w.qa_status, w.qa_approved_at, w.client_approval_status, f.name as facility_name')
                    ->join('facilities f', 'f.id = w.facility_id', 'left')
                    ->whereIn('w.qa_status', ['pending', 'approved', 'rejected'])
                    ->where('DATE(w.updated_at) >=', $from)
                    ->where('DATE(w.updated_at) <=', $to);
                if ($facility) { $q->where('w.facility_id', $facility); }
                foreach ($q->get()->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['qc'], $showCost);
                }
                break;

            case 'helpdesk':
                $headers = $this->pickHeaders($columns, self::COLUMNS['helpdesk']);
                $q = $this->db->table('maintenance_requests mr')
                    ->select('mr.*, f.name as facility_name, un.unit_number')
                    ->join('facilities f', 'f.id = mr.facility_id', 'left')
                    ->join('units un', 'un.id = mr.unit_id', 'left')
                    ->where('DATE(mr.created_at) >=', $from)
                    ->where('DATE(mr.created_at) <=', $to);
                if ($facility) { $q->where('mr.facility_id', $facility); }
                if ($unit)     { $q->where('mr.unit_id', $unit); }
                foreach ($q->get()->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['helpdesk'], $showCost);
                }
                break;

            case 'jobcards':
                $headers = $this->pickHeaders($columns, self::COLUMNS['jobcards']);
                $q = $this->db->table('job_cards jc')
                    ->select('jc.jc_number, jc.status, jc.labor_hours, jc.completed_at, w.wo_number, f.name as facility_name, un.unit_number, u.name as technician_name')
                    ->join('work_orders w', 'w.id = jc.wo_id', 'left')
                    ->join('facilities f', 'f.id = w.facility_id', 'left')
                    ->join('units un', 'un.id = w.unit_id', 'left')
                    ->join('users u', 'u.id = jc.assigned_to', 'left')
                    ->where('DATE(COALESCE(jc.completed_at, jc.created_at)) >=', $from)
                    ->where('DATE(COALESCE(jc.completed_at, jc.created_at)) <=', $to);
                if ($this->db->fieldExists('deleted_at', 'job_cards')) {
                    $q->where('jc.deleted_at', null);
                }
                if ($facility) { $q->where('w.facility_id', $facility); }
                if ($unit)     { $q->where('w.unit_id', $unit); }
                foreach ($q->get()->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['jobcards'], $showCost);
                }
                break;

            case 'invoices':
                $headers = $this->pickHeaders($columns, self::COLUMNS['invoices']);
                $q = $this->db->table('invoices i')
                    ->select('i.invoice_number, i.issue_date, i.total, i.status, i.invoice_type, f.name as facility_name, un.unit_number')
                    ->join('facilities f', 'f.id = i.facility_id', 'left')
                    ->join('work_orders w', 'w.id = i.work_order_id', 'left')
                    ->join('units un', 'un.id = w.unit_id', 'left')
                    ->where('DATE(i.issue_date) >=', $from)
                    ->where('DATE(i.issue_date) <=', $to);
                if ($facility) { $q->where('i.facility_id', $facility); }
                if ($unit)     { $q->where('w.unit_id', $unit); }
                foreach ($q->get()->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['invoices'], $showCost);
                }
                break;

            case 'customer':
                $headers = $this->pickHeaders($columns, self::COLUMNS['customer']);
                $q = $this->db->table('invoices i')
                    ->select('i.invoice_number, i.issue_date, i.due_date, i.subtotal, i.vat_amount, i.total, i.status, i.invoice_type,
                              f.name as facility_name, c.contract_number, c.client_email,
                              COALESCE(cu.name, c.client_email, \'\') as client_name')
                    ->join('facilities f',  'f.id = i.facility_id', 'left')
                    ->join('contracts c',   'c.id = i.contract_id', 'left')
                    ->join('users cu',      'cu.id = i.created_by', 'left')
                    ->where('DATE(i.issue_date) >=', $from)
                    ->where('DATE(i.issue_date) <=', $to);
                if ($facility) { $q->where('i.facility_id', $facility); }
                foreach ($q->get()->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['customer'], $showCost);
                }
                break;

            case 'technician':
                $headers = $this->pickHeaders($columns, self::COLUMNS['technician']);
                $facilityCondition = $facility ? "AND wo.facility_id = {$facility}" : '';
                $sql = "SELECT u.name,
                    COUNT(wo.id) AS total_assigned,
                    SUM(CASE WHEN wo.status = 'completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(wo.sla_breached) AS sla_breached
                    FROM users u
                    JOIN roles r ON r.id = u.role_id AND r.name = 'technician'
                    LEFT JOIN work_orders wo ON wo.assigned_to = u.id
                      AND DATE(wo.created_at) >= ? AND DATE(wo.created_at) <= ?
                      {$facilityCondition}
                    GROUP BY u.id, u.name ORDER BY completed DESC";
                foreach ($this->db->query($sql, [$from, $to])->getResultArray() as $r) {
                    $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['technician'], $showCost);
                }
                break;

            case 'activity':
                $headers = $this->pickHeaders($columns, self::COLUMNS['activity']);
                if ($this->db->tableExists('activity_logs')) {
                    $q = $this->db->table('activity_logs al')
                        ->select('al.created_at, al.action, al.module, al.description, u.name as user_name')
                        ->join('users u', 'u.id = al.user_id', 'left')
                        ->where('DATE(al.created_at) >=', $from)
                        ->where('DATE(al.created_at) <=', $to)
                        ->orderBy('al.created_at', 'DESC')
                        ->limit(5000);
                    foreach ($q->get()->getResultArray() as $r) {
                        $rows[] = $this->rowFromMap($r, $columns, self::COLUMNS['activity'], $showCost);
                    }
                }
                break;

            default:
                $headers = ['Message'];
                $rows[]  = ['Unknown report type.'];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** @param  list<string>  $columns */
    private function pickHeaders(array $columns, array $allowed): array
    {
        $out = [];
        foreach ($columns as $c) {
            if (in_array($c, $allowed, true)) {
                $out[] = ucwords(str_replace('_', ' ', $c));
            }
        }

        return $out ?: array_map(fn ($c) => ucwords(str_replace('_', ' ', $c)), $allowed);
    }

    /** @return list<string|float> */
    private function rowFromMap(array $r, array $columns, array $allowed, bool $showCost): array
    {
        $row = [];
        foreach ($columns as $c) {
            if (! in_array($c, $allowed, true)) {
                continue;
            }
            if (! $showCost && str_contains($c, 'cost') && ! str_contains($c, 'total_cost')) {
                $row[] = '—';
                continue;
            }
            $row[] = $r[$c] ?? '';
        }

        return $row;
    }
}
