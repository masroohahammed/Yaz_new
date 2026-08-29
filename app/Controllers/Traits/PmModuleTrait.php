<?php

namespace App\Controllers\Traits;

/**
 * Shared PM module helpers.
 */
trait PmModuleTrait
{
    protected function pmCompanyId(): ?int
    {
        $id = session()->get('company_id');

        return $id ? (int) $id : null;
    }

    protected function pmTableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }

    /** @return array<string,mixed>|null */
    protected function pmFind(string $table, int $id, ?string $companyColumn = null, ?string $facilityColumn = null): ?array
    {
        if (! $this->pmTableExists($table)) {
            return null;
        }

        $row = $this->db->table($table)->where('id', $id);
        if ($this->db->fieldExists('deleted_at', $table)) {
            $row->where('deleted_at', null);
        }

        $companyCol = $companyColumn ?? ($this->db->fieldExists('company_id', $table) ? 'company_id' : null);
        if ($companyCol !== null) {
            $this->scopeCompany($row, $companyCol);
        }

        $facilityCol = $facilityColumn ?? ($this->db->fieldExists('facility_id', $table) ? 'facility_id' : null);
        if ($facilityCol !== null) {
            $this->scopeFacilities($row, $facilityCol);
        }

        return $row->get()->getRowArray() ?: null;
    }
}
