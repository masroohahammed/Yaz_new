<?php

namespace App\Controllers\Traits;

use App\Services\RbacService;

/**
 * Centralized HR permission checks — always use server-side, never UI-only.
 */
trait HrRbacTrait
{
    protected function hrRbac(): RbacService
    {
        return new RbacService($this->db ?? null);
    }

    protected function hrRole(): string
    {
        return (string) session()->get('user_role');
    }

    protected function hrCan(string $permission): bool
    {
        return $this->hrRbac()->can($this->hrRole(), $permission);
    }

    protected function hrCanAny(string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hrCan($permission)) {
                return true;
            }
        }

        return false;
    }

    protected function requireHrPermission(string $permission, ?string $message = null): void
    {
        if ($this->hrCan($permission)) {
            return;
        }
        $message ??= 'You do not have permission to perform this action.';
        if ($this->request->isAJAX()) {
            $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => $message])->send();
            exit;
        }
        session()->setFlashdata('error', $message);
        redirect()->back()->send();
        exit;
    }

    /** @return array<string, bool> */
    protected function hrPermissionFlags(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->hrCan($key);
        }

        return $out;
    }
}
