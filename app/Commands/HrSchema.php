<?php

namespace App\Commands;

use App\Services\Hr\HrSchemaService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Align HR department/designation/grade master tables for legacy databases.
 */
class HrSchema extends BaseCommand
{
    protected $group       = 'HR';
    protected $name        = 'hr:schema';
    protected $description = 'Align HR lookup/master tables (company_id, code columns) and employee FK backfills';
    protected $usage       = 'hr:schema';

    public function run(array $params)
    {
        $schema = new HrSchemaService();

        try {
            $schema->ensureAllSchemas();
        } catch (\Throwable $e) {
            CLI::error('HR schema alignment failed: ' . $e->getMessage());

            return;
        }

        if ($schema->employeeMasterReady() && $schema->leaveTypesReady()) {
            CLI::write('HR schema is aligned (employee master + lookup tables).', 'green');
        } else {
            CLI::write('HR schema step completed — run patch_hr_upgrade_complete.sql if modules still report migration required.', 'yellow');
        }
    }
}
