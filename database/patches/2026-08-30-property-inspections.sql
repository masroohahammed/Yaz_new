-- Property / asset inspection columns — PFMS production patch
-- Database: pfmsalyazwa_pfms (select it in phpMyAdmin before running)
-- Safe to re-run. No stored procedures required.
--
-- Fixes:
--   • "Property inspections require a database update"
--   • pm-inspections Unknown column 'ic.facility_id'
--
-- Alternative: php spark migrate (runs 2026-08-30-140000_PropertyInspectionColumns)

-- ---------------------------------------------------------------------------
-- A) unit_checklists — property / unit / asset inspection scope
-- ---------------------------------------------------------------------------
ALTER TABLE `unit_checklists`
  ADD COLUMN IF NOT EXISTS `facility_id` INT UNSIGNED NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `asset_id` INT UNSIGNED NULL AFTER `facility_id`,
  ADD COLUMN IF NOT EXISTS `scope_type` VARCHAR(20) NOT NULL DEFAULT 'unit' AFTER `asset_id`,
  ADD COLUMN IF NOT EXISTS `floor_label` VARCHAR(80) NULL AFTER `scope_type`;

-- Property inspections may omit unit_id
SET @uc_unit_not_null := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'unit_checklists'
     AND column_name = 'unit_id'
     AND is_nullable = 'NO'
);
SET @sql := IF(
  @uc_unit_not_null > 0,
  'ALTER TABLE `unit_checklists` MODIFY `unit_id` INT(10) UNSIGNED NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- unit_checklists.id AUTO_INCREMENT (skip if already applied)
SET @uc_has_ai := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'unit_checklists'
     AND column_name = 'id'
     AND extra LIKE '%auto_increment%'
);
SET @uc_max_id := (SELECT COALESCE(MAX(`id`), 0) FROM `unit_checklists`);
SET @uc_has_zero := (SELECT COUNT(*) FROM `unit_checklists` WHERE `id` = 0);
SET @uc_new_id := @uc_max_id + 1;
SET @sql := IF(
  @uc_has_zero > 0,
  CONCAT('UPDATE `unit_checklists` SET `id` = ', @uc_new_id, ' WHERE `id` = 0'),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @uc_next := IF(@uc_has_zero > 0, @uc_new_id + 1, @uc_max_id + 1);
SET @sql := IF(
  @uc_has_ai = 0,
  CONCAT(
    'ALTER TABLE `unit_checklists` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=',
    @uc_next
  ),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------------------------------------------------------------------------
-- B) asset_scan_logs — fixes asset QR "Duplicate entry 0 for key"
-- ---------------------------------------------------------------------------
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
  @asl_has_ai = 0 AND EXISTS (
    SELECT 1 FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'asset_scan_logs'
  ),
  CONCAT('ALTER TABLE `asset_scan_logs` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=', @asl_next),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------------------------------------------------------------------------
-- C) qr_scan_logs — same AUTO_INCREMENT repair
-- ---------------------------------------------------------------------------
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
  @qsl_has_ai = 0 AND EXISTS (
    SELECT 1 FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'qr_scan_logs'
  ),
  CONCAT('ALTER TABLE `qr_scan_logs` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=', @qsl_next),
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Verify (optional — run separately):
-- SHOW COLUMNS FROM unit_checklists LIKE 'facility_id';
-- SHOW COLUMNS FROM unit_checklists LIKE 'scope_type';
