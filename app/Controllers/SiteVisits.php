<?php

namespace App\Controllers;

use App\Services\SignatureStorageService;

class SiteVisits extends BaseController
{
    public function index()
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor', 'technician');
        $status = (string) ($this->request->getGet('status') ?? '');
        $from   = $this->request->getGet('from') ?? date('Y-m-01');
        $to     = $this->request->getGet('to') ?? date('Y-m-d');

        if (! $this->db->tableExists('site_visits')) {
            return redirect()->to(base_url('settings'))->with('error', 'Run php spark migrate to enable Site Visits.');
        }

        $q = $this->db->table('site_visits sv')
            ->select('sv.*, f.name as facility_name, u.name as technician_name')
            ->join('facilities f', 'f.id = sv.facility_id', 'left')
            ->join('users u', 'u.id = sv.technician_id', 'left')
            ->where('DATE(COALESCE(sv.scheduled_at, sv.created_at)) >=', $from)
            ->where('DATE(COALESCE(sv.scheduled_at, sv.created_at)) <=', $to);
        $this->scopeFacilities($q, 'sv.facility_id');
        if ($status !== '') {
            $q->where('sv.status', $status);
        }
        $visits = $q->orderBy('sv.scheduled_at', 'DESC')->limit(100)->get()->getResultArray();

        return view('site_visits/index', $this->viewData([
            'title'  => 'Site Visits',
            'visits' => $visits,
            'from'   => $from,
            'to'     => $to,
            'status' => $status,
        ]));
    }

    public function create()
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor');
        $facilities = $this->db->table('facilities')->where('status', 'active')->get()->getResultArray();
        $units = [];
        if ($this->db->tableExists('units')) {
            $uq = $this->db->table('units')->orderBy('unit_number');
            if ($this->db->fieldExists('deleted_at', 'units')) {
                $uq->where('deleted_at', null);
            }
            $units = $uq->get()->getResultArray();
        }
        $technicians = (new \App\Models\UserModel())->getUsersByRole('technician');

        return view('site_visits/create', $this->viewData([
            'title'       => 'Schedule Site Visit',
            'facilities'  => $facilities,
            'units'       => $units,
            'technicians' => $technicians,
        ]));
    }

    public function store()
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor');
        if (! $this->db->tableExists('site_visits')) {
            return redirect()->to(base_url('site-visits'))->with('error', 'Run php spark migrate to enable Site Visits.');
        }
        $num = 'SV-' . date('Y') . '-' . sprintf('%04d', (int) $this->db->table('site_visits')->countAllResults() + 1);
        $this->db->table('site_visits')->insert([
            'visit_number'   => $num,
            'facility_id'    => $this->request->getPost('facility_id') ?: null,
            'unit_id'        => $this->request->getPost('unit_id') ?: null,
            'work_order_id'  => $this->request->getPost('work_order_id') ?: null,
            'scheduled_at'   => $this->request->getPost('scheduled_at') ?: null,
            'status'         => 'scheduled',
            'purpose'        => $this->request->getPost('purpose'),
            'requirements'   => $this->request->getPost('requirements'),
            'technician_id'  => $this->request->getPost('technician_id') ?: null,
            'supervisor_id'  => $this->request->getPost('supervisor_id') ?: session()->get('user_id'),
            'created_by'     => session()->get('user_id'),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->logActivity('create', 'site_visits', $id, $num);
        (new \App\Services\AlertDispatchService($this->db, $this->settings))
            ->notifyUser((int) ($this->request->getPost('technician_id') ?: session()->get('user_id')), 'Site visit scheduled', $num, 'work_order', $id);

        return redirect()->to(base_url('site-visits/view/' . $id))->with('success', 'Site visit scheduled.');
    }

    public function view(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor', 'technician');
        $v = $this->db->table('site_visits sv')
            ->select('sv.*, f.name as facility_name, ut.unit_number, t.name as technician_name, s.name as supervisor_name')
            ->join('facilities f', 'f.id = sv.facility_id', 'left')
            ->join('units ut', 'ut.id = sv.unit_id', 'left')
            ->join('users t', 't.id = sv.technician_id', 'left')
            ->join('users s', 's.id = sv.supervisor_id', 'left')
            ->where('sv.id', $id)
            ->get()
            ->getRowArray();
        if (! $v) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('site_visits/view', $this->viewData([
            'title' => $v['visit_number'],
            'visit' => $v,
        ]));
    }

    public function complete(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor', 'technician');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $sig = new SignatureStorageService();
        $photo = $this->request->getFile('photo');
        $photoPath = null;
        if ($photo && $photo->isValid() && ! $photo->hasMoved()) {
            $dir = FCPATH . 'uploads/site_visits';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $newName = $photo->getRandomName();
            $photo->move($dir, $newName);
            $photoPath = 'uploads/site_visits/' . $newName;
        }
        $this->db->table('site_visits')->where('id', $id)->update([
            'status'               => 'completed',
            'visited_at'           => date('Y-m-d H:i:s'),
            'observations'         => $this->request->getPost('observations'),
            'technician_remarks'   => $this->request->getPost('technician_remarks'),
            'supervisor_remarks'   => $this->request->getPost('supervisor_remarks'),
            'follow_up_date'       => $this->request->getPost('follow_up_date') ?: null,
            'client_signature'     => $sig->storeFromPost($this->request->getPost('client_signature'), 'sv_client_' . $id),
            'technician_signature' => $sig->storeFromPost($this->request->getPost('technician_signature'), 'sv_tech_' . $id),
            'photo_path'           => $photoPath,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('complete', 'site_visits', $id, 'Site visit completed');

        return redirect()->to(base_url('site-visits/view/' . $id))->with('success', 'Site visit completed.');
    }

    // ----------------------------------------------------------
    // Store site visit from within a Work Order
    // POST /workorders/{woId}/site-visit
    // ----------------------------------------------------------

    public function storeForWo(int $woId)
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor');

        if (! $this->db->tableExists('site_visits')) {
            return redirect()->to(base_url('workorders/view/' . $woId))->with('error', 'Run php spark migrate to enable Site Visits.');
        }

        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (! $wo) {
            return redirect()->to(base_url('workorders'))->with('error', 'Work order not found.');
        }

        $num = 'SV-' . date('Y') . '-' . sprintf('%04d', (int) $this->db->table('site_visits')->countAllResults() + 1);

        $this->db->table('site_visits')->insert([
            'visit_number'  => $num,
            'facility_id'   => $wo['facility_id'] ?? null,
            'unit_id'       => $wo['unit_id']     ?? null,
            'work_order_id' => $woId,
            'scheduled_at'  => $this->request->getPost('scheduled_at') ?: null,
            'status'        => 'scheduled',
            'purpose'       => $this->request->getPost('purpose'),
            'requirements'  => $this->request->getPost('notes'), // stored in requirements column
            'technician_id' => $this->request->getPost('technician_id') ?: null,
            'supervisor_id' => session()->get('user_id'),
            'created_by'    => session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $svId = (int) $this->db->insertID();
        $this->logActivity('create', 'site_visits', $svId, $num . ' linked to WO #' . $woId);

        if ($techId = (int) $this->request->getPost('technician_id')) {
            try {
                (new \App\Services\AlertDispatchService($this->db, $this->settings))
                    ->notifyUser($techId, 'Site visit scheduled', $num . ' — WO #' . ($wo['wo_number'] ?? $woId), 'work_order', $svId);
            } catch (\Throwable $e) {}
        }

        return redirect()->to(base_url('workorders/view/' . $woId) . '#tab-sitevisit')
            ->with('success', 'Site visit ' . $num . ' scheduled.');
    }
}
