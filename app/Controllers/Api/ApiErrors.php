<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * Returns JSON errors for unmatched API routes; HTML 404 for the web app.
 */
class ApiErrors extends BaseController
{
    public function notFound()
    {
        $path = trim($this->request->getUri()->getPath(), '/');

        if ($this->isApiPath($path)) {
            return $this->response
                ->setStatusCode(404)
                ->setContentType('application/json')
                ->setJSON([
                    'status'  => false,
                    'message' => 'Endpoint not found',
                ]);
        }

        return view('errors/html/error_404');
    }

    private function isApiPath(string $path): bool
    {
        if ($path === 'api' || str_starts_with($path, 'api/')) {
            return true;
        }

        // baseURL includes /public/ — path may be public/api/...
        if (str_starts_with($path, 'public/api/') || $path === 'public/api') {
            return true;
        }

        return false;
    }
}
