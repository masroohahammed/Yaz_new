<?php

namespace App\Models;

use CodeIgniter\Model;

class Contract_model extends Model
{
    protected $table          = 'lease_contracts';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'company_id', 'contract_number', 'tenant_id', 'facility_id', 'unit_id', 'template_id',
        'status', 'signed_date', 'billing_start_date', 'start_date', 'end_date',
        'rent_amount', 'security_deposit', 'payment_frequency', 'payment_type', 'payment_day',
        'has_free_period', 'free_period_months', 'free_period_desc', 'free_period_position',
        'includes_utilities', 'utilities_desc', 'includes_furnished', 'furnished_desc',
        'deposit_payment_method', 'deposit_cheque_no', 'prorata_basis', 'late_penalty_pct',
        'grace_period_days', 'vat_applicable', 'vat_rate', 'discount_pct', 'auto_renew',
        'auto_generate_invoices', 'contract_terms', 'custom_content_en', 'custom_content_ar',
        'notes', 'parent_contract_id', 'termination_reason', 'edited_at', 'edited_by', 'created_by',
    ];

    public function listPaginated(array $filters, int $perPage = 20, int $page = 1, ?array $facilityIds = null): array
    {
        $builder = $this->db->table('lease_contracts lc')
            ->select('lc.*, t.full_name AS tenant_name, f.name AS property_name, u.unit_number')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->where('lc.deleted_at', null);

        if ($facilityIds !== null) {
            if (empty($facilityIds)) {
                $builder->where('1 = 0', null, false);
            } else {
                $builder->whereIn('lc.facility_id', $facilityIds);
            }
        }

        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('lc.contract_number', $s)
                ->orLike('t.full_name', $s)
                ->orLike('t.phone', $s)
                ->groupEnd();
        }
        if (! empty($filters['status'])) {
            $builder->where('lc.status', $filters['status']);
        }
        if (! empty($filters['property_id'])) {
            $builder->where('lc.facility_id', (int) $filters['property_id']);
        }

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->orderBy('lc.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return [
            'rows'    => $rows,
            'total'   => $total,
            'perPage' => $perPage,
            'page'    => $page,
        ];
    }

    public function findDetail(int $id): ?array
    {
        $tenantBlacklist = $this->db->fieldExists('is_blacklisted', 'tenants')
            ? 't.is_blacklisted'
            : 'CASE WHEN t.status = \'blacklisted\' THEN 1 ELSE 0 END AS is_blacklisted';

        $row = $this->db->table('lease_contracts lc')
            ->select('lc.*, t.full_name AS tenant_name, t.phone AS tenant_phone, t.email AS tenant_email,
                ' . $tenantBlacklist . ', t.status AS tenant_status, f.name AS property_name, u.unit_number')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->where('lc.id', $id)
            ->where('lc.deleted_at', null)
            ->get()->getRowArray();

        return $row ?: null;
    }

    public function get360Data(int $contractId): array
    {
        $out = [
            'payments'     => [],
            'rentSchedule' => [],
            'amendments'   => [],
            'cheques'      => [],
            'documents'    => [],
        ];

        if ($this->db->tableExists('lease_payments')) {
            $out['payments'] = $this->db->table('lease_payments')
                ->where('contract_id', $contractId)
                ->orderBy('due_date', 'DESC')
                ->limit(50)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('contract_rent_schedule')) {
            $out['rentSchedule'] = $this->db->table('contract_rent_schedule')
                ->where('contract_id', $contractId)
                ->orderBy('year_number')
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('lease_amendments')) {
            $out['amendments'] = $this->db->table('lease_amendments la')
                ->select('la.*, u.name AS created_by_name')
                ->join('users u', 'u.id = la.created_by', 'left')
                ->where('la.contract_id', $contractId)
                ->orderBy('la.created_at', 'DESC')
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('cheques')) {
            $out['cheques'] = $this->db->table('cheques')
                ->where('contract_id', $contractId)
                ->orderBy('cheque_date', 'DESC')
                ->limit(20)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('documents')) {
            $out['documents'] = $this->db->table('documents')
                ->whereIn('module', ['leases', 'contracts'])
                ->where('ref_id', $contractId)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        return $out;
    }

    public function unitHasActiveContract(int $unitId, int $excludeContractId = 0): bool
    {
        $q = $this->db->table('lease_contracts')
            ->where('unit_id', $unitId)
            ->where('status', 'active')
            ->where('deleted_at', null);
        if ($excludeContractId > 0) {
            $q->where('id !=', $excludeContractId);
        }

        return $q->countAllResults() > 0;
    }

    public function syncRentSchedule(int $contractId, array $annualRent): void
    {
        if (! $this->db->tableExists('contract_rent_schedule')) {
            return;
        }

        $this->db->table('contract_rent_schedule')->where('contract_id', $contractId)->delete();

        foreach ($annualRent as $yearIdx => $amount) {
            if ((float) $amount <= 0) {
                continue;
            }
            $this->db->table('contract_rent_schedule')->insert([
                'contract_id'  => $contractId,
                'year_number'  => (int) $yearIdx + 1,
                'rent_amount'  => (float) $amount,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /** @return array{data: array, errors: array<string, string>} */
    public function validateAndBuild(array $post, array $user, bool $isEdit, int $contractId = 0): array
    {
        $errors = [];
        $data   = [];

        $tenantId   = (int) ($post['tenant_id'] ?? 0);
        $propertyId = (int) ($post['property_id'] ?? $post['facility_id'] ?? 0);
        $unitId     = (int) ($post['unit_id'] ?? 0);
        $startDate  = trim((string) ($post['start_date'] ?? ''));
        $endDate    = trim((string) ($post['end_date'] ?? ''));
        $rentAmount = $post['rent_amount'] ?? null;

        if ($tenantId <= 0) {
            $errors['tenant_id'] = 'Tenant is required.';
        }
        if ($propertyId <= 0) {
            $errors['property_id'] = 'Property is required.';
        }
        if ($unitId <= 0) {
            $errors['unit_id'] = 'Unit is required.';
        }
        if ($startDate === '') {
            $errors['start_date'] = 'Start date is required.';
        }
        if ($endDate === '') {
            $errors['end_date'] = 'End date is required.';
        }
        if ($startDate && $endDate && $endDate <= $startDate) {
            $errors['end_date'] = 'End date must be after start date.';
        }
        if (! is_numeric($rentAmount) || (float) $rentAmount <= 0) {
            $errors['rent_amount'] = 'Rent amount must be greater than zero.';
        }
        if (empty($post['payment_frequency'])) {
            $errors['payment_frequency'] = 'Payment frequency is required.';
        }

        $vatApplicable = ! empty($post['vat_applicable']);
        if ($vatApplicable && ! is_numeric($post['vat_rate'] ?? null)) {
            $errors['vat_rate'] = 'VAT rate is required when VAT is applicable.';
        }

        $depositMethod = $post['deposit_payment_method'] ?? '';
        if ($depositMethod === 'cheque' && empty(trim((string) ($post['deposit_cheque_no'] ?? '')))) {
            $errors['deposit_cheque_no'] = 'Deposit cheque number is required.';
        }

        if ($unitId > 0 && $this->unitHasActiveContract($unitId, $isEdit ? $contractId : 0)) {
            $errors['unit_id'] = 'This unit already has an active contract.';
        }

        if ($tenantId > 0 && $this->db->tableExists('tenants')) {
            $t = $this->db->table('tenants')->where('id', $tenantId)->get()->getRowArray();
            $blacklisted = $t && (
                ! empty($t['is_blacklisted'])
                || (($t['status'] ?? '') === 'blacklisted')
            );
            if ($blacklisted) {
                // Warning only — stored in flash after save if needed
            }
        }

        if ($errors) {
            return ['data' => [], 'errors' => $errors];
        }

        $freq = $post['payment_frequency'];
        if ($freq === 'annual') {
            $freq = 'yearly';
        }

        $data = [
            'tenant_id'              => $tenantId,
            'facility_id'            => $propertyId,
            'unit_id'                => $unitId,
            'template_id'            => (int) ($post['template_id'] ?? 0) ?: null,
            'status'                 => $post['status'] ?? ($isEdit ? null : 'active'),
            'signed_date'            => $post['signed_date'] ?: null,
            'start_date'             => $startDate,
            'end_date'               => $endDate,
            'rent_amount'            => (float) $rentAmount,
            'security_deposit'       => $post['security_deposit'] ?: null,
            'payment_frequency'      => $freq,
            'payment_type'           => $post['payment_type'] ?? $post['deposit_payment_method'] ?? 'cheque',
            'payment_day'            => $post['payment_day'] ?: null,
            'has_free_period'        => ! empty($post['has_free_period']) ? 1 : 0,
            'free_period_months'     => $post['free_period_months'] ?: null,
            'free_period_desc'       => $post['free_period_desc'] ?? null,
            'free_period_position'   => $post['free_period_position'] ?? 'beginning',
            'includes_utilities'     => ! empty($post['includes_utilities']) ? 1 : 0,
            'utilities_desc'         => $post['utilities_desc'] ?? null,
            'includes_furnished'     => ! empty($post['includes_furnished']) ? 1 : 0,
            'furnished_desc'         => $post['furnished_desc'] ?? null,
            'deposit_payment_method' => $post['deposit_payment_method'] ?? null,
            'deposit_cheque_no'      => $post['deposit_cheque_no'] ?? null,
            'prorata_basis'          => $post['prorata_basis'] ?? null,
            'late_penalty_pct'       => $post['late_penalty_pct'] ?: null,
            'grace_period_days'      => $post['grace_period_days'] ?: null,
            'vat_applicable'         => $vatApplicable ? 1 : 0,
            'vat_rate'               => $vatApplicable ? $post['vat_rate'] : null,
            'discount_pct'           => $post['discount_pct'] ?: null,
            'auto_renew'             => ! empty($post['auto_renew']) ? 1 : 0,
            'auto_generate_invoices' => ! empty($post['auto_generate_invoices']) ? 1 : ($isEdit ? null : 1),
            'contract_terms'         => $post['contract_terms'] ?? null,
            'custom_content_en'      => $post['custom_content_en'] ?? null,
            'custom_content_ar'      => $post['custom_content_ar'] ?? null,
            'notes'                  => $post['notes'] ?? null,
            'updated_at'             => date('Y-m-d H:i:s'),
        ];

        if (! $isEdit) {
            $data['company_id']  = (int) ($user['company_id'] ?? 1);
            $data['created_by']  = (int) $user['id'];
            $data['created_at']  = date('Y-m-d H:i:s');
            $data['status']      = $data['status'] ?? 'active';
            $data['auto_generate_invoices'] = $data['auto_generate_invoices'] ?? 1;
        } else {
            unset($data['status']);
            if ($post['status'] ?? '') {
                $data['status'] = $post['status'];
            }
        }

        return ['data' => $data, 'errors' => []];
    }

    public function activateUnitAndTenant(int $contractId): void
    {
        $c = $this->find($contractId);
        if (! $c) {
            return;
        }

        if ($this->db->tableExists('units') && ! empty($c['unit_id'])) {
            $this->db->table('units')->where('id', (int) $c['unit_id'])->update([
                'status'     => 'occupied',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->db->tableExists('tenants') && ! empty($c['tenant_id']) && ! empty($c['unit_id'])) {
            $updates = ['updated_at' => date('Y-m-d H:i:s')];
            if ($this->db->fieldExists('current_unit_id', 'tenants')) {
                $updates['current_unit_id'] = (int) $c['unit_id'];
            }
            $this->db->table('tenants')->where('id', (int) $c['tenant_id'])->update($updates);
        }
    }

    public function releaseUnitAndTenant(array $contract): void
    {
        if ($this->db->tableExists('units') && ! empty($contract['unit_id'])) {
            $this->db->table('units')->where('id', (int) $contract['unit_id'])->update([
                'status'     => 'vacant',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->db->tableExists('tenants') && ! empty($contract['tenant_id'])) {
            $updates = ['updated_at' => date('Y-m-d H:i:s')];
            if ($this->db->fieldExists('current_unit_id', 'tenants')) {
                $q = $this->db->table('tenants')
                    ->where('id', (int) $contract['tenant_id'])
                    ->where('current_unit_id', (int) $contract['unit_id']);
                $updates['current_unit_id'] = null;
                $q->update($updates);
            }
        }
    }
}
