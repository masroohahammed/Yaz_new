-- =============================================================================
-- Lease contract digital signature columns (run in phpMyAdmin on production DB)
-- Fixes: "Run database migration for lease contract signatures first."
-- Safe to run multiple times on MariaDB 10.3+ / MySQL 8.0+
-- =============================================================================

ALTER TABLE `lease_contracts`
  ADD COLUMN IF NOT EXISTS `tenant_signature_path` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `signature_token` VARCHAR(64) NULL,
  ADD COLUMN IF NOT EXISTS `tenant_signed_at` DATETIME NULL;

CREATE INDEX IF NOT EXISTS `idx_lc_signature_token` ON `lease_contracts` (`signature_token`);

-- Verify:
-- SHOW COLUMNS FROM lease_contracts LIKE 'tenant_signature_path';
-- SHOW COLUMNS FROM lease_contracts LIKE 'signature_token';
-- SHOW COLUMNS FROM lease_contracts LIKE 'tenant_signed_at';
