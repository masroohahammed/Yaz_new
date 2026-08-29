<?php

namespace App\Controllers\Api\V1;

use App\Services\Finance\GlReportService;

class Finance extends BaseApiController
{
    private function assertFinanceApi(): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $user = $this->jwtUser();
        if (! $user || ! in_array($user['role_name'], ['super_admin', 'finance_manager', 'finance_user'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => false,
                'message' => 'Finance API access requires finance role.',
            ]);
        }

        return null;
    }

    public function trialBalance()
    {
        if ($denied = $this->assertFinanceApi()) {
            return $denied;
        }

        $asOf = $this->request->getGet('as_of') ?? date('Y-m-d');
        $svc  = new GlReportService($this->db);
        $rows = $svc->trialBalance($asOf);

        return $this->response->setJSON([
            'status'       => true,
            'as_of'        => $asOf,
            'gl_enabled'   => $svc->isEnabled(),
            'rows'         => $rows,
            'total_debit'  => round(array_sum(array_column($rows, 'debit')), 2),
            'total_credit' => round(array_sum(array_column($rows, 'credit')), 2),
        ]);
    }

    public function reconciliation()
    {
        if ($denied = $this->assertFinanceApi()) {
            return $denied;
        }

        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to') ?? date('Y-m-d');
        $act  = (new GlReportService($this->db))->bankActivity($from, $to);

        $bankAccounts = [];
        if ($this->db->tableExists('finance_bank_accounts')) {
            $q = $this->db->table('finance_bank_accounts')->where('is_active', 1);
            $user = $this->jwtUser();
            if ($user && ! empty($user['company_id']) && $this->db->fieldExists('company_id', 'finance_bank_accounts')) {
                $q->where('company_id', (int) $user['company_id']);
            }
            $bankAccounts = $q->get()->getResultArray();
        }

        return $this->response->setJSON([
            'status'        => true,
            'from'          => $from,
            'to'            => $to,
            'activity'      => $act['payments'],
            'total_in'      => $act['total_in'],
            'total_out'     => $act['total_out'],
            'net'           => round($act['total_in'] - $act['total_out'], 2),
            'bank_accounts' => $bankAccounts,
        ]);
    }
}
