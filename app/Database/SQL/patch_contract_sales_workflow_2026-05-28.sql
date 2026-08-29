-- =============================================================================
-- FM ERP — Contract, Real Estate Manager, Sales & Non-Facility Workflow
-- Date: 2026-05-28 / 2026-05-29
-- =============================================================================
-- Fixes: "Customers module not available. Run migrations."
-- Includes: user_facilities, customers, work_orders links, complaint sales fields,
--           estimation sales fields, invoice estimation link, new roles.
--
-- Requires: MariaDB 10.3+ or MySQL 8.0.12+ (uses ADD COLUMN IF NOT EXISTS).
-- For older MySQL: run each ALTER manually and ignore "Duplicate column" errors.
--
-- Usage:
--   mysql -u YOUR_USER -p YOUR_DATABASE < patch_contract_sales_workflow_2026-05-28.sql
--
-- Or phpMyAdmin: Import this file.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. CUSTOMERS (required for non-facility complaints + modal "+")
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customers_mobile` (`mobile`),
  KEY `idx_customers_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 2. USER ↔ FACILITY ASSIGNMENT (Real Estate / Property managers)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_facilities` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `facility_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_facility` (`user_id`,`facility_id`),
  KEY `idx_user_facilities_facility` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- 3. NEW ROLES
-- -----------------------------------------------------------------------------
INSERT INTO `roles` (`name`, `display_name`, `created_at`)
SELECT 'real_estate_manager', 'Real Estate Manager', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `name` = 'real_estate_manager');

INSERT INTO `roles` (`name`, `display_name`, `created_at`)
SELECT 'salesman', 'Salesman', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `name` = 'salesman');

-- Optional: Property Manager role (if missing from older installs)
INSERT INTO `roles` (`name`, `display_name`, `created_at`)
SELECT 'property_manager', 'Property Manager', NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `name` = 'property_manager');

-- -----------------------------------------------------------------------------
-- 4. WORK ORDERS — contract & complaint links (+ estimation_id if missing)
-- -----------------------------------------------------------------------------
ALTER TABLE `work_orders`
  ADD COLUMN IF NOT EXISTS `estimation_id` int(10) UNSIGNED DEFAULT NULL AFTER `invoice_id`,
  ADD COLUMN IF NOT EXISTS `contract_id` int(10) UNSIGNED DEFAULT NULL AFTER `estimation_id`,
  ADD COLUMN IF NOT EXISTS `maintenance_request_id` int(10) UNSIGNED DEFAULT NULL AFTER `contract_id`,
  ADD COLUMN IF NOT EXISTS `selling_total` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `estimated_cost`,
  ADD COLUMN IF NOT EXISTS `execution_percent` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `actual_cost`;

-- Indexes (skip if you get "Duplicate key name" on re-run)
-- ALTER TABLE `work_orders` ADD KEY `idx_wo_contract` (`contract_id`);
-- ALTER TABLE `work_orders` ADD KEY `idx_wo_estimation` (`estimation_id`);
-- ALTER TABLE `work_orders` ADD KEY `idx_wo_maintenance_request` (`maintenance_request_id`);

-- -----------------------------------------------------------------------------
-- 5. MAINTENANCE REQUESTS (COMPLAINTS) — facility vs non-facility
-- -----------------------------------------------------------------------------
ALTER TABLE `maintenance_requests`
  ADD COLUMN IF NOT EXISTS `work_type` enum('facility','non_facility') NOT NULL DEFAULT 'facility' AFTER `company_id`,
  ADD COLUMN IF NOT EXISTS `customer_id` int(10) UNSIGNED DEFAULT NULL AFTER `work_type`,
  ADD COLUMN IF NOT EXISTS `salesman_id` int(10) UNSIGNED DEFAULT NULL AFTER `customer_id`,
  ADD COLUMN IF NOT EXISTS `sales_rep_name` varchar(150) DEFAULT NULL AFTER `salesman_id`,
  ADD COLUMN IF NOT EXISTS `forwarded_to_fm` tinyint(1) NOT NULL DEFAULT 0 AFTER `approved_at`,
  ADD COLUMN IF NOT EXISTS `forwarded_by` int(10) UNSIGNED DEFAULT NULL AFTER `forwarded_to_fm`,
  ADD COLUMN IF NOT EXISTS `forwarded_at` datetime DEFAULT NULL AFTER `forwarded_by`;

-- ALTER TABLE `maintenance_requests` ADD KEY `idx_mr_work_type` (`work_type`);
-- ALTER TABLE `maintenance_requests` ADD KEY `idx_mr_customer` (`customer_id`);
-- ALTER TABLE `maintenance_requests` ADD KEY `idx_mr_salesman` (`salesman_id`);

-- Backfill: existing rows without facility → non_facility
UPDATE `maintenance_requests`
SET `work_type` = 'non_facility'
WHERE `facility_id` IS NULL
  AND (`work_type` IS NULL OR `work_type` = 'facility');

-- -----------------------------------------------------------------------------
-- 6. ESTIMATIONS — salesman & complaint link; allow null facility (non-facility)
-- -----------------------------------------------------------------------------
ALTER TABLE `estimations`
  ADD COLUMN IF NOT EXISTS `salesman_id` int(10) UNSIGNED DEFAULT NULL AFTER `created_by`,
  ADD COLUMN IF NOT EXISTS `customer_id` int(10) UNSIGNED DEFAULT NULL AFTER `salesman_id`,
  ADD COLUMN IF NOT EXISTS `maintenance_request_id` int(10) UNSIGNED DEFAULT NULL AFTER `customer_id`;

ALTER TABLE `estimations`
  MODIFY COLUMN `facility_id` int(10) UNSIGNED NULL DEFAULT NULL;

-- ALTER TABLE `estimations` ADD KEY `idx_est_salesman` (`salesman_id`);
-- ALTER TABLE `estimations` ADD KEY `idx_est_complaint` (`maintenance_request_id`);

-- -----------------------------------------------------------------------------
-- 7. INVOICES — link to estimation; extend invoice_type for advance/partial/final
-- -----------------------------------------------------------------------------
ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `estimation_id` int(10) UNSIGNED DEFAULT NULL AFTER `work_order_id`;

-- ALTER TABLE `invoices` ADD KEY `idx_invoices_estimation` (`estimation_id`);

-- Extend enum (merge existing values used in the app)
ALTER TABLE `invoices`
  MODIFY COLUMN `invoice_type` enum(
    'contract','work_order','adhoc',
    'advance','partial','final',
    'monthly','quarterly','annual','wo_based'
  ) NOT NULL DEFAULT 'adhoc';

-- -----------------------------------------------------------------------------
-- 8. FINANCIAL WORKFLOW COLUMNS (safe if patch_financial_workflow.sql not run yet)
-- -----------------------------------------------------------------------------
ALTER TABLE `estimations`
  ADD COLUMN IF NOT EXISTS `actual_labor_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `other_cost`,
  ADD COLUMN IF NOT EXISTS `actual_material_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_labor_cost`,
  ADD COLUMN IF NOT EXISTS `actual_transport_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_material_cost`,
  ADD COLUMN IF NOT EXISTS `actual_equipment_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_transport_cost`,
  ADD COLUMN IF NOT EXISTS `actual_misc_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_equipment_cost`,
  ADD COLUMN IF NOT EXISTS `actual_other_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_misc_cost`,
  ADD COLUMN IF NOT EXISTS `actual_total` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_other_cost`,
  ADD COLUMN IF NOT EXISTS `actual_total_cost` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_total`,
  ADD COLUMN IF NOT EXISTS `selling_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`,
  ADD COLUMN IF NOT EXISTS `estimated_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `selling_subtotal`,
  ADD COLUMN IF NOT EXISTS `actual_subtotal` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `estimated_subtotal`,
  ADD COLUMN IF NOT EXISTS `total_profit` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `actual_subtotal`,
  ADD COLUMN IF NOT EXISTS `total_margin` decimal(8,2) NOT NULL DEFAULT 0.00 AFTER `total_profit`,
  ADD COLUMN IF NOT EXISTS `cost_variance` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `total_margin`;

ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `total`,
  ADD COLUMN IF NOT EXISTS `pending_amount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`,
  ADD COLUMN IF NOT EXISTS `due_amount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `pending_amount`;

-- -----------------------------------------------------------------------------
-- 9. CI4 MIGRATIONS TABLE (optional — mark as applied so spark migrate skips)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-05-28-100000', 'App\\Database\\Migrations\\ContractReManager', 'default', 'App', UNIX_TIMESTAMP(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-05-28-100000');

INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-05-29-100000', 'App\\Database\\Migrations\\SalesNonFacilityWorkflow', 'default', 'App', UNIX_TIMESTAMP(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-05-29-100000');

INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-05-30-100000', 'App\\Database\\Migrations\\HelpdeskForwardToFm', 'default', 'App', UNIX_TIMESTAMP(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-05-30-100000');

CREATE TABLE IF NOT EXISTS `invoice_edit_logs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'update',
  `summary` text DEFAULT NULL,
  `changes_json` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_edit_logs_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-05-31-100000', 'App\\Database\\Migrations\\InvoiceEditLogs', 'default', 'App', UNIX_TIMESTAMP(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-05-31-100000');

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- VERIFY (run manually after import):
--   SHOW TABLES LIKE 'customers';
--   SHOW COLUMNS FROM maintenance_requests LIKE 'work_type';
--   SELECT name, display_name FROM roles WHERE name IN ('salesman','real_estate_manager');
-- =============================================================================
