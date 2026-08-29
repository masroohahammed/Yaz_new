<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HrLeave20260725 extends Migration
{
    public function up()
    {
        $file = ROOTPATH . 'database/patch_hr_m6_leave.sql';
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
                log_message('warning', 'HR M6 migration skipped: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
    }
}
