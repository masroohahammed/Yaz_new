<?php

namespace App\Controllers\Hr;

class HrPerformance extends HrBaseController
{
    public function index()
    {
        $this->requireHrAccess();
        $reviews = [];
        $goals   = [];
        if ($this->db->tableExists('hr_performance_reviews')) {
            $reviews = $this->db->table('hr_performance_reviews pr')
                ->select('pr.*, u.name AS employee_name, r.name AS reviewer_name')
                ->join('users u', 'u.id = pr.user_id', 'left')
                ->join('users r', 'r.id = pr.reviewer_user_id', 'left')
                ->orderBy('pr.created_at', 'DESC')
                ->limit(100)->get()->getResultArray();
        }
        if ($this->db->tableExists('hr_performance_goals')) {
            $goals = $this->db->table('hr_performance_goals pg')
                ->select('pg.*, u.name AS employee_name')
                ->join('users u', 'u.id = pg.user_id', 'left')
                ->orderBy('pg.due_date', 'ASC')
                ->limit(100)->get()->getResultArray();
        }

        return view('hr/performance/index', $this->viewData([
            'title'    => 'Performance',
            'hrActive' => 'performance',
            'reviews'  => $reviews,
            'goals'    => $goals,
        ]));
    }

    public function createReview()
    {
        $this->requireHrAccess();
        $employees = $this->db->table('employee_profiles ep')
            ->select('ep.user_id, u.name')
            ->join('users u', 'u.id = ep.user_id')
            ->where('ep.deleted_at', null)
            ->get()->getResultArray();

        return view('hr/performance/review_form', $this->viewData([
            'title'     => 'Performance Review',
            'hrActive'  => 'performance',
            'employees' => $employees,
        ]));
    }

    public function storeReview()
    {
        $this->requireHrAccess();
        if (! $this->db->tableExists('hr_performance_reviews')) {
            return redirect()->back()->with('error', 'Run pm_hrms_modules_patch.sql');
        }

        $this->db->table('hr_performance_reviews')->insert([
            'user_id'          => (int) $this->request->getPost('user_id'),
            'reviewer_user_id' => (int) session()->get('user_id'),
            'period_label'     => esc($this->request->getPost('period_label')),
            'rating'           => (float) $this->request->getPost('rating'),
            'strengths'        => esc($this->request->getPost('strengths')),
            'improvements'     => esc($this->request->getPost('improvements')),
            'comments'         => esc($this->request->getPost('comments')),
            'status'           => 'submitted',
            'review_date'      => $this->request->getPost('review_date') ?: date('Y-m-d'),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('hr/performance'))->with('success', 'Review saved.');
    }

    public function createGoal()
    {
        $this->requireHrAccess();
        $employees = $this->db->table('employee_profiles ep')
            ->select('ep.user_id, u.name')
            ->join('users u', 'u.id = ep.user_id')
            ->where('ep.deleted_at', null)
            ->get()->getResultArray();

        return view('hr/performance/goal_form', $this->viewData([
            'title'     => 'Set Goal / KPI',
            'hrActive'  => 'performance',
            'employees' => $employees,
        ]));
    }

    public function storeGoal()
    {
        $this->requireHrAccess();
        if (! $this->db->tableExists('hr_performance_goals')) {
            return redirect()->back()->with('error', 'Run pm_hrms_modules_patch.sql');
        }

        $this->db->table('hr_performance_goals')->insert([
            'user_id'      => (int) $this->request->getPost('user_id'),
            'title'        => esc($this->request->getPost('title')),
            'kpi_target'   => esc($this->request->getPost('kpi_target')),
            'due_date'     => $this->request->getPost('due_date'),
            'progress_pct' => (int) $this->request->getPost('progress_pct'),
            'status'       => 'active',
            'created_by'   => (int) session()->get('user_id'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('hr/performance'))->with('success', 'Goal created.');
    }
}
