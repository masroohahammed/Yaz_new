<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;

class Dashboard extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    public function index()
    {
        $this->requireHrPermission('hr.dashboard');

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $stats     = $this->buildStats($companyId);

        return view('hr/dashboard/index', $this->viewData([
            'title'    => 'HR Dashboard',
            'stats'    => $stats,
            'hrNavActive' => 'dashboard',
            'quickLinks' => $this->quickLinks(),
        ]));
    }

    /** @return array<string, int|string> */
    private function buildStats(?int $companyId): array
    {
        $stats = [
            'employees_active'  => 0,
            'leave_pending'     => 0,
            'doc_expiring'      => 0,
            'contracts_expiring'=> 0,
            'payroll_draft'     => 0,
            'approvals_pending' => 0,
        ];

        if ($this->db->tableExists('employees')) {
            $q = $this->db->table('employees')->where('status', 'active');
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            $stats['employees_active'] = $q->countAllResults();
        }

        if ($this->db->tableExists('hr_leave_requests')) {
            $q = $this->db->table('hr_leave_requests lr')
                ->join('employees e', 'e.id = lr.employee_id', 'left')
                ->where('lr.status', 'pending');
            if ($companyId) {
                $q->where('e.company_id', $companyId);
            }
            $stats['leave_pending'] = $q->countAllResults();
        }

        if ($this->db->tableExists('documents')) {
            $q = $this->db->table('documents')
                ->where('module', 'employee')
                ->where('expiry_date IS NOT NULL')
                ->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
                ->where('expiry_date >=', date('Y-m-d'));
            $stats['doc_expiring'] = $q->countAllResults();
        }

        if ($this->db->tableExists('hr_employment_contracts')) {
            $endCol = $this->db->fieldExists('contract_end_date', 'hr_employment_contracts')
                ? 'contract_end_date'
                : ($this->db->fieldExists('end_date', 'hr_employment_contracts') ? 'end_date' : null);

            if ($endCol !== null) {
                $q = $this->db->table('hr_employment_contracts')
                    ->where('contract_status', 'active')
                    ->where($endCol . ' IS NOT NULL', null, false)
                    ->where($endCol . ' <=', date('Y-m-d', strtotime('+60 days')))
                    ->where($endCol . ' >=', date('Y-m-d'));
                if ($companyId) {
                    $q->where('company_id', $companyId);
                }
                $stats['contracts_expiring'] = $q->countAllResults();
            }
        }

        if ($this->db->tableExists('hr_payroll_runs')) {
            $q = $this->db->table('hr_payroll_runs')->whereIn('status', ['draft', 'calculated']);
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            $stats['payroll_draft'] = $q->countAllResults();
        }

        $pending = 0;
        if ($this->db->tableExists('hr_employee_transfers')) {
            $q = $this->db->table('hr_employee_transfers')->where('status', 'pending');
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            $pending += $q->countAllResults();
        }
        if ($this->db->tableExists('hr_employee_requests')) {
            $q = $this->db->table('hr_employee_requests')->whereIn('status', ['pending', 'submitted']);
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            $pending += $q->countAllResults();
        }
        $stats['approvals_pending'] = $pending;

        return $stats;
    }

    /** @return list<array{label: string, href: string, icon: string, perm: string, desc: string}> */
    private function quickLinks(): array
    {
        return [
            ['label' => 'Employees', 'href' => 'employees', 'icon' => 'bi-people', 'perm' => 'employees', 'desc' => 'Workforce master'],
            ['label' => 'Attendance', 'href' => 'hr/attendance', 'icon' => 'bi-clock-history', 'perm' => 'attendance.view', 'desc' => 'Reports & regularizations'],
            ['label' => 'Leave', 'href' => 'hr/leave', 'icon' => 'bi-calendar2-week', 'perm' => 'leave.view', 'desc' => 'Balances & requests'],
            ['label' => 'Payroll', 'href' => 'hr/payroll', 'icon' => 'bi-calculator', 'perm' => 'payroll.process', 'desc' => 'Monthly payroll runs'],
            ['label' => 'Manpower', 'href' => 'hr/manpower', 'icon' => 'bi-diagram-3', 'perm' => 'manpower.view', 'desc' => 'Planning vs assignments'],
            ['label' => 'Onboarding', 'href' => 'hr/onboarding', 'icon' => 'bi-person-plus', 'perm' => 'employee.edit', 'desc' => 'New hire checklists'],
            ['label' => 'HR Settings', 'href' => 'hr/settings', 'icon' => 'bi-gear', 'perm' => 'hr.settings', 'desc' => 'Masters & configuration'],
        ];
    }
}
