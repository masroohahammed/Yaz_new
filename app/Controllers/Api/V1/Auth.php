<?php

namespace App\Controllers\Api\V1;

class Auth extends BaseApiController
{
    public function login()
    {
        $payload  = $this->request->getJSON(true) ?? [];
        $email    = $payload['email'] ?? $this->request->getPost('email');
        $password = $payload['password'] ?? $this->request->getPost('password');

        if (! $email || ! $password) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Email and password required',
            ]);
        }

        $user = $this->db->table('users u')
            ->select('u.*, r.name as role_name, r.display_name as role_display')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->where('u.email', $email)
            ->where('u.status', 'active')
            ->get()
            ->getRowArray();

        if (! $user || ! password_verify($password, $user['password'])) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => false,
                'message' => 'Invalid credentials',
            ]);
        }

        $token = bin2hex(random_bytes(32));
        $this->db->table('user_sessions')->insert([
            'user_id'    => $user['id'],
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
        ]);
        $this->db->table('users')->where('id', $user['id'])->update([
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => (int) $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role_name'],
            ],
        ]);
    }

    public function me()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => false,
                'message' => 'User not found',
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'user'   => [
                'id'           => (int) $user['id'],
                'name'         => $user['name'],
                'email'        => $user['email'],
                'phone'        => $user['phone'] ?? '',
                'role'         => $user['role_name'],
                'role_display' => $user['role_display'] ?? $user['role_name'],
                'company_id'   => $user['company_id'] ? (int) $user['company_id'] : null,
            ],
        ]);
    }
}
