-- Link landlord portal users to landlord records; enables property ownership scoping.
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `landlord_id` INT UNSIGNED NULL AFTER `tenant_id`;

CREATE INDEX IF NOT EXISTS `idx_users_landlord` ON `users` (`landlord_id`);
