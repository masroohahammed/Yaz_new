<?php

namespace App\Controllers\Api;

/**
 * @deprecated Use /api/v1/work-orders. Kept as a compatibility proxy.
 */
class WorkOrders extends \App\Controllers\Api\V1\WorkOrders
{
    private function deprecate(): void
    {
        $this->response->setHeader('Deprecation', 'true');
        $this->response->setHeader('Sunset', 'Sat, 29 Aug 2027 00:00:00 GMT');
        $this->response->setHeader('Link', '</api/v1/work-orders>; rel="successor-version"');
    }

    public function index()
    {
        $this->deprecate();

        return parent::index();
    }

    public function show(int $id)
    {
        $this->deprecate();

        return parent::show($id);
    }

    public function create()
    {
        $this->deprecate();

        return parent::create();
    }

    public function update(int $id)
    {
        $this->deprecate();

        return parent::update($id);
    }

    public function delete(int $id)
    {
        $this->deprecate();

        return parent::delete($id);
    }
}
