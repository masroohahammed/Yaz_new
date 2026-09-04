<?php
namespace App\Controllers;

use App\Services\UserFacilityService;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS     = 5;
    private const LOCKOUT_MINUTES  = 15;

    public function login()
    {
        $this->captureLoginRedirect();

        if (session()->get('logged_in')) {
            return $this->postLoginRedirect();
        }

        return view('auth/login', [
            'settings'       => $this->settings,
            'companyLogoUrl' => $this->logoUrl(),
            'title'          => 'Login',
            'redirectAfter'  => session()->get('redirect_after_login'),
        ]);
    }

    public function doLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $ip    = $this->request->getIPAddress();

        // Brute force lockout check
        if ($this->isLockedOut($email, $ip)) {
            return redirect()->back()->withInput()
                ->with('error', 'Too many failed login attempts. Try again in '.self::LOCKOUT_MINUTES.' minutes.');
        }

        $password = $this->request->getPost('password');

        $user = $this->db->table('users u')
            ->select('u.*, r.name as role_name, r.display_name as role_display')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.email', $email)
            ->where('u.status', 'active')
            ->get()->getRowArray();

        if (! $user || ! password_verify($password, $user['password'])) {
            $this->logLoginAttempt($email, false);
            return redirect()->back()->withInput()
                ->with('error', 'Invalid email or password. Please try again.');
        }

        $this->logLoginAttempt($email, true);

        // MFA check
        if (!empty($user['mfa_enabled']) && !empty($user['mfa_secret'])) {
            session()->set('pending_user_id', (int) $user['id']);
            return redirect()->to(base_url('auth/mfa'));
        }

        $this->completeLogin($user);

        return $this->postLoginRedirect();
    }

    public function mfaVerify()
    {
        $pendingId = (int) session()->get('pending_user_id');
        if (!$pendingId) return redirect()->to(base_url('login'));

        if ($this->request->is('get')) {
            return view('auth/mfa', [
                'settings'       => $this->settings,
                'companyLogoUrl' => $this->logoUrl(),
                'title'          => 'Two-Factor Authentication',
            ]);
        }

        $code = trim((string) $this->request->getPost('totp_code'));
        $user = $this->db->table('users u')
            ->select('u.*, r.name as role_name, r.display_name as role_display')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $pendingId)
            ->get()->getRowArray();

        if (!$user || !$this->verifyTotp((string) ($user['mfa_secret'] ?? ''), $code)) {
            return redirect()->back()->with('error', 'Invalid 2FA code. Please try again.');
        }

        session()->remove('pending_user_id');
        $this->completeLogin($user);

        return $this->postLoginRedirect();
    }

    public function mfaSetup()
    {
        $userId = (int) session()->get('user_id');
        if (!$userId) return redirect()->to(base_url('login'));

        if ($this->request->is('post')) {
            $action = $this->request->getPost('action');
            if ($action === 'generate') {
                $secret = $this->generateTotpSecret();
                $this->db->table('users')->where('id', $userId)->update(['mfa_secret' => $secret]);
                $user = $this->db->table('users')->where('id', $userId)->get()->getRowArray();
                $label = urlencode($user['email'] ?? 'user');
                $otpUrl = "otpauth://totp/{$label}?secret={$secret}&issuer=FMERP";
                return redirect()->to(base_url('auth/mfa-setup'))->with('mfa_secret', $secret)->with('otp_url', $otpUrl);
            } elseif ($action === 'enable') {
                $code = trim((string) $this->request->getPost('totp_code'));
                $user = $this->db->table('users')->where('id', $userId)->get()->getRowArray();
                if ($this->verifyTotp((string) ($user['mfa_secret'] ?? ''), $code)) {
                    $this->db->table('users')->where('id', $userId)->update(['mfa_enabled' => 1]);
                    return redirect()->to(base_url('profile'))->with('success', 'Two-factor authentication enabled.');
                }
                return redirect()->back()->with('error', 'Invalid code. Please try again.');
            } elseif ($action === 'disable') {
                $this->db->table('users')->where('id', $userId)->update(['mfa_enabled' => 0, 'mfa_secret' => null]);
                return redirect()->to(base_url('profile'))->with('success', 'Two-factor authentication disabled.');
            }
        }

        $user   = $this->db->table('users')->where('id', $userId)->get()->getRowArray();
        $secret = $user['mfa_secret'] ?? '';
        $otpUrl = '';
        if ($secret) {
            $label  = urlencode($user['email'] ?? 'user');
            $otpUrl = "otpauth://totp/{$label}?secret={$secret}&issuer=FMERP";
        }
        return view('auth/mfa_setup', [
            'settings'       => $this->settings,
            'companyLogoUrl' => $this->logoUrl(),
            'title'          => 'MFA Setup',
            'user'           => $user,
            'mfa_secret'     => $secret,
            'otp_url'        => $otpUrl,
        ]);
    }

    private function completeLogin(array $user): void
    {
        $workspaceService = new \App\Services\WorkspaceService($this->db);
        $workspace        = $workspaceService->sessionWorkspace((string) $user['role_name']);
        $landlordId       = 0;
        if (($user['role_name'] ?? '') === 'landlord') {
            $landlordId = UserFacilityService::landlordIdForUser($this->db, (int) $user['id'], (string) ($user['email'] ?? ''));
        }

        session()->set([
            'logged_in'    => true,
            'user_id'      => (int) $user['id'],
            'user_name'    => $user['name'],
            'user_email'   => $user['email'],
            'user_role'    => $user['role_name'],
            'role_display' => $user['role_display'],
            'company_id'   => $user['company_id'] ?? null,
            'workspace'    => $workspace,
            'landlord_id'  => $landlordId > 0 ? $landlordId : null,
        ]);

        session()->regenerate(false);

        $update = ['last_login' => date('Y-m-d H:i:s')];
        if ($this->db->fieldExists('password_changed_at', 'users') && empty($user['password_changed_at'])) {
            $update['password_changed_at'] = date('Y-m-d H:i:s');
        }
        $this->db->table('users')->where('id', $user['id'])->update($update);

        // Force password change after 90 days
        if ($this->db->fieldExists('password_changed_at', 'users') && ! empty($user['password_changed_at'])) {
            $changed = strtotime((string) $user['password_changed_at']);
            if ($changed && $changed < strtotime('-90 days')) {
                session()->set('force_password_change', true);
                redirect()->to(base_url('profile/force-password-change'))->send();
                exit;
            }
        }
    }

    private function isLockedOut(string $email, string $ip): bool
    {
        if (!$this->db->tableExists('login_attempts')) return false;

        $since   = date('Y-m-d H:i:s', strtotime('-'.self::LOCKOUT_MINUTES.' minutes'));
        $count   = $this->db->table('login_attempts')
            ->where('success', 0)
            ->where('created_at >=', $since)
            ->groupStart()
            ->where('email', $email)
            ->orWhere('ip_address', $ip)
            ->groupEnd()
            ->countAllResults();

        return $count >= self::MAX_ATTEMPTS;
    }

    /**
     * Simple TOTP implementation (RFC 6238, SHA-1, 30-second window).
     * Compatible with Google Authenticator.
     */
    public function verifyTotp(string $base32Secret, string $code, int $window = 1): bool
    {
        $secret  = $this->base32Decode($base32Secret);
        $counter = (int) floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            if ($this->hotp($secret, $counter + $i) === $code) {
                return true;
            }
        }
        return false;
    }

    private function hotp(string $key, int $counter): string
    {
        $data = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $data, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;
        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $base32): string
    {
        static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper(rtrim($base32, '='));
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;
        foreach (str_split($base32) as $char) {
            $val = strpos($alphabet, $char);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        return $output;
    }

    private function generateTotpSecret(int $length = 16): string
    {
        static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, 31)];
        }
        return $secret;
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))
            ->with('success', 'You have been logged out successfully.');
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/register', [
            'settings'       => $this->settings,
            'companyLogoUrl' => $this->logoUrl(),
            'title'          => 'Register',
        ]);
    }

    public function doRegister()
    {
        $rules = [
            'name'             => 'required|min_length[2]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];
        $messages = [
            'email'            => ['is_unique' => 'This email is already registered.'],
            'confirm_password' => ['matches'   => 'Passwords do not match.'],
        ];
        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $clientRoleId = $this->roleIdByName('client');
        if (!$clientRoleId) {
            return redirect()->back()->with('error', 'Client role is not configured. Contact administrator.');
        }
        $this->db->table('users')->insert([
            'role_id'  => $clientRoleId,
            'name'     => esc($this->request->getPost('name')),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'status'   => 'pending',
        ]);

        return redirect()->to(base_url('login'))
            ->with('success', 'Account created! An administrator must approve your account before you can log in.');
    }

    private function logLoginAttempt(string $email, bool $success): void
    {
        try {
            if ($this->db->tableExists('login_attempts')) {
                $this->db->table('login_attempts')->insert([
                    'email'      => strtolower(trim($email)),
                    'ip_address' => $this->request->getIPAddress(),
                    'success'    => $success ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'login_attempt log failed: ' . $e->getMessage());
        }
    }

    private function captureLoginRedirect(): void
    {
        $redirect = $this->request->getGet('redirect');
        if (! is_string($redirect) || trim($redirect) === '') {
            return;
        }

        $redirect = trim($redirect);
        $base     = rtrim(base_url(), '/');
        if (str_starts_with($redirect, $base)) {
            session()->set('redirect_after_login', $redirect);

            return;
        }

        if (str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            session()->set('redirect_after_login', base_url(ltrim($redirect, '/')));
        }
    }

    private function postLoginRedirect(): \CodeIgniter\HTTP\RedirectResponse
    {
        $target = session()->get('redirect_after_login');
        if (is_string($target) && $target !== '') {
            session()->remove('redirect_after_login');

            return redirect()->to($target);
        }

        $ws = session()->get('workspace');
        if ($ws === 'portal') {
            return redirect()->to(base_url('portal'));
        }
        if ($ws === 'collector') {
            return redirect()->to(base_url('collector'));
        }

        return redirect()->to(base_url('dashboard'));
    }
}
