<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PortalCompletion extends Migration
{
    public function up()
    {
        foreach ([
            'procurement_match_required' => '1',
        ] as $key => $val) {
            if ($this->db->tableExists('system_settings')) {
                $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
                if (! $exists) {
                    $this->db->table('system_settings')->insert(['setting_key' => $key, 'setting_value' => $val]);
                }
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('system_settings')) {
            $this->db->table('system_settings')->where('setting_key', 'procurement_match_required')->delete();
        }
    }
}
