<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Builds view data for lease contract documents (print + public sign).
 */
class LeaseContractDocumentService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @param array<string, mixed> $contract */
    public function isParking(array $contract): bool
    {
        return ($contract['contract_kind'] ?? '') === 'parking'
            || strtolower(trim((string) ($contract['unit_type'] ?? ''))) === 'parking';
    }

    /**
     * @param array<string, mixed>  $contract
     * @param array<string, string> $systemSettings
     * @return array<string, mixed>
     */
    public function standardViewData(array $contract, array $systemSettings, string $tenantSignatureB64 = ''): array
    {
        helper('fm');

        $companyId = (int) ($contract['company_id'] ?? 1) ?: 1;
        $branding  = fm_company_branding($systemSettings, $companyId);
        $svc       = new ContractSignatureService($this->db);
        $tenantQid = $svc->tenantQid($contract);

        $templateEn = (string) ($contract['custom_content_en'] ?? '');
        $templateAr = (string) ($contract['custom_content_ar'] ?? '');

        if ($templateEn === '' && $this->db->tableExists('contract_templates')) {
            $tplId = $contract['template_id'] ?? null;
            $q     = $this->db->table('contract_templates')->where('is_active', 1);
            if ($tplId) {
                $q->where('id', $tplId);
            }
            $tpl = $q->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($tpl) {
                $templateEn = (string) ($tpl['content_en'] ?? '');
                $templateAr = (string) ($tpl['content_ar'] ?? '');
            }
        }

        $vars = [
            '{{unit_number}}'       => esc($contract['unit_number'] ?? ''),
            '{{property_name}}'     => esc($contract['facility_name'] ?? ''),
            '{{tenant_name}}'       => esc($contract['tenant_name'] ?? ''),
            '{{tenant_qid}}'        => esc($tenantQid),
            '{{rent_amount}}'       => number_format((float) ($contract['rent_amount'] ?? 0), 2),
            '{{currency}}'          => $systemSettings['currency'] ?? 'QAR',
            '{{payment_frequency}}' => esc($contract['payment_frequency'] ?? ''),
            '{{start_date}}'        => esc($contract['start_date'] ?? ''),
            '{{end_date}}'          => esc($contract['end_date'] ?? ''),
            '{{contract_number}}'   => esc($contract['contract_number'] ?? ''),
        ];

        return [
            'contract'           => $contract,
            'settings'           => $branding['settings'],
            'companyBranding'    => $branding,
            'companyLogoUrl'     => $branding['logoUrl'],
            'companyLogoB64'     => $branding['logoB64'],
            'currency'           => $systemSettings['currency'] ?? 'QAR',
            'tenantQid'          => $tenantQid,
            'templateEn'         => strtr($templateEn, $vars),
            'templateAr'         => strtr($templateAr, $vars),
            'tenantSignatureB64' => $tenantSignatureB64,
            'usePdf'             => true,
        ];
    }

    /**
     * @param array<string, mixed>  $contract
     * @param array<string, string> $systemSettings
     * @return array<string, mixed>
     */
    public function parkingViewData(array $contract, array $systemSettings, string $tenantSignatureB64 = ''): array
    {
        helper('fm');

        $companyId = (int) ($contract['company_id'] ?? 1) ?: 1;
        $branding  = fm_company_branding($systemSettings, $companyId);
        $svc       = new ParkingContractService($this->db);
        $unitId    = (int) ($contract['unit_id'] ?? 0);
        $d         = $svc->buildDefaults($unitId, (int) ($contract['id'] ?? 0));
        $contractDate = (string) ($d['contract_date'] ?? date('Y-m-d'));

        return [
            'd'                  => $d,
            'settings'           => $branding['settings'],
            'companyBranding'    => $branding,
            'companyLogoUrl'     => $branding['logoUrl'],
            'companyLogoB64'     => $branding['logoB64'],
            'usePdf'             => true,
            'tenantSignatureB64' => $tenantSignatureB64,
            'englishDay'         => $svc->englishDayName($contractDate),
            'arabicDay'          => $svc->arabicDayName($contractDate),
            'contractDateEn'     => $svc->formatDateEnLong($contractDate),
            'contractDateAr'     => $svc->formatDateAr($contractDate),
            'startDateEn'        => $svc->formatDateEn((string) ($d['start_date'] ?? '')),
            'endDateEn'          => $svc->formatDateEn((string) ($d['end_date'] ?? '')),
            'startDateAr'        => $svc->formatDateAr((string) ($d['start_date'] ?? '')),
            'endDateAr'          => $svc->formatDateAr((string) ($d['end_date'] ?? '')),
            'poaDateFmt'         => $svc->formatPoaDate((string) ($d['poa_date'] ?? '')),
            'vehicleEn'          => $svc->vehicleTypeEnglish((string) ($d['vehicle_type'] ?? '')),
        ];
    }
}
