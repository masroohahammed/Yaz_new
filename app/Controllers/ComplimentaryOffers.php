<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class ComplimentaryOffers extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'complimentary_offers';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return view('offers/index', $this->viewData([
                'title'             => 'Complimentary Offers',
                'migrationRequired' => true,
                'offers'            => [],
                'total'             => 0,
                'currentPage'       => 1,
                'perPage'           => 25,
            ]));
        }

        $filters = [
            'status'      => $this->request->getGet('status') ?? '',
            'contract_id' => (int) ($this->request->getGet('contract_id') ?? 0),
        ];

        $q = $this->db->table(self::TABLE . ' o')
            ->select('o.*, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name, u.unit_number')
            ->join('lease_contracts lc', 'lc.id = o.contract_id', 'left')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left');

        if ($filters['status'] !== '') {
            $q->where('o.status', $filters['status']);
        }
        if ($filters['contract_id'] > 0) {
            $q->where('o.contract_id', $filters['contract_id']);
        }

        $pg     = $this->paginate(25);
        $total  = (clone $q)->countAllResults(false);
        $offers = $q->orderBy('o.id', 'DESC')
            ->limit($pg['perPage'], $pg['offset'])
            ->get()->getResultArray();

        return view('offers/index', $this->viewData([
            'title'       => 'Complimentary Offers',
            'offers'      => $offers,
            'filters'     => $filters,
            'total'       => $total,
            'currentPage' => $pg['page'],
            'perPage'     => $pg['perPage'],
        ]));
    }

    public function create()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Complimentary offers table not available. Run migration.');
        }

        return view('offers/form', $this->viewData([
            'title'     => 'New Complimentary Offer',
            'offer'     => null,
            'contracts' => $this->contractOptions(),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Table not available.');
        }

        $rules = [
            'contract_id' => 'required|is_natural_no_zero',
            'offer_type'  => 'required|max_length[50]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table(self::TABLE)->insert([
            'contract_id'        => (int) $this->request->getPost('contract_id'),
            'offer_type'         => esc($this->request->getPost('offer_type')),
            'free_period_value'  => $this->request->getPost('free_period_value') ?: null,
            'discount_percent'   => $this->request->getPost('discount_percent') ?: null,
            'start_date'         => $this->request->getPost('start_date') ?: null,
            'end_date'           => $this->request->getPost('end_date') ?: null,
            'status'             => 'active',
            'notes'              => esc($this->request->getPost('notes')) ?: null,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->db->insertID();
        $this->logActivity('create', 'complimentary_offers', $id, 'Offer created');

        return redirect()->to(base_url('complimentary-offers'))->with('success', 'Complimentary offer created.');
    }

    public function edit(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Table not available.');
        }

        $offer = $this->pmFind(self::TABLE, $id);
        if (! $offer) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Offer not found.');
        }

        return view('offers/form', $this->viewData([
            'title'     => 'Edit Complimentary Offer',
            'offer'     => $offer,
            'contracts' => $this->contractOptions(),
        ]));
    }

    public function update(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Table not available.');
        }

        $offer = $this->pmFind(self::TABLE, $id);
        if (! $offer) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Offer not found.');
        }

        $rules = ['offer_type' => 'required|max_length[50]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table(self::TABLE)->where('id', $id)->update([
            'offer_type'         => esc($this->request->getPost('offer_type')),
            'free_period_value'  => $this->request->getPost('free_period_value') ?: null,
            'discount_percent'   => $this->request->getPost('discount_percent') ?: null,
            'start_date'         => $this->request->getPost('start_date') ?: null,
            'end_date'           => $this->request->getPost('end_date') ?: null,
            'notes'              => esc($this->request->getPost('notes')) ?: null,
        ]);

        $this->logActivity('update', 'complimentary_offers', $id, 'Offer updated');

        return redirect()->to(base_url('complimentary-offers'))->with('success', 'Offer updated.');
    }

    public function expire(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Table not available.');
        }

        $offer = $this->pmFind(self::TABLE, $id);
        if (! $offer) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Offer not found.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update(['status' => 'expired']);
        $this->logActivity('expire', 'complimentary_offers', $id, 'Offer expired');

        return redirect()->to(base_url('complimentary-offers'))->with('success', 'Offer marked as expired.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('complimentary-offers'))->with('error', 'Table not available.');
        }

        $this->db->table(self::TABLE)->where('id', $id)->update(['status' => 'cancelled']);
        $this->logActivity('delete', 'complimentary_offers', $id, 'Offer cancelled');

        return redirect()->to(base_url('complimentary-offers'))->with('success', 'Offer cancelled.');
    }

    /** @return list<array<string,mixed>> */
    private function contractOptions(): array
    {
        if (! $this->pmTableExists('lease_contracts')) {
            return [];
        }

        $q = $this->db->table('lease_contracts lc')
            ->select('lc.id, lc.contract_number, t.full_name AS tenant_name, f.name AS facility_name')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->whereIn('lc.status', ['active', 'draft'])
            ->where('lc.deleted_at', null)
            ->orderBy('lc.contract_number', 'DESC');

        $this->scopeCompany($q, 'lc.company_id');

        return $q->get()->getResultArray();
    }
}
