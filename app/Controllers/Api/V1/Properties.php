<?php

namespace App\Controllers\Api\V1;

class Properties extends BaseApiController
{
    public function index()
    {
        $q = $this->db->table('facilities f')
            ->select('f.id, f.name, f.code, f.address, f.city, f.status, f.category, f.property_type, f.listing_status, f.for_sale, f.sale_price, f.expected_monthly_income, f.landlord_id')
            ->where('f.deleted_at', null);
        $this->scopeFacilitiesForApi($q, 'f.id');
        $properties = $q->orderBy('f.name', 'ASC')->get()->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data'   => $properties,
            'count'  => count($properties),
        ]);
    }

    public function kpis(int $id)
    {
        $q = $this->db->table('facilities f')
            ->select('f.id, f.name, f.code, f.status, f.category, f.property_type, f.expected_monthly_income')
            ->where('f.id', $id)
            ->where('f.deleted_at', null);
        $this->scopeFacilitiesForApi($q, 'f.id');
        $property = $q->get()->getRowArray();

        if (! $property) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Property not found',
            ]);
        }

        $totalUnits = $this->db->table('units')
            ->where('facility_id', $id)
            ->where('deleted_at', null)
            ->countAllResults();
        $occupied = $this->db->table('units')
            ->where('facility_id', $id)
            ->where('status', 'occupied')
            ->where('deleted_at', null)
            ->countAllResults();
        $occupancy = $totalUnits > 0 ? round(($occupied / $totalUnits) * 100, 1) : 0.0;

        $activeContracts = 0;
        $expiringSoon    = 0;
        if ($this->db->tableExists('lease_contracts')) {
            $activeContracts = $this->db->table('lease_contracts')
                ->where('facility_id', $id)
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->countAllResults();
            $expiringSoon = $this->db->table('lease_contracts')
                ->where('facility_id', $id)
                ->where('status', 'active')
                ->where('end_date <=', date('Y-m-d', strtotime('+60 days')))
                ->where('end_date >=', date('Y-m-d'))
                ->countAllResults();
        }

        $overduePayments = 0;
        $pendingRent     = 0.0;
        if ($this->db->tableExists('lease_payments')) {
            $overduePayments = $this->db->table('lease_payments')
                ->where('facility_id', $id)
                ->where('status', 'overdue')
                ->countAllResults();
            $pendingRent = (float) ($this->db->table('lease_payments')
                ->selectSum('amount', 't')
                ->where('facility_id', $id)
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->get()
                ->getRowArray()['t'] ?? 0);
        }

        $openMaintenance = $this->db->table('maintenance_requests')
            ->where('facility_id', $id)
            ->whereIn('status', ['pending', 'reviewed', 'approved'])
            ->countAllResults();

        $healthScore = null;
        if ($this->db->tableExists('ai_property_scores')) {
            $scoreRow    = $this->db->table('ai_property_scores')->where('facility_id', $id)->get()->getRowArray();
            $healthScore = $scoreRow ? [
                'score'             => (int) ($scoreRow['score'] ?? 0),
                'occupancy_health'  => (int) ($scoreRow['occupancy_health'] ?? 0),
                'revenue_health'    => (int) ($scoreRow['revenue_health'] ?? 0),
                'maintenance_index' => (int) ($scoreRow['maintenance_index'] ?? 0),
            ] : null;
        }

        return $this->response->setJSON([
            'status'   => true,
            'property' => $property,
            'kpis'     => [
                'total_units'        => $totalUnits,
                'occupied_units'     => $occupied,
                'occupancy_pct'      => $occupancy,
                'active_contracts'   => $activeContracts,
                'expiring_contracts' => $expiringSoon,
                'overdue_payments'   => $overduePayments,
                'pending_rent'       => round($pendingRent, 2),
                'open_maintenance'   => $openMaintenance,
                'health'             => $healthScore,
                'currency'           => $this->settings['currency'] ?? 'QAR',
            ],
        ]);
    }
}
