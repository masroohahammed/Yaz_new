<?php
namespace App\Controllers;

class PublicRequest extends BaseController
{
    public function index()
    {
        $facilities = $this->db->table('facilities')->where('status', 'active')->get()->getResultArray();
        $logoUrl    = fm_logo_url($this->settings['company_logo'] ?? '');

        return view('public/request', [
            'settings'   => $this->settings,
            'title'      => 'Maintenance Request',
            'facilities' => $facilities,
            'logoUrl'    => $logoUrl,
        ]);
    }

    public function unitsForFacility(int $facilityId)
    {
        if (! $this->db->tableExists('units')) {
            return $this->response->setJSON([]);
        }
        $q = $this->db->table('units')->select('id, unit_number, floor')->where('facility_id', $facilityId);
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }
        $units = $q->orderBy('unit_number')->get()->getResultArray();

        return $this->response->setJSON($units);
    }

    public function submit()
    {
        $rules = ['requester_name'=>'required|min_length[2]','description'=>'required|min_length[10]'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $year   = date('Y');
        $last   = $this->db->table('maintenance_requests')->like('ticket_number','MR-'.$year.'-','after')->orderBy('id','DESC')->limit(1)->get()->getRowArray();
        $seq    = $last ? (int)explode('-', $last['ticket_number'])[2] + 1 : 1;
        $ticket = 'MR-'.$year.'-'.sprintf('%04d',$seq);

        // Handle image upload
        $imagePath = null;
        $img = $this->request->getFile('image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'uploads/requests', $newName);
            $imagePath = 'uploads/requests/' . $newName;
        }

        $facilityId = $this->request->getPost('facility_id') ?: null;
        $companyId  = null;
        if ($facilityId) {
            $fac = $this->db->table('facilities')->select('company_id')->where('id', $facilityId)->get()->getRowArray();
            $companyId = $fac['company_id'] ?? null;
        }

        $unitId = (int) ($this->request->getPost('unit_id') ?: 0) ?: null;

        $this->db->table('maintenance_requests')->insert([
            'ticket_number'  => $ticket,
            'facility_id'    => $facilityId,
            'unit_id'        => $this->db->fieldExists('unit_id', 'maintenance_requests') ? $unitId : null,
            'company_id'     => $companyId,
            'requester_name' => esc($this->request->getPost('requester_name')),
            'requester_email'=> esc($this->request->getPost('requester_email')),
            'requester_phone'=> esc($this->request->getPost('requester_phone')),
            'category'       => esc($this->request->getPost('category')),
            'description'    => esc($this->request->getPost('description')),
            'priority'       => $this->request->getPost('priority') ?? 'medium',
            'status'         => 'pending',
            'image_path'     => $imagePath,
        ]);

        // Notify managers
        $managers = array_merge(
            $this->usersByRole('facility_manager'),
            $this->usersByRole('super_admin')
        );
        $alert = new \App\Services\AlertDispatchService($this->db, $this->settings);
        foreach ($managers as $m) {
            $alert->notifyUser(
                (int) $m['id'],
                'New Maintenance Request: ' . $ticket,
                'From: ' . esc($this->request->getPost('requester_name')),
                'work_order'
            );
        }
        return redirect()->to(base_url('request'))->with('success', "Request submitted! Your ticket number is: <strong>$ticket</strong> — Save this for tracking.");
    }

    public function track(string $ticket)
    {
        $select = 'mr.*, f.name as facility_name';
        $q      = $this->db->table('maintenance_requests mr')
            ->join('facilities f', 'f.id = mr.facility_id', 'left')
            ->where('mr.ticket_number', strtoupper($ticket));
        if ($this->db->fieldExists('unit_id', 'maintenance_requests') && $this->db->tableExists('units')) {
            $select .= ', u.unit_number';
            $q->join('units u', 'u.id = mr.unit_id', 'left');
        }
        $req = $q->select($select)->get()->getRowArray();

        return view('public/track', [
            'settings' => $this->settings,
            'title'    => 'Track Request',
            'req'      => $req,
            'ticket'   => strtoupper($ticket),
            'logoUrl'  => fm_logo_url($this->settings['company_logo'] ?? ''),
        ]);
    }
}
