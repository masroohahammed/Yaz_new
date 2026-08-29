-- FM ERP — Complete schema patches (run if migrations cannot execute)
-- Order: run after base dump import

-- 1. Estimations actual cost columns (fixes actual_labor_cost INSERT error)
ALTER TABLE `estimations`
  ADD COLUMN IF NOT EXISTS `actual_labor_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_material_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_transport_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_equipment_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_misc_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_other_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_total_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `selling_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `estimated_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0;

-- 2. Estimation line items
ALTER TABLE `estimation_items`
  ADD COLUMN IF NOT EXISTS `item_name` VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS `unit` VARCHAR(30) NOT NULL DEFAULT 'unit',
  ADD COLUMN IF NOT EXISTS `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0;

-- 3. Work orders — allow non-facility
ALTER TABLE `work_orders`
  MODIFY `facility_id` INT(10) UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS `customer_type` ENUM('facility','non_facility','walk_in','direct') NOT NULL DEFAULT 'facility',
  ADD COLUMN IF NOT EXISTS `actual_labor_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_material_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_transport_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_equipment_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_misc_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `actual_total_cost` DECIMAL(12,2) NOT NULL DEFAULT 0;

-- 4. Service customers (non-facility complaints)
CREATE TABLE IF NOT EXISTS `service_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sc_name` (`name`),
  KEY `idx_sc_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `maintenance_requests`
  ADD COLUMN IF NOT EXISTS `service_customer_id` INT(10) UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS `requester_location` VARCHAR(255) NULL;

-- 5. Invoice payment balances
ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `due_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `pending_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `is_partial` TINYINT(1) NOT NULL DEFAULT 0;

-- 6. Non-facility invoices — bill to customer
ALTER TABLE `invoices`
  MODIFY `facility_id` INT(10) UNSIGNED NULL;
ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `bill_to_name` VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS `bill_to_email` VARCHAR(120) NULL,
  ADD COLUMN IF NOT EXISTS `bill_to_phone` VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS `bill_to_address` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `service_customer_id` INT(10) UNSIGNED NULL;

-- 7. Work order ↔ contract + contract documents
ALTER TABLE `work_orders`
  ADD COLUMN IF NOT EXISTS `contract_id` INT(10) UNSIGNED NULL;
ALTER TABLE `contracts`
  ADD COLUMN IF NOT EXISTS `attachment_path` VARCHAR(255) NULL;
