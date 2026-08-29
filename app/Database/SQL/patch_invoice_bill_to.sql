-- Non-facility invoices: allow NULL facility and store customer bill-to
ALTER TABLE `invoices`
  MODIFY `facility_id` INT(10) UNSIGNED NULL;

ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `bill_to_name` VARCHAR(200) NULL AFTER `facility_id`,
  ADD COLUMN IF NOT EXISTS `bill_to_email` VARCHAR(120) NULL,
  ADD COLUMN IF NOT EXISTS `bill_to_phone` VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS `bill_to_address` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `service_customer_id` INT(10) UNSIGNED NULL;

ALTER TABLE `work_orders`
  ADD COLUMN IF NOT EXISTS `service_customer_id` INT(10) UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS `requester_location` VARCHAR(255) NULL;
