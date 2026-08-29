<?php
namespace App\Controllers;

class Profile extends BaseController
{
    public function index()
    {
        return view('profile/index', $this->viewData([
            'title'       => 'My Profile',
            'currentUser' => $this->currentUserProfile(),
        ]));
    }

    public function update()
    {
        $id = session()->get('user_id');
        $this->db->table('users')->where('id',$id)->update([
            'name'  => esc($this->request->getPost('name')),
            'phone' => esc($this->request->getPost('phone')),
        ]);
        session()->set('user_name', esc($this->request->getPost('name')));
        return redirect()->to(base_url('profile'))->with('success','Profile updated.');
    }

    public function changePassword()
    {
        $id   = session()->get('user_id');
        $user = $this->db->table('users')->where('id',$id)->get()->getRowArray();
        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error','Current password is incorrect.');
        }
        $new = $this->request->getPost('new_password');
        $con = $this->request->getPost('confirm_password');
        if ($new !== $con) return redirect()->back()->with('error','New passwords do not match.');

        $policyError = $this->checkPasswordPolicy($new);
        if ($policyError) return redirect()->back()->with('error', $policyError);

        $update = [
            'password' => password_hash($new, PASSWORD_BCRYPT),
        ];
        if ($this->db->fieldExists('password_changed_at', 'users')) {
            $update['password_changed_at'] = date('Y-m-d H:i:s');
        }
        $this->db->table('users')->where('id',$id)->update($update);
        return redirect()->to(base_url('profile'))->with('success','Password changed successfully.');
    }

    private function checkPasswordPolicy(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }
        return null;
    }

    public function forcePasswordChange()
    {
        if (!session()->get('logged_in')) return redirect()->to(base_url('login'));

        if ($this->request->is('post')) {
            $new = $this->request->getPost('new_password');
            $con = $this->request->getPost('confirm_password');
            if ($new !== $con) return redirect()->back()->with('error','Passwords do not match.');

            $policyError = $this->checkPasswordPolicy($new);
            if ($policyError) return redirect()->back()->with('error', $policyError);

            $id     = session()->get('user_id');
            $update = ['password' => password_hash($new, PASSWORD_BCRYPT)];
            if ($this->db->fieldExists('password_changed_at', 'users')) {
                $update['password_changed_at'] = date('Y-m-d H:i:s');
            }
            $this->db->table('users')->where('id', $id)->update($update);
            session()->remove('force_password_change');
            return redirect()->to(base_url('dashboard'))->with('success','Password updated successfully.');
        }

        return view('profile/force_password_change', $this->viewData(['title' => 'Change Password Required']));
    }
}
