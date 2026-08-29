-- Work orders linked to contracts + contract document attachment
ALTER TABLE `work_orders`
  ADD COLUMN IF NOT EXISTS `contract_id` INT(10) UNSIGNED NULL AFTER `unit_id`;

ALTER TABLE `contracts`
  ADD COLUMN IF NOT EXISTS `attachment_path` VARCHAR(255) NULL AFTER `notes`;
