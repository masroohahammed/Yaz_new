<?php

namespace App\Controllers\Api;

/**
 * @deprecated Use /api/v1/auth/login. Compatibility proxy for existing mobile clients.
 */
class Auth extends \App\Controllers\Api\V1\Auth
{
    public function login()
    {
        $this->response->setHeader('Deprecation', 'true');
        $this->response->setHeader('Link', '</api/v1/auth/login>; rel="successor-version"');

        return parent::login();
    }

    public function register()
    {
        $this->response->setHeader('Deprecation', 'true');
        $data = $this->request->getJSON(true) ?? [];
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        if (! $name || ! $email || ! $password) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Name, email, and password required']);
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Invalid email']);
        }
        if ($this->db->table('users')->where('email', $email)->countAllResults()) {
            return $this->response->setStatusCode(409)->setJSON(['status' => false, 'message' => 'Email already registered']);
        }
        $clientRoleId = $this->roleIdByName('client');
        if (! $clientRoleId) {
            return $this->response->setStatusCode(500)->setJSON(['status' => false, 'message' => 'Client role not configured']);
        }
        $this->db->table('users')->insert([
            'role_id'  => $clientRoleId,
            'name'     => esc($name),
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'status'   => 'active',
        ]);

        return $this->response->setStatusCode(201)->setJSON(['status' => true, 'message' => 'Account created']);
    }
}
