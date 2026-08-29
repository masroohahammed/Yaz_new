<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Shared finance ERP navigation (merged into PM/FM/admin sidebars).
 */
class FinanceMenu extends BaseConfig
{
    /** @var list<array<string, mixed>> */
    public array $items = [
        ['type' => 'heading', 'label' => 'Finance'],
        ['key' => 'finance_hub', 'label' => 'Module Hub', 'icon' => 'bi-diagram-3', 'url' => 'finance/hub'],
        ['key' => 'finance_overview', 'label' => 'Overview', 'icon' => 'bi-graph-up', 'url' => 'finance'],

        ['type' => 'heading', 'label' => 'Finance — Accounting'],
        ['key' => 'finance_coa', 'label' => 'Chart of Accounts', 'icon' => 'bi-list-columns', 'url' => 'finance/coa'],
        ['key' => 'finance_gl', 'label' => 'General Ledger', 'icon' => 'bi-journal-bookmark', 'url' => 'finance/gl'],
        ['key' => 'finance_ledger', 'label' => 'Ledger', 'icon' => 'bi-journal-text', 'url' => 'finance/ledger'],
        ['key' => 'finance_bank_mod', 'label' => 'Bank & Cash', 'icon' => 'bi-bank2', 'url' => 'finance-bank'],
        ['key' => 'finance_petty_mod', 'label' => 'Petty Cash Module', 'icon' => 'bi-cash-stack', 'url' => 'finance-petty'],
        ['key' => 'vendor_bills', 'label' => 'Vendor Bills (AP)', 'icon' => 'bi-truck', 'url' => 'finance/vendor-bills'],
        ['key' => 'accounts_payable', 'label' => 'Accounts Payable', 'icon' => 'bi-cash-stack', 'url' => 'finance/accounts-payable'],

        ['type' => 'heading', 'label' => 'Finance — Treasury'],
        ['key' => 'petty_cash', 'label' => 'Petty Cash', 'icon' => 'bi-wallet', 'url' => 'finance/petty-cash'],
        ['key' => 'bank_reconciliation', 'label' => 'Bank Reconciliation', 'icon' => 'bi-bank', 'url' => 'finance/bank-reconciliation'],
        ['key' => 'reimbursements', 'label' => 'Reimbursements', 'icon' => 'bi-arrow-return-left', 'url' => 'finance/reimbursements'],
        ['key' => 'cash_flow', 'label' => 'Cash Flow', 'icon' => 'bi-graph-up-arrow', 'url' => 'finance/cash-flow'],

        ['type' => 'heading', 'label' => 'Finance — Billing'],
        ['key' => 'finance_contracts', 'label' => 'Finance Contracts', 'icon' => 'bi-file-earmark-text', 'url' => 'finance/contracts'],
        ['key' => 'amc_billing', 'label' => 'AMC Billing', 'icon' => 'bi-calendar-check', 'url' => 'finance/amc-billing'],
        ['key' => 'finance_budgets', 'label' => 'Finance Budgets', 'icon' => 'bi-piggy-bank', 'url' => 'finance/budgets'],

        ['type' => 'heading', 'label' => 'Finance — Reports'],
        ['key' => 'finance_reports', 'label' => 'Financial Reports', 'icon' => 'bi-file-earmark-bar-graph', 'url' => 'finance/reports'],
    ];
}
