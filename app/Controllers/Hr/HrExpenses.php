<?php

namespace App\Controllers\Hr;

use App\Services\Hr\HrExpenseService;

class HrExpenses extends HrBaseController
{
    public function index()
    {
        $this->requireHrAccess();
        $status = trim((string) $this->request->getGet('status'));

        return view('hr/expenses/index', $this->viewData([
            'title'    => 'HR Expense Claims',
            'hrActive' => 'expenses',
            'claims'   => (new HrExpenseService($this->db))->list(['status' => $status]),
            'status'   => $status,
        ]));
    }

    public function create()
    {
        $this->requireHrAccess();

        return view('hr/expenses/form', $this->viewData([
            'title'    => 'Submit Expense Claim',
            'hrActive' => 'expenses',
        ]));
    }

    public function store()
    {
        $this->requireHrAccess();
        $userId = (int) session()->get('user_id');
        $receiptPath = null;
        $file = $this->request->getFile('receipt');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $dir = WRITEPATH . 'uploads/hr_expenses';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $name = $file->getRandomName();
            $file->move($dir, $name);
            $receiptPath = 'hr_expenses/' . $name;
        }

        (new HrExpenseService($this->db))->store($userId, [
            'title'        => $this->request->getPost('title'),
            'category'     => $this->request->getPost('category'),
            'amount'       => $this->request->getPost('amount'),
            'expense_date' => $this->request->getPost('expense_date'),
            'description'  => $this->request->getPost('description'),
        ], $receiptPath, $this->profileIdForUser($userId));

        return redirect()->to(base_url('hr/expenses'))->with('success', 'Expense claim submitted.');
    }

    public function approve(int $id)
    {
        $this->requireHrAccess();
        $ok = (new HrExpenseService($this->db))->approve($id, (int) session()->get('user_id'));

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Claim approved and posted to GL.' : 'Could not approve claim.');
    }

    public function reject(int $id)
    {
        $this->requireHrAccess();
        (new HrExpenseService($this->db))->reject($id, (int) session()->get('user_id'));

        return redirect()->back()->with('success', 'Claim rejected.');
    }
}
