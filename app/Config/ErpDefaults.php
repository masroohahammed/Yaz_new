<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Central ERP fallbacks when system_settings or env are unset.
 */
class ErpDefaults extends BaseConfig
{
    public string $defaultCurrency = 'QAR';

    public float $defaultVatRate = 5.0;

    public string $defaultCompanyName = 'FM ERP';

    public int $apiSessionHours = 24;

    public int $apiListLimit = 50;

    public string $qrServiceUrl = 'https://api.qrserver.com/v1/create-qr-code/';

    public string $alertFromEmail = 'noreply@localhost';

    public int $invoiceDueDays = 30;

    public int $contractExpiryWarningDays = 30;

    public int $reportExportLimit = 5000;

    public function __construct()
    {
        parent::__construct();

        $this->defaultCurrency = (string) ($_ENV['erp.defaultCurrency'] ?? getenv('erp.defaultCurrency') ?: $this->defaultCurrency);
        $this->defaultVatRate = (float) ($_ENV['erp.defaultVatRate'] ?? getenv('erp.defaultVatRate') ?: $this->defaultVatRate);
        $this->defaultCompanyName = (string) ($_ENV['erp.defaultCompanyName'] ?? getenv('erp.defaultCompanyName') ?: $this->defaultCompanyName);
        $this->apiSessionHours = (int) ($_ENV['erp.apiSessionHours'] ?? getenv('erp.apiSessionHours') ?: $this->apiSessionHours);
        $this->apiListLimit = (int) ($_ENV['erp.apiListLimit'] ?? getenv('erp.apiListLimit') ?: $this->apiListLimit);
        $this->qrServiceUrl = (string) ($_ENV['erp.qrServiceUrl'] ?? getenv('erp.qrServiceUrl') ?: $this->qrServiceUrl);
    }
}
