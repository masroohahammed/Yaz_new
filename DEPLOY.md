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

SQL alternative (if you cannot run spark on the host):

```
database/patches/2026-08-29-fm-erp-remediation.sql
```

The SQL patch is idempotent (safe to re-run). It creates missing columns/tables and
adds missing indexes only.

Do **not** re-run old SQL from leftover ZIP archives.

This product is Facility + Property Management. There is no kitchen / POS register.
Work-order status updates and invoice / lease-payment totals are the live equivalents
of “order”, revenue, voided, and cancelled amounts.

## Production debugging

`app/Config/Database.php` sets `DBDebug = false` when `CI_ENVIRONMENT = production`.
Set that in `.env` on the live host.
