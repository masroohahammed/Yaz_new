<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\HrPayrollService;

class Payroll extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrPayrollService $payroll;

    public function __construct()
    {
        $this->payroll = new HrPayrollService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('payroll.process');

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;

        return view('hr/payroll/index', $this->viewData([
            'title'             => 'Payroll Runs',
            'runs'              => $this->payroll->listRuns($companyId),
            'statuses'          => HrPayrollService::STATUSES,
            'branches'          => $this->branches($companyId),
            'migrationRequired' => ! $this->payroll->tablesReady(),
            'canApprove'        => $this->hrCan('payroll.approve'),
            'canUnlock'         => $this->hrCan('payroll.unlock'),
        ]));
    }

    public function view(int $id)
    {
        $this->requireHrPermission('payroll.process');

        $run = $this->payroll->findRun($id);
        if (! $run) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $validation = ! empty($run['validation_summary']) ? json_decode($run['validation_summary'], true) : [];

        return view('hr/payroll/view', $this->viewData([
            'title'      => 'Payroll ' . ($run['run_number'] ?? $id),
            'run'        => $run,
            'lines'      => $this->payroll->linesForRun($id),
            'validation' => is_array($validation) ? $validation : [],
            'statuses'   => HrPayrollService::STATUSES,
            'canApprove' => $this->hrCan('payroll.approve'),
            'canUnlock'  => $this->hrCan('payroll.unlock'),
        ]));
    }

    public function create()
    {
        $this->requireHrPermission('payroll.process');
        if (! $this->request->is('post') || ! $this->payroll->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $period    = $this->request->getPost('period_month') ?: date('Y-m');

        try {
            $runId = $this->payroll->createRun([
                'company_id'   => $companyId,
                'branch_id'    => (int) ($this->request->getPost('branch_id') ?: 0) ?: null,
                'period_start' => date('Y-m-01', strtotime($period . '-01')),
                'period_end'   => date('Y-m-t', strtotime($period . '-01')),
                'pay_date'     => $this->request->getPost('pay_date') ?: date('Y-m-t', strtotime($period . '-01')),
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/payroll/view/' . $runId))->with('success', 'Payroll run created. Click Calculate to process employees.');
    }

    public function calculate(int $id)
    {
        $this->requireHrPermission('payroll.process');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        try {
            $result = $this->payroll->calculateRun($id);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $msg = sprintf(
            'Calculated %d employees — gross %s, net %s.',
            $result['employee_count'],
            number_format($result['total_gross'], 2),
            number_format($result['total_net'], 2)
        );

        if (! empty($result['errors'])) {
            $msg .= ' ' . count($result['errors']) . ' error(s).';
        }

        return redirect()->to(base_url('hr/payroll/view/' . $id))->with('success', $msg);
    }

    public function approve(int $id)
    {
        $this->requireHrPermission('payroll.approve');
        if (! $this->payroll->approveRun($id, (int) session()->get('user_id'))) {
            return redirect()->back()->with('error', 'Could not approve payroll run.');
        }

        return redirect()->to(base_url('hr/payroll/view/' . $id))->with('success', 'Payroll approved.');
    }

    public function lock(int $id)
    {
        $this->requireHrPermission('payroll.approve');
        if (! $this->payroll->lockRun($id, (int) session()->get('user_id'))) {
            return redirect()->back()->with('error', 'Could not lock payroll run.');
        }

        return redirect()->to(base_url('hr/payroll/view/' . $id))->with('success', 'Payroll locked — no further recalculation.');
    }

    public function unlock(int $id)
    {
        $this->requireHrPermission('payroll.unlock');
        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->with('error', 'Unlock reason is required.');
        }

        if (! $this->payroll->unlockRun($id, (int) session()->get('user_id'), $reason)) {
            return redirect()->back()->with('error', 'Could not unlock payroll run.');
        }

        return redirect()->to(base_url('hr/payroll/view/' . $id))->with('success', 'Payroll unlocked (audited).');
    }

    public function postGl(int $id)
    {
        $this->requireHrPermission('payroll.approve');
        $jid = $this->payroll->postToGl($id, (int) session()->get('user_id'));
        if (! $jid) {
            return redirect()->back()->with('error', 'GL posting failed — check finance module and account mapping.');
        }

        return redirect()->to(base_url('hr/payroll/view/' . $id))->with('success', 'Posted to GL (journal #' . $jid . ').');
    }

    public function cancel(int $id)
    {
        $this->requireHrPermission('payroll.process');
        if (! $this->payroll->cancelRun($id)) {
            return redirect()->back()->with('error', 'Could not cancel payroll run.');
        }

        return redirect()->to(base_url('hr/payroll'))->with('success', 'Payroll run cancelled.');
    }

    /** @return list<array<string, mixed>> */
    private function branches(?int $companyId): array
    {
        if (! $this->db->tableExists('finance_branches')) {
            return [];
        }

        $q = $this->db->table('finance_branches')->where('is_active', 1)->orderBy('name');
        if ($companyId) {
            $q->where('company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }
}
