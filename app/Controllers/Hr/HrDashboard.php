<?php

namespace App\Controllers\Hr;

class HrDashboard extends HrBaseController
{
    protected ?string $workspaceRequired = null;

    public function index()
    {
        $this->requireHrAccess();

        $stats = [
            'total_employees'   => 0,
            'active'            => 0,
            'on_leave'          => 0,
            'new_joiners'       => 0,
            'pending_approvals' => 0,
            'expiring_docs'     => 0,
        ];

        if ($this->db->tableExists('employee_profiles')) {
            $stats['total_employees'] = $this->db->table('employee_profiles')->where('deleted_at', null)->countAllResults();
            $stats['active']          = $this->db->table('employee_profiles')->where('status', 'active')->where('deleted_at', null)->countAllResults();
            $stats['on_leave']        = $this->db->table('employee_profiles')->where('status', 'on_leave')->where('deleted_at', null)->countAllResults();
            $stats['new_joiners']     = $this->db->table('employee_profiles')
                ->where('hire_date >=', date('Y-m-01'))
                ->where('deleted_at', null)
                ->countAllResults();
        }

        if ($this->db->tableExists('hr_leave_requests')) {
            $stats['pending_approvals'] += $this->db->table('hr_leave_requests')->where('status', 'pending')->countAllResults();
        }

        if ($this->db->tableExists('hr_expense_claims')) {
            $stats['pending_approvals'] += $this->db->table('hr_expense_claims')->where('status', 'pending')->countAllResults();
        }

        if ($this->db->tableExists('pm_salary_runs')) {
            $stats['pending_approvals'] += $this->db->table('pm_salary_runs')->where('status', 'draft')->countAllResults();
        }

        if ($this->db->tableExists('documents')) {
            $stats['expiring_docs'] = $this->db->table('documents')
                ->where('expiry_date >=', date('Y-m-d'))
                ->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
                ->countAllResults();
        }

        return view('hr/dashboard', $this->viewData([
            'title'    => 'HR Dashboard',
            'hrActive' => 'dashboard',
            'stats'    => $stats,
        ]));
    }
}
