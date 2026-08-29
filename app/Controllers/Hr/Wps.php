<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\HrPayrollService;
use App\Services\Hr\HrWpsService;

class Wps extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrWpsService $wps;

    public function __construct()
    {
        $this->wps = new HrWpsService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('wps.generate');

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $payroll   = new HrPayrollService($this->db);

        $eligibleRuns = [];
        if ($payroll->tablesReady()) {
            foreach ($payroll->listRuns($companyId, 20) as $run) {
                if (in_array($run['status'], ['locked', 'posted'], true)) {
                    $eligibleRuns[] = $run;
                }
            }
        }

        return view('hr/wps/index', $this->viewData([
            'title'             => 'WPS Batches',
            'batches'           => $this->wps->listBatches($companyId),
            'eligibleRuns'      => $eligibleRuns,
            'selectedRunId'   => (int) ($this->request->getGet('run_id') ?: 0) ?: null,
            'migrationRequired' => ! $this->wps->tablesReady(),
        ]));
    }

    public function view(int $id)
    {
        $this->requireHrPermission('wps.generate');

        $batch = $this->wps->findBatch($id);
        if (! $batch) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('hr/wps/view', $this->viewData([
            'title'   => 'WPS ' . ($batch['batch_number'] ?? $id),
            'batch'   => $batch,
            'records' => $this->wps->recordsForBatch($id),
        ]));
    }

    public function generate()
    {
        $this->requireHrPermission('wps.generate');
        if (! $this->request->is('post') || ! $this->wps->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $runId = (int) $this->request->getPost('payroll_run_id');
        if ($runId < 1) {
            return redirect()->back()->with('error', 'Select a payroll run.');
        }

        try {
            $batchId = $this->wps->generateBatch($runId, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/wps/view/' . $batchId))->with('success', 'WPS batch generated.');
    }

    public function download(int $id)
    {
        $this->requireHrPermission('wps.generate');

        $batch = $this->wps->findBatch($id);
        if (! $batch || empty($batch['file_content'])) {
            return redirect()->back()->with('error', 'File not available.');
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . ($batch['file_name'] ?? 'wps.csv') . '"')
            ->setBody($batch['file_content']);
    }
}
