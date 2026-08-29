<?php
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Authentication required']);
        }
        $token = substr($authHeader, 7);
        $db = \Config\Database::connect();
        $session = $db->table('user_sessions')
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();
        if (!$session) {
            return service('response')->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Invalid or expired token']);
        }
        $request->jwt_user_id = $session['user_id'];
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
