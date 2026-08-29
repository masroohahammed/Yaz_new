<?php

namespace App\Controllers;

use App\Controllers\Traits\PmModuleTrait;

class Budgeting extends BaseController
{
    use PmModuleTrait;

    protected ?string $workspaceRequired = 'pm';

    private const TABLE = 'property_budgets';

    public function index()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return view('budgeting/index', $this->viewData([
                'title'             => 'Property Budgets',
                'migrationRequired' => true,
                'budgets'           => [],
                'facilities'        => [],
                'year'              => date('Y'),
            ]));
        }

        $year       = (int) ($this->request->getGet('year') ?? date('Y'));
        $facilityId = (int) ($this->request->getGet('facility_id') ?? 0);

        $q = $this->db->table(self::TABLE . ' pb')
            ->select('pb.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = pb.facility_id', 'left')
            ->where('pb.year', $year);
        $this->scopeCompany($q, 'pb.company_id');

        if ($facilityId > 0) {
            $q->where('pb.facility_id', $facilityId);
        }

        $budgets = $q->orderBy('pb.facility_id')->orderBy('pb.month')->get()->getResultArray();

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('budgeting/index', $this->viewData([
            'title'      => 'Property Budgets',
            'budgets'    => $budgets,
            'facilities' => $facilities,
            'year'       => $year,
            'facilityId' => $facilityId,
        ]));
    }

    public function create()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('budgets'))->with('error', 'Run migration first.');
        }

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('budgeting/form', $this->viewData([
            'title'      => 'Set Budget',
            'facilities' => $facilities,
            'year'       => date('Y'),
        ]));
    }

    public function store()
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('budgets'))->with('error', 'Run migration first.');
        }

        $rules = [
            'facility_id' => 'required|is_natural_no_zero',
            'year'        => 'required|is_natural_no_zero',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facilityId = (int) $this->request->getPost('facility_id');
        $year       = (int) $this->request->getPost('year');
        $this->assertFacilityAccess($facilityId);

        $incomes   = $this->request->getPost('income')  ?? [];
        $expenses  = $this->request->getPost('expense') ?? [];

        for ($month = 1; $month <= 12; $month++) {
            $income  = (float) ($incomes[$month]  ?? 0);
            $expense = (float) ($expenses[$month] ?? 0);

            $existing = $this->db->table(self::TABLE)
                ->where('facility_id', $facilityId)
                ->where('year', $year)
                ->where('month', $month)
                ->get()->getRowArray();

            if ($existing) {
                $this->db->table(self::TABLE)
                    ->where('facility_id', $facilityId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->update([
                        'income'     => $income,
                        'expense'    => $expense,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            } else {
                $this->db->table(self::TABLE)->insert([
                    'company_id'  => $this->pmCompanyId(),
                    'facility_id' => $facilityId,
                    'year'        => $year,
                    'month'       => $month,
                    'income'      => $income,
                    'expense'     => $expense,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->logActivity('budget', 'property_budgets', $facilityId, "Budget set for {$year}");

        return redirect()->to(base_url('budgets?year=' . $year . '&facility_id=' . $facilityId))->with('success', 'Budget saved.');
    }

    public function variance(int $facilityId, int $year)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->to(base_url('budgets'))->with('error', 'Run migration first.');
        }

        $this->assertFacilityAccess($facilityId);

        $facility = $this->db->table('facilities')->where('id', $facilityId)->get()->getRowArray();
        if (! $facility) {
            return redirect()->to(base_url('budgets'))->with('error', 'Property not found.');
        }

        $budgets = $this->db->table(self::TABLE)
            ->where('facility_id', $facilityId)
            ->where('year', $year)
            ->orderBy('month')
            ->get()->getResultArray();

        $budgetMap = [];
        foreach ($budgets as $b) {
            $budgetMap[(int) $b['month']] = $b;
        }

        $months     = [];
        $totalBudgetIncome   = 0;
        $totalBudgetExpense  = 0;
        $totalActualIncome   = 0;
        $totalActualExpense  = 0;

        for ($m = 1; $m <= 12; $m++) {
            $from = sprintf('%04d-%02d-01', $year, $m);
            $to   = date('Y-m-t', strtotime($from));

            $actualIncome = 0;
            if ($this->pmTableExists('lease_payments')) {
                $row = $this->db->table('lease_payments')
                    ->selectSum('amount', 'total')
                    ->where('facility_id', $facilityId)
                    ->where('status', 'paid')
                    ->where('payment_date >=', $from)
                    ->where('payment_date <=', $to)
                    ->get()->getRowArray();
                $actualIncome = (float) ($row['total'] ?? 0);
            }

            $actualExpense = 0;
            if ($this->pmTableExists('expenses')) {
                $row = $this->db->table('expenses')
                    ->selectSum('amount', 'total')
                    ->where('facility_id', $facilityId)
                    ->where('expense_date >=', $from)
                    ->where('expense_date <=', $to)
                    ->get()->getRowArray();
                $actualExpense = (float) ($row['total'] ?? 0);
            }

            $budgetIncome  = (float) ($budgetMap[$m]['income']  ?? 0);
            $budgetExpense = (float) ($budgetMap[$m]['expense'] ?? 0);

            $months[$m] = [
                'month'           => $m,
                'budget_income'   => $budgetIncome,
                'budget_expense'  => $budgetExpense,
                'actual_income'   => $actualIncome,
                'actual_expense'  => $actualExpense,
                'income_variance' => $actualIncome - $budgetIncome,
                'expense_variance'=> $actualExpense - $budgetExpense,
            ];

            $totalBudgetIncome  += $budgetIncome;
            $totalBudgetExpense += $budgetExpense;
            $totalActualIncome  += $actualIncome;
            $totalActualExpense += $actualExpense;
        }

        return view('budgeting/variance', $this->viewData([
            'title'               => 'Budget vs Actual — ' . $facility['name'] . ' ' . $year,
            'facility'            => $facility,
            'year'                => $year,
            'months'              => $months,
            'totalBudgetIncome'   => $totalBudgetIncome,
            'totalBudgetExpense'  => $totalBudgetExpense,
            'totalActualIncome'   => $totalActualIncome,
            'totalActualExpense'  => $totalActualExpense,
        ]));
    }

    public function forecast(int $facilityId)
    {
        if (! $this->pmTableExists('lease_payments')) {
            return redirect()->to(base_url('budgets'))->with('error', 'Lease payments table not available.');
        }

        $this->assertFacilityAccess($facilityId);

        $facility = $this->db->table('facilities')->where('id', $facilityId)->get()->getRowArray();
        if (! $facility) {
            return redirect()->to(base_url('budgets'))->with('error', 'Property not found.');
        }

        // 3-month trailing average
        $threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));
        $avgRow = $this->db->table('lease_payments')
            ->selectSum('amount', 'total')
            ->where('facility_id', $facilityId)
            ->where('status', 'paid')
            ->where('payment_date >=', $threeMonthsAgo)
            ->get()->getRowArray();
        $trailingAvg = round((float) ($avgRow['total'] ?? 0) / 3, 2);

        // Active lease recurring income
        $activeIncome = 0;
        if ($this->pmTableExists('lease_contracts')) {
            $activeContracts = $this->db->table('lease_contracts')
                ->select('rent_amount, payment_frequency')
                ->where('facility_id', $facilityId)
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->get()->getResultArray();

            foreach ($activeContracts as $lc) {
                $freq = strtolower($lc['payment_frequency'] ?? 'monthly');
                $monthly = match ($freq) {
                    'annual', 'yearly' => (float) $lc['rent_amount'] / 12,
                    'quarterly'        => (float) $lc['rent_amount'] / 3,
                    'bi-annual'        => (float) $lc['rent_amount'] / 6,
                    default            => (float) $lc['rent_amount'],
                };
                $activeIncome += $monthly;
            }
        }

        $forecast = [];
        for ($i = 1; $i <= 6; $i++) {
            $monthTs    = strtotime('+' . $i . ' months');
            $label      = date('M Y', $monthTs);
            $blended    = round(($trailingAvg + $activeIncome) / 2, 2);
            $forecast[] = [
                'label'        => $label,
                'lease_income' => round($activeIncome, 2),
                'trailing_avg' => $trailingAvg,
                'blended'      => $blended,
            ];
        }

        return view('budgeting/forecast', $this->viewData([
            'title'        => '6-Month Forecast — ' . $facility['name'],
            'facility'     => $facility,
            'forecast'     => $forecast,
            'trailingAvg'  => $trailingAvg,
            'activeIncome' => round($activeIncome, 2),
        ]));
    }

    public function reconcile()
    {
        $filters = [
            'facility_id' => (int) ($this->request->getGet('facility_id') ?? 0),
            'year'        => (int) ($this->request->getGet('year') ?? date('Y')),
            'month'       => (int) ($this->request->getGet('month') ?? date('n')),
        ];

        $facilities = $this->db->table('facilities')->select('id, name')
            ->where('status', 'active')->orderBy('name')->get()->getResultArray();

        $leasePayments = [];
        $financeItems  = [];
        $unmatched     = [];

        if ($filters['facility_id'] > 0 && $this->pmTableExists('lease_payments')) {
            $this->assertFacilityAccess($filters['facility_id']);

            $from = sprintf('%04d-%02d-01', $filters['year'], $filters['month']);
            $to   = date('Y-m-t', strtotime($from));

            $leasePayments = $this->db->table('lease_payments lp')
                ->select('lp.payment_number, lp.amount, lp.payment_date, lp.status, t.full_name AS tenant_name')
                ->join('tenants t', 't.id = lp.tenant_id', 'left')
                ->where('lp.facility_id', $filters['facility_id'])
                ->where('lp.payment_date >=', $from)
                ->where('lp.payment_date <=', $to)
                ->orderBy('lp.payment_date')
                ->get()->getResultArray();

            if ($this->pmTableExists('finance_invoices')) {
                $financeItems = $this->db->table('finance_invoices fi')
                    ->select('fi.invoice_number AS ref_number, fi.total_amount AS amount, fi.invoice_date AS date, fi.status')
                    ->where('fi.facility_id', $filters['facility_id'])
                    ->where('fi.invoice_date >=', $from)
                    ->where('fi.invoice_date <=', $to)
                    ->orderBy('fi.invoice_date')
                    ->get()->getResultArray();
            }

            $leaseNumbers = array_column($leasePayments, 'payment_number');
            $financeRefs  = array_column($financeItems, 'ref_number');
            $allLeaseAmounts   = array_sum(array_column($leasePayments, 'amount'));
            $allFinanceAmounts = array_sum(array_column($financeItems, 'amount'));
            $unmatched = [
                'lease_total'   => $allLeaseAmounts,
                'finance_total' => $allFinanceAmounts,
                'difference'    => $allLeaseAmounts - $allFinanceAmounts,
            ];
        }

        return view('budgeting/reconcile', $this->viewData([
            'title'         => 'Budget Reconciliation',
            'facilities'    => $facilities,
            'filters'       => $filters,
            'leasePayments' => $leasePayments,
            'financeItems'  => $financeItems,
            'unmatched'     => $unmatched,
        ]));
    }
}
