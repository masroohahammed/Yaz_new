-- Fix user_facilities / user_property_assignments when id lacks AUTO_INCREMENT (Duplicate entry '0' for key 'PRIMARY')

-- user_facilities: renumber id=0 rows, then enable AUTO_INCREMENT
UPDATE `user_facilities` uf
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `user_facilities`) t
SET uf.id = t.mx + uf.user_id
WHERE uf.id = 0;

ALTER TABLE `user_facilities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- user_property_assignments: extend role types + AUTO_INCREMENT
ALTER TABLE `user_property_assignments`
  MODIFY `role_type` enum('manager','property_manager','real_estate_manager','landlord','caretaker','other') NOT NULL DEFAULT 'manager';

UPDATE `user_property_assignments` upa
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `user_property_assignments`) t
SET upa.id = t.mx + upa.user_id
WHERE upa.id = 0;

ALTER TABLE `user_property_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
