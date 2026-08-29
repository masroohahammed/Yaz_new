<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Rule-based AI intelligence — no external API.
 * Raises/resolves flags stored as module + ref_id.
 */
class AiModel
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** Run all analyzers (cron or dashboard load). */
    public function runAnalysis(?int $companyId = null): array
    {
        $counts = [
            'contracts' => $this->scanExpiringContracts($companyId),
            'payments'  => $this->scanOverduePayments($companyId),
            'cheques'   => $this->scanBouncedCheques($companyId),
            'tenants'   => $this->scanIdExpiry($companyId),
            'maintenance'=> $this->scanEmergencyMaintenance($companyId),
            'properties'=> $this->scoreProperties($companyId),
        ];
        $this->resolveStaleFlags();

        return $counts;
    }

    /** @return list<array<string,mixed>> */
    public function flagsForWorkspace(string $workspace, int $limit = 10): array
    {
        if (! $this->db->tableExists('ai_flags')) {
            return [];
        }

        $b = $this->db->table('ai_flags')
            ->where('is_resolved', 0)
            ->orderBy('FIELD(severity, \'critical\', \'warning\', \'info\')', '', false)
            ->orderBy('created_at', 'DESC')
            ->limit($limit);

        if ($workspace === 'pm') {
            $b->whereIn('workspace', ['pm', 'both']);
        } elseif ($workspace === 'fm') {
            $b->whereIn('workspace', ['fm', 'both']);
        }

        return $b->get()->getResultArray();
    }

    public function raiseFlag(string $module, int $refId, string $flagType, string $title, string $message = '', string $severity = 'warning', string $ws = 'both'): void
    {
        if (! $this->db->tableExists('ai_flags')) {
            return;
        }

        $exists = $this->db->table('ai_flags')
            ->where('module', $module)
            ->where('ref_id', $refId)
            ->where('flag_type', $flagType)
            ->where('is_resolved', 0)
            ->countAllResults();

        if ($exists) {
            return;
        }

        $this->db->table('ai_flags')->insert([
            'module'      => $module,
            'ref_id'      => $refId,
            'flag_type'   => $flagType,
            'severity'    => $severity,
            'title'       => $title,
            'message'     => $message,
            'workspace'   => $ws,
            'is_resolved' => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function resolveFlag(string $module, int $refId, string $flagType): void
    {
        if (! $this->db->tableExists('ai_flags')) {
            return;
        }

        $this->db->table('ai_flags')
            ->where('module', $module)
            ->where('ref_id', $refId)
            ->where('flag_type', $flagType)
            ->where('is_resolved', 0)
            ->update(['is_resolved' => 1, 'resolved_at' => date('Y-m-d H:i:s')]);
    }

    private function scanExpiringContracts(?int $companyId): int
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return 0;
        }

        $count = 0;
        $rows  = $this->db->table('lease_contracts')
            ->where('status', 'active')
            ->where('end_date <=', date('Y-m-d', strtotime('+90 days')))
            ->where('end_date >=', date('Y-m-d'))
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $days = (int) ((strtotime($r['end_date']) - time()) / 86400);
            $sev  = $days <= 30 ? 'critical' : 'warning';
            $this->raiseFlag('lease_contract', (int) $r['id'], 'expiring_contract',
                'Contract ' . $r['contract_number'] . ' expires in ' . $days . ' days',
                'Lease ends ' . $r['end_date'], $sev, 'pm');
            $count++;
        }

        return $count;
    }

    private function scanOverduePayments(?int $companyId): int
    {
        if (! $this->db->tableExists('lease_payments')) {
            return 0;
        }

        $count = 0;
        $rows  = $this->db->table('lease_payments')
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_date <', date('Y-m-d'))
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $this->db->table('lease_payments')->where('id', $r['id'])->update(['status' => 'overdue']);
            $this->raiseFlag('lease_payment', (int) $r['id'], 'overdue_payment',
                'Overdue payment ' . $r['payment_number'],
                'Amount ' . $r['amount'] . ' due ' . $r['due_date'], 'warning', 'pm');
            $count++;
        }

        return $count;
    }

    private function scanBouncedCheques(?int $companyId): int
    {
        if (! $this->db->tableExists('cheques')) {
            return 0;
        }

        $count = 0;
        $rows  = $this->db->table('cheques')->where('status', 'bounced')->get()->getResultArray();
        foreach ($rows as $r) {
            $this->raiseFlag('cheque', (int) $r['id'], 'bounced_cheque',
                'Bounced cheque #' . $r['cheque_no'],
                $r['bounce_reason'] ?? 'Cheque bounced', 'critical', 'pm');
            $count++;
        }

        return $count;
    }

    private function scanIdExpiry(?int $companyId): int
    {
        if (! $this->db->tableExists('tenants')) {
            return 0;
        }

        $count = 0;
        $soon  = date('Y-m-d', strtotime('+60 days'));
        $rows  = $this->db->table('tenants')
            ->where('deleted_at', null)
            ->groupStart()
                ->where('qid_expiry <=', $soon)
                ->orWhere('passport_expiry <=', $soon)
            ->groupEnd()
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $this->raiseFlag('tenant', (int) $r['id'], 'id_expiry',
                'ID expiring: ' . $r['full_name'],
                'QID or passport expires soon', 'warning', 'pm');
            $count++;
        }

        return $count;
    }

    private function scanEmergencyMaintenance(?int $companyId): int
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return 0;
        }

        $count = 0;
        $rows  = $this->db->table('maintenance_requests')
            ->where('priority', 'critical')
            ->whereIn('status', ['pending', 'reviewed', 'approved'])
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $this->raiseFlag('maintenance_request', (int) $r['id'], 'emergency_maintenance',
                'Emergency: ' . ($r['ticket_number'] ?? 'Ticket'),
                $r['description'] ?? '', 'critical', 'fm');
            $count++;
        }

        return $count;
    }

    private function scoreProperties(?int $companyId): int
    {
        if (! $this->db->tableExists('ai_property_scores') || ! $this->db->tableExists('facilities')) {
            return 0;
        }

        $count = 0;
        $props = $this->db->table('facilities')->where('deleted_at', null)->where('status', 'active')->get()->getResultArray();

        foreach ($props as $p) {
            $fid      = (int) $p['id'];
            $units    = $this->db->table('units')->where('facility_id', $fid)->where('deleted_at', null)->countAllResults();
            $occupied = $units > 0 ? $this->db->table('units')->where('facility_id', $fid)->where('status', 'occupied')->countAllResults() : 0;
            $occPct   = $units > 0 ? ($occupied / $units) * 100 : 0;

            $openMaint = $this->db->table('maintenance_requests')
                ->where('facility_id', $fid)
                ->whereIn('status', ['pending', 'reviewed', 'approved'])
                ->countAllResults();

            $occHealth  = (int) min(100, $occPct);
            $revHealth  = (int) min(100, $occHealth + 10);
            $maintIndex = (int) max(0, 100 - ($openMaint * 15));
            $score      = (int) round(($occHealth + $revHealth + $maintIndex) / 3);

            $existing = $this->db->table('ai_property_scores')->where('facility_id', $fid)->get()->getRowArray();
            $data     = [
                'score'             => $score,
                'occupancy_health'  => $occHealth,
                'revenue_health'    => $revHealth,
                'maintenance_index' => $maintIndex,
                'calculated_at'     => date('Y-m-d H:i:s'),
            ];
            if ($existing) {
                $this->db->table('ai_property_scores')->where('facility_id', $fid)->update($data);
            } else {
                $data['facility_id'] = $fid;
                $this->db->table('ai_property_scores')->insert($data);
            }

            if ($score < 40) {
                $this->raiseFlag('facility', $fid, 'property_risk',
                    'Property health low: ' . $p['name'],
                    'Score ' . $score . '/100', 'warning', 'both');
            }
            $count++;
        }

        return $count;
    }

    private function resolveStaleFlags(): void
    {
        if (! $this->db->tableExists('ai_flags')) {
            return;
        }

        // Resolve expiring contract flags for renewed/terminated contracts
        if ($this->db->tableExists('lease_contracts')) {
            $resolved = $this->db->table('lease_contracts')
                ->select('id')
                ->whereNotIn('status', ['active'])
                ->get()->getResultArray();
            foreach ($resolved as $r) {
                $this->resolveFlag('lease_contract', (int) $r['id'], 'expiring_contract');
            }
        }

        if ($this->db->tableExists('lease_payments')) {
            $paid = $this->db->table('lease_payments')->select('id')->where('status', 'paid')->get()->getResultArray();
            foreach ($paid as $r) {
                $this->resolveFlag('lease_payment', (int) $r['id'], 'overdue_payment');
            }
        }
    }
}
