<?php

namespace Config;

/**
 * Form field definitions for PM registry modules.
 *
 * @return array<string, list<array<string, mixed>>>
 */
class PmFormFields
{
  public static function sections(string $slug): array
  {
    return self::map()[$slug] ?? [];
  }

  public static function allFieldNames(string $slug): array
  {
    $names = [];
    foreach (self::sections($slug) as $section) {
      foreach ($section['fields'] as $field) {
        $names[] = $field['name'];
      }
    }

    return $names;
  }

  /** @return array<string, list<array<string, mixed>>> */
  private static function map(): array
  {
    return [
      'landlords' => [
        self::sec('Identity', [
          self::f('full_name', 'Full Name', 'text', true),
          self::f('full_name_ar', 'Full Name (Arabic)', 'text'),
          self::f('status', 'Status', 'select', true, ['active' => 'Active', 'inactive' => 'Inactive']),
        ]),
        self::sec('Contact', [
          self::f('phone', 'Phone', 'tel'),
          self::f('phone2', 'Phone 2', 'tel'),
          self::f('email', 'Email', 'email'),
          self::f('nationality', 'Nationality', 'text'),
        ]),
        self::sec('ID & Banking', [
          self::f('id_type', 'ID Type', 'text'),
          self::f('id_number', 'ID Number', 'text'),
          self::f('id_expiry', 'ID Expiry', 'date'),
          self::f('bank_name', 'Bank Name', 'text'),
          self::f('bank_account', 'Bank Account', 'text'),
          self::f('bank_iban', 'IBAN', 'text'),
          self::f('commission_pct', 'Commission %', 'number'),
        ]),
        self::sec('Other', [
          self::f('address', 'Address', 'textarea'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'tenants' => [
        self::sec('Tenant', [
          self::f('tenant_type', 'Type', 'select', true, ['Personal' => 'Personal', 'Corporate' => 'Corporate']),
          self::f('full_name', 'Full Name', 'text', true),
          self::f('status', 'Status', 'select', true, ['active' => 'Active', 'inactive' => 'Inactive', 'blacklisted' => 'Blacklisted']),
          self::f('phone', 'Phone', 'tel', true),
          self::f('whatsapp', 'WhatsApp', 'tel'),
          self::f('email', 'Email', 'email'),
        ]),
        self::sec('Personal', [
          self::f('nationality', 'Nationality', 'text'),
          self::f('gender', 'Gender', 'text'),
          self::f('dob', 'Date of Birth', 'date'),
          self::f('company_name', 'Company Name', 'text'),
          self::f('company_cr', 'Company CR', 'text'),
        ]),
        self::sec('Documents', [
          self::f('qid_no', 'QID No', 'text'),
          self::f('qid_expiry', 'QID Expiry', 'date'),
          self::f('passport_no', 'Passport No', 'text'),
          self::f('passport_expiry', 'Passport Expiry', 'date'),
        ]),
        self::sec('Emergency', [
          self::f('emergency_name', 'Emergency Name', 'text'),
          self::f('emergency_phone', 'Emergency Phone', 'tel'),
          self::f('emergency_relation', 'Relation', 'text'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'leases' => [
        self::sec('Parties', [
          self::fk('tenant_id', 'Tenant', 'tenants', 'full_name', true),
          self::fk('facility_id', 'Property', 'facilities', 'name', true),
          self::fk('unit_id', 'Unit', 'units', 'unit_number', true),
          self::f('status', 'Status', 'select', true, [
            'draft' => 'Draft', 'active' => 'Active', 'expired' => 'Expired',
            'terminated' => 'Terminated', 'renewed' => 'Renewed',
          ]),
        ]),
        self::sec('Terms', [
          self::f('signed_date', 'Signed Date', 'date'),
          self::f('billing_start_date', 'Billing Start', 'date'),
          self::f('start_date', 'Start Date', 'date', true),
          self::f('end_date', 'End Date', 'date', true),
          self::f('rent_amount', 'Rent Amount', 'number', true),
          self::f('security_deposit', 'Security Deposit', 'number'),
          self::f('payment_frequency', 'Payment Frequency', 'select', true, [
            'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly',
          ]),
          self::f('payment_type', 'Payment Type', 'select', true, [
            'cheque' => 'Cheque', 'cash' => 'Cash', 'transfer' => 'Transfer',
          ]),
          self::f('payment_day', 'Payment Day', 'number'),
          self::f('late_penalty_pct', 'Late Penalty %', 'number'),
          self::f('grace_period_days', 'Grace Days', 'number'),
          self::f('discount_pct', 'Discount %', 'number'),
        ]),
        self::sec('VAT & Options', [
          self::f('vat_applicable', 'VAT Applicable', 'checkbox'),
          self::f('vat_rate', 'VAT Rate %', 'number'),
          self::f('auto_renew', 'Auto Renew', 'checkbox'),
          self::f('auto_generate_invoices', 'Auto Generate Invoices', 'checkbox'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'rent-payments' => [
        self::sec('Payment', [
          self::fk('contract_id', 'Contract', 'lease_contracts', 'contract_number'),
          self::fk('tenant_id', 'Tenant', 'tenants', 'full_name'),
          self::fk('facility_id', 'Property', 'facilities', 'name'),
          self::fk('unit_id', 'Unit', 'units', 'unit_number'),
          self::f('payment_type', 'Payment Type', 'select', true, ['rent' => 'Rent', 'deposit' => 'Deposit', 'other' => 'Other']),
          self::f('payment_method', 'Method', 'select', true, [
            'cash' => 'Cash', 'cheque' => 'Cheque', 'transfer' => 'Transfer', 'card' => 'Card',
          ]),
          self::f('amount', 'Amount', 'number', true),
          self::f('status', 'Status', 'select', true, [
            'pending' => 'Pending', 'paid' => 'Paid', 'partial' => 'Partial',
            'overdue' => 'Overdue', 'cancelled' => 'Cancelled', 'postponed' => 'Postponed',
          ]),
        ]),
        self::sec('Dates & Reference', [
          self::f('due_date', 'Due Date', 'date'),
          self::f('payment_date', 'Payment Date', 'date'),
          self::f('period_from', 'Period From', 'date'),
          self::f('period_to', 'Period To', 'date'),
          self::f('reference_no', 'Reference', 'text'),
          self::f('cheque_no', 'Cheque No', 'text'),
          self::f('bank_name', 'Bank', 'text'),
          self::f('transfer_reference', 'Transfer Ref', 'text'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'cheques' => [
        self::sec('Cheque', [
          self::fk('contract_id', 'Contract', 'lease_contracts', 'contract_number'),
          self::fk('tenant_id', 'Tenant', 'tenants', 'full_name'),
          self::fk('facility_id', 'Property', 'facilities', 'name'),
          self::f('cheque_no', 'Cheque No', 'text', true),
          self::f('amount', 'Amount', 'number', true),
          self::f('status', 'Status', 'select', true, [
            'pending' => 'Pending', 'deposited' => 'Deposited', 'cleared' => 'Cleared',
            'bounced' => 'Bounced', 'cancelled' => 'Cancelled', 'replaced' => 'Replaced',
          ]),
          self::f('bank_name', 'Bank', 'text'),
          self::f('account_name', 'Account Name', 'text'),
          self::f('account_no', 'Account No', 'text'),
          self::f('cheque_date', 'Cheque Date', 'date'),
          self::f('received_date', 'Received Date', 'date'),
          self::f('period_from', 'Period From', 'date'),
          self::f('period_to', 'Period To', 'date'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
        self::sec('Bounce / Legal', [
          self::f('bounce_reason', 'Bounce Reason', 'textarea'),
          self::f('file_legal', 'File Legal Case', 'checkbox'),
          self::f('case_no', 'Case No', 'text'),
          self::f('filed_date', 'Filed Date', 'date'),
          self::f('case_notes', 'Case Notes', 'textarea'),
          self::f('cash_conversion_date', 'Cash Conversion Date', 'date'),
        ]),
      ],
      'outgoing-cheques' => [
        self::sec('Outgoing Cheque', [
          self::f('cheque_no', 'Cheque No', 'text', true),
          self::f('bank_name', 'Bank', 'text', true),
          self::f('amount', 'Amount', 'number', true),
          self::f('cheque_date', 'Cheque Date', 'date', true),
          self::f('payee_name', 'Payee Name', 'text', true),
          self::f('payee_type', 'Payee Type', 'select', [
            'Landlord' => 'Landlord', 'Supplier' => 'Supplier', 'Contractor' => 'Contractor',
            'Security Deposit' => 'Security Deposit', 'Other' => 'Other',
          ]),
          self::f('purpose', 'Purpose', 'select', true, [
            'Rent' => 'Rent', 'Deposit' => 'Deposit', 'Maintenance' => 'Maintenance',
            'Supplier' => 'Supplier', 'Common Area' => 'Common Area', 'Other' => 'Other',
          ]),
          self::fk('facility_id', 'Property', 'facilities', 'name'),
          self::f('status', 'Status', 'select', true, [
            'pending' => 'Pending', 'issued' => 'Issued', 'cleared' => 'Cleared', 'cancelled' => 'Cancelled',
          ]),
          self::f('description', 'Description', 'textarea'),
        ]),
      ],
      'crm' => [
        self::sec('Lead', [
          self::f('full_name', 'Full Name', 'text', true),
          self::f('phone', 'Phone', 'tel'),
          self::f('email', 'Email', 'email'),
          self::f('nationality', 'Nationality', 'text'),
          self::f('source', 'Source', 'text'),
          self::f('interest_type', 'Interest', 'select', true, ['Buy' => 'Buy', 'Rent' => 'Rent', 'Both' => 'Both']),
          self::f('preferred_location', 'Preferred Location', 'text'),
          self::f('budget_min', 'Budget Min', 'number'),
          self::f('budget_max', 'Budget Max', 'number'),
          self::f('bedrooms', 'Bedrooms', 'number'),
        ]),
        self::sec('Pipeline', [
          self::f('temperature', 'Temperature', 'select', true, ['Hot' => 'Hot', 'Warm' => 'Warm', 'Cold' => 'Cold']),
          self::f('stage', 'Stage', 'text'),
          self::fkUser('assigned_to', 'Assigned To'),
          self::f('follow_up_date', 'Follow-up Date', 'date'),
          self::f('follow_up_time', 'Follow-up Time', 'text'),
          self::f('lost_reason', 'Lost Reason', 'textarea'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'sales' => [
        self::sec('Deal', [
          self::f('deal_type', 'Deal Type', 'select', true, ['Sale' => 'Sale', 'Lease' => 'Lease']),
          self::fk('lead_id', 'Lead', 'crm_leads', 'full_name'),
          self::f('buyer_name', 'Buyer Name', 'text', true),
          self::f('buyer_phone', 'Buyer Phone', 'tel'),
          self::f('buyer_email', 'Buyer Email', 'email'),
          self::fk('facility_id', 'Property', 'facilities', 'name'),
          self::fk('unit_id', 'Unit', 'units', 'unit_number'),
          self::f('deal_value', 'Deal Value', 'number'),
          self::f('agreed_price', 'Agreed Price', 'number'),
          self::f('stage', 'Stage', 'text'),
          self::fkUser('agent_id', 'Agent'),
          self::f('expected_close_date', 'Expected Close', 'date'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'pm-utilities' => [
        self::sec('Utility Account', [
          self::fk('facility_id', 'Property', 'facilities', 'name', true),
          self::fk('unit_id', 'Unit', 'units', 'unit_number'),
          self::f('utility_name', 'Utility', 'text', true),
          self::f('provider_name', 'Provider', 'text'),
          self::f('account_number', 'Account Number', 'text'),
          self::f('meter_number', 'Meter Number', 'text'),
          self::f('managed_by', 'Managed By', 'text'),
          self::f('billing_mode', 'Billing Mode', 'select', true, [
            'included' => 'Included', 'billed_separately' => 'Billed Separately',
            'tenant_pays_direct' => 'Tenant Pays Direct', 'complimentary' => 'Complimentary',
          ]),
          self::f('monthly_charge', 'Monthly Charge', 'number'),
          self::f('status', 'Status', 'select', true, ['active' => 'Active', 'inactive' => 'Inactive']),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'landlord-payouts' => [
        self::sec('Payout', [
          self::fk('landlord_id', 'Landlord', 'landlords', 'full_name', true),
          self::f('period_from', 'Period From', 'date'),
          self::f('period_to', 'Period To', 'date'),
          self::f('gross_rent', 'Gross Rent', 'number'),
          self::f('commission', 'Commission', 'number'),
          self::f('deductions', 'Deductions', 'number'),
          self::f('net_amount', 'Net Amount', 'number'),
          self::f('status', 'Status', 'select', true, ['pending' => 'Pending', 'paid' => 'Paid']),
          self::f('paid_date', 'Paid Date', 'date'),
          self::f('payment_method', 'Payment Method', 'text'),
          self::f('reference_no', 'Reference', 'text'),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
      'budgeting' => [
        self::sec('Budget Line', [
          self::fk('facility_id', 'Property', 'facilities', 'name', true),
          self::f('year', 'Year', 'number', true),
          self::f('month', 'Month', 'number', true),
          self::f('income', 'Income', 'number'),
          self::f('expense', 'Expense', 'number'),
          self::f('notes', 'Notes', 'text'),
        ]),
      ],
      'complimentary-offers' => [
        self::sec('Offer', [
          self::fk('contract_id', 'Contract', 'lease_contracts', 'contract_number', true),
          self::f('offer_type', 'Offer Type', 'text', true),
          self::f('free_period_value', 'Free Period (months)', 'number'),
          self::f('discount_percent', 'Discount %', 'number'),
          self::f('start_date', 'Start Date', 'date'),
          self::f('end_date', 'End Date', 'date'),
          self::f('status', 'Status', 'select', true, ['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']),
          self::f('notes', 'Notes', 'textarea'),
        ]),
      ],
    ];
  }

  private static function sec(string $label, array $fields): array
  {
    return ['label' => $label, 'fields' => $fields];
  }

  private static function f(string $name, string $label, string $type, bool $required = false, array $options = []): array
  {
    return compact('name', 'label', 'type', 'required', 'options');
  }

  private static function fk(string $name, string $label, string $table, string $display, bool $required = false): array
  {
    return [
      'name'     => $name,
      'label'    => $label,
      'type'     => 'fk',
      'table'    => $table,
      'display'  => $display,
      'required' => $required,
    ];
  }

  private static function fkUser(string $name, string $label, bool $required = false): array
  {
    return [
      'name'     => $name,
      'label'    => $label,
      'type'     => 'fk_user',
      'required' => $required,
    ];
  }
}
