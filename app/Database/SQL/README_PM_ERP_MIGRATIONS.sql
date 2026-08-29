-- Consolidated PM ERP upgrade patches (run after base fmstech_fm.sql)
-- Prefer: php spark migrate
-- Migrations covered:
--   2026-07-23-100000_WorkspaceArchitecture
--   2026-07-23-120000_PmErpModules
--   2026-07-23-140000_PmWorkflowExtras
--   2026-07-23-150000_PmSecondaryModules
--   2026-07-23-160000_PmOpsSecurityMedia

-- If spark is unavailable, run migrations via CLI or apply each migration class.
SELECT 'Run: php spark migrate' AS deploy_instruction;
