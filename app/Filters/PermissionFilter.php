<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Permissions;

/**
 * Extra controller::method gate for sensitive actions.
 *
 * Does NOT replace auth / rbac / workspace. Those remain the primary layers.
 * This filter is applied only to routes that opt in via Routes.php (finance
 * treasury mutations, contract termination, tenant blacklist, payouts, etc.).
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('user_role');

        if (! $role) {
            return redirect()->to(base_url('login'))->with('error', 'Please log in to continue.');
        }

        $router     = service('router');
        $controller = class_basename((string) $router->controllerName());
        $method     = (string) $router->methodName();

        if ($controller === '' || $method === '') {
            return;
        }

        if (! Permissions::can($role, $controller, $method)) {
            log_message('warning', sprintf(
                'PermissionFilter denied %s::%s for role %s on %s',
                $controller,
                $method,
                $role,
                $request->getUri()->getPath()
            ));

            if (service('request')->isAJAX()) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['status' => 'error', 'message' => 'Access denied.']);
            }

            return redirect()->to(base_url('dashboard'))
                ->with('error', 'You do not have permission to perform that action.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
