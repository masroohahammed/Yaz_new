<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Financial workflow: estimation item costing, actual cost breakdown,
 * material tracking, invoice payment balances, role-ready schema.
 */
class FinancialWorkflowRedesign extends Migration
{
    public function up()
    {
        $this->migrateEstimations();
        $this->migrateEstimationItems();
        $this->migrateWorkOrders();
        $this->migrateWoMaterials();
        $this->migrateInvoices();
        $this->migrateInvoiceItems();
        $this->createEstimationMaterialsIfNeeded();
    }

    private function migrateEstimations(): void
    {
        if (! $this->db->tableExists('estimations')) {
            return;
        }

        $cols = [
            'actual_labor_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'other_cost'],
            'actual_material_cost'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_labor_cost'],
            'actual_transport_cost'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_material_cost'],
            'actual_equipment_cost'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_transport_cost'],
            'actual_misc_cost'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_equipment_cost'],
            'actual_other_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_misc_cost'],
            'actual_total'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_other_cost'],
            'actual_total_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_total'],
            'selling_subtotal'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'subtotal'],
            'estimated_subtotal'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'selling_subtotal'],
            'actual_subtotal'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'estimated_subtotal'],
            'total_profit'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_subtotal'],
            'total_margin'            => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0, 'after' => 'total_profit'],
            'cost_variance'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'total_margin'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'estimations')) {
                $this->forge->addColumn('estimations', [$name => $def]);
            }
        }

        // Backfill actual_total from legacy actual_other breakdown if present
        if ($this->db->fieldExists('actual_labor_cost', 'estimations')) {
            $this->db->query('
                UPDATE estimations SET
                    actual_total = COALESCE(actual_labor_cost,0) + COALESCE(actual_material_cost,0)
                                   + COALESCE(actual_transport_cost,0) + COALESCE(actual_equipment_cost,0)
                                   + COALESCE(actual_misc_cost,0) + COALESCE(actual_other_cost,0),
                    actual_total_cost = COALESCE(actual_labor_cost,0) + COALESCE(actual_material_cost,0)
                                      + COALESCE(actual_transport_cost,0) + COALESCE(actual_equipment_cost,0)
                                      + COALESCE(actual_misc_cost,0) + COALESCE(actual_other_cost,0)
                WHERE actual_total = 0 OR actual_total IS NULL
            ');
        }
    }

    private function migrateEstimationItems(): void
    {
        if (! $this->db->tableExists('estimation_items')) {
            return;
        }

        $cols = [
            'item_name'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true, 'after' => 'type'],
            'unit'                 => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unit', 'after' => 'quantity'],
            'unit_price'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'unit'],
            'estimated_unit_cost'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'unit_price'],
            'actual_unit_cost'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'estimated_unit_cost'],
            'line_total'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'total_cost'],
            'estimated_total'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'line_total'],
            'actual_total'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'estimated_total'],
            'profit'               => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_total'],
            'margin_percent'       => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0, 'after' => 'profit'],
            'variance'             => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'margin_percent'],
            'actual_used_qty'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0, 'after' => 'variance'],
            'wastage_qty'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0, 'after' => 'actual_used_qty'],
            'wastage_cost'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'wastage_qty'],
            'sort_order'           => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'wastage_cost'],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true, 'after' => 'created_at'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'estimation_items')) {
                $this->forge->addColumn('estimation_items', [$name => $def]);
            }
        }

        // Migrate legacy unit_cost → estimated_unit_cost, populate item_name from description
        if ($this->db->fieldExists('unit_cost', 'estimation_items')) {
            $this->db->query('
                UPDATE estimation_items SET
                    item_name = COALESCE(NULLIF(item_name, ""), description),
                    estimated_unit_cost = CASE WHEN estimated_unit_cost = 0 THEN unit_cost ELSE estimated_unit_cost END,
                    line_total = CASE WHEN line_total = 0 THEN quantity * unit_cost ELSE line_total END,
                    estimated_total = CASE WHEN estimated_total = 0 THEN quantity * unit_cost ELSE estimated_total END,
                    total_cost = CASE WHEN total_cost = 0 THEN quantity * unit_cost ELSE total_cost END
            ');
        }
    }

    private function migrateWorkOrders(): void
    {
        if (! $this->db->tableExists('work_orders')) {
            return;
        }

        $cols = [
            'customer_type'           => ['type' => 'ENUM', 'constraint' => ['facility', 'non_facility', 'walk_in', 'direct'], 'default' => 'facility', 'after' => 'requester_email'],
            'actual_labor_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_cost'],
            'actual_material_cost'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_labor_cost'],
            'actual_transport_cost'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_material_cost'],
            'actual_equipment_cost'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_transport_cost'],
            'actual_misc_cost'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_equipment_cost'],
            'actual_total_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_misc_cost'],
            'billed_amount'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'actual_total_cost'],
            'pending_billing_amount'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'billed_amount'],
            'execution_percent'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'after' => 'pending_billing_amount'],
            'billing_percent'         => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'after' => 'execution_percent'],
            'selling_total'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'billing_percent'],
            'estimation_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'invoice_id'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'work_orders')) {
                $this->forge->addColumn('work_orders', [$name => $def]);
            }
        }

        if ($this->db->fieldExists('actual_cost', 'work_orders') && $this->db->fieldExists('actual_total_cost', 'work_orders')) {
            $this->db->query('UPDATE work_orders SET actual_total_cost = COALESCE(actual_cost, 0) WHERE actual_total_cost = 0');
        }
    }

    private function migrateWoMaterials(): void
    {
        if (! $this->db->tableExists('wo_materials')) {
            return;
        }

        $cols = [
            'unit'               => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unit', 'after' => 'quantity'],
            'unit_price'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'unit'],
            'estimated_cost'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'unit_cost'],
            'actual_cost'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'estimated_cost'],
            'actual_used_qty'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0, 'after' => 'actual_cost'],
            'wastage_qty'        => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0, 'after' => 'actual_used_qty'],
            'wastage_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'wastage_qty'],
            'estimation_item_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'item_id'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'wo_materials')) {
                $this->forge->addColumn('wo_materials', [$name => $def]);
            }
        }

        if ($this->db->fieldExists('total_cost', 'wo_materials')) {
            $this->db->query('
                UPDATE wo_materials SET
                    estimated_cost = CASE WHEN estimated_cost = 0 THEN total_cost ELSE estimated_cost END,
                    actual_cost = CASE WHEN actual_cost = 0 THEN total_cost ELSE actual_cost END,
                    actual_used_qty = CASE WHEN actual_used_qty = 0 THEN quantity ELSE actual_used_qty END
            ');
        }
    }

    private function migrateInvoices(): void
    {
        if (! $this->db->tableExists('invoices')) {
            return;
        }

        if ($this->db->fieldExists('status', 'invoices')) {
            $this->db->query("
                ALTER TABLE `invoices`
                MODIFY `status` enum('draft','pending','approved','sent','partial','paid','overdue','cancelled')
                NOT NULL DEFAULT 'draft'
            ");
        }

        $cols = [
            'paid_amount'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'total'],
            'pending_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'paid_amount'],
            'due_amount'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'pending_amount'],
            'is_partial'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'due_amount'],
            'wo_billing_seq' => ['type' => 'INT', 'unsigned' => true, 'default' => 1, 'after' => 'work_order_id'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'invoices')) {
                $this->forge->addColumn('invoices', [$name => $def]);
            }
        }

        if ($this->db->fieldExists('due_amount', 'invoices')) {
            $this->db->query('
                UPDATE invoices SET
                    paid_amount = CASE WHEN status = "paid" THEN total ELSE 0 END,
                    due_amount = CASE WHEN status = "paid" THEN 0 ELSE total END,
                    pending_amount = CASE WHEN status IN ("sent","overdue","partial","draft","pending","approved") THEN total ELSE 0 END
            ');
        }
    }

    private function migrateInvoiceItems(): void
    {
        if (! $this->db->tableExists('invoice_items')) {
            return;
        }

        $cols = [
            'unit'                 => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unit', 'after' => 'quantity'],
            'estimated_cost'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'unit_cost_internal'],
            'actual_cost'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'estimated_cost'],
            'estimation_item_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'work_order_id'],
            'wo_material_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'estimation_item_id'],
        ];

        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, 'invoice_items')) {
                $this->forge->addColumn('invoice_items', [$name => $def]);
            }
        }
    }

    private function createEstimationMaterialsIfNeeded(): void
    {
        if ($this->db->tableExists('estimation_materials')) {
            return;
        }

        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'est_id'              => ['type' => 'INT', 'unsigned' => true],
            'estimation_item_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'wo_id'               => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'material_name'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'quantity'            => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 1],
            'unit'                => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unit'],
            'unit_price'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'estimated_cost'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'actual_cost'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_amount'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'actual_used_qty'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'wastage_qty'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'wastage_cost'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'inventory_item_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('est_id');
        $this->forge->addKey('wo_id');
        $this->forge->createTable('estimation_materials', true);
    }

    public function down()
    {
        // Non-destructive rollback omitted for production safety
    }
}
