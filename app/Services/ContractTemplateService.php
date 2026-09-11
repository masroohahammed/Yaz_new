<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ContractTemplateService
{
    public const PLACEHOLDERS = [
        '{{contract_number}}',
        '{{property_name}}',
        '{{unit_number}}',
        '{{tenant_name}}',
        '{{tenant_qid}}',
        '{{rent_amount}}',
        '{{rent_amount_fmt}}',
        '{{rent_words_en}}',
        '{{currency}}',
        '{{payment_frequency}}',
        '{{start_date}}',
        '{{end_date}}',
        '{{start_date_en}}',
        '{{end_date_en}}',
        '{{start_date_ar}}',
        '{{end_date_ar}}',
        '{{signed_date}}',
        '{{security_deposit}}',
        '{{plate_number}}',
        '{{vehicle_type}}',
        '{{vehicle_description}}',
        '{{title_deed_no}}',
        '{{building_no}}',
        '{{zone_no}}',
        '{{street_no}}',
        '{{duration_en}}',
        '{{duration_ar}}',
        '{{cheque_count}}',
        '{{collector_company}}',
        '{{collector_account}}',
        '{{parking_unit_no}}',
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    /** @param array<string,mixed> $contract */
    public function resolveForContract(array $contract, ?string $tenantQid = null): array
    {
        $typeId = (int) ($contract['contract_type_id'] ?? 0);
        $tplId  = (int) ($contract['template_id'] ?? 0);

        if ($typeId < 1) {
            $typeId = $this->typeIdForSlug((string) ($contract['contract_kind'] ?? ''));
        }

        $templateEn = trim((string) ($contract['custom_content_en'] ?? ''));
        $templateAr = trim((string) ($contract['custom_content_ar'] ?? ''));
        $termsEn    = trim((string) ($contract['contract_terms'] ?? ''));
        $termsAr    = '';

        if ($templateEn === '') {
            $tpl = $this->loadActiveTemplate($typeId, $tplId, (string) ($contract['contract_kind'] ?? ''));
            if ($tpl) {
                $templateEn = (string) ($tpl['content_en'] ?? '');
                $templateAr = (string) ($tpl['content_ar'] ?? '');
                $termsEn    = $termsEn !== '' ? $termsEn : (string) ($tpl['terms_en'] ?? '');
                $termsAr    = (string) ($tpl['terms_ar'] ?? '');
            }
        }

        $vars = $this->buildVars($contract, $tenantQid);

        return $this->substituteAll($templateEn, $templateAr, $termsEn, $termsAr, $vars);
    }

    /**
     * Resolve parking agreement body from Contract Setup (or legacy defaults).
     *
     * @param array<string,mixed>      $d
     * @param array<string,mixed>|null $leaseRow
     * @return array{content_en: string, content_ar: string, terms_en: string, terms_ar: string}
     */
    public function resolveForParkingDocument(array $d, ?array $leaseRow = null): array
    {
        $parkSvc  = new ParkingContractService($this->db);
        $leaseRow = $leaseRow ?? [];
        $typeId   = (int) ($leaseRow['contract_type_id'] ?? 0);
        if ($typeId < 1) {
            $typeId = $this->typeIdForSlug('parking');
        }

        $templateEn = trim((string) ($leaseRow['custom_content_en'] ?? ''));
        $templateAr = trim((string) ($leaseRow['custom_content_ar'] ?? ''));
        $termsEn    = trim((string) ($leaseRow['contract_terms'] ?? ''));
        $termsAr    = '';

        if ($templateEn === '') {
            $tpl = $this->loadActiveTemplate($typeId, (int) ($leaseRow['template_id'] ?? 0), 'parking');
            if ($tpl) {
                $templateEn = (string) ($tpl['content_en'] ?? '');
                $templateAr = (string) ($tpl['content_ar'] ?? '');
                $termsEn    = $termsEn !== '' ? $termsEn : (string) ($tpl['terms_en'] ?? '');
                $termsAr    = (string) ($tpl['terms_ar'] ?? '');
            } else {
                $defaults   = ParkingContractTemplateDefaults::templateRow();
                $templateEn = $defaults['content_en'];
                $templateAr = $defaults['content_ar'];
                $termsEn    = $defaults['terms_en'];
                $termsAr    = $defaults['terms_ar'];
            }
        }

        $vars = $this->buildParkingVars($d, $parkSvc);

        return $this->substituteAll($templateEn, $templateAr, $termsEn, $termsAr, $vars);
    }

    /** @return array{content_en: string, content_ar: string, terms_en: string, terms_ar: string} */
    private function substituteAll(string $en, string $ar, string $termsEn, string $termsAr, array $vars): array
    {
        return [
            'content_en' => strtr($en, $vars),
            'content_ar' => strtr($ar, $vars),
            'terms_en'   => strtr($termsEn, $vars),
            'terms_ar'   => strtr($termsAr, $vars),
        ];
    }

    /** @return array<string,mixed>|null */
    private function loadActiveTemplate(int $typeId, int $tplId, string $kindSlug): ?array
    {
        if (! $this->db->tableExists('contract_templates')) {
            return $kindSlug === 'parking' ? ParkingContractTemplateDefaults::templateRow() : null;
        }

        $q = $this->db->table('contract_templates')->where('is_active', 1);
        if ($tplId > 0) {
            $q->where('id', $tplId);
        } elseif ($typeId > 0 && $this->db->fieldExists('contract_type_id', 'contract_templates')) {
            $q->where('contract_type_id', $typeId);
        } elseif ($kindSlug !== '' && $this->db->tableExists('contract_types')) {
            $slugTypeId = $this->typeIdForSlug($kindSlug);
            if ($slugTypeId > 0) {
                $q->where('contract_type_id', $slugTypeId);
            }
        } else {
            return $kindSlug === 'parking' ? ParkingContractTemplateDefaults::templateRow() : null;
        }

        $tpl = $q->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();

        return $tpl ?: ($kindSlug === 'parking' ? ParkingContractTemplateDefaults::templateRow() : null);
    }

    private function typeIdForSlug(string $slug): int
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! $this->db->tableExists('contract_types')) {
            return 0;
        }

        $row = $this->db->table('contract_types')->select('id')->where('slug', $slug)->get()->getRowArray();

        return (int) ($row['id'] ?? 0);
    }

    /** @param array<string,mixed> $contract */
    private function buildVars(array $contract, ?string $tenantQid): array
    {
        $rent = (float) ($contract['rent_amount'] ?? 0);

        return [
            '{{contract_number}}'     => esc($contract['contract_number'] ?? ''),
            '{{property_name}}'       => esc($contract['facility_name'] ?? $contract['property_name'] ?? ''),
            '{{unit_number}}'         => esc($contract['unit_number'] ?? $contract['parking_unit_no'] ?? ''),
            '{{tenant_name}}'         => esc($contract['tenant_name'] ?? ''),
            '{{tenant_qid}}'          => esc($tenantQid ?? $contract['tenant_qid'] ?? ''),
            '{{rent_amount}}'         => number_format($rent, 2),
            '{{rent_amount_fmt}}'     => number_format($rent, 0),
            '{{rent_words_en}}'       => esc($contract['rent_words_en'] ?? ''),
            '{{currency}}'            => esc($contract['currency'] ?? 'QAR'),
            '{{payment_frequency}}'   => esc($contract['payment_frequency'] ?? ''),
            '{{start_date}}'          => esc($contract['start_date'] ?? ''),
            '{{end_date}}'            => esc($contract['end_date'] ?? ''),
            '{{start_date_en}}'       => esc($contract['start_date_en'] ?? $contract['start_date'] ?? ''),
            '{{end_date_en}}'         => esc($contract['end_date_en'] ?? $contract['end_date'] ?? ''),
            '{{start_date_ar}}'       => esc($contract['start_date_ar'] ?? $contract['start_date'] ?? ''),
            '{{end_date_ar}}'         => esc($contract['end_date_ar'] ?? $contract['end_date'] ?? ''),
            '{{signed_date}}'         => esc($contract['signed_date'] ?? ''),
            '{{security_deposit}}'    => number_format((float) ($contract['security_deposit'] ?? 0), 2),
            '{{plate_number}}'        => esc($contract['plate_number'] ?? ''),
            '{{vehicle_type}}'        => esc($contract['vehicle_type'] ?? ''),
            '{{vehicle_description}}' => esc($contract['vehicle_description'] ?? ''),
            '{{title_deed_no}}'       => esc($contract['title_deed_no'] ?? ''),
            '{{building_no}}'         => esc($contract['building_no'] ?? ''),
            '{{zone_no}}'             => esc($contract['zone_no'] ?? ''),
            '{{street_no}}'           => esc($contract['street_no'] ?? ''),
            '{{duration_en}}'         => esc($contract['duration_en'] ?? ''),
            '{{duration_ar}}'         => esc($contract['duration_ar'] ?? ''),
            '{{cheque_count}}'        => (string) (int) ($contract['cheque_count'] ?? 0),
            '{{collector_company}}'   => esc($contract['collector_company'] ?? ''),
            '{{collector_account}}'   => esc($contract['collector_account'] ?? ''),
            '{{parking_unit_no}}'     => esc($contract['parking_unit_no'] ?? $contract['unit_number'] ?? ''),
        ];
    }

    /** @param array<string,mixed> $d */
    private function buildParkingVars(array $d, ParkingContractService $svc): array
    {
        $contract = [
            'contract_number'   => $d['contract_number'] ?? '',
            'property_name'     => $d['property_name'] ?? '',
            'facility_name'     => $d['property_name'] ?? '',
            'unit_number'       => $d['parking_unit_no'] ?? '',
            'parking_unit_no'   => $d['parking_unit_no'] ?? '',
            'tenant_name'       => $d['tenant_name'] ?? '',
            'tenant_qid'        => $d['tenant_qid'] ?? '',
            'rent_amount'       => $d['rent_amount'] ?? 0,
            'rent_words_en'     => $d['rent_words_en'] ?? '',
            'currency'          => $d['currency'] ?? 'QAR',
            'payment_frequency' => $d['payment_frequency'] ?? '',
            'start_date'        => $d['start_date'] ?? '',
            'end_date'          => $d['end_date'] ?? '',
            'start_date_en'     => $svc->formatDateEn((string) ($d['start_date'] ?? '')),
            'end_date_en'       => $svc->formatDateEn((string) ($d['end_date'] ?? '')),
            'start_date_ar'     => $svc->formatDateAr((string) ($d['start_date'] ?? '')),
            'end_date_ar'       => $svc->formatDateAr((string) ($d['end_date'] ?? '')),
            'signed_date'       => $d['contract_date'] ?? '',
            'plate_number'      => $d['plate_number'] ?? '',
            'vehicle_type'      => $d['vehicle_type'] ?? '',
            'vehicle_description' => $d['vehicle_description'] ?? '',
            'title_deed_no'     => $d['title_deed_no'] ?? '',
            'building_no'       => $d['building_no'] ?? '',
            'zone_no'           => $d['zone_no'] ?? '',
            'street_no'         => $d['street_no'] ?? '',
            'duration_en'       => $d['duration_en'] ?? '',
            'duration_ar'       => $d['duration_ar'] ?? '',
            'cheque_count'      => $d['cheque_count'] ?? 0,
            'collector_company' => $d['collector_company'] ?? '',
            'collector_account' => $d['collector_account'] ?? '',
        ];

        return $this->buildVars($contract, (string) ($d['tenant_qid'] ?? ''));
    }
}
