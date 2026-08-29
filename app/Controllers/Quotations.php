<?php

namespace App\Controllers;

class Quotations extends BaseController
{
    protected ?string $workspaceRequired = 'fm';

    private function tableExists(): bool
    {
        return $this->db->tableExists('vendor_quotations');
    }

    public function index()
    {
        if (!$this->tableExists()) {
            return view('quotations/index', $this->viewData([
                'title'    => 'Vendor Quotations',
                'rows'     => [],
                'total'    => 0,
                'currentPage' => 1,
                'perPage'  => 25,
                'migrationRequired' => true,
            ]));
        }

        $filters = [
            'search'      => trim((string) ($this->request->getGet('search') ?? '')),
            'status'      => $this->request->getGet('status') ?? '',
            'facility_id' => (int) ($this->request->getGet('facility_id') ?? 0),
        ];

        $q = $this->db->table('vendor_quotations vq')
            ->select('vq.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = vq.facility_id', 'left');
        $this->scopeFacilities($q, 'vq.facility_id');

        if ($filters['search'] !== '') {
            $q->groupStart()
                ->like('vq.vendor_name', $filters['search'])
                ->orLike('vq.description', $filters['search'])
                ->groupEnd();
        }
        if ($filters['status'] !== '') {
            $q->where('vq.status', $filters['status']);
        }
        if ($filters['facility_id'] > 0) {
            $q->where('vq.facility_id', $filters['facility_id']);
        }

        $pg    = $this->paginate(25);
        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('vq.created_at', 'DESC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();

        $facQ = $this->db->table('facilities')->where('status', 'active')->orderBy('name');
        $facilities = $this->scopeFacilities($facQ)->get()->getResultArray();

        return view('quotations/index', $this->viewData([
            'title'       => 'Vendor Quotations',
            'rows'        => $rows,
            'facilities'  => $facilities,
            'filters'     => $filters,
            'total'       => $total,
            'currentPage' => $pg['page'],
            'perPage'     => $pg['perPage'],
        ]));
    }

    public function create()
    {
        $facilities = $this->db->table('facilities')->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('quotations/form', $this->viewData([
            'title'      => 'New Quotation',
            'quotation'  => null,
            'facilities' => $facilities,
            'items'      => [],
        ]));
    }

    public function store()
    {
        $rules = [
            'facility_id'  => 'required|is_natural_no_zero',
            'vendor_name'  => 'required|min_length[2]|max_length[200]',
            'valid_until'  => 'permit_empty|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $this->db->table('vendor_quotations')->insert([
            'facility_id'    => $post['facility_id'],
            'vendor_name'    => esc($post['vendor_name']),
            'vendor_contact' => esc($post['vendor_contact'] ?? ''),
            'description'    => esc($post['description'] ?? ''),
            'valid_until'    => $post['valid_until'] ?: null,
            'status'         => $post['status'] ?? 'draft',
            'notes'          => esc($post['notes'] ?? ''),
            'created_by'     => (int) session()->get('user_id'),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $qId = (int) $this->db->insertID();

        $this->saveItems($qId, $post['items'] ?? []);

        $this->logActivity('create', 'vendor_quotations', $qId, 'Quotation created: '.$post['vendor_name']);

        return redirect()->to(base_url('quotations/'.$qId))->with('success', 'Quotation created.');
    }

    public function view(int $id)
    {
        $quotation = $this->fetchQuotation($id);
        if (!$quotation) return redirect()->to(base_url('quotations'))->with('error', 'Quotation not found.');

        $items = $this->db->tableExists('vendor_quotation_items')
            ? $this->db->table('vendor_quotation_items')->where('quotation_id', $id)->orderBy('sort_order')->get()->getResultArray()
            : [];

        $subtotal = array_sum(array_map(fn($i) => (float)$i['qty'] * (float)$i['unit_price'], $items));

        return view('quotations/view', $this->viewData([
            'title'     => 'Quotation — '.esc($quotation['vendor_name']),
            'quotation' => $quotation,
            'items'     => $items,
            'subtotal'  => $subtotal,
        ]));
    }

    public function edit(int $id)
    {
        $quotation = $this->fetchQuotation($id);
        if (!$quotation) return redirect()->to(base_url('quotations'))->with('error', 'Quotation not found.');

        $facilities = $this->db->table('facilities')->where('status', 'active')->orderBy('name')->get()->getResultArray();
        $items      = $this->db->tableExists('vendor_quotation_items')
            ? $this->db->table('vendor_quotation_items')->where('quotation_id', $id)->orderBy('sort_order')->get()->getResultArray()
            : [];

        return view('quotations/form', $this->viewData([
            'title'      => 'Edit Quotation',
            'quotation'  => $quotation,
            'facilities' => $facilities,
            'items'      => $items,
        ]));
    }

    public function update(int $id)
    {
        $quotation = $this->fetchQuotation($id);
        if (!$quotation) return redirect()->to(base_url('quotations'))->with('error', 'Quotation not found.');

        $post = $this->request->getPost();
        $this->db->table('vendor_quotations')->where('id', $id)->update([
            'facility_id'    => $post['facility_id'],
            'vendor_name'    => esc($post['vendor_name']),
            'vendor_contact' => esc($post['vendor_contact'] ?? ''),
            'description'    => esc($post['description'] ?? ''),
            'valid_until'    => $post['valid_until'] ?: null,
            'status'         => $post['status'] ?? 'draft',
            'notes'          => esc($post['notes'] ?? ''),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->tableExists('vendor_quotation_items')) {
            $this->db->table('vendor_quotation_items')->where('quotation_id', $id)->delete();
        }
        $this->saveItems($id, $post['items'] ?? []);

        $this->logActivity('update', 'vendor_quotations', $id, 'Quotation updated');

        return redirect()->to(base_url('quotations/'.$id))->with('success', 'Quotation updated.');
    }

    public function delete(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->db->table('vendor_quotations')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete', 'vendor_quotations', $id);

        return redirect()->to(base_url('quotations'))->with('success', 'Quotation removed.');
    }

    private function fetchQuotation(int $id): ?array
    {
        if (!$this->tableExists()) return null;
        $row = $this->db->table('vendor_quotations vq')
            ->select('vq.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = vq.facility_id', 'left')
            ->where('vq.id', $id)
            ->where('vq.deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    private function saveItems(int $quotationId, array $items): void
    {
        if (!$this->db->tableExists('vendor_quotation_items')) return;

        $sort = 0;
        foreach ($items as $item) {
            $desc = trim((string) ($item['description'] ?? ''));
            if ($desc === '') continue;
            $this->db->table('vendor_quotation_items')->insert([
                'quotation_id' => $quotationId,
                'description'  => esc($desc),
                'qty'          => (float) ($item['qty'] ?? 1),
                'unit'         => esc($item['unit'] ?? 'pcs'),
                'unit_price'   => (float) ($item['unit_price'] ?? 0),
                'sort_order'   => $sort++,
            ]);
        }
    }
}
