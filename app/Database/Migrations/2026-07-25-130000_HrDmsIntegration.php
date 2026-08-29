<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HrDmsIntegration20260725 extends Migration
{
    public function up()
    {
        $file = ROOTPATH . 'database/patch_hr_m2_dms.sql';
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
                log_message('warning', 'HR M2 migration skipped: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
    }
}
