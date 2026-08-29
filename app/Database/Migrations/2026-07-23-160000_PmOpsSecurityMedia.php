<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * PmOpsSecurityMedia — comprehensive migration for all new features added 2026-07-23:
 *  - Vendor quotations (FM)
 *  - Media albums + items
 *  - MFA / password policy columns on users
 *  - Login attempts table (brute force)
 *  - Facilities finance columns (if not from PmErpModules)
 */
class PmOpsSecurityMedia extends Migration
{
    public function up(): void
    {
        // ── Users: MFA + password policy ────────────────────────────
        if ($this->db->tableExists('users')) {
            $userCols = [
                'mfa_secret'          => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'password'],
                'mfa_enabled'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'password_changed_at' => ['type' => 'DATETIME', 'null' => true],
            ];
            foreach ($userCols as $col => $def) {
                if (!$this->db->fieldExists($col, 'users')) {
                    $this->forge->addColumn('users', [$col => $def]);
                }
            }
        }

        // ── Login attempts (brute force) ─────────────────────────────
        if (!$this->db->tableExists('login_attempts')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 150],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
                'success'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['email', 'created_at']);
            $this->forge->createTable('login_attempts', true);
        }

        // ── Facilities finance columns ───────────────────────────────
        if ($this->db->tableExists('facilities')) {
            $facilityCols = [
                'category'                => ['type' => 'ENUM', 'constraint' => ['Residential', 'Commercial'], 'null' => true],
                'property_type'           => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'listing_status'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'for_sale'                => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'sale_price'              => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'landlord_id'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'expected_monthly_income' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'landlord_share_pct'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'management_fee_pct'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'finance_notes'           => ['type' => 'TEXT', 'null' => true],
            ];
            foreach ($facilityCols as $col => $def) {
                if (!$this->db->fieldExists($col, 'facilities')) {
                    $this->forge->addColumn('facilities', [$col => $def]);
                }
            }
        }

        // ── Vendor quotations ────────────────────────────────────────
        if (!$this->db->tableExists('vendor_quotations')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'facility_id'    => ['type' => 'INT', 'unsigned' => true],
                'vendor_name'    => ['type' => 'VARCHAR', 'constraint' => 200],
                'vendor_contact' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'description'    => ['type' => 'TEXT', 'null' => true],
                'valid_until'    => ['type' => 'DATE', 'null' => true],
                'status'         => ['type' => 'ENUM', 'constraint' => ['draft', 'submitted', 'approved', 'rejected', 'expired'], 'default' => 'draft'],
                'notes'          => ['type' => 'TEXT', 'null' => true],
                'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('facility_id');
            $this->forge->createTable('vendor_quotations', true);
        }

        // ── Vendor quotation items ───────────────────────────────────
        if (!$this->db->tableExists('vendor_quotation_items')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'quotation_id' => ['type' => 'INT', 'unsigned' => true],
                'description'  => ['type' => 'TEXT'],
                'qty'          => ['type' => 'DECIMAL', 'constraint' => '10,3', 'default' => 1],
                'unit'         => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pcs'],
                'unit_price'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'sort_order'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('quotation_id');
            $this->forge->createTable('vendor_quotation_items', true);
        }

        // ── Media albums ─────────────────────────────────────────────
        if (!$this->db->tableExists('media_albums')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'module'         => ['type' => 'VARCHAR', 'constraint' => 50],
                'ref_id'         => ['type' => 'INT', 'unsigned' => true],
                'title'          => ['type' => 'VARCHAR', 'constraint' => 200],
                'album_type'     => ['type' => 'ENUM', 'constraint' => ['handover', 'return', 'condition', 'before_after', 'general'], 'default' => 'general'],
                'is_locked'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'signature_path' => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
                'locked_at'      => ['type' => 'DATETIME', 'null' => true],
                'locked_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['module', 'ref_id']);
            $this->forge->createTable('media_albums', true);
        }

        // ── Media items ──────────────────────────────────────────────
        if (!$this->db->tableExists('media_items')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'album_id'      => ['type' => 'INT', 'unsigned' => true],
                'file_path'     => ['type' => 'VARCHAR', 'constraint' => 500],
                'caption'       => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
                'condition_tag' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'sort_order'    => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('album_id');
            $this->forge->createTable('media_items', true);
        }

        // ── Role permissions (if not already created by PmErpModules) ─
        if (!$this->db->tableExists('role_permissions')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'role_id'    => ['type' => 'INT', 'unsigned' => true],
                'module'     => ['type' => 'VARCHAR', 'constraint' => 80],
                'can_view'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'can_create' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'can_edit'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'can_delete' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['role_id', 'module']);
            $this->forge->createTable('role_permissions', true);
        }

        // ── Inspection checklists: add damage_amount column ──────────
        if ($this->db->tableExists('inspection_checklists')) {
            if (!$this->db->fieldExists('damage_amount', 'inspection_checklists')) {
                $this->forge->addColumn('inspection_checklists', [
                    'damage_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (['media_items', 'media_albums', 'vendor_quotation_items', 'vendor_quotations', 'login_attempts'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($this->db->tableExists('users')) {
            foreach (['mfa_secret', 'mfa_enabled', 'password_changed_at'] as $col) {
                if ($this->db->fieldExists($col, 'users')) {
                    $this->forge->dropColumn('users', $col);
                }
            }
        }
    }
}
