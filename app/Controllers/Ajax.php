<?php
namespace App\Controllers;

/**
 * Ajax — handles all AJAX/JSON endpoint requests.
 * Routes: defined in Routes.php under 'ajax/*'
 */
class Ajax extends BaseController
{
    // ── Live Work Orders ─────────────────────────────────────

    public function liveWorkOrders()
    {
        $role = session()->get('user_role');
        $uid  = session()->get('user_id');

        $q = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.status, w.priority, w.sla_breached, w.sla_due,
                      f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=w.assigned_to','left')
            ->whereNotIn('w.status',['cancelled','closed','completed']);

        if ($role === 'technician') $q->where('w.assigned_to', $uid);

        $wos = $q->orderBy('CASE w.priority WHEN \'critical\' THEN 1 WHEN \'high\' THEN 2 WHEN \'medium\' THEN 3 ELSE 4 END', '')
                 ->orderBy('w.created_at','DESC')
                 ->limit(20)->get()->getResultArray();

        return $this->response->setJSON(['status' => true, 'data' => $wos]);
    }

    // ── Notifications ────────────────────────────────────────

    public function notifications()
    {
        $uid = session()->get('user_id');
        $count = $this->db->table('notifications')
            ->where('user_id', $uid)->where('is_read', 0)->countAllResults();

        $recent = $this->db->table('notifications')
            ->where('user_id', $uid)
            ->orderBy('created_at','DESC')
            ->limit(10)->get()->getResultArray();

        return $this->response->setJSON([
            'status'  => true,
            'count'   => $count,
            'items'   => $recent,
        ]);
    }

    // ── Dashboard Stats ──────────────────────────────────────

    public function dashboardStats()
    {
        $role = session()->get('user_role');
        $uid  = session()->get('user_id');

        $data = [];

        if ($role === 'technician') {
            $data = [
                'assigned'    => $this->db->table('work_orders')->where('assigned_to',$uid)->where('status','assigned')->countAllResults(),
                'in_progress' => $this->db->table('work_orders')->where('assigned_to',$uid)->where('status','in_progress')->countAllResults(),
                'completed_today' => $this->db->table('work_orders')->where('assigned_to',$uid)->where('status','completed')->where('DATE(completed_at)', date('Y-m-d'))->countAllResults(),
            ];
        } else {
            $data = [
                'open_wo'  => $this->db->table('work_orders')->whereIn('status',['new','assigned'])->countAllResults(),
                'overdue'  => $this->db->table('work_orders')->where('sla_breached',1)->whereNotIn('status',['completed','closed','cancelled'])->countAllResults(),
                'low_stock'=> $this->db->table('inventory_items')->where('quantity <=', $this->db->escapeIdentifiers('min_quantity'), false)->countAllResults(),
            ];
        }

        return $this->response->setJSON(['status' => true, 'data' => $data]);
    }

    // ── Inventory Price Lookup ───────────────────────────────

    public function inventoryPrice(int $itemId)
    {
        $item = $this->db->table('inventory_items')
            ->select('id, name, unit_cost, quantity, unit, item_code')
            ->where('id', $itemId)->get()->getRowArray();

        if (!$item) {
            return $this->response->setJSON(['status' => false, 'message' => 'Item not found']);
        }

        return $this->response->setJSON([
            'status'    => true,
            'unit_cost' => (float)$item['unit_cost'],
            'quantity'  => (float)$item['quantity'],
            'unit'      => $item['unit'],
            'name'      => $item['name'],
        ]);
    }


    private function canAccessWoChat(array $wo): bool
    {
        $uid  = (int) session()->get('user_id');
        $role = (string) session()->get('user_role');

        if (in_array($role, ['super_admin', 'facility_manager', 'helpdesk'], true)) {
            return true;
        }

        if ($role === 'supervisor' && (int) ($wo['supervisor_id'] ?? 0) === $uid) {
            return true;
        }

        if ($role === 'technician') {
            if ((int) ($wo['assigned_to'] ?? 0) === $uid) {
                return true;
            }
            $onJc = $this->db->table('job_cards')
                ->where('wo_id', $wo['id'])
                ->where('assigned_to', $uid)
                ->where('deleted_at', null)
                ->countAllResults();
            return $onJc > 0;
        }

        return false;
    }

    // ── Work Order Chat ──────────────────────────────────────

    public function woChat(int $woId)
    {
        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (! $wo) {
            return $this->response->setJSON(['status' => false, 'message' => 'WO not found']);
        }
        if (! $this->canAccessWoChat($wo)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Access denied']);
        }

        $afterId = (int)($this->request->getGet('after') ?? 0);

        $messages = $this->db->table('wo_chat_messages m')
            ->select('m.id, m.wo_id, m.user_id, m.message, m.created_at, u.name AS sender_name')
            ->join('users u','u.id=m.user_id','left')
            ->where('m.wo_id', $woId)
            ->where('m.id >', $afterId)
            ->orderBy('m.created_at','ASC')
            ->get()->getResultArray();

        // Format timestamps
        foreach ($messages as &$m) {
            $m['created_at'] = date('d M H:i', strtotime($m['created_at']));
        }

        return $this->response->setJSON([
            'status'   => true,
            'messages' => $messages,
        ]);
    }

    public function sendWoChat(int $woId)
    {
        // POST: add a new chat message (CSRF handled globally)
        $message = trim($this->request->getPost('message') ?? '');
        if (empty($message)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Message cannot be empty']);
        }

        $uid = session()->get('user_id');

        // IDOR check: user must be assigned to or managing this WO
        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (!$wo) return $this->response->setJSON(['status' => false, 'message' => 'WO not found']);

        if (! $this->canAccessWoChat($wo)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Access denied']);
        }

        $this->db->table('wo_chat_messages')->insert([
            'wo_id'      => $woId,
            'user_id'    => $uid,
            'message'    => esc($message),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $newId = $this->db->insertID();

        $user = $this->db->table('users')->select('name')->where('id', $uid)->get()->getRowArray();

        $msg = [
            'id'          => $newId,
            'wo_id'       => $woId,
            'user_id'     => $uid,
            'message'     => esc($message),
            'sender_name' => $user['name'] ?? 'You',
            'created_at'  => date('d M H:i'),
        ];

        // Return fresh CSRF token
        $csrfName  = csrf_token();
        $csrfValue = csrf_hash();

        return $this->response->setJSON([
            'status' => true,
            'msg'    => $msg,
            $csrfName => $csrfValue,
        ]);
    }
}
