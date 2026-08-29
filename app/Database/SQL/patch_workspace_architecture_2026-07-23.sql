-- Workspace architecture patch (roles.workspace)
-- Run after deploying app/Database/Migrations/2026-07-23-100000_WorkspaceArchitecture.php

ALTER TABLE `roles`
  ADD COLUMN IF NOT EXISTS `workspace` ENUM('pm','fm','both','portal','collector') NULL AFTER `display_name`;

UPDATE `roles` SET `workspace` = 'both'    WHERE `name` = 'super_admin';
UPDATE `roles` SET `workspace` = 'fm'      WHERE `name` IN ('facility_manager','technician','qa_inspector','procurement_officer');
UPDATE `roles` SET `workspace` = 'pm'      WHERE `name` IN ('property_manager','real_estate_manager','salesman','finance_manager','finance_user','supervisor');
UPDATE `roles` SET `workspace` = 'portal'  WHERE `name` = 'client';
