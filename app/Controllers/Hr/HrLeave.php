<?php

namespace App\Controllers\Hr;

use App\Services\Hr\LeaveService;

class HrLeave extends HrBaseController
{
    public function index()
    {
        $this->requireHrAccess();
        $status = trim((string) $this->request->getGet('status'));

        $service = new LeaveService($this->db);
        $types   = $this->db->tableExists('hr_leave_types')
            ? $this->db->table('hr_leave_types')->where('status', 'active')->get()->getResultArray()
            : [];

        return view('hr/leave/index', $this->viewData([
            'title'    => 'Leave Management',
            'hrActive' => 'leave',
            'requests' => $service->listRequests(['status' => $status]),
            'types'    => $types,
            'status'   => $status,
        ]));
    }

    public function create()
    {
        $this->requireHrAccess();
        $types = $this->db->tableExists('hr_leave_types')
            ? $this->db->table('hr_leave_types')->where('status', 'active')->get()->getResultArray()
            : [];
        $employees = $this->db->tableExists('employee_profiles')
            ? $this->db->table('employee_profiles ep')->select('ep.user_id, u.name')->join('users u', 'u.id = ep.user_id')->where('ep.deleted_at', null)->get()->getResultArray()
            : [];

        return view('hr/leave/form', $this->viewData([
            'title'     => 'Request Leave',
            'hrActive'  => 'leave',
            'types'     => $types,
            'employees' => $employees,
        ]));
    }

    public function store()
    {
        $this->requireHrAccess();
        $userId = (int) $this->request->getPost('user_id') ?: (int) session()->get('user_id');
        $service = new LeaveService($this->db);
        $service->submit($userId, [
            'leave_type_id' => $this->request->getPost('leave_type_id'),
            'start_date'    => $this->request->getPost('start_date'),
            'end_date'      => $this->request->getPost('end_date'),
            'reason'        => $this->request->getPost('reason'),
        ]);

        return redirect()->to(base_url('hr/leave'))->with('success', 'Leave request submitted.');
    }

    public function approve(int $id)
    {
        $this->requireHrAccess();
        (new LeaveService($this->db))->approve($id, (int) session()->get('user_id'));

        return redirect()->back()->with('success', 'Leave approved.');
    }

    public function reject(int $id)
    {
        $this->requireHrAccess();
        (new LeaveService($this->db))->reject($id, (int) session()->get('user_id'), (string) $this->request->getPost('reason'));

        return redirect()->back()->with('success', 'Leave rejected.');
    }
}
