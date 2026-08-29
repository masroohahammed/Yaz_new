<?php
namespace App\Controllers;

class Notifications extends BaseController
{
    public function index()
    {
        $uid = session()->get('user_id');
        $notifications = $this->db->table('notifications')->where('user_id',$uid)->orderBy('created_at','DESC')->limit(50)->get()->getResultArray();
        $this->db->table('notifications')->where('user_id',$uid)->update(['is_read'=>1]);
        cache()->delete('fm_unread_' . $uid);
        return view('notifications/index', $this->viewData(['title'=>'Notifications','notifications'=>$notifications]));
    }

    public function markRead(int $id)
    {
        $uid = (int) session()->get('user_id');
        $this->db->table('notifications')->where('id',$id)->where('user_id',$uid)->update(['is_read'=>1]);
        cache()->delete('fm_unread_' . $uid);
        return $this->response->setJSON(['status'=>true]);
    }

    public function recent()
    {
        $uid = (int) session()->get('user_id');
        if ($uid < 1) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $rows = $this->db->table('notifications')
            ->where('user_id', $uid)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $ts = strtotime($r['created_at'] ?? 'now');
            $diff = time() - $ts;
            if ($diff < 60) {
                $ago = 'just now';
            } elseif ($diff < 3600) {
                $ago = (int) floor($diff / 60) . 'm ago';
            } elseif ($diff < 86400) {
                $ago = (int) floor($diff / 3600) . 'h ago';
            } else {
                $ago = date('d M', $ts);
            }
            $out[] = [
                'id'      => (int) $r['id'],
                'title'   => $r['title'] ?? '',
                'message' => $r['message'] ?? '',
                'is_read' => (int) ($r['is_read'] ?? 0),
                'time_ago'=> $ago,
            ];
        }

        return $this->response->setJSON($out);
    }

    public function markAllRead()
    {
        $uid = (int) session()->get('user_id');
        $this->db->table('notifications')->where('user_id',$uid)->update(['is_read'=>1]);
        cache()->delete('fm_unread_' . $uid);
        return $this->response->setJSON(['status'=>true]);
    }
}
