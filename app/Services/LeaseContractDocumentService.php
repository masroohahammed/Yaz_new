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

        $contract['currency'] = $systemSettings['currency'] ?? 'QAR';
        $resolved = (new ContractTemplateService($this->db))->resolveForContract($contract, $tenantQid);

        return [
            'contract'           => $contract,
            'settings'           => $branding['settings'],
            'companyBranding'    => $branding,
            'companyLogoUrl'     => $branding['logoUrl'],
            'companyLogoB64'     => $branding['logoB64'],
            'currency'           => $systemSettings['currency'] ?? 'QAR',
            'tenantQid'          => $tenantQid,
            'templateEn'         => $resolved['content_en'],
            'templateAr'         => $resolved['content_ar'],
            'termsEn'            => $resolved['terms_en'],
            'termsAr'            => $resolved['terms_ar'],
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

        $resolved = (new ContractTemplateService($this->db))->resolveForParkingDocument($d, $contract);

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
            'templateEn'         => $resolved['content_en'],
            'templateAr'         => $resolved['content_ar'],
            'termsEn'            => $resolved['terms_en'],
            'termsAr'            => $resolved['terms_ar'],
            'useCustomTemplate'  => true,
        ];
    }
}
