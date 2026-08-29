<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PmErpModules extends Migration
{
    public function up()
    {
        // ── Property extensions ──────────────────────────────────────
        if ($this->db->tableExists('facilities')) {
            $facilityCols = [
                'category'               => ['type' => 'ENUM', 'constraint' => ['Residential', 'Commercial'], 'null' => true, 'after' => 'name'],
                'property_type'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'area'                   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'listing_status'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'for_sale'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'sale_price'             => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'price_per_sqm'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'landlord_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'expected_monthly_income'=> ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'landlord_share_pct'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'management_fee_pct'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'finance_notes'          => ['type' => 'TEXT', 'null' => true],
            ];
            foreach ($facilityCols as $col => $def) {
                if (! $this->db->fieldExists($col, 'facilities')) {
                    $this->forge->addColumn('facilities', [$col => $def]);
                }
            }
        }

        // ── Documents polymorphic ────────────────────────────────────
        if ($this->db->tableExists('documents')) {
            $docCols = [
                'module'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'ref_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'category_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'doc_number'      => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'issued_by'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'doc_date'        => ['type' => 'DATE', 'null' => true],
                'is_primary'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_confidential' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            ];
            foreach ($docCols as $col => $def) {
                if (! $this->db->fieldExists($col, 'documents')) {
                    $this->forge->addColumn('documents', [$col => $def]);
                }
            }
        }

        // ── Landlords ────────────────────────────────────────────────
        if (! $this->db->tableExists('landlords')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'full_name'      => ['type' => 'VARCHAR', 'constraint' => 200],
                'full_name_ar'   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
                'phone'          => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'phone2'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'email'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'nationality'    => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'id_type'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'id_number'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'id_expiry'      => ['type' => 'DATE', 'null' => true],
                'address'        => ['type' => 'TEXT', 'null' => true],
                'bank_name'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'bank_account'   => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'bank_iban'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'commission_pct' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'status'         => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
                'notes'          => ['type' => 'TEXT', 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('landlords', true);
        }

        // ── Tenants ──────────────────────────────────────────────────
        if (! $this->db->tableExists('tenants')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'tenant_type'       => ['type' => 'ENUM', 'constraint' => ['Personal', 'Corporate'], 'default' => 'Personal'],
                'full_name'         => ['type' => 'VARCHAR', 'constraint' => 200],
                'nationality'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'gender'            => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'dob'               => ['type' => 'DATE', 'null' => true],
                'phone'             => ['type' => 'VARCHAR', 'constraint' => 30],
                'whatsapp'          => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'email'             => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'company_name'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
                'company_cr'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'qid_no'            => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'qid_expiry'        => ['type' => 'DATE', 'null' => true],
                'passport_no'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'passport_expiry'   => ['type' => 'DATE', 'null' => true],
                'emergency_name'    => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'emergency_phone'   => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'emergency_relation'=> ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'inactive', 'blacklisted'], 'default' => 'active'],
                'notes'             => ['type' => 'TEXT', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('tenants', true);
        }

        // ── Lease contracts (PM) ─────────────────────────────────────
        if (! $this->db->tableExists('lease_contracts')) {
            $this->forge->addField([
                'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'contract_number'       => ['type' => 'VARCHAR', 'constraint' => 30],
                'tenant_id'             => ['type' => 'INT', 'unsigned' => true],
                'facility_id'           => ['type' => 'INT', 'unsigned' => true],
                'unit_id'               => ['type' => 'INT', 'unsigned' => true],
                'template_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'status'                => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'expired', 'terminated', 'renewed'], 'default' => 'draft'],
                'signed_date'           => ['type' => 'DATE', 'null' => true],
                'billing_start_date'    => ['type' => 'DATE', 'null' => true],
                'start_date'            => ['type' => 'DATE'],
                'end_date'              => ['type' => 'DATE'],
                'rent_amount'           => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'security_deposit'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'payment_frequency'     => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'monthly'],
                'payment_type'          => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'cheque'],
                'payment_day'           => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'late_penalty_pct'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'grace_period_days'     => ['type' => 'INT', 'null' => true],
                'discount_pct'          => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'vat_applicable'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'vat_rate'              => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'auto_renew'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'auto_generate_invoices'=> ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'custom_content_en'     => ['type' => 'MEDIUMTEXT', 'null' => true],
                'custom_content_ar'     => ['type' => 'MEDIUMTEXT', 'null' => true],
                'contract_terms'        => ['type' => 'TEXT', 'null' => true],
                'termination_reason'    => ['type' => 'TEXT', 'null' => true],
                'notes'                 => ['type' => 'TEXT', 'null' => true],
                'created_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'            => ['type' => 'DATETIME', 'null' => true],
                'updated_at'            => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'            => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('contract_number');
            $this->forge->createTable('lease_contracts', true);
        }

        // ── Lease payments ───────────────────────────────────────────
        if (! $this->db->tableExists('lease_payments')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'payment_number'   => ['type' => 'VARCHAR', 'constraint' => 30],
                'contract_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'tenant_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'unit_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'payment_type'     => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'rent'],
                'payment_method'   => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'cash'],
                'amount'           => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'paid', 'partial', 'overdue', 'cancelled', 'postponed'], 'default' => 'pending'],
                'bank_name'        => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'transfer_reference'=> ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'cheque_no'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'payment_date'     => ['type' => 'DATE', 'null' => true],
                'due_date'         => ['type' => 'DATE', 'null' => true],
                'period_from'      => ['type' => 'DATE', 'null' => true],
                'period_to'        => ['type' => 'DATE', 'null' => true],
                'reference_no'     => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'notes'            => ['type' => 'TEXT', 'null' => true],
                'created_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('payment_number');
            $this->forge->createTable('lease_payments', true);
        }

        // ── Cheques (incoming PDC) ───────────────────────────────────
        if (! $this->db->tableExists('cheques')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'contract_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'tenant_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'cheque_no'     => ['type' => 'VARCHAR', 'constraint' => 50],
                'amount'        => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'status'        => ['type' => 'ENUM', 'constraint' => ['pending', 'deposited', 'cleared', 'bounced', 'cancelled', 'replaced'], 'default' => 'pending'],
                'bank_name'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'account_name'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'account_no'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'cheque_date'   => ['type' => 'DATE', 'null' => true],
                'received_date' => ['type' => 'DATE', 'null' => true],
                'period_from'   => ['type' => 'DATE', 'null' => true],
                'period_to'     => ['type' => 'DATE', 'null' => true],
                'bounce_reason' => ['type' => 'TEXT', 'null' => true],
                'notes'         => ['type' => 'TEXT', 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
                'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('cheques', true);
        }

        // ── Outgoing cheques ─────────────────────────────────────────
        if (! $this->db->tableExists('outgoing_cheques')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'cheque_no'   => ['type' => 'VARCHAR', 'constraint' => 50],
                'bank_name'   => ['type' => 'VARCHAR', 'constraint' => 120],
                'amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'cheque_date' => ['type' => 'DATE'],
                'payee_name'  => ['type' => 'VARCHAR', 'constraint' => 200],
                'payee_type'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'purpose'     => ['type' => 'VARCHAR', 'constraint' => 80],
                'facility_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'description' => ['type' => 'TEXT', 'null' => true],
                'status'      => ['type' => 'ENUM', 'constraint' => ['pending', 'issued', 'cleared', 'cancelled'], 'default' => 'pending'],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
                'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('outgoing_cheques', true);
        }

        // ── CRM ────────────────────────────────────────────────────
        if (! $this->db->tableExists('crm_leads')) {
            $this->forge->addField([
                'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'lead_number'         => ['type' => 'VARCHAR', 'constraint' => 30],
                'full_name'           => ['type' => 'VARCHAR', 'constraint' => 200],
                'phone'               => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'email'               => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'nationality'         => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'source'              => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'interest_type'       => ['type' => 'ENUM', 'constraint' => ['Buy', 'Rent', 'Both'], 'default' => 'Rent'],
                'preferred_location'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'budget_min'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'budget_max'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'bedrooms'            => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'temperature'         => ['type' => 'ENUM', 'constraint' => ['Hot', 'Warm', 'Cold'], 'default' => 'Warm'],
                'stage'               => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'new'],
                'assigned_to'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'follow_up_date'      => ['type' => 'DATE', 'null' => true],
                'follow_up_time'      => ['type' => 'TIME', 'null' => true],
                'lost_reason'         => ['type' => 'TEXT', 'null' => true],
                'notes'               => ['type' => 'TEXT', 'null' => true],
                'created_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'          => ['type' => 'DATETIME', 'null' => true],
                'updated_at'          => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('lead_number');
            $this->forge->createTable('crm_leads', true);
        }

        if (! $this->db->tableExists('crm_activities')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'lead_id'          => ['type' => 'INT', 'unsigned' => true],
                'activity_type'    => ['type' => 'VARCHAR', 'constraint' => 50],
                'outcome'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'subject'          => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
                'description'      => ['type' => 'TEXT', 'null' => true],
                'duration_minutes' => ['type' => 'INT', 'null' => true],
                'next_follow_up'   => ['type' => 'DATETIME', 'null' => true],
                'created_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('crm_activities', true);
        }

        if (! $this->db->tableExists('crm_visits')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'lead_id'           => ['type' => 'INT', 'unsigned' => true],
                'facility_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'unit_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'visit_date'        => ['type' => 'DATE'],
                'visit_time'        => ['type' => 'TIME', 'null' => true],
                'visit_type'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'agent_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'rating'            => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'customer_feedback' => ['type' => 'TEXT', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('crm_visits', true);
        }

        // ── Sales ────────────────────────────────────────────────────
        if (! $this->db->tableExists('sales_deals')) {
            $this->forge->addField([
                'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'deal_number'        => ['type' => 'VARCHAR', 'constraint' => 30],
                'deal_type'          => ['type' => 'ENUM', 'constraint' => ['Sale', 'Lease'], 'default' => 'Lease'],
                'lead_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'buyer_name'         => ['type' => 'VARCHAR', 'constraint' => 200],
                'buyer_phone'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'buyer_email'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'facility_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'unit_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'deal_value'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'agreed_price'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'stage'              => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'prospect'],
                'agent_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'expected_close_date'=> ['type' => 'DATE', 'null' => true],
                'notes'              => ['type' => 'TEXT', 'null' => true],
                'created_at'         => ['type' => 'DATETIME', 'null' => true],
                'updated_at'         => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('deal_number');
            $this->forge->createTable('sales_deals', true);
        }

        if (! $this->db->tableExists('commission_rules')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'rule_name'       => ['type' => 'VARCHAR', 'constraint' => 120],
                'deal_type'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'commission_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'percentage'],
                'agent_rate'      => ['type' => 'DECIMAL', 'constraint' => '5,2'],
                'company_rate'    => ['type' => 'DECIMAL', 'constraint' => '5,2'],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('commission_rules', true);
        }

        // ── Complimentary offers ─────────────────────────────────────
        if (! $this->db->tableExists('complimentary_offers')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'contract_id'       => ['type' => 'INT', 'unsigned' => true],
                'offer_type'        => ['type' => 'VARCHAR', 'constraint' => 50],
                'free_period_value' => ['type' => 'INT', 'null' => true],
                'discount_percent'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'start_date'        => ['type' => 'DATE', 'null' => true],
                'end_date'          => ['type' => 'DATE', 'null' => true],
                'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'expired', 'cancelled'], 'default' => 'active'],
                'notes'             => ['type' => 'TEXT', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('complimentary_offers', true);
        }

        // ── Contract templates (bilingual) ───────────────────────────
        if (! $this->db->tableExists('contract_templates')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
                'content_en' => ['type' => 'MEDIUMTEXT', 'null' => true],
                'content_ar' => ['type' => 'MEDIUMTEXT', 'null' => true],
                'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('contract_templates', true);

            $this->db->table('contract_templates')->insert([
                'name'       => 'Standard Residential Lease (EN/AR)',
                'content_en' => '<p>This lease agreement is between the landlord and tenant for unit {{unit_number}} at {{property_name}}.</p><p>Rent: {{rent_amount}} {{currency}} per {{payment_frequency}}.</p><p>Period: {{start_date}} to {{end_date}}.</p>',
                'content_ar' => '<p>عقد إيجار بين المالك والمستأجر للوحدة {{unit_number}} في {{property_name}}.</p>',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // ── AI flags & scores ────────────────────────────────────────
        if (! $this->db->tableExists('ai_flags')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'module'      => ['type' => 'VARCHAR', 'constraint' => 50],
                'ref_id'      => ['type' => 'INT', 'unsigned' => true],
                'flag_type'   => ['type' => 'VARCHAR', 'constraint' => 80],
                'severity'    => ['type' => 'ENUM', 'constraint' => ['info', 'warning', 'critical'], 'default' => 'warning'],
                'title'       => ['type' => 'VARCHAR', 'constraint' => 200],
                'message'     => ['type' => 'TEXT', 'null' => true],
                'workspace'   => ['type' => 'ENUM', 'constraint' => ['pm', 'fm', 'both'], 'default' => 'both'],
                'is_resolved' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'resolved_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['module', 'ref_id']);
            $this->forge->createTable('ai_flags', true);
        }

        if (! $this->db->tableExists('ai_tenant_scores')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
                'score'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 50],
                'risk_level'   => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high'], 'default' => 'medium'],
                'factors_json' => ['type' => 'TEXT', 'null' => true],
                'calculated_at'=> ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('tenant_id');
            $this->forge->createTable('ai_tenant_scores', true);
        }

        if (! $this->db->tableExists('ai_property_scores')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'facility_id'       => ['type' => 'INT', 'unsigned' => true],
                'score'             => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 50],
                'occupancy_health'  => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'revenue_health'    => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'maintenance_index' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
                'calculated_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('facility_id');
            $this->forge->createTable('ai_property_scores', true);
        }

        // ── Role permissions matrix ──────────────────────────────────
        if (! $this->db->tableExists('role_permissions')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'role_id'    => ['type' => 'INT', 'unsigned' => true],
                'module'     => ['type' => 'VARCHAR', 'constraint' => 80],
                'can_view'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'can_create' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'can_edit'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'can_delete' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['role_id', 'module']);
            $this->forge->createTable('role_permissions', true);
        }
    }

    public function down()
    {
        foreach ([
            'role_permissions', 'ai_property_scores', 'ai_tenant_scores', 'ai_flags',
            'contract_templates', 'complimentary_offers', 'commission_rules', 'sales_deals',
            'crm_visits', 'crm_activities', 'crm_leads', 'outgoing_cheques', 'cheques',
            'lease_payments', 'lease_contracts', 'tenants', 'landlords',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
