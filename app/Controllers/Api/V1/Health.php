<?php

namespace App\Controllers\Api\V1;

/**
 * Public API health check — no authentication required.
 */
class Health extends BaseApiController
{
    public function index()
    {
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'API is healthy',
        ]);
    }
}
