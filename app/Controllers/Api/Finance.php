<?php

namespace App\Controllers\Api;

/**
 * @deprecated Use /api/v1/finance/invoices. Kept as a compatibility proxy.
 */
class Finance extends \App\Controllers\Api\V1\Invoices
{
    private function deprecate(): void
    {
        $this->response->setHeader('Deprecation', 'true');
        $this->response->setHeader('Link', '</api/v1/finance/invoices>; rel="successor-version"');
    }

    public function invoices()
    {
        $this->deprecate();

        return parent::index();
    }

    public function createInvoice()
    {
        $this->deprecate();

        return parent::create();
    }
}
