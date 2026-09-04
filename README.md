# Yaz_new — FM ERP

## Sep 4 remediation restore

**Branch:** `cursor/fm-erp-remediation-a002` (or `main` — kept in sync)

### Fresh deploy (replace stale server files)

```bash
git clone https://github.com/masroohahammed/Yaz_new.git
cd Yaz_new
git checkout cursor/fm-erp-remediation-a002
bash scripts/verify-remediation.sh
```

### Database (required once)

Run in phpMyAdmin: `database/patches/fm-erp-complete.sql`

### Verify after deploy

Open: `https://your-domain/public/remediation-check`

Should return `"ok": true` with all files listed as `"present": true`.

### Included features

- Digital signature & tenant signing link generation
- Property access scoping (PM company-wide, REM assigned-only)
- KPI visibility permission (`ui.kpi` in Settings → Roles)
- Parking contract photos, sidebar branding, user update PK fix
