<?php

namespace App\Controllers;

use App\Services\EntityQrService;

/**
 * In-app QR scanner for field staff (property managers, supervisors, technicians).
 */
class Scan extends BaseController
{
    protected ?string $workspaceRequired = null;

    public function index()
    {
        $role = (string) (session()->get('user_role') ?? '');
        if (! EntityQrService::roleCanScan($role)) {
            return redirect()->to(base_url('dashboard'))->with('error', 'QR scanner is not available for your role.');
        }

        return view('scan/index', $this->viewData([
            'title'     => 'QR Scanner',
            'pageTitle' => 'QR Scanner',
        ]));
    }
}
