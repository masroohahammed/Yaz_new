<?php

namespace App\Services;

use App\Models\Unit_model;
use CodeIgniter\Database\BaseConnection;

/** Shared AJAX: units by property (facility_id) */
class UnitByPropertyService
{
    public function __construct(
        private BaseConnection $db,
        private ?Unit_model $unitModel = null
    ) {
        $this->unitModel ??= new Unit_model();
    }

    public function json(int $facilityId): array
    {
        return $this->unitModel->unitsJsonForProperty($facilityId);
    }

    public function all(int $facilityId): array
    {
        return $this->unitModel->listForProperty($facilityId);
    }
}
