-- Asset QR / barcode / scan lifecycle patch
-- Run: mysql -u user -p dbname < app/Database/SQL/patch_asset_qr_lifecycle.sql

ALTER TABLE `assets`
  ADD COLUMN IF NOT EXISTS `tag_number` VARCHAR(50) NULL AFTER `asset_code`,
  ADD COLUMN IF NOT EXISTS `asset_type` VARCHAR(100) NULL AFTER `category`,
  ADD COLUMN IF NOT EXISTS `manufacturer` VARCHAR(120) NULL AFTER `brand`,
  ADD COLUMN IF NOT EXISTS `warranty_start` DATE NULL AFTER `purchase_date`,
  ADD COLUMN IF NOT EXISTS `floor_room` VARCHAR(120) NULL AFTER `location_in_facility`,
  ADD COLUMN IF NOT EXISTS `department` VARCHAR(120) NULL AFTER `location_in_facility`,
  ADD COLUMN IF NOT EXISTS `cost_center` VARCHAR(80) NULL AFTER `department`,
  ADD COLUMN IF NOT EXISTS `assigned_to` INT UNSIGNED NULL AFTER `cost_center`,
  ADD COLUMN IF NOT EXISTS `criticality` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `qr_token` VARCHAR(64) NULL AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `barcode_value` VARCHAR(80) NULL AFTER `qr_token`,
  ADD COLUMN IF NOT EXISTS `qr_generated_at` DATETIME NULL AFTER `barcode_value`;

ALTER TABLE `assets`
  MODIFY COLUMN `status` ENUM('active','under_maintenance','faulty','retired','disposed') NOT NULL DEFAULT 'active';

CREATE TABLE IF NOT EXISTS `asset_scan_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `scanned_by` INT UNSIGNED NULL,
  `scan_source` VARCHAR(30) NOT NULL DEFAULT 'qr',
  `action_taken` VARCHAR(80) NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `gps_lat` DECIMAL(10,7) NULL,
  `gps_lng` DECIMAL(10,7) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_asset_scan_asset` (`asset_id`),
  KEY `idx_asset_scan_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `asset_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `doc_type` VARCHAR(50) NOT NULL DEFAULT 'general',
  `uploaded_by` INT UNSIGNED NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_asset_docs_asset` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `maintenance_requests`
  ADD COLUMN IF NOT EXISTS `asset_id` INT UNSIGNED NULL AFTER `unit_id`,
  ADD COLUMN IF NOT EXISTS `scan_source` VARCHAR(30) NULL AFTER `asset_id`;

UPDATE `assets` SET
  qr_token = LOWER(HEX(RANDOM_BYTES(16))),
  barcode_value = COALESCE(NULLIF(barcode_value, ''), asset_code),
  qr_generated_at = COALESCE(qr_generated_at, NOW())
WHERE qr_token IS NULL OR qr_token = '';
