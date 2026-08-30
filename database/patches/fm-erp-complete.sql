-- FM ERP / PFMS — single complete patch
-- Apply on an existing MySQL / MariaDB database AFTER taking a backup.
-- Preferred: php spark migrate
-- Safe to re-run. Compatible with MariaDB 10.4+ and MySQL 8.0.
-- Covers remediation 100000–100600 plus landlord-report cheque dates and expense categories.
-- Do not re-run old SQL from leftover ZIP archives.

-- 1) Tenant blacklist audit
ALTER TABLE `tenants`
  ADD COLUMN IF NOT EXISTS `blacklist_reason` TEXT NULL,
  ADD COLUMN IF NOT EXISTS `blacklisted_at` DATETIME NULL,
  ADD COLUMN IF NOT EXISTS `blacklisted_by` INT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS `unblacklist_reason` TEXT NULL,
  ADD COLUMN IF NOT EXISTS `unblacklisted_at` DATETIME NULL,
  ADD COLUMN IF NOT EXISTS `unblacklisted_by` INT UNSIGNED NULL;

CREATE TABLE IF NOT EXISTS `tenant_blacklist_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED NULL,
  `action` VARCHAR(20) NOT NULL,
  `reason` TEXT NULL,
  `performed_by` INT UNSIGNED NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Maintenance request history
CREATE TABLE IF NOT EXISTS `maintenance_request_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `maintenance_request_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `previous_status` VARCHAR(50) NULL,
  `new_status` VARCHAR(50) NULL,
  `performed_by` INT UNSIGNED NULL,
  `note` TEXT NULL,
  `metadata` JSON NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_request_id` (`maintenance_request_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Backfill duplicate helpdesk email, then drop only if the column still exists
SET @has_email := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'maintenance_requests'
     AND column_name = 'email'
);
SET @sql := IF(
  @has_email > 0,
  'UPDATE `maintenance_requests` SET `requester_email` = `email` WHERE (`requester_email` IS NULL OR `requester_email` = '''') AND `email` IS NOT NULL AND `email` <> ''''',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF(
  @has_email > 0,
  'ALTER TABLE `maintenance_requests` DROP COLUMN `email`',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 4) Reminder dismiss metadata
ALTER TABLE `reminders`
  ADD COLUMN IF NOT EXISTS `dismissed_by` INT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS `dismissed_at` DATETIME NULL;

-- 5) Cheque deposit / clearance dates (status actions already exist; columns did not)
ALTER TABLE `cheques`
  ADD COLUMN IF NOT EXISTS `deposit_date` DATE NULL,
  ADD COLUMN IF NOT EXISTS `clearance_date` DATE NULL;

-- 6) Expand expenses.category keeping every existing production value
SET @need_exp_enum := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'expenses'
     AND column_name = 'category'
     AND COLUMN_TYPE NOT LIKE '%insurance%'
);
SET @sql := IF(
  @need_exp_enum > 0,
  'ALTER TABLE `expenses` MODIFY COLUMN `category` ENUM(''labor'',''spare_parts'',''vendor'',''utility'',''administrative'',''emergency'',''other'',''insurance'',''municipality'',''cleaning'',''security'',''management_fee'',''maintenance'',''utilities'',''repairs'',''staff'',''admin'',''tax'',''renovation'') NOT NULL DEFAULT ''other''',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 7) Idempotent index helper (drops itself at the end)
DROP PROCEDURE IF EXISTS fm_add_index_if_missing;
DELIMITER //
CREATE PROCEDURE fm_add_index_if_missing(IN p_table VARCHAR(64), IN p_index VARCHAR(64), IN p_cols VARCHAR(255))
BEGIN
  IF (SELECT COUNT(*) FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = p_table) > 0
     AND (SELECT COUNT(*) FROM information_schema.statistics
        WHERE table_schema = DATABASE() AND table_name = p_table AND index_name = p_index) = 0 THEN
    SET @idx_sql = CONCAT('ALTER TABLE `', p_table, '` ADD INDEX `', p_index, '` (', p_cols, ')');
    PREPARE idx_stmt FROM @idx_sql;
    EXECUTE idx_stmt;
    DEALLOCATE PREPARE idx_stmt;
  END IF;
END //
DELIMITER ;

CALL fm_add_index_if_missing('lease_contracts', 'idx_lc_company', '`company_id`');
CALL fm_add_index_if_missing('lease_contracts', 'idx_lc_template', '`template_id`');
CALL fm_add_index_if_missing('lease_contracts', 'idx_lc_parent', '`parent_contract_id`');
CALL fm_add_index_if_missing('lease_contracts', 'idx_lc_status', '`status`');
CALL fm_add_index_if_missing('tenants', 'idx_tenants_company', '`company_id`');
CALL fm_add_index_if_missing('tenants', 'idx_tenants_current_unit', '`current_unit_id`');
CALL fm_add_index_if_missing('facilities', 'idx_fac_landlord', '`landlord_id`');
CALL fm_add_index_if_missing('facilities', 'idx_fac_caretaker', '`caretaker_id`');
CALL fm_add_index_if_missing('landlords', 'idx_landlords_company', '`company_id`');
CALL fm_add_index_if_missing('users', 'idx_users_company', '`company_id`');
CALL fm_add_index_if_missing('users', 'idx_users_tenant', '`tenant_id`');

CALL fm_add_index_if_missing('invoices', 'idx_inv_status_due', '`status`, `due_date`');
CALL fm_add_index_if_missing('invoices', 'idx_inv_facility_status', '`facility_id`, `status`');
CALL fm_add_index_if_missing('invoices', 'idx_inv_issue', '`issue_date`');
CALL fm_add_index_if_missing('invoices', 'idx_inv_company', '`company_id`');
CALL fm_add_index_if_missing('expenses', 'idx_exp_status_date', '`status`, `expense_date`');
CALL fm_add_index_if_missing('expenses', 'idx_exp_facility', '`facility_id`');
CALL fm_add_index_if_missing('expenses', 'idx_exp_category', '`category`');
CALL fm_add_index_if_missing('notifications', 'idx_notif_user_read', '`user_id`, `is_read`');
CALL fm_add_index_if_missing('employee_profiles', 'idx_ep_company', '`company_id`');
CALL fm_add_index_if_missing('employee_profiles', 'idx_ep_user', '`user_id`');
CALL fm_add_index_if_missing('work_orders', 'idx_wo_assigned', '`assigned_to`');
CALL fm_add_index_if_missing('work_orders', 'idx_wo_supervisor', '`supervisor_id`');
CALL fm_add_index_if_missing('lease_payments', 'idx_lpay_unit_status', '`unit_id`, `status`');
CALL fm_add_index_if_missing('lease_payments', 'idx_lpay_facility_status', '`facility_id`, `status`');
CALL fm_add_index_if_missing('commission_rules', 'idx_cr_company', '`company_id`');
CALL fm_add_index_if_missing('cheques', 'idx_chq_facility_date', '`facility_id`, `cheque_date`');
CALL fm_add_index_if_missing('cheques', 'idx_chq_status', '`status`');

-- 8) Parking lease contract columns (lease print / parking agreement)
ALTER TABLE `lease_contracts`
  ADD COLUMN IF NOT EXISTS `contract_kind` VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS `tenant_qid` VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS `plate_number` VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS `vehicle_type` VARCHAR(60) NULL,
  ADD COLUMN IF NOT EXISTS `vehicle_description` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `title_deed_no` VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS `zone_no` VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS `street_no` VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS `building_no` VARCHAR(20) NULL;

ALTER TABLE `units`
  ADD COLUMN IF NOT EXISTS `plate_number` VARCHAR(30) NULL;

-- 9) Landlord short code (reports / index)
ALTER TABLE `landlords`
  ADD COLUMN IF NOT EXISTS `short_code` VARCHAR(20) NULL AFTER `full_name`;

UPDATE `landlords`
SET `short_code` = UPPER(TRIM(SUBSTRING(`notes`, LOCATE('Code:', `notes`) + 5, 20)))
WHERE (`short_code` IS NULL OR `short_code` = '')
  AND `notes` LIKE '%Code:%'
  AND LOCATE('Code:', `notes`) > 0;

DROP PROCEDURE IF EXISTS fm_add_index_if_missing;

-- 10) Property / unit QR tokens + unified scan logs
ALTER TABLE `facilities`
  ADD COLUMN IF NOT EXISTS `qr_token` VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS `qr_generated_at` DATETIME NULL;

ALTER TABLE `units`
  ADD COLUMN IF NOT EXISTS `qr_token` VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS `qr_generated_at` DATETIME NULL;

CREATE TABLE IF NOT EXISTS `qr_scan_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(20) NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `scanned_by` INT UNSIGNED NULL,
  `scan_source` VARCHAR(30) NOT NULL DEFAULT 'qr',
  `action_taken` VARCHAR(40) NOT NULL DEFAULT 'view',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_qr_scan_entity` (`entity_type`, `entity_id`),
  KEY `idx_qr_scan_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE `facilities`
SET `qr_token` = LOWER(HEX(RANDOM_BYTES(16))), `qr_generated_at` = COALESCE(`qr_generated_at`, NOW())
WHERE `qr_token` IS NULL OR `qr_token` = '';

UPDATE `units`
SET `qr_token` = LOWER(HEX(RANDOM_BYTES(16))), `qr_generated_at` = COALESCE(`qr_generated_at`, NOW())
WHERE `qr_token` IS NULL OR `qr_token` = '';

-- 11) unit_checklists.id AUTO_INCREMENT (fixes pm-inspections/store duplicate PK '0')
SET @uc_has_ai := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'unit_checklists'
     AND column_name = 'id'
     AND extra LIKE '%auto_increment%'
);
SET @uc_max_id := (
  SELECT COALESCE(MAX(`id`), 0) FROM `unit_checklists`
);
SET @uc_has_zero := (
  SELECT COUNT(*) FROM `unit_checklists` WHERE `id` = 0
);
SET @uc_new_id := @uc_max_id + 1;
SET @sql := IF(
  @uc_has_zero > 0,
  CONCAT('UPDATE `unit_checklists` SET `id` = ', @uc_new_id, ' WHERE `id` = 0'),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @uc_next := IF(@uc_has_zero > 0, @uc_new_id + 1, @uc_max_id + 1);
SET @sql := IF(
  @uc_has_ai = 0 AND EXISTS (
    SELECT 1 FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'unit_checklists'
  ),
  CONCAT(
    'ALTER TABLE `unit_checklists` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=',
    @uc_next
  ),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 12) asset_scan_logs / qr_scan_logs AUTO_INCREMENT (fixes asset QR scan duplicate PK '0')
-- asset_scan_logs
SET @asl_has_ai := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE() AND table_name = 'asset_scan_logs'
     AND column_name = 'id' AND extra LIKE '%auto_increment%'
);
SET @asl_max := (SELECT COALESCE(MAX(`id`), 0) FROM `asset_scan_logs`);
SET @asl_zero := (SELECT COUNT(*) FROM `asset_scan_logs` WHERE `id` = 0);
SET @asl_new := @asl_max + 1;
SET @sql := IF(@asl_zero > 0, CONCAT('UPDATE `asset_scan_logs` SET `id` = ', @asl_new, ' WHERE `id` = 0'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @asl_next := IF(@asl_zero > 0, @asl_new + 1, @asl_max + 1);
SET @sql := IF(
  @asl_has_ai = 0 AND EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'asset_scan_logs'),
  CONCAT('ALTER TABLE `asset_scan_logs` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=', @asl_next),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- qr_scan_logs
SET @qsl_has_ai := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE() AND table_name = 'qr_scan_logs'
     AND column_name = 'id' AND extra LIKE '%auto_increment%'
);
SET @qsl_max := (SELECT COALESCE(MAX(`id`), 0) FROM `qr_scan_logs`);
SET @qsl_zero := (SELECT COUNT(*) FROM `qr_scan_logs` WHERE `id` = 0);
SET @qsl_new := @qsl_max + 1;
SET @sql := IF(@qsl_zero > 0, CONCAT('UPDATE `qr_scan_logs` SET `id` = ', @qsl_new, ' WHERE `id` = 0'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @qsl_next := IF(@qsl_zero > 0, @qsl_new + 1, @qsl_max + 1);
SET @sql := IF(
  @qsl_has_ai = 0 AND EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'qr_scan_logs'),
  CONCAT('ALTER TABLE `qr_scan_logs` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=', @qsl_next),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 13) Property / asset inspection columns on unit_checklists
CALL fm_add_column_if_missing('unit_checklists', 'facility_id', '`facility_id` INT UNSIGNED NULL AFTER `id`');
CALL fm_add_column_if_missing('unit_checklists', 'asset_id', '`asset_id` INT UNSIGNED NULL AFTER `facility_id`');
CALL fm_add_column_if_missing('unit_checklists', 'scope_type', '`scope_type` VARCHAR(20) NOT NULL DEFAULT ''unit'' AFTER `asset_id`');
CALL fm_add_column_if_missing('unit_checklists', 'floor_label', '`floor_label` VARCHAR(80) NULL AFTER `scope_type`');

-- After applying: ROTATE THE DATABASE PASSWORD. Previous password was in source.
-- Landlord reports do not add tables. Occupancy trend is lease-overlap, not a snapshot table.
