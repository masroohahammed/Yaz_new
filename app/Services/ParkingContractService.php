<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Builds defaults and renders data for bilingual parking rental agreements.
 */
class ParkingContractService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function isParkingUnit(array $unit): bool
    {
        return strtolower(trim((string) ($unit['unit_type'] ?? ''))) === 'parking';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDefaults(int $unitId, ?int $leaseContractId = null): array
    {
        $unit = $this->db->table('units u')
            ->select('u.*, f.name as facility_name, f.city as facility_city, f.address as facility_address, f.code as facility_code')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.id', $unitId)
            ->get()->getRowArray();

        if (! $unit) {
            return [];
        }

        $settings = $this->loadSettings();
        $lease    = $this->resolveLease($unitId, $leaseContractId);
        $tenant   = $lease ? $this->tenantRow((int) ($lease['tenant_id'] ?? 0)) : null;

        $start = $lease['start_date'] ?? $unit['contract_start'] ?? date('Y-m-d');
        $end   = $lease['end_date'] ?? $unit['contract_end'] ?? date('Y-m-d', strtotime('+4 months', strtotime($start)));

        $contractDate = $lease['signed_date'] ?? $start ?? date('Y-m-d');
        $rent         = (float) ($lease['rent_amount'] ?? $unit['rent_amount'] ?? 0);

        $tenantName = $tenant['full_name'] ?? $unit['tenant_name'] ?? '';
        $tenantPhone = $tenant['phone'] ?? $unit['tenant_mobile'] ?? '';

        return [
            'contract_number'     => $lease['contract_number'] ?? $unit['contract_number'] ?? '',
            'contract_date'       => $contractDate,
            'landlord_name'       => $settings['company_name'] ?? 'Al Yazwa Real Estate',
            'landlord_cr'         => $settings['company_cr'] ?? 'CR-159425',
            'landlord_email'      => $settings['company_email'] ?? 'admin@alyazwa.com',
            'landlord_address'    => $settings['company_address'] ?? 'Doha, Qatar',
            'tenant_name'         => $tenantName,
            'tenant_qid'          => trim((string) ($lease['tenant_qid'] ?? '')) !== ''
                ? (string) $lease['tenant_qid']
                : ($tenant['qid_no'] ?? $tenant['passport_no'] ?? ''),
            'tenant_phone'        => $tenantPhone,
            'tenant_nationality'  => $tenant['nationality'] ?? 'Qatar',
            'tenant_address'      => ($tenant['nationality'] ?? 'Qatar') . ' / Qatar',
            'title_deed_no'       => $lease['title_deed_no'] ?? '',
            'property_city'       => $unit['facility_city'] ?? 'Al Wakra',
            'building_no'         => $lease['building_no'] ?? '',
            'zone_no'             => $lease['zone_no'] ?? '',
            'street_no'           => $lease['street_no'] ?? '',
            'property_name'       => $unit['facility_name'] ?? '',
            'property_address'    => $unit['facility_address'] ?? '',
            'parking_unit_no'     => $unit['unit_number'] ?? '',
            'vehicle_type'        => $lease['vehicle_type'] ?? 'Motorcycle',
            'vehicle_description' => $lease['vehicle_description'] ?? '',
            'plate_number'        => $lease['plate_number'] ?? $unit['plate_number'] ?? '',
            'start_date'          => $start,
            'end_date'            => $end,
            'rent_amount'         => $rent,
            'payment_terms'       => $lease['payment_type'] ?? 'cash',
            'payment_frequency'   => $lease['payment_frequency'] ?? 'monthly',
            'collector_company'   => $settings['company_name'] ?? 'Al Yazwa Real Estate',
            'collector_cr'        => $settings['company_cr'] ?? 'CR-159425',
            'currency'            => $settings['currency'] ?? 'QAR',
            'lease_contract_id'   => $lease['id'] ?? null,
            'unit_id'             => $unitId,
        ];
    }

    /**
     * Merge POST input over defaults (editable print form).
     *
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public function mergeFormInput(array $defaults, array $input): array
    {
        $keys = [
            'contract_number', 'contract_date', 'landlord_name', 'landlord_cr', 'landlord_email', 'landlord_address',
            'tenant_name', 'tenant_qid', 'tenant_phone', 'tenant_nationality', 'tenant_address',
            'title_deed_no', 'property_city', 'building_no', 'zone_no', 'street_no', 'property_name', 'property_address',
            'parking_unit_no', 'vehicle_type', 'vehicle_description', 'plate_number',
            'start_date', 'end_date', 'rent_amount', 'payment_terms', 'payment_frequency',
            'collector_company', 'collector_cr', 'currency',
        ];

        foreach ($keys as $key) {
            if (isset($input[$key]) && trim((string) $input[$key]) !== '') {
                $defaults[$key] = trim((string) $input[$key]);
            }
        }

        if (isset($input['rent_amount'])) {
            $defaults['rent_amount'] = (float) $input['rent_amount'];
        }

        return $defaults;
    }

    public function durationMonths(string $start, string $end): int
    {
        if (! $start || ! $end) {
            return 0;
        }
        $s = strtotime($start);
        $e = strtotime($end);
        if (! $s || ! $e || $e < $s) {
            return 0;
        }

        return max(1, (int) round(($e - $s) / (30.437 * 86400)));
    }

    public function englishDayName(string $date): string
    {
        $ts = strtotime($date);

        return $ts ? date('l', $ts) : '';
    }

    public function arabicDayName(string $date): string
    {
        $map = [
            'Sunday'    => 'الأحد',
            'Monday'    => 'الإثنين',
            'Tuesday'   => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday'  => 'الخميس',
            'Friday'    => 'الجمعة',
            'Saturday'  => 'السبت',
        ];
        $en = $this->englishDayName($date);

        return $map[$en] ?? $en;
    }

    /** @return array<string, string> */
    private function loadSettings(): array
    {
        try {
            $rows = $this->db->table('system_settings')->select('setting_key, setting_value')->get()->getResultArray();

            return array_column($rows, 'setting_value', 'setting_key');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** @return array<string, mixed>|null */
    private function resolveLease(int $unitId, ?int $leaseContractId): ?array
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return null;
        }

        if ($leaseContractId) {
            $row = $this->db->table('lease_contracts')
                ->where('id', $leaseContractId)
                ->where('deleted_at', null)
                ->get()->getRowArray();

            return $row ?: null;
        }

        $row = $this->db->table('lease_contracts')
            ->where('unit_id', $unitId)
            ->where('deleted_at', null)
            ->whereIn('status', ['active', 'draft'])
            ->orderBy('FIELD(status, \'active\', \'draft\')', '', false)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    private function tenantRow(int $tenantId): ?array
    {
        if ($tenantId < 1 || ! $this->db->tableExists('tenants')) {
            return null;
        }

        return $this->db->table('tenants')->where('id', $tenantId)->get()->getRowArray() ?: null;
    }
}
