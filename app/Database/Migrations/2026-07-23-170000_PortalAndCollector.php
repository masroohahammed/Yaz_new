<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * PortalAndCollector — creates collector workspace tables and ensures the
 * cash_collector role exists with workspace=collector.
 *
 * Tables created (if not exist):
 *   collector_sessions      — daily field-collection sessions
 *   collection_assignments  — per-tenant/payment assignments to a collector
 *   collector_handoffs      — cash handoff from collector to finance
 *
 * Schema additions on existing tables:
 *   users.tenant_id              — links a user to a tenant record
 *   lease_payments.received_by   — collector user_id who took the payment
 *   lease_payments.collection_session_id — FK to collector_sessions
 */
class PortalAndCollector extends Migration
{
    public function up(): void
    {
        // ── 1. users.tenant_id ──────────────────────────────────────────
        if ($this->db->tableExists('users') && ! $this->db->fieldExists('tenant_id', 'users')) {
            $this->forge->addColumn('users', [
                'tenant_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'company_id',
                ],
            ]);
        }

        // ── 1b. tenants.user_id (reverse link for portal) ─────────────
        if ($this->db->tableExists('tenants') && ! $this->db->fieldExists('user_id', 'tenants')) {
            $this->forge->addColumn('tenants', [
                'user_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'company_id',
                ],
            ]);
        }

        // ── 2. lease_payments extra collector columns ─────────────────
        if ($this->db->tableExists('lease_payments')) {
            $lpCols = [
                'received_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'collection_session_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
            ];
            foreach ($lpCols as $col => $def) {
                if (! $this->db->fieldExists($col, 'lease_payments')) {
                    $this->forge->addColumn('lease_payments', [$col => $def]);
                }
            }
        }

        // ── 3. collector_sessions ────────────────────────────────────
        if (! $this->db->tableExists('collector_sessions')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'collector_id'  => ['type' => 'INT', 'unsigned' => true],
                'session_code'  => ['type' => 'VARCHAR', 'constraint' => 30],
                'started_at'    => ['type' => 'DATETIME', 'null' => true],
                'closed_at'     => ['type' => 'DATETIME', 'null' => true],
                'status'        => ['type' => 'ENUM', 'constraint' => ['open', 'closed'], 'default' => 'open'],
                'opening_float' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
                'closing_cash'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'notes'         => ['type' => 'TEXT', 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('collector_id');
            $this->forge->addKey('session_code');
            $this->forge->createTable('collector_sessions', true);
        }

        // ── 4. collection_assignments ────────────────────────────────
        if (! $this->db->tableExists('collection_assignments')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'collector_id'  => ['type' => 'INT', 'unsigned' => true],
                'tenant_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'facility_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'payment_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'assigned_date' => ['type' => 'DATE', 'null' => true],
                'status'        => ['type' => 'ENUM', 'constraint' => ['pending', 'collected', 'skipped', 'cancelled'], 'default' => 'pending'],
                'notes'         => ['type' => 'TEXT', 'null' => true],
                'created_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('collector_id');
            $this->forge->addKey('tenant_id');
            $this->forge->createTable('collection_assignments', true);
        }

        // ── 5. collector_handoffs ────────────────────────────────────
        if (! $this->db->tableExists('collector_handoffs')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'company_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'session_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'collector_id'    => ['type' => 'INT', 'unsigned' => true],
                'amount'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
                'acknowledged_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'acknowledged_at' => ['type' => 'DATETIME', 'null' => true],
                'status'          => ['type' => 'ENUM', 'constraint' => ['pending', 'acknowledged'], 'default' => 'pending'],
                'notes'           => ['type' => 'TEXT', 'null' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('collector_id');
            $this->forge->createTable('collector_handoffs', true);
        }

        // ── 6. Ensure cash_collector + tenant roles exist ─────────────
        if ($this->db->tableExists('roles')) {
            $roleSeeds = [
                'cash_collector' => ['display' => 'Cash Collector', 'workspace' => 'collector'],
                'tenant'         => ['display' => 'Tenant (Portal)', 'workspace' => 'portal'],
            ];
            foreach ($roleSeeds as $name => $meta) {
                $exists = $this->db->table('roles')->where('name', $name)->countAllResults();
                if (! $exists) {
                    $insert = [
                        'name'         => $name,
                        'display_name' => $meta['display'],
                    ];
                    if ($this->db->fieldExists('workspace', 'roles')) {
                        $insert['workspace'] = $meta['workspace'];
                    }
                    $this->db->table('roles')->insert($insert);
                } elseif ($this->db->fieldExists('workspace', 'roles')) {
                    $this->db->table('roles')
                        ->where('name', $name)
                        ->update(['workspace' => $meta['workspace']]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['collector_handoffs', 'collection_assignments', 'collector_sessions'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($this->db->tableExists('lease_payments')) {
            foreach (['received_by', 'collection_session_id'] as $col) {
                if ($this->db->fieldExists($col, 'lease_payments')) {
                    $this->forge->dropColumn('lease_payments', $col);
                }
            }
        }

        if ($this->db->tableExists('users') && $this->db->fieldExists('tenant_id', 'users')) {
            $this->forge->dropColumn('users', 'tenant_id');
        }
    }
}
