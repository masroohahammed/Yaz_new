-- FM ERP / PFMS remediation patch
-- Apply on an existing MySQL / MariaDB database AFTER taking a backup.
-- Preferred: php spark migrate
-- Safe to re-run. Compatible with MariaDB 10.4+ and MySQL 8.0.

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

-- 5) Idempotent index helper (drops itself at the end)
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
CALL fm_add_index_if_missing('notifications', 'idx_notif_user_read', '`user_id`, `is_read`');
CALL fm_add_index_if_missing('employee_profiles', 'idx_ep_company', '`company_id`');
CALL fm_add_index_if_missing('employee_profiles', 'idx_ep_user', '`user_id`');
CALL fm_add_index_if_missing('work_orders', 'idx_wo_assigned', '`assigned_to`');
CALL fm_add_index_if_missing('work_orders', 'idx_wo_supervisor', '`supervisor_id`');
CALL fm_add_index_if_missing('lease_payments', 'idx_lpay_unit_status', '`unit_id`, `status`');
CALL fm_add_index_if_missing('lease_payments', 'idx_lpay_facility_status', '`facility_id`, `status`');
CALL fm_add_index_if_missing('commission_rules', 'idx_cr_company', '`company_id`');

DROP PROCEDURE IF EXISTS fm_add_index_if_missing;

-- After applying: ROTATE THE DATABASE PASSWORD. Previous password was in source.
