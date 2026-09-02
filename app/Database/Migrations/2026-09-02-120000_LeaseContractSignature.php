<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LeaseContractSignature extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return;
        }

        $fields = [
            'tenant_signature_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'tenant_qid',
            ],
            'signature_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'tenant_signature_path',
            ],
            'tenant_signed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'signature_token',
            ],
        ];

        $this->forge->addColumn('lease_contracts', $fields);

        if ($this->db->fieldExists('signature_token', 'lease_contracts')) {
            $this->db->query('CREATE INDEX IF NOT EXISTS idx_lc_signature_token ON lease_contracts (signature_token)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return;
        }

        foreach (['tenant_signed_at', 'signature_token', 'tenant_signature_path'] as $col) {
            if ($this->db->fieldExists($col, 'lease_contracts')) {
                $this->forge->dropColumn('lease_contracts', $col);
            }
        }
    }
}
