<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table         = 'companies';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'name', 'code', 'address', 'contact_person',
        'email', 'phone', 'vat_number', 'logo', 'status',
    ];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[200]',
        'code' => 'required|max_length[20]',
    ];
}
