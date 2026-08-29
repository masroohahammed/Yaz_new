<?php

namespace App\Controllers;

use App\Services\AiModel;

class Cron extends BaseController
{
    public function runAll()
    {
        if (! $this->authorizeCron()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => false,
                'message' => 'Unauthorized',
            ]);
        }

        $companyId = session()->get('company_id') ? (int) session()->get('company_id') : null;
        $ai        = new AiModel($this->db);
        $analysis  = $ai->runAnalysis($companyId);
        $expired   = $this->expireComplimentaryOffers();
        $overdue   = $this->markOverduePayments();
        $expiredLc = $this->expireLeaseContracts();
        $reminders = $this->notifyCostReminders();
        $docAlerts = $this->processHrDocumentExpiryAlerts();
        $contractAlerts = $this->processHrContractExpiryAlerts();

        return $this->response->setJSON([
            'status'            => true,
            'timestamp'         => date('c'),
            'analysis'          => $analysis,
            'overdue'           => $overdue,
            'expired'           => $expired,
            'expired_contracts' => $expiredLc,
            'reminders'         => $reminders,
            'hr_document_alerts'=> $docAlerts,
            'hr_contract_alerts'=> $contractAlerts,
        ]);
    }

    private function authorizeCron(): bool
    {
        $secret = $this->settings['cron_secret'] ?? '';
        $key    = $this->request->getGet('key');

        return $secret !== '' && $key !== null && hash_equals($secret, (string) $key);
    }

    private function markOverduePayments(): int
    {
        if (! $this->db->tableExists('lease_payments')) {
            return 0;
        }

        $this->db->table('lease_payments')
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date <', date('Y-m-d'))
            ->update(['status' => 'overdue', 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows();
    }

    private function expireComplimentaryOffers(): int
    {
        if (! $this->db->tableExists('complimentary_offers')) {
            return 0;
        }

        $this->db->table('complimentary_offers')
            ->where('status', 'active')
            ->where('end_date <', date('Y-m-d'))
            ->update(['status' => 'expired']);

        return $this->db->affectedRows();
    }

    private function expireLeaseContracts(): int
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return 0;
        }

        $this->db->table('lease_contracts')
            ->where('status', 'active')
            ->where('end_date <', date('Y-m-d'))
            ->update(['status' => 'expired', 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows();
    }

    private function notifyCostReminders(): int
    {
        if (! $this->db->tableExists('cost_reminders')) {
            return 0;
        }

        $due = $this->db->table('cost_reminders')
            ->where('status', 'pending')
            ->where('due_date <=', date('Y-m-d'))
            ->get()->getResultArray();

        $count = 0;
        foreach ($due as $r) {
            $this->notifyManagers(
                'Cost reminder: ' . ($r['title'] ?? 'Due'),
                'Reminder due ' . ($r['due_date'] ?? '') . ' — amount ' . ($r['amount'] ?? '0')
            );
            $count++;
        }

        return $count;
    }

    private function processHrDocumentExpiryAlerts(): array
    {
        try {
            $alerts = new \App\Services\AlertDispatchService($this->db, $this->settings);
            $svc    = new \App\Services\Hr\HrDocumentService($this->db);

            return $svc->processExpiryAlerts($alerts, $this->settings);
        } catch (\Throwable $e) {
            log_message('error', 'HR document expiry cron: ' . $e->getMessage());

            return ['synced' => 0, 'alerts_sent' => 0, 'error' => $e->getMessage()];
        }
    }

    private function processHrContractExpiryAlerts(): array
    {
        try {
            $alerts = new \App\Services\AlertDispatchService($this->db, $this->settings);
            $svc    = new \App\Services\Hr\EmploymentContractService($this->db);

            return $svc->processExpiryAlerts($alerts, $this->settings);
        } catch (\Throwable $e) {
            log_message('error', 'HR contract expiry cron: ' . $e->getMessage());

            return ['synced' => 0, 'alerts_sent' => 0, 'error' => $e->getMessage()];
        }
    }
}
