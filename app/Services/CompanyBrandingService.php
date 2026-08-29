<?php

namespace App\Services;

/**
 * Letterhead / footer branding from the companies table (Settings → Companies).
 */
class CompanyBrandingService
{
    public function __construct(private $db)
    {
    }

    public function resolveCompanyId(?int $preferred = null): int
    {
        if ($preferred !== null && $preferred > 0) {
            return $preferred;
        }

        $sessionId = (int) session()->get('company_id');
        if ($sessionId > 0) {
            return $sessionId;
        }

        return 1;
    }

    /**
     * @param array<string,string> $systemSettings
     * @return array{
     *   company_id: int,
     *   settings: array<string,string>,
     *   logoUrl: string,
     *   logoB64: string,
     *   row: array<string,mixed>|null
     * }
     */
    public function branding(array $systemSettings = [], ?int $companyId = null): array
    {
        helper('fm');

        $companyId = $this->resolveCompanyId($companyId);
        $row       = null;

        if ($this->db->tableExists('companies')) {
            $row = $this->db->table('companies')->where('id', $companyId)->get()->getRowArray();
            if (! $row && $companyId !== 1) {
                $row = $this->db->table('companies')->where('id', 1)->get()->getRowArray();
                $companyId = $row ? 1 : $companyId;
            }
        }

        $merged = $systemSettings;
        $logoStored = trim((string) ($systemSettings['company_logo'] ?? ''));

        if ($row) {
            if (! empty($row['name'])) {
                $merged['company_name'] = (string) $row['name'];
            }
            if (! empty($row['code'])) {
                $merged['company_code'] = (string) $row['code'];
            }
            if (! empty($row['address'])) {
                $merged['company_address'] = (string) $row['address'];
            }
            if (! empty($row['phone'])) {
                $merged['company_phone'] = (string) $row['phone'];
            }
            if (! empty($row['email'])) {
                $merged['company_email'] = (string) $row['email'];
            }
            if (! empty($row['contact_person'])) {
                $merged['company_contact'] = (string) $row['contact_person'];
            }
            if (! empty($row['vat_number'])) {
                $merged['company_vat'] = (string) $row['vat_number'];
            }
            if (! empty($row['logo'])) {
                $logoStored = (string) $row['logo'];
                $merged['company_logo'] = $logoStored;
            }
        }

        $logoUrl = fm_logo_url($logoStored);
        $logoB64 = fm_logo_data_uri($logoStored);

        return [
            'company_id' => $companyId,
            'settings'   => $merged,
            'logoUrl'    => $logoUrl,
            'logoB64'    => $logoB64,
            'row'        => $row,
        ];
    }
}
