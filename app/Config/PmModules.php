<?php

namespace Config;

/**
 * PM module registry — maps URL slug to table, permission key, and list columns.
 */
class PmModules
{
  /** @var array<string, array<string, mixed>> */
  public static array $modules = [
    'landlords' => [
      'table'       => 'landlords',
      'permission'  => 'landlords',
      'title'       => 'Landlords',
      'icon'        => 'bi-person-badge',
      'search'      => ['full_name', 'phone', 'email'],
      'columns'     => [
        ['key' => 'full_name', 'label' => 'Name'],
        ['key' => 'phone', 'label' => 'Phone'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'tenants' => [
      'table'       => 'tenants',
      'permission'  => 'tenants',
      'title'       => 'Tenants',
      'icon'        => 'bi-people',
      'search'      => ['full_name', 'phone', 'email', 'qid_no'],
      'columns'     => [
        ['key' => 'full_name', 'label' => 'Tenant'],
        ['key' => 'phone', 'label' => 'Phone'],
        ['key' => 'tenant_type', 'label' => 'Type'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'leases' => [
      'table'       => 'lease_contracts',
      'permission'  => 'leases',
      'title'       => 'Lease Contracts',
      'icon'        => 'bi-file-earmark-text',
      'number'      => ['field' => 'contract_number', 'prefix' => 'LC'],
      'search'      => ['contract_number'],
      'columns'     => [
        ['key' => 'contract_number', 'label' => 'Contract No'],
        ['key' => 'tenant_id', 'label' => 'Tenant ID'],
        ['key' => 'rent_amount', 'label' => 'Rent'],
        ['key' => 'start_date', 'label' => 'Start'],
        ['key' => 'end_date', 'label' => 'End'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'rent-payments' => [
      'table'       => 'lease_payments',
      'permission'  => 'rent_payments',
      'title'       => 'Rent Payments',
      'icon'        => 'bi-cash-stack',
      'number'      => ['field' => 'payment_number', 'prefix' => 'PAY'],
      'search'      => ['payment_number', 'reference_no'],
      'columns'     => [
        ['key' => 'payment_number', 'label' => 'Payment No'],
        ['key' => 'payment_type', 'label' => 'Type'],
        ['key' => 'amount', 'label' => 'Amount'],
        ['key' => 'due_date', 'label' => 'Due'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'cheques' => [
      'table'       => 'cheques',
      'permission'  => 'cheques',
      'title'       => 'Incoming Cheques (PDC)',
      'icon'        => 'bi-bank',
      'search'      => ['cheque_no', 'bank_name'],
      'columns'     => [
        ['key' => 'cheque_no', 'label' => 'Cheque No'],
        ['key' => 'amount', 'label' => 'Amount'],
        ['key' => 'bank_name', 'label' => 'Bank'],
        ['key' => 'cheque_date', 'label' => 'Date'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'outgoing-cheques' => [
      'table'       => 'outgoing_cheques',
      'permission'  => 'outgoing_cheques',
      'title'       => 'Outgoing Cheques',
      'icon'        => 'bi-bank2',
      'search'      => ['cheque_no', 'payee_name'],
      'columns'     => [
        ['key' => 'cheque_no', 'label' => 'Cheque No'],
        ['key' => 'payee_name', 'label' => 'Payee'],
        ['key' => 'amount', 'label' => 'Amount'],
        ['key' => 'cheque_date', 'label' => 'Date'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'crm' => [
      'table'       => 'crm_leads',
      'permission'  => 'crm',
      'title'       => 'CRM Leads',
      'icon'        => 'bi-person-lines-fill',
      'number'      => ['field' => 'lead_number', 'prefix' => 'LD'],
      'search'      => ['lead_number', 'full_name', 'phone', 'email'],
      'columns'     => [
        ['key' => 'lead_number', 'label' => 'Lead No'],
        ['key' => 'full_name', 'label' => 'Name'],
        ['key' => 'phone', 'label' => 'Phone'],
        ['key' => 'stage', 'label' => 'Stage'],
        ['key' => 'temperature', 'label' => 'Temp'],
      ],
    ],
    'sales' => [
      'table'       => 'sales_deals',
      'permission'  => 'sales',
      'title'       => 'Sales & Deals',
      'icon'        => 'bi-briefcase',
      'number'      => ['field' => 'deal_number', 'prefix' => 'SD'],
      'search'      => ['deal_number', 'buyer_name'],
      'columns'     => [
        ['key' => 'deal_number', 'label' => 'Deal No'],
        ['key' => 'buyer_name', 'label' => 'Buyer'],
        ['key' => 'deal_type', 'label' => 'Type'],
        ['key' => 'deal_value', 'label' => 'Value'],
        ['key' => 'stage', 'label' => 'Stage'],
      ],
    ],
    'pm-utilities' => [
      'table'       => 'utility_accounts',
      'permission'  => 'pm_utilities',
      'title'       => 'Utility Accounts',
      'icon'        => 'bi-lightning',
      'search'      => ['account_number', 'provider_name'],
      'columns'     => [
        ['key' => 'provider_name', 'label' => 'Provider'],
        ['key' => 'account_number', 'label' => 'Account'],
        ['key' => 'billing_mode', 'label' => 'Billing'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'landlord-payouts' => [
      'table'       => 'landlord_payouts',
      'permission'  => 'landlord_payouts',
      'title'       => 'Landlord Payouts',
      'icon'        => 'bi-cash-coin',
      'search'      => ['reference_no'],
      'columns'     => [
        ['key' => 'landlord_id', 'label' => 'Landlord'],
        ['key' => 'gross_rent', 'label' => 'Gross'],
        ['key' => 'net_amount', 'label' => 'Net'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'paid_date', 'label' => 'Paid'],
      ],
    ],
    'budgeting' => [
      'table'       => 'property_budgets',
      'permission'  => 'budgeting',
      'title'       => 'Property Budgets',
      'icon'        => 'bi-piggy-bank',
      'search'      => ['year'],
      'columns'     => [
        ['key' => 'facility_id', 'label' => 'Property'],
        ['key' => 'year', 'label' => 'Year'],
        ['key' => 'month', 'label' => 'Month'],
        ['key' => 'income', 'label' => 'Income'],
        ['key' => 'expense', 'label' => 'Expense'],
      ],
    ],
    'complimentary-offers' => [
      'table'       => 'complimentary_offers',
      'permission'  => 'complimentary_offers',
      'title'       => 'Complimentary Offers',
      'icon'        => 'bi-gift',
      'search'      => ['offer_type'],
      'columns'     => [
        ['key' => 'contract_id', 'label' => 'Contract'],
        ['key' => 'offer_type', 'label' => 'Type'],
        ['key' => 'start_date', 'label' => 'Start'],
        ['key' => 'end_date', 'label' => 'End'],
        ['key' => 'status', 'label' => 'Status'],
      ],
    ],
    'commission-rules' => [
      'table'       => 'commission_rules',
      'permission'  => 'commission_rules',
      'title'       => 'Commission Rules',
      'icon'        => 'bi-percent',
      'search'      => ['rule_name'],
      'columns'     => [
        ['key' => 'rule_name', 'label' => 'Rule'],
        ['key' => 'deal_type', 'label' => 'Deal Type'],
        ['key' => 'commission_type', 'label' => 'Type'],
        ['key' => 'agent_rate', 'label' => 'Agent %'],
        ['key' => 'company_rate', 'label' => 'Company %'],
      ],
    ],
  ];

  public static function get(string $slug): ?array
  {
    return self::$modules[$slug] ?? null;
  }

  /** @return list<string> */
  public static function slugs(): array
  {
    return array_keys(self::$modules);
  }
}
