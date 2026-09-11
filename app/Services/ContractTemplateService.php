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
        '{{currency}}',
        '{{payment_frequency}}',
        '{{start_date}}',
        '{{end_date}}',
        '{{signed_date}}',
        '{{security_deposit}}',
        '{{plate_number}}',
        '{{vehicle_type}}',
        '{{vehicle_description}}',
        '{{title_deed_no}}',
        '{{building_no}}',
        '{{zone_no}}',
        '{{street_no}}',
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    /** @param array<string,mixed> $contract */
    public function resolveForContract(array $contract, ?string $tenantQid = null): array
    {
        $typeId = (int) ($contract['contract_type_id'] ?? 0);
        $tplId  = (int) ($contract['template_id'] ?? 0);

        $templateEn = trim((string) ($contract['custom_content_en'] ?? ''));
        $templateAr = trim((string) ($contract['custom_content_ar'] ?? ''));
        $termsEn    = trim((string) ($contract['contract_terms'] ?? ''));
        $termsAr    = '';

        if ($templateEn === '' && $this->db->tableExists('contract_templates')) {
            $q = $this->db->table('contract_templates')->where('is_active', 1);
            if ($tplId > 0) {
                $q->where('id', $tplId);
            } elseif ($typeId > 0 && $this->db->fieldExists('contract_type_id', 'contract_templates')) {
                $q->where('contract_type_id', $typeId);
            }
            $tpl = $q->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($tpl) {
                $templateEn = $tpl['content_en'] ?? '';
                $templateAr = $tpl['content_ar'] ?? '';
                $termsEn    = $termsEn !== '' ? $termsEn : (string) ($tpl['terms_en'] ?? '');
                $termsAr    = (string) ($tpl['terms_ar'] ?? '');
            }
        }

        $vars = $this->buildVars($contract, $tenantQid);

        return [
            'content_en' => strtr($templateEn, $vars),
            'content_ar' => strtr($templateAr, $vars),
            'terms_en'   => strtr($termsEn, $vars),
            'terms_ar'   => strtr($termsAr, $vars),
        ];
    }

    /** @param array<string,mixed> $contract */
    private function buildVars(array $contract, ?string $tenantQid): array
    {
        return [
            '{{contract_number}}'     => esc($contract['contract_number'] ?? ''),
            '{{property_name}}'       => esc($contract['facility_name'] ?? $contract['property_name'] ?? ''),
            '{{unit_number}}'         => esc($contract['unit_number'] ?? ''),
            '{{tenant_name}}'         => esc($contract['tenant_name'] ?? ''),
            '{{tenant_qid}}'          => esc($tenantQid ?? $contract['tenant_qid'] ?? ''),
            '{{rent_amount}}'         => number_format((float) ($contract['rent_amount'] ?? 0), 2),
            '{{currency}}'            => esc($contract['currency'] ?? 'QAR'),
            '{{payment_frequency}}'   => esc($contract['payment_frequency'] ?? ''),
            '{{start_date}}'          => esc($contract['start_date'] ?? ''),
            '{{end_date}}'            => esc($contract['end_date'] ?? ''),
            '{{signed_date}}'         => esc($contract['signed_date'] ?? ''),
            '{{security_deposit}}'    => number_format((float) ($contract['security_deposit'] ?? 0), 2),
            '{{plate_number}}'        => esc($contract['plate_number'] ?? ''),
            '{{vehicle_type}}'        => esc($contract['vehicle_type'] ?? ''),
            '{{vehicle_description}}' => esc($contract['vehicle_description'] ?? ''),
            '{{title_deed_no}}'       => esc($contract['title_deed_no'] ?? ''),
            '{{building_no}}'         => esc($contract['building_no'] ?? ''),
            '{{zone_no}}'             => esc($contract['zone_no'] ?? ''),
            '{{street_no}}'           => esc($contract['street_no'] ?? ''),
        ];
    }
}
