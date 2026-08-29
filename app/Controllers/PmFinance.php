<?php

namespace App\Controllers;

use App\Services\CashCollectionService;
use App\Services\PmFinanceService;

/**
 * PM Finance / General Ledger — property P&L, collections, owner statements.
 */
class PmFinance extends BaseController
{
    protected ?string $workspaceRequired = 'pm';
    private PmFinanceService $finance;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->finance = new PmFinanceService($this->db);
    }

    private function requireFinance(): void
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user', 'property_manager');
    }

    public function index()
    {
        $this->requireFinance();
        $from = date('Y-m-01');
        $to   = date('Y-m-d');
        $entries = $this->finance->ledgerEntries($from, $to);

        $income = 0;
        $expense = 0;
        foreach ($entries as $e) {
            if ($e['direction'] === 'income') {
                $income += (float) $e['amount'];
            } else {
                $expense += (float) $e['amount'];
            }
        }

        return view('pm_finance/index', $this->viewData([
            'title'   => 'PM Finance',
            'income'  => $income,
            'expense' => $expense,
            'net'     => $income - $expense,
            'pendingAck' => count((new CashCollectionService($this->db))->pendingAcknowledgements()),
        ]));
    }

    public function ledger()
    {
        $this->requireFinance();
        $from = $this->request->getGet('date_from') ?? $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('date_to') ?? $this->request->getGet('to') ?? date('Y-m-d');
        $facilityId = (int) ($this->request->getGet('property_id') ?? $this->request->getGet('facility_id') ?? 0);
        $unitId = (int) ($this->request->getGet('unit_id') ?? 0);

        $entries = $this->finance->ledgerEntries($from, $to, $facilityId ?: null, $unitId ?: null);
        $facilities = $this->facilitiesOptions();

        return view('pm_finance/ledger', $this->viewData([
            'title'      => 'Ledger',
            'entries'    => $entries,
            'from'       => $from,
            'to'         => $to,
            'facilityId' => $facilityId,
            'unitId'     => $unitId,
            'facilities' => $facilities,
        ]));
    }

    public function property(int $propertyId)
    {
        $this->requireFinance();
        $this->assertFacilityAccess($propertyId);
        $from = $this->request->getGet('date_from') ?? date('Y-01-01');
        $to   = $this->request->getGet('date_to') ?? date('Y-m-d');
        $pnl  = $this->finance->propertyPnL($propertyId, $from, $to);
        $property = $this->scopeFacilities(
            $this->db->table('facilities')->where('id', $propertyId)
        )->get()->getRowArray();

        return view('pm_finance/property_pnl', $this->viewData([
            'title'    => 'Property P&L',
            'property' => $property,
            'pnl'      => $pnl,
            'from'     => $from,
            'to'       => $to,
        ]));
    }

    public function unit(int $unitId)
    {
        $this->requireFinance();
        $from = $this->request->getGet('date_from') ?? date('Y-01-01');
        $to   = $this->request->getGet('date_to') ?? date('Y-m-d');
        $pnl  = $this->finance->unitPnL($unitId, $from, $to);

        return view('pm_finance/unit_pnl', $this->viewData([
            'title' => 'Unit P&L',
            'pnl'   => $pnl,
            'from'  => $from,
            'to'    => $to,
        ]));
    }

    public function add_transaction()
    {
        $this->requireFinance();
        if ($this->request->is('post')) {
            return $this->storeTransaction();
        }

        return view('pm_finance/transaction_form', $this->viewData([
            'title'      => 'Record Transaction',
            'costTypes'  => $this->finance->costTypes(),
            'facilities' => $this->facilitiesOptions(),
            'landlords'  => $this->db->tableExists('landlords')
                ? $this->db->table('landlords')->where('status', 'active')->orderBy('full_name')->get()->getResultArray()
                : [],
        ]));
    }

    private function storeTransaction(): \CodeIgniter\HTTP\RedirectResponse
    {
        $amount = (float) $this->request->getPost('amount');
        $costTypeId = (int) $this->request->getPost('cost_type_id');
        $paymentDate = $this->request->getPost('payment_date') ?: date('Y-m-d');

        if ($amount <= 0 || $costTypeId <= 0 || ! $paymentDate) {
            return redirect()->back()->withInput()->with('error', 'Amount, cost type, and payment date are required.');
        }

        $costType = $this->db->table('pm_cost_types')->where('id', $costTypeId)->get()->getRowArray();
        $direction = ($costType['category'] ?? 'expense') === 'income' ? 'income' : 'expense';
        $user = $this->currentUser();

        $this->finance->insertEntry([
            'company_id'     => $user['company_id'] ?? null,
            'entry_type'     => 'manual',
            'direction'      => $direction,
            'amount'         => $amount,
            'facility_id'    => (int) $this->request->getPost('property_id') ?: null,
            'unit_id'        => (int) $this->request->getPost('unit_id') ?: null,
            'landlord_id'    => (int) $this->request->getPost('landlord_id') ?: null,
            'cost_type_id'   => $costTypeId,
            'description'    => trim((string) $this->request->getPost('description')),
            'frequency'      => $this->request->getPost('frequency') ?: 'one-off',
            'paid_by'        => $this->request->getPost('paid_by'),
            'paid_to'        => $this->request->getPost('paid_to'),
            'payment_method' => $this->request->getPost('payment_method'),
            'reference_no'   => $this->request->getPost('reference_no'),
            'entry_date'     => $paymentDate,
        ], (int) $user['id']);

        return redirect()->to(base_url('finance/pm/ledger'))->with('success', 'Transaction recorded.');
    }

    public function add_property_cost()
    {
        $this->requireFinance();
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $facilityId = (int) $this->request->getPost('property_id');
        $amount = (float) $this->request->getPost('amount');
        if ($facilityId <= 0 || $amount <= 0) {
            return redirect()->back()->with('error', 'Property and amount required.');
        }

        $user = $this->currentUser();
        $entryId = $this->finance->insertEntry([
            'company_id'   => $user['company_id'] ?? null,
            'entry_type'   => 'property_cost',
            'direction'    => 'expense',
            'amount'       => $amount,
            'facility_id'  => $facilityId,
            'cost_type_id' => (int) $this->request->getPost('cost_type_id') ?: null,
            'description'  => trim((string) $this->request->getPost('description')),
            'frequency'    => $this->request->getPost('frequency') ?: 'one-off',
            'entry_date'   => $this->request->getPost('start_date') ?: date('Y-m-d'),
        ], (int) $user['id']);

        if ($this->db->tableExists('property_costs')) {
            $this->db->table('property_costs')->insert([
                'company_id'       => $user['company_id'] ?? null,
                'facility_id'      => $facilityId,
                'cost_type_id'     => (int) $this->request->getPost('cost_type_id') ?: null,
                'amount'           => $amount,
                'description'      => trim((string) $this->request->getPost('description')),
                'frequency'        => $this->request->getPost('frequency') ?: 'one-off',
                'start_date'       => $this->request->getPost('start_date'),
                'end_date'         => $this->request->getPost('end_date'),
                'finance_entry_id' => $entryId,
                'created_by'       => $user['id'],
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to(base_url('finance/pm/property/' . $facilityId))->with('success', 'Property cost added.');
    }

    public function delete_property_cost(int $id)
    {
        $this->requireFinance();
        if ($this->db->tableExists('property_costs')) {
            $row = $this->db->table('property_costs')->where('id', $id)->get()->getRowArray();
            if ($row && ! empty($row['finance_entry_id'])) {
                $this->db->table('finance_entries')->where('id', $row['finance_entry_id'])->delete();
            }
            $this->db->table('property_costs')->where('id', $id)->delete();
        }

        return redirect()->back()->with('success', 'Cost removed.');
    }

    public function add_unit_cost()
    {
        $this->requireFinance();
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $unitId = (int) $this->request->getPost('unit_id');
        $amount = (float) $this->request->getPost('amount');
        if ($unitId <= 0 || $amount <= 0) {
            return redirect()->back()->with('error', 'Unit and amount required.');
        }

        $unit = $this->db->table('units')->where('id', $unitId)->get()->getRowArray();
        $user = $this->currentUser();
        $entryId = $this->finance->insertEntry([
            'company_id'   => $user['company_id'] ?? null,
            'entry_type'   => 'unit_cost',
            'direction'    => 'expense',
            'amount'       => $amount,
            'facility_id'  => $unit['facility_id'] ?? null,
            'unit_id'      => $unitId,
            'cost_type_id' => (int) $this->request->getPost('cost_type_id') ?: null,
            'description'  => trim((string) $this->request->getPost('description')),
            'frequency'    => $this->request->getPost('frequency') ?: 'one-off',
            'entry_date'   => $this->request->getPost('start_date') ?: date('Y-m-d'),
        ], (int) $user['id']);

        if ($this->db->tableExists('unit_costs')) {
            $this->db->table('unit_costs')->insert([
                'company_id'       => $user['company_id'] ?? null,
                'facility_id'      => $unit['facility_id'] ?? null,
                'unit_id'          => $unitId,
                'cost_type_id'     => (int) $this->request->getPost('cost_type_id') ?: null,
                'amount'           => $amount,
                'description'      => trim((string) $this->request->getPost('description')),
                'frequency'        => $this->request->getPost('frequency') ?: 'one-off',
                'start_date'       => $this->request->getPost('start_date'),
                'end_date'         => $this->request->getPost('end_date'),
                'finance_entry_id' => $entryId,
                'created_by'       => $user['id'],
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to(base_url('finance/pm/unit/' . $unitId))->with('success', 'Unit cost added.');
    }

    public function delete_unit_cost(int $id)
    {
        $this->requireFinance();
        if ($this->db->tableExists('unit_costs')) {
            $row = $this->db->table('unit_costs')->where('id', $id)->get()->getRowArray();
            if ($row && ! empty($row['finance_entry_id'])) {
                $this->db->table('finance_entries')->where('id', $row['finance_entry_id'])->delete();
            }
            $this->db->table('unit_costs')->where('id', $id)->delete();
        }

        return redirect()->back()->with('success', 'Cost removed.');
    }

    public function journal()
    {
        return $this->ledger();
    }

    public function trial_balance()
    {
        $this->requireFinance();
        $asOf = $this->request->getGet('as_of') ?? date('Y-m-d');
        $rows = $this->finance->trialBalance($asOf);

        return view('pm_finance/trial_balance', $this->viewData([
            'title' => 'Trial Balance',
            'rows'  => $rows,
            'asOf'  => $asOf,
        ]));
    }

    public function collection_report()
    {
        $this->requireFinance();
        $from = $this->request->getGet('date_from') ?? date('Y-m-01');
        $to   = $this->request->getGet('date_to') ?? date('Y-m-d');
        $facilityId = (int) ($this->request->getGet('property_id') ?? 0);
        $report = $this->finance->collectionReport($from, $to, $facilityId ?: null);

        return view('pm_finance/collection_report', $this->viewData([
            'title'  => 'Collection Report',
            'report' => $report,
            'from'   => $from,
            'to'     => $to,
        ]));
    }

    public function owner_statement(int $landlordId = 0)
    {
        $this->requireFinance();
        $landlordId = $landlordId ?: (int) ($this->request->getGet('landlord_id') ?? 0);
        $from = $this->request->getGet('date_from') ?? date('Y-01-01');
        $to   = $this->request->getGet('date_to') ?? date('Y-m-d');

        $landlords = $this->db->tableExists('landlords')
            ? $this->db->table('landlords')->orderBy('full_name')->get()->getResultArray()
            : [];

        $statement = $landlordId > 0 ? $this->finance->ownerStatement($landlordId, $from, $to) : null;

        return view('pm_finance/owner_statement', $this->viewData([
            'title'     => 'Owner Statement',
            'landlords' => $landlords,
            'landlordId'=> $landlordId,
            'statement' => $statement,
            'from'      => $from,
            'to'        => $to,
        ]));
    }

    public function vat_report()
    {
        $this->requireFinance();
        $from = $this->request->getGet('date_from') ?? date('Y-m-01');
        $to   = $this->request->getGet('date_to') ?? date('Y-m-d');
        $report = $this->finance->vatReport($from, $to);

        return view('pm_finance/vat_report', $this->viewData([
            'title'  => 'VAT Report',
            'report' => $report,
            'from'   => $from,
            'to'     => $to,
        ]));
    }

    public function aging()
    {
        $this->requireFinance();
        $facilityId = (int) ($this->request->getGet('property_id') ?? 0);
        $buckets = $this->finance->agingReport($facilityId ?: null);

        return view('pm_finance/aging', $this->viewData([
            'title'   => 'Aging Report',
            'buckets' => $buckets,
        ]));
    }

    public function cash_acknowledge()
    {
        $this->requireFinance();
        $svc = new CashCollectionService($this->db);

        if ($this->request->is('post')) {
            $ids = (array) ($this->request->getPost('collection_ids') ?? []);
            $depositDate = $this->request->getPost('deposit_date');
            if (empty($ids) || ! $depositDate) {
                return redirect()->back()->with('error', 'Select collections and deposit date.');
            }
            $count = $svc->acknowledgeBulk(
                $ids,
                (int) $this->currentUser()['id'],
                $depositDate,
                $this->request->getPost('deposit_ref'),
                $this->request->getPost('notes')
            );

            return redirect()->to(base_url('finance/pm/cash-acknowledge'))->with('success', "{$count} collection(s) acknowledged.");
        }

        return view('pm_finance/cash_acknowledge', $this->viewData([
            'title'    => 'Cash Acknowledgement',
            'pending'  => $svc->pendingAcknowledgements(),
        ]));
    }

    public function cash_flow()
    {
        return redirect()->to(base_url('finance/cash-flow'));
    }

    public function add_landlord_rent()
    {
        $this->requireFinance();
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('finance/pm'));
        }

        $landlordId = (int) $this->request->getPost('landlord_id');
        $amount = (float) $this->request->getPost('amount');
        $facilityId = (int) $this->request->getPost('property_id');
        $date = $this->request->getPost('payment_date') ?: date('Y-m-d');

        if ($landlordId <= 0 || $amount <= 0) {
            return redirect()->back()->with('error', 'Landlord and amount required.');
        }

        $this->finance->insertEntry([
            'company_id'  => $this->currentUser()['company_id'] ?? null,
            'entry_type'  => 'landlord_rent',
            'direction'   => 'income',
            'amount'      => $amount,
            'facility_id' => $facilityId ?: null,
            'landlord_id' => $landlordId,
            'description' => trim((string) $this->request->getPost('description')) ?: 'Landlord rent income',
            'entry_date'  => $date,
        ], (int) $this->currentUser()['id']);

        return redirect()->to(base_url('finance/pm/ledger'))->with('success', 'Landlord rent recorded.');
    }

    /** @return list<array<string,mixed>> */
    private function facilitiesOptions(): array
    {
        return $this->scopeFacilities(
            $this->db->table('facilities')->where('status', 'active')->orderBy('name')
        )->get()->getResultArray();
    }
}
