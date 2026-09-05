-- Fix units / contracts when id lacks AUTO_INCREMENT (Duplicate entry '0' for key 'PRIMARY')

UPDATE `units` u
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `units`) t
SET u.id = t.mx + 1
WHERE u.id = 0
LIMIT 1;

ALTER TABLE `units`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `contracts` c
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `contracts`) t
SET c.id = t.mx + 1
WHERE c.id = 0
LIMIT 1;

ALTER TABLE `contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
