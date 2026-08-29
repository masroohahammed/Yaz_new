<?php

namespace App\Controllers;

use App\Services\AssetCodeService;
use App\Services\AssetScanLogService;

/**
 * Public and authenticated asset scan deep links (QR / barcode).
 */
class AssetScan extends BaseController
{
    public function byToken(string $token)
    {
        $codeSvc = new AssetCodeService($this->db);
        $asset   = $codeSvc->findByToken($token);
        if (! $asset) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Asset not found.');
        }

        return $this->renderScanPage($asset, 'qr');
    }

    public function byId(int $id)
    {
        $asset = $this->db->table('assets a')
            ->select('a.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->where('a.id', $id)
            ->where('a.deleted_at', null)
            ->get()->getRowArray();

        if (! $asset) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->renderScanPage($asset, 'direct');
    }

    /**
     * POST — public complaint from QR scan.
     */
    public function submitComplaint(string $token)
    {
        $codeSvc = new AssetCodeService($this->db);
        $asset   = $codeSvc->findByToken($token);
        if (! $asset) {
            return redirect()->back()->with('error', 'Asset not found.');
        }

        $rules = [
            'requester_name' => 'required|min_length[2]',
            'description'    => 'required|min_length[10]',
            'priority'       => 'required|in_list[critical,high,medium,low]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ticket = 'TKT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
        if ($this->db->tableExists('maintenance_requests')) {
            $data = [
                'ticket_number'   => $ticket,
                'facility_id'     => $asset['facility_id'] ?: null,
                'asset_id'        => $this->db->fieldExists('asset_id', 'maintenance_requests') ? (int) $asset['id'] : null,
                'scan_source'     => $this->db->fieldExists('scan_source', 'maintenance_requests') ? 'qr_scan' : null,
                'requester_name'  => esc($this->request->getPost('requester_name')),
                'requester_email' => esc($this->request->getPost('requester_email') ?? ''),
                'requester_phone' => esc($this->request->getPost('requester_phone') ?? ''),
                'category'        => esc($this->request->getPost('category') ?: 'breakdown'),
                'description'     => esc($this->request->getPost('description')),
                'priority'        => $this->request->getPost('priority'),
                'status'          => 'pending',
                'approval_status' => 'pending',
            ];
            $this->db->table('maintenance_requests')->insert($data);
            $complaintId = (int) $this->db->insertID();

            (new AssetScanLogService($this->db))->log(
                (int) $asset['id'],
                session()->get('user_id') ? (int) session()->get('user_id') : null,
                'qr',
                'complaint_submitted',
                $this->request->getIPAddress(),
                $this->request->getUserAgent()?->getAgentString()
            );

            return redirect()->to(base_url('scan/asset/' . $token))
                ->with('success', 'Complaint submitted. Ticket: ' . $ticket)
                ->with('complaint_id', $complaintId);
        }

        return redirect()->back()->with('error', 'Complaint module not available.');
    }

    /**
     * @param array<string, mixed> $asset
     */
    private function renderScanPage(array $asset, string $source): string
    {
        $userId = session()->get('user_id') ? (int) session()->get('user_id') : null;
        (new AssetScanLogService($this->db))->log(
            (int) $asset['id'],
            $userId,
            $source,
            $userId ? 'authenticated_view' : 'public_view',
            $this->request->getIPAddress(),
            $this->request->getUserAgent()?->getAgentString(),
            $this->request->getGet('lat') ? (float) $this->request->getGet('lat') : null,
            $this->request->getGet('lng') ? (float) $this->request->getGet('lng') : null
        );

        $codeSvc = new AssetCodeService($this->db);
        $scanUrl = $codeSvc->scanUrl($asset);

        $openWos = $this->db->table('work_orders')
            ->where('asset_id', (int) $asset['id'])
            ->where('deleted_at', null)
            ->whereIn('status', ['new', 'assigned', 'in_progress', 'on_hold'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $isLoggedIn = (bool) $userId;

        return view('asset_scan/public', [
            'title'      => $asset['name'],
            'asset'      => $asset,
            'scanUrl'    => $scanUrl,
            'qrImageUrl' => $codeSvc->qrImageUrl($scanUrl, 180),
            'openWos'    => $openWos,
            'isLoggedIn' => $isLoggedIn,
            'currency'   => $this->settings['currency'] ?? 'QAR',
            'settings'   => $this->settings,
        ]);
    }
}
