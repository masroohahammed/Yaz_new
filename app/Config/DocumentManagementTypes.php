<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Default document types for the documents module (merged with custom types in system_settings).
 */
class DocumentManagementTypes extends BaseConfig
{
    /** @var array<string, string> slug => label */
    public array $types = [
        'general'     => 'General',
        'contract'    => 'Contract',
        'id_copy'     => 'ID copy',
        'bank_letter' => 'Bank letter',
        'poa'         => 'Power of Attorney',
        'certificate' => 'Certificate',
        'invoice'     => 'Invoice',
        'warranty'    => 'Warranty',
        'inspection_report' => 'Inspection report',
        'other'       => 'Other',
    ];
}
