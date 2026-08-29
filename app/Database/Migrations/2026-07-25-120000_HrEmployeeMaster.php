<?php

namespace App\Database\Migrations;

use App\Services\Hr\HrSchemaService;
use CodeIgniter\Database\Migration;

/**
 * HRMS Phase M0 + M1 — Employee master extension.
 * SQL equivalents: database/patch_hr_m0_prerequisites.sql, patch_hr_m1_employee_master.sql
 */
class HrEmployeeMaster20260725 extends Migration
{
    public function up()
    {
        $files = [
            ROOTPATH . 'database/patch_hr_m0_prerequisites.sql',
            ROOTPATH . 'database/patch_hr_m1_employee_master.sql',
        ];

        foreach ($files as $file) {
            $this->runSqlFile($file);
        }

        try {
            $schema = new HrSchemaService($this->db);
            $schema->ensureAllSchemas();
        } catch (\Throwable $e) {
            log_message('warning', 'HR schema alignment: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Non-destructive upgrade — no down migration.
    }

    private function runSqlFile(string $file): void
    {
        if (! is_file($file)) {
            return;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            return;
        }

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }
            try {
                $this->db->query($statement);
            } catch (\Throwable $e) {
                log_message('warning', 'HR migration statement skipped: ' . $e->getMessage());
            }
        }
    }
}
