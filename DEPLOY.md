# Deployment notes — FM ERP / PFMS remediation

## Database password rotation (required)

The previous production database password was committed in `app/Config/Database.php`
and **must be treated as compromised**.

1. Create a new MySQL user password (do not reuse the old one).
2. Put the new credentials in the server `.env` only:
   - `database.default.hostname`
   - `database.default.database`
   - `database.default.username`
   - `database.default.password`
3. Do not write the replacement password into source control.
4. Restart PHP-FPM / Apache after updating `.env`.

## Migrations

From the project root, after Composer install:

```
php spark migrate
```

Migrations in this release:

- `2026-08-29-100000` operational indexes
- `2026-08-29-100100` tenant blacklist audit columns + history table
- `2026-08-29-100200` `maintenance_request_history`
- `2026-08-29-100300` drop duplicate `maintenance_requests.email` (data copied to `requester_email` first)
- `2026-08-29-100400` reminder dismiss metadata
- `2026-08-29-100500` dashboard / invoice / notification / HR indexes
- `2026-08-29-100600` cheque `deposit_date` / `clearance_date` + expanded `expenses.category`
- `2026-08-29-100700` parking lease columns on `lease_contracts` / `units.plate_number`

SQL alternative (if you cannot run spark on the host) — **one file for all of the above**:

```
database/patches/fm-erp-complete.sql
```

The SQL patch is idempotent (safe to re-run). It creates missing columns/tables,
expands `expenses.category` while keeping existing values, and adds missing indexes only.
`database/patches/2026-08-29-fm-erp-remediation.sql` is the same statements (legacy path).

Do **not** re-run old SQL from leftover ZIP archives.

### Property inspections + asset QR scan (2026-08-30)

If phpMyAdmin shows **`PROCEDURE fm_add_column_if_missing does not exist`**, do **not** use old section 13 from an archived patch. Use the standalone file instead:

```
database/patches/2026-08-30-property-inspections.sql
```

In phpMyAdmin:

1. Select database **`pfmsalyazwa_pfms`**
2. Open **SQL** tab
3. Paste the full contents of `2026-08-30-property-inspections.sql`
4. Click **Go**

This adds `facility_id`, `asset_id`, `scope_type`, `floor_label` on `unit_checklists`, makes `unit_id` nullable for property inspections, and repairs `AUTO_INCREMENT` on `unit_checklists`, `asset_scan_logs`, and `qr_scan_logs`.

Verify:

```sql
SHOW COLUMNS FROM unit_checklists LIKE 'facility_id';
SHOW COLUMNS FROM unit_checklists LIKE 'scope_type';
```

Then upload latest app files (`Inspections.php`, etc.) and retry **pm-inspections → New Property Inspection**.

Or run: `php spark migrate` (migration `2026-08-30-140000_PropertyInspectionColumns`).

This product is Facility + Property Management. There is no kitchen / POS register.
Work-order status updates and invoice / lease-payment totals are the live equivalents
of “order”, revenue, voided, and cancelled amounts.

## Public maintenance deploy (fixes whereKey SQL error)

### Why production showed the SQL error

Production uses `app.baseURL = https://pfms.alyazwa.com/public/` (app lives in the `/public/` subfolder).
The browser URL `https://pfms.alyazwa.com/public/maintenance` maps to CI route **`maintenance`**, not `public/maintenance`.

Previously, route `maintenance` pointed to **Helpdesk** (auth + query builder). The fix registers **`maintenance` → PublicMaintenance** (no auth, raw SQL only). Staff ticket list moved to **`helpdesk`**.

### Files to upload

After merging branch `cursor/fm-erp-remediation-a002`, upload or pull:

1. `app/Controllers/PublicMaintenance.php` **(new — required)**
2. `app/Services/MaintenanceScopeQuery.php`
3. `app/Views/public/maintenance.php`
4. `app/Config/Routes.php` **(critical — wires maintenance → PublicMaintenance)**
5. `app/Controllers/PublicEntity.php`
6. `app/Controllers/MaintenanceList.php` **(new — PM Maintenance sidebar)**
7. `app/Config/FmMenu.php` and `app/Config/PmMenu.php`

Ensure production `.env` has:

```
app.baseURL = 'https://pfms.alyazwa.com/public/'
```

Then clear PHP opcache and restart PHP-FPM.

**Verify deployment** (build must be `2026-08-29-8`):

PM sidebar **Maintenance** → `/public/maintenance/list` (read-only history, no SQL error)
PM sidebar **Complaints** → `/public/helpdesk` (helpdesk workflow)

```
GET https://pfms.alyazwa.com/public/maintenance?ping=1
GET https://pfms.alyazwa.com/public/maintenance/list
GET https://pfms.alyazwa.com/public/maintenance?facility_id=9103
```

Expected ping JSON: `"build":"2026-08-29-8"`

## Production debugging

`app/Config/Database.php` sets `DBDebug = false` when `CI_ENVIRONMENT = production`.
Set that in `.env` on the live host.
