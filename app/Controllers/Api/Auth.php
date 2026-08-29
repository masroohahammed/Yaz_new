<?php
namespace App\Controllers\Api;
use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function login()
    {
        $email    = $this->request->getJSON(true)['email'] ?? $this->request->getPost('email');
        $password = $this->request->getJSON(true)['password'] ?? $this->request->getPost('password');
        if (!$email || !$password) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'Email and password required']);
        $user = $this->db->table('users u')->select('u.*, r.name as role_name')->join('roles r','r.id=u.role_id','left')
            ->where('u.email',$email)->where('u.status','active')->get()->getRowArray();
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->response->setStatusCode(401)->setJSON(['status'=>false,'message'=>'Invalid credentials']);
        }
        $token = bin2hex(random_bytes(32));
        $this->db->table('user_sessions')->insert(['user_id'=>$user['id'],'token'=>$token,'expires_at'=>date('Y-m-d H:i:s',strtotime('+24 hours'))]);
        $this->db->table('users')->where('id',$user['id'])->update(['last_login'=>date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['status'=>true,'message'=>'Login successful','token'=>$token,'user'=>['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role_name']]]);
    }

    public function register()
    {
        $data = $this->request->getJSON(true) ?? [];
        $name  = $data['name'] ?? ''; $email = $data['email'] ?? ''; $password = $data['password'] ?? '';
        if (!$name||!$email||!$password) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'Name, email, and password required']);
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) return $this->response->setStatusCode(400)->setJSON(['status'=>false,'message'=>'Invalid email']);
        $exists = $this->db->table('users')->where('email',$email)->countAllResults();
        if ($exists) return $this->response->setStatusCode(409)->setJSON(['status'=>false,'message'=>'Email already registered']);
        $clientRoleId = $this->roleIdByName('client');
        if (!$clientRoleId) {
            return $this->response->setStatusCode(500)->setJSON(['status' => false, 'message' => 'Client role not configured']);
        }
        $this->db->table('users')->insert([
            'role_id'  => $clientRoleId,
            'name'     => esc($name),
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'status'   => 'active',
        ]);
        return $this->response->setStatusCode(201)->setJSON(['status'=>true,'message'=>'Account created']);
    }
}
