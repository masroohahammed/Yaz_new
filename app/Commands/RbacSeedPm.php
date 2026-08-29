<?php

namespace App\Commands;

use App\Services\RbacSeedService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class RbacSeedPm extends BaseCommand
{
    protected $group       = 'RBAC';
    protected $name        = 'rbac:seed-pm';
    protected $description = 'Grant PM roles unit view and lease/contract view (role_permissions + rbac_overrides)';
    protected $usage       = 'rbac:seed-pm';

    public function run(array $params)
    {
        $svc    = new RbacSeedService(Database::connect());
        $result = $svc->seedPmUnitAndLeaseAccess();

        CLI::write('PM permissions seeded.', 'green');
        CLI::write('  role_permissions rows upserted: ' . $result['role_permissions']);
        CLI::write('  rbac_overrides roles patched: ' . $result['rbac_roles']);
        CLI::write('PM users can now view units and contracts when assigned these roles.', 'cyan');
    }
}
