<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WorkspaceArchitecture extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('roles')) {
            return;
        }

        if (! $this->db->fieldExists('workspace', 'roles')) {
            $this->forge->addColumn('roles', [
                'workspace' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pm', 'fm', 'both', 'portal', 'collector'],
                    'null'       => true,
                    'after'      => 'display_name',
                ],
            ]);
        }

        $map = [
            'super_admin'          => 'both',
            'facility_manager'     => 'fm',
            'technician'           => 'fm',
            'qa_inspector'         => 'fm',
            'procurement_officer'  => 'fm',
            'property_manager'     => 'pm',
            'real_estate_manager'  => 'pm',
            'salesman'             => 'pm',
            'finance_manager'      => 'pm',
            'finance_user'         => 'pm',
            'supervisor'           => 'pm',
            'client'               => 'portal',
        ];

        foreach ($map as $name => $workspace) {
            $this->db->table('roles')->where('name', $name)->update(['workspace' => $workspace]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('roles') && $this->db->fieldExists('workspace', 'roles')) {
            $this->forge->dropColumn('roles', 'workspace');
        }
    }
}
