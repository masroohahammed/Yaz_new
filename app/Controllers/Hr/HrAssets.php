<?php

namespace App\Controllers\Hr;

class HrAssets extends HrBaseController
{
    public function index()
    {
        $this->requireHrAccess();
        $assets = [];
        if ($this->db->tableExists('hr_employee_assets')) {
            $assets = $this->db->table('hr_employee_assets a')
                ->select('a.*, u.name AS employee_name')
                ->join('users u', 'u.id = a.user_id', 'left')
                ->orderBy('a.assigned_date', 'DESC')
                ->limit(200)->get()->getResultArray();
        }

        return view('hr/assets/index', $this->viewData([
            'title'    => 'Employee Assets',
            'hrActive' => 'assets',
            'assets'   => $assets,
        ]));
    }

    public function create()
    {
        $this->requireHrAccess();
        $employees = $this->db->table('employee_profiles ep')
            ->select('ep.id, ep.user_id, u.name')
            ->join('users u', 'u.id = ep.user_id')
            ->where('ep.deleted_at', null)
            ->get()->getResultArray();

        return view('hr/assets/form', $this->viewData([
            'title'     => 'Assign Asset',
            'hrActive'  => 'assets',
            'employees' => $employees,
        ]));
    }

    public function store()
    {
        $this->requireHrAccess();
        if (! $this->db->tableExists('hr_employee_assets')) {
            return redirect()->back()->with('error', 'Run pm_hrms_modules_patch.sql');
        }

        $userId = (int) $this->request->getPost('user_id');
        $this->db->table('hr_employee_assets')->insert([
            'user_id'       => $userId,
            'profile_id'    => $this->profileIdForUser($userId),
            'asset_type'    => $this->request->getPost('asset_type'),
            'asset_tag'     => esc($this->request->getPost('asset_tag')),
            'description'   => esc($this->request->getPost('description')),
            'serial_number' => esc($this->request->getPost('serial_number')),
            'assigned_date' => $this->request->getPost('assigned_date') ?: date('Y-m-d'),
            'status'        => 'assigned',
            'notes'         => esc($this->request->getPost('notes')),
            'assigned_by'   => (int) session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('hr/assets'))->with('success', 'Asset assigned.');
    }

    public function return(int $id)
    {
        $this->requireHrAccess();
        $this->db->table('hr_employee_assets')->where('id', $id)->update([
            'status'      => 'returned',
            'return_date' => date('Y-m-d'),
        ]);

        return redirect()->back()->with('success', 'Asset marked as returned.');
    }
}
