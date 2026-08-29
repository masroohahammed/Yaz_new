<?php

namespace App\Controllers\Api\V1;

class AppLog extends BaseApiController
{
    /**
     * POST /api/v1/app-log — mobile client telemetry (no JWT required).
     */
    public function store()
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => false,
                'message' => 'Method not allowed',
            ]);
        }

        if (! $this->db->tableExists('app_mobile_logs')) {
            return $this->response->setStatusCode(503)->setJSON([
                'status'  => false,
                'message' => 'App log table missing. Run database/app_mobile_logs_patch.sql',
            ]);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        $action = trim((string) ($payload['action'] ?? ''));
        if ($action === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'action is required',
            ]);
        }

        $status = strtolower(trim((string) ($payload['status'] ?? 'info')));
        if (! in_array($status, ['info', 'success', 'error'], true)) {
            $status = 'info';
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        if ($userId < 1) {
            $jwtId = (int) ($this->request->jwt_user_id ?? 0);
            if ($jwtId > 0) {
                $userId = $jwtId;
            }
        }

        $context = $payload['context'] ?? [];
        if (! is_array($context)) {
            $context = ['raw' => (string) $context];
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '' && $context !== []) {
            $message = json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $this->db->table('app_mobile_logs')->insert([
            'user_id'      => $userId > 0 ? $userId : null,
            'action'       => substr($action, 0, 120),
            'status'       => substr($status, 0, 20),
            'message'      => $message !== '' ? $message : null,
            'context_json' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'app_version'  => substr((string) ($payload['app_version'] ?? ''), 0, 32),
            'platform'     => substr((string) ($payload['platform'] ?? ''), 0, 32),
            'ip_address'   => $this->request->getIPAddress(),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Logged',
        ]);
    }
}
