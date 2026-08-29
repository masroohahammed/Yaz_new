<?php

namespace App\Controllers\Hr;

use App\Services\Hr\PayrollService;

class HrPayroll extends HrBaseController
{
    public function index()
    {
        $this->requireHrAccess();
        $month = trim((string) $this->request->getGet('month')) ?: date('Y-m');
        $service = new PayrollService($this->db);

        return view('hr/payroll/index', $this->viewData([
            'title'    => 'Payroll',
            'hrActive' => 'payroll',
            'month'    => $month,
            'runs'     => $service->listRuns($month),
        ]));
    }

    public function generate()
    {
        $this->requireHrAccess();
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $month = trim((string) $this->request->getPost('month')) ?: date('Y-m');
        $count = (new PayrollService($this->db))->generateMonth($month, (int) session()->get('user_id'));

        return redirect()->to(base_url('hr/payroll?month=' . urlencode($month)))->with('success', "{$count} payroll run(s) generated.");
    }

    public function approve(int $id)
    {
        $this->requireHrAccess();
        $ok = (new PayrollService($this->db))->approve($id, (int) session()->get('user_id'));

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Payroll approved and posted to GL.' : 'Could not approve payroll run.');
    }

    public function pay(int $id)
    {
        $this->requireHrAccess();
        $ok = (new PayrollService($this->db))->markPaid($id, (int) session()->get('user_id'));

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Marked paid and GL payment posted.' : 'Could not mark payroll as paid.');
    }
}
