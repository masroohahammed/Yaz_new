-- Fix lease_contracts / tenants / documents when id lacks AUTO_INCREMENT
-- (Duplicate entry '0' for key 'PRIMARY' on parking contract print/save)

UPDATE `lease_contracts` lc
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `lease_contracts`) t
SET lc.id = t.mx + 1
WHERE lc.id = 0
LIMIT 1;

ALTER TABLE `lease_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `tenants` tn
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `tenants`) t
SET tn.id = t.mx + 1
WHERE tn.id = 0
LIMIT 1;

ALTER TABLE `tenants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `documents` d
JOIN (SELECT COALESCE(MAX(id), 0) AS mx FROM `documents`) t
SET d.id = t.mx + 1
WHERE d.id = 0
LIMIT 1;

ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
