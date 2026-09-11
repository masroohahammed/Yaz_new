<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContractTypesAndPaymentTracking extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('contract_types')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'slug'       => ['type' => 'VARCHAR', 'constraint' => 40],
                'name_en'    => ['type' => 'VARCHAR', 'constraint' => 120],
                'name_ar'    => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'is_system'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'sort_order' => ['type' => 'INT', 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('slug');
            $this->forge->createTable('contract_types', true);
        }

        if ($this->db->tableExists('contract_templates') && ! $this->db->fieldExists('contract_type_id', 'contract_templates')) {
            $this->forge->addColumn('contract_templates', [
                'contract_type_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'name'],
                'terms_en'         => ['type' => 'TEXT', 'null' => true, 'after' => 'content_ar'],
                'terms_ar'         => ['type' => 'TEXT', 'null' => true, 'after' => 'terms_en'],
            ]);
        }

        if ($this->db->tableExists('lease_contracts')) {
            $cols = [];
            if (! $this->db->fieldExists('contract_type_id', 'lease_contracts')) {
                $cols['contract_type_id'] = ['type' => 'INT', 'unsigned' => true, 'null' => true];
            }
            if (! $this->db->fieldExists('utility_transfer_applicable', 'lease_contracts')) {
                $cols['utility_transfer_applicable'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
            }
            if (! $this->db->fieldExists('utility_transfer_details', 'lease_contracts')) {
                $cols['utility_transfer_details'] = ['type' => 'TEXT', 'null' => true];
            }
            if ($cols !== []) {
                $this->forge->addColumn('lease_contracts', $cols);
            }
        }

        if ($this->db->tableExists('lease_payments')) {
            $cols = [];
            if (! $this->db->fieldExists('amount_paid', 'lease_payments')) {
                $cols['amount_paid'] = ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0];
            }
            if (! $this->db->fieldExists('original_due_date', 'lease_payments')) {
                $cols['original_due_date'] = ['type' => 'DATE', 'null' => true];
            }
            if (! $this->db->fieldExists('postponed_from_date', 'lease_payments')) {
                $cols['postponed_from_date'] = ['type' => 'DATE', 'null' => true];
            }
            if ($cols !== []) {
                $this->forge->addColumn('lease_payments', $cols);
            }
        }

        if ($this->db->tableExists('cheques')) {
            $cols = [];
            foreach ([
                'payment_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'landlord_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'payable_to_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'payable_to_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'due_date'        => ['type' => 'DATE', 'null' => true],
                'image_path'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            ] as $field => $def) {
                if (! $this->db->fieldExists($field, 'cheques')) {
                    $cols[$field] = $def;
                }
            }
            if ($cols !== []) {
                $this->forge->addColumn('cheques', $cols);
            }
        }

        if (! $this->db->tableExists('payment_status_history')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'payment_id'  => ['type' => 'INT', 'unsigned' => true],
                'contract_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'from_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'to_status'   => ['type' => 'VARCHAR', 'constraint' => 40],
                'amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'notes'       => ['type' => 'TEXT', 'null' => true],
                'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'  => ['type' => 'DATETIME'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('payment_id');
            $this->forge->createTable('payment_status_history', true);
        }

        if (! $this->db->tableExists('cheque_status_history')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'cheque_id'   => ['type' => 'INT', 'unsigned' => true],
                'from_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'to_status'   => ['type' => 'VARCHAR', 'constraint' => 40],
                'notes'       => ['type' => 'TEXT', 'null' => true],
                'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'  => ['type' => 'DATETIME'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('cheque_id');
            $this->forge->createTable('cheque_status_history', true);
        }

        $this->seedContractTypes();
    }

    public function down()
    {
        foreach (['cheque_status_history', 'payment_status_history', 'contract_types'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }

    private function seedContractTypes(): void
    {
        if (! $this->db->tableExists('contract_types')) {
            return;
        }

        $defaults = [
            ['slug' => 'parking', 'name_en' => 'Parking Contract', 'name_ar' => 'عقد موقف', 'sort_order' => 1],
            ['slug' => 'residential', 'name_en' => 'Residential Contract', 'name_ar' => 'عقد سكني', 'sort_order' => 2],
            ['slug' => 'commercial', 'name_en' => 'Commercial Contract', 'name_ar' => 'عقد تجاري', 'sort_order' => 3],
            ['slug' => 'other', 'name_en' => 'Other Contract', 'name_ar' => 'عقد آخر', 'sort_order' => 4],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($defaults as $row) {
            $exists = $this->db->table('contract_types')->where('slug', $row['slug'])->countAllResults();
            if ($exists > 0) {
                continue;
            }
            $this->db->table('contract_types')->insert(array_merge($row, [
                'is_system'  => 1,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
