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
        $months       = $this->durationMonths($start, $end);

        $tenantName  = $tenant['full_name'] ?? $unit['tenant_name'] ?? '';
        $tenantPhone = $tenant['phone'] ?? $unit['tenant_mobile'] ?? '';
        $vehicleType = $lease['vehicle_type'] ?? 'Motorcycle';

        return [
            'contract_number'       => $lease['contract_number'] ?? $unit['contract_number'] ?? '',
            'contract_date'         => $contractDate,
            'header_title_deed_no'  => $settings['parking_header_title_deed'] ?? '211207',
            'title_deed_no'         => $lease['title_deed_no'] ?? '',
            'owner_name_ar'         => $settings['parking_owner_name_ar'] ?? 'الهلال شرق العقارات (ذ م م )',
            'owner_name_en'         => $settings['parking_owner_name_en'] ?? 'Al Hilal East Real Estate (W.L.L.)',
            'owner_cr'              => $settings['parking_owner_cr'] ?? '70823',
            'rep_company_ar'        => $settings['parking_rep_company_ar'] ?? 'اليزوة للعقارات (ذ م م)',
            'rep_company_en'        => $settings['parking_rep_company_en'] ?? 'Al Yazwa Real Estate (W.L.L.)',
            'rep_cr'                => $settings['parking_rep_cr'] ?? ($settings['company_cr'] ?? '159425'),
            'poa_no'                => $settings['parking_poa_no'] ?? '98256/2024',
            'poa_date'              => $settings['parking_poa_date'] ?? '2024-03-12',
            'rep_name_ar'           => $settings['parking_rep_name_ar'] ?? 'السيد إسماعيل محمد إسماعيل مندني العمادي',
            'rep_name_en'           => $settings['parking_rep_name_en'] ?? 'Mr. Ismail Mohammed Ismail Mandani Al Emadi',
            'rep_nationality_ar'    => $settings['parking_rep_nationality_ar'] ?? 'قطري',
            'rep_nationality_en'    => $settings['parking_rep_nationality_en'] ?? 'Qatari',
            'rep_qid'               => $settings['parking_rep_qid'] ?? '28563400749',
            'landlord_name'         => $settings['company_name'] ?? 'AL YAZWA REAL ESTATE CO. W.L.L',
            'landlord_cr'           => $settings['company_cr'] ?? '159425',
            'landlord_phone'        => $settings['company_phone'] ?? '66555953',
            'landlord_email'        => $settings['company_email'] ?? 'admin@alyazwa.com',
            'landlord_address'      => $settings['company_address'] ?? 'D-Ring Road Al Hilal, Doha, State of Qatar',
            'landlord_po_box'       => $settings['company_po_box'] ?? '200199',
            'tenant_name'           => $tenantName,
            'tenant_qid'            => trim((string) ($lease['tenant_qid'] ?? '')) !== ''
                ? (string) $lease['tenant_qid']
                : ($tenant['qid_no'] ?? $tenant['passport_no'] ?? ''),
            'tenant_phone'          => $tenantPhone,
            'tenant_po_box'         => '',
            'tenant_nationality'    => $tenant['nationality'] ?? 'Qatar',
            'tenant_address'        => 'Doha, Qatar',
            'property_city'         => $unit['facility_city'] ?? 'Al Wakrah',
            'building_no'           => $lease['building_no'] ?? '',
            'zone_no'               => $lease['zone_no'] ?? '',
            'street_no'             => $lease['street_no'] ?? '',
            'property_name'         => $unit['facility_name'] ?? '',
            'property_address'      => $unit['facility_address'] ?? '',
            'parking_unit_no'       => $unit['unit_number'] ?? '',
            'vehicle_type'          => $vehicleType,
            'vehicle_type_ar'       => $this->vehicleTypeArabic($vehicleType),
            'vehicle_description'   => $lease['vehicle_description'] ?? '',
            'plate_number'          => $lease['plate_number'] ?? $unit['plate_number'] ?? '',
            'start_date'            => $start,
            'end_date'              => $end,
            'rent_amount'           => $rent,
            'rent_words_en'         => $this->rentWordsEnglish($rent),
            'duration_months'       => $months,
            'duration_ar'           => $this->arabicDurationMonths($months),
            'duration_en'           => $this->englishDurationMonths($months),
            'cheque_count'          => $months,
            'cheque_count_words_en' => $this->numberWordsEnglish($months),
            'payment_terms'         => $lease['payment_type'] ?? 'cheque',
            'payment_frequency'     => $lease['payment_frequency'] ?? 'monthly',
            'collector_company'     => $settings['parking_rep_company_en'] ?? 'Al Yazwa Real Estate',
            'collector_cr'          => $settings['company_cr'] ?? 'CR-159425',
            'collector_account'     => $settings['parking_collector_account'] ?? 'CR-159425 (AL YAZWA REAL ESTATE)',
            'currency'              => $settings['currency'] ?? 'QAR',
            'lease_contract_id'     => $lease['id'] ?? null,
            'unit_id'               => $unitId,
            'contract_photos'       => ParkingContractPhotoService::pathsFromJson($lease['photos_json'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public function mergeFormInput(array $defaults, array $input): array
    {
        $keys = [
            'contract_number', 'contract_date', 'header_title_deed_no', 'title_deed_no',
            'owner_name_ar', 'owner_name_en', 'owner_cr', 'rep_company_ar', 'rep_company_en', 'rep_cr',
            'poa_no', 'poa_date', 'rep_name_ar', 'rep_name_en', 'rep_nationality_ar', 'rep_nationality_en', 'rep_qid',
            'landlord_name', 'landlord_cr', 'landlord_email', 'landlord_address', 'landlord_phone', 'landlord_po_box',
            'tenant_name', 'tenant_qid', 'tenant_phone', 'tenant_nationality', 'tenant_address', 'tenant_po_box',
            'property_city', 'building_no', 'zone_no', 'street_no', 'property_name', 'property_address',
            'parking_unit_no', 'vehicle_type', 'vehicle_type_ar', 'vehicle_description', 'plate_number',
            'start_date', 'end_date', 'rent_amount', 'payment_terms', 'payment_frequency',
            'collector_company', 'collector_cr', 'collector_account', 'currency', 'cheque_count',
        ];

        foreach ($keys as $key) {
            if (isset($input[$key]) && trim((string) $input[$key]) !== '') {
                $defaults[$key] = trim((string) $input[$key]);
            }
        }

        if (isset($input['rent_amount'])) {
            $defaults['rent_amount'] = (float) $input['rent_amount'];
        }
        if (isset($input['cheque_count'])) {
            $defaults['cheque_count'] = max(1, (int) $input['cheque_count']);
        }

        $defaults['duration_months'] = $this->durationMonths(
            (string) ($defaults['start_date'] ?? ''),
            (string) ($defaults['end_date'] ?? '')
        );
        if (! isset($input['cheque_count']) || trim((string) $input['cheque_count']) === '') {
            $defaults['cheque_count'] = $defaults['duration_months'];
        }
        $defaults['duration_ar']           = $this->arabicDurationMonths((int) $defaults['duration_months']);
        $defaults['duration_en']           = $this->englishDurationMonths((int) $defaults['duration_months']);
        $defaults['rent_words_en']         = $this->rentWordsEnglish((float) ($defaults['rent_amount'] ?? 0));
        $defaults['cheque_count_words_en'] = $this->numberWordsEnglish((int) ($defaults['cheque_count'] ?? 0));
        if (empty($defaults['vehicle_type_ar']) && ! empty($defaults['vehicle_type'])) {
            $defaults['vehicle_type_ar'] = $this->vehicleTypeArabic((string) $defaults['vehicle_type']);
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
            'Sunday' => 'الأحد', 'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت',
        ];

        return $map[$this->englishDayName($date)] ?? $this->englishDayName($date);
    }

    public function formatDateEn(string $date): string
    {
        $ts = strtotime($date);

        return $ts ? date('d/m/Y', $ts) : $date;
    }

    public function formatDateEnLong(string $date): string
    {
        $ts = strtotime($date);

        return $ts ? date('l, j F Y', $ts) : $date;
    }

    public function formatDateAr(string $date): string
    {
        return $this->formatDateEn($date);
    }

    public function formatPoaDate(string $date): string
    {
        $ts = strtotime($date);

        return $ts ? date('j/n/Y', $ts) : $date;
    }

    public function arabicDurationMonths(int $months): string
    {
        return match ($months) {
            1       => 'شهر واحد',
            2       => 'شهرين',
            3       => 'ثلاثة أشهر',
            4       => 'اربعة شهور',
            5       => 'خمسة أشهر',
            6       => 'ستة أشهر',
            7       => 'سبعة أشهر',
            8       => 'ثمانية أشهر',
            9       => 'تسعة أشهر',
            10      => 'عشرة أشهر',
            11      => 'أحد عشر شهراً',
            12      => 'اثنا عشر شهراً',
            default => $months . ' شهر/أشهر',
        };
    }

    public function englishDurationMonths(int $months): string
    {
        return match ($months) {
            1       => 'one (1) month',
            2       => 'two (2) months',
            3       => 'three (3) months',
            4       => 'four (4) months',
            5       => 'five (5) months',
            6       => 'six (6) months',
            default => $months . ' months',
        };
    }

    public function numberWordsEnglish(int $n): string
    {
        $map = [
            1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
            11 => 'eleven', 12 => 'twelve',
        ];

        return $map[$n] ?? (string) $n;
    }

    public function rentWordsEnglish(float $amount): string
    {
        $n = (int) round($amount);
        $map = [
            100 => 'One Hundred', 200 => 'Two Hundred', 300 => 'Three Hundred', 400 => 'Four Hundred',
            500 => 'Five Hundred', 600 => 'Six Hundred', 700 => 'Seven Hundred', 800 => 'Eight Hundred',
            900 => 'Nine Hundred', 1000 => 'One Thousand',
        ];

        $words = $map[$n] ?? number_format($n, 0);

        return $words . ' Qatari Riyals';
    }

    public function vehicleTypeArabic(string $type): string
    {
        $t = strtolower(trim($type));
        if (str_contains($t, 'motor') || str_contains($t, 'bike') || str_contains($t, 'دراج')) {
            return 'دراجته النارية';
        }
        if (str_contains($t, 'car') || str_contains($t, 'vehicle')) {
            return 'مركبته';
        }

        return 'مركبته';
    }

    public function vehicleTypeEnglish(string $type): string
    {
        $t = strtolower(trim($type));
        if (str_contains($t, 'motor') || str_contains($t, 'bike')) {
            return 'motorcycle';
        }

        return strtolower($type) ?: 'vehicle';
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
