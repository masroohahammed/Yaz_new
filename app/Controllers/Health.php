<?php

namespace App\Controllers;

/**
 * Public health check for load balancers and monitoring.
 */
class Health extends BaseController
{
    public function index()
    {
        $checks = ['app' => 'ok', 'database' => 'unknown'];

        try {
            $this->db->query('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'fail';
        }

        $ok = $checks['database'] === 'ok';

        return $this->response
            ->setStatusCode($ok ? 200 : 503)
            ->setJSON([
                'status'    => $ok ? 'healthy' : 'degraded',
                'service'   => 'fm-erp',
                'timestamp' => date('c'),
                'checks'    => $checks,
            ]);
    }
}
