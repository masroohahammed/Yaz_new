-- Financial Workflow Redesign patch
-- Run if CI4 migration cannot be executed: mysql -u user -p dbname < patch_financial_workflow.sql

ALTER TABLE `estimations`
  ADD COLUMN IF NOT EXISTS `actual_labor_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `other_cost`,
  ADD COLUMN IF NOT EXISTS `actual_material_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_labor_cost`,
  ADD COLUMN IF NOT EXISTS `actual_transport_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_material_cost`,
  ADD COLUMN IF NOT EXISTS `actual_equipment_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_transport_cost`,
  ADD COLUMN IF NOT EXISTS `actual_misc_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_equipment_cost`,
  ADD COLUMN IF NOT EXISTS `actual_other_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_misc_cost`,
  ADD COLUMN IF NOT EXISTS `actual_total` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_other_cost`,
  ADD COLUMN IF NOT EXISTS `actual_total_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_total`,
  ADD COLUMN IF NOT EXISTS `selling_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `subtotal`,
  ADD COLUMN IF NOT EXISTS `estimated_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `selling_subtotal`,
  ADD COLUMN IF NOT EXISTS `actual_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `estimated_subtotal`,
  ADD COLUMN IF NOT EXISTS `total_profit` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_subtotal`,
  ADD COLUMN IF NOT EXISTS `total_margin` DECIMAL(8,2) NOT NULL DEFAULT 0 AFTER `total_profit`,
  ADD COLUMN IF NOT EXISTS `cost_variance` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `total_margin`;

ALTER TABLE `estimation_items`
  ADD COLUMN IF NOT EXISTS `item_name` VARCHAR(200) NULL AFTER `type`,
  ADD COLUMN IF NOT EXISTS `unit` VARCHAR(30) NOT NULL DEFAULT 'unit' AFTER `quantity`,
  ADD COLUMN IF NOT EXISTS `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `unit`,
  ADD COLUMN IF NOT EXISTS `estimated_unit_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `unit_price`,
  ADD COLUMN IF NOT EXISTS `actual_unit_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `estimated_unit_cost`,
  ADD COLUMN IF NOT EXISTS `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `total_cost`,
  ADD COLUMN IF NOT EXISTS `estimated_total` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `line_total`,
  ADD COLUMN IF NOT EXISTS `actual_total` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `estimated_total`,
  ADD COLUMN IF NOT EXISTS `profit` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_total`,
  ADD COLUMN IF NOT EXISTS `margin_percent` DECIMAL(8,2) NOT NULL DEFAULT 0 AFTER `profit`,
  ADD COLUMN IF NOT EXISTS `variance` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `margin_percent`;

ALTER TABLE `work_orders`
  ADD COLUMN IF NOT EXISTS `actual_labor_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_cost`,
  ADD COLUMN IF NOT EXISTS `actual_material_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_labor_cost`,
  ADD COLUMN IF NOT EXISTS `actual_transport_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_material_cost`,
  ADD COLUMN IF NOT EXISTS `actual_equipment_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_transport_cost`,
  ADD COLUMN IF NOT EXISTS `actual_misc_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_equipment_cost`,
  ADD COLUMN IF NOT EXISTS `actual_total_cost` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `actual_misc_cost`;

ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `total`,
  ADD COLUMN IF NOT EXISTS `pending_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `paid_amount`,
  ADD COLUMN IF NOT EXISTS `due_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `pending_amount`;
