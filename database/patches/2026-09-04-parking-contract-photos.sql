-- Optional parking contract photos (JSON array of upload paths, max 3)
ALTER TABLE `lease_contracts`
  ADD COLUMN IF NOT EXISTS `photos_json` TEXT NULL AFTER `building_no`;
