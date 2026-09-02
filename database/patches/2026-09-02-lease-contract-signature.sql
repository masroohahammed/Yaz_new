-- Lease contract digital signature columns (phpMyAdmin-safe)
ALTER TABLE `lease_contracts`
  ADD COLUMN IF NOT EXISTS `tenant_signature_path` VARCHAR(255) NULL AFTER `tenant_qid`,
  ADD COLUMN IF NOT EXISTS `signature_token` VARCHAR(64) NULL AFTER `tenant_signature_path`,
  ADD COLUMN IF NOT EXISTS `tenant_signed_at` DATETIME NULL AFTER `signature_token`;

CREATE INDEX IF NOT EXISTS `idx_lc_signature_token` ON `lease_contracts` (`signature_token`);
