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

This product is Facility + Property Management. There is no kitchen / POS register.
Work-order status updates and invoice / lease-payment totals are the live equivalents
of “order”, revenue, voided, and cancelled amounts.

## Public maintenance deploy (fixes whereKey SQL error)

After merging branch `cursor/fm-erp-remediation-a002`, upload or pull these files to production:

1. `app/Controllers/PublicMaintenance.php` **(new — required)**
2. `app/Services/MaintenanceScopeQuery.php`
3. `app/Views/public/maintenance.php`
4. `app/Config/Routes.php`
5. `app/Controllers/PublicEntity.php` (inspections only; maintenance moved out)

Then clear PHP opcache and restart PHP-FPM:

```bash
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'ok'; }"
sudo systemctl restart php-fpm   # or your host's PHP service name
```

**Verify deployment** (must return build `2026-08-29-5`):

```
GET https://your-domain/public/maintenance/ping
```

Expected JSON: `{"ok":true,"build":"2026-08-29-5","service":"2026-08-29-5","controller":"App\\Controllers\\PublicMaintenance"}`

Open `https://your-domain/public/maintenance?facility_id=9103` and view page source — look for:

```html
<!-- fm-maintenance-build: 2026-08-29-5 -->
```

If you still see the old error or an older build marker, production is running stale files (partial deploy or opcache).

## Production debugging

`app/Config/Database.php` sets `DBDebug = false` when `CI_ENVIRONMENT = production`.
Set that in `.env` on the live host.
