-- PM ERP / FM ERP — deployment checklist (Sep 2026 remediation)
-- Preferred: php spark migrate
-- Alternative: run database/patches/fm-erp-complete.sql (single consolidated patch)

-- Individual patches (if applying selectively):
--   database/patches/2026-09-02-lease-contract-signature.sql   — digital signature + signing links
--   database/patches/2026-09-04-parking-contract-photos.sql    — parking contract photos_json
--   database/patches/2026-09-04-user-landlord-link.sql         — users.landlord_id
--   database/patches/2026-09-04-user-facilities-autoincrement.sql — user update PK fix

-- Application features on cursor/fm-erp-remediation-a002 / main (948bdb8+):
--   • Digital signature: generate/regenerate tenant signing link, public sign page, signed PDF
--   • Property access: PM company-wide, REM/landlord assigned-only, multi-assign on property form
--   • KPI visibility: ui.kpi permission in Settings → Roles & Permissions
--   • Parking contract photos (optional, max 3)
--   • Sidebar company logo from session; company picker on user forms

SELECT 'Run: php spark migrate  OR  source database/patches/fm-erp-complete.sql' AS deploy_instruction;
