# FM ERP — Mobile API Developer Guide

This document describes the **REST API** used by the FM ERP mobile clients (Flutter references exist in server logs; mobile source is **not** included in the `Yaz_new` backend repository).

## Base URL

Production example:

```
https://your-domain.com/public/api/v1
```

If `app.baseURL` already includes `/public/`, use:

```
https://your-domain.com/api/v1
```

Legacy (deprecated, still supported):

```
/api/legacy
```

## Health check (no token required)

```http
GET /api/v1/health
```

**Response 200:**

```json
{
  "status": true,
  "message": "API is healthy"
}
```

Use this endpoint for load balancers, mobile app startup checks, and monitoring.

## Authentication

### Login (no token required)

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "secret"
}
```

**Response 200:**

```json
{
  "status": true,
  "message": "Login successful",
  "token": "<64-char-hex-session-token>",
  "user": {
    "id": 1,
    "name": "Ahmed",
    "email": "user@example.com",
    "role": "facility_manager"
  }
}
```

Tokens are stored in `user_sessions` and expire after **24 hours**.

### Authenticated requests

Send the token on every protected call:

```http
Authorization: Bearer <token>
```

### Current user

```http
GET /api/v1/auth/me
Authorization: Bearer <token>
```

## Response conventions

| Field     | Meaning                                      |
|-----------|----------------------------------------------|
| `status`  | `true` on success, `false` on error            |
| `message` | Human-readable message                       |
| `data`    | Payload (object or array)                    |
| `count`   | Optional list length                         |

Errors use HTTP status codes: `400`, `401`, `403`, `404`, `422`, `503`.

## App telemetry (optional, no JWT)

Mobile clients can POST splash/CTA/error events:

```http
POST /api/v1/app-log
Content-Type: application/json

{
  "action": "splash_loaded",
  "status": "success",
  "message": "optional text",
  "app_version": "1.2.0",
  "platform": "android",
  "user_id": 0,
  "context": { "screen": "home" }
}
```

`status`: `info` | `success` | `error`

---

## Property Management API

Scoped to the user's company / assigned facilities.

### List properties

```http
GET /api/v1/properties
Authorization: Bearer <token>
```

### Property KPIs

```http
GET /api/v1/properties/kpis/{facilityId}
Authorization: Bearer <token>
```

Returns occupancy, contracts, overdue payments, maintenance counts, optional AI health score.

---

## Facility Management (FM) mobile API

**Roles:** `facility_manager`, `supervisor`, `technician`, `super_admin`, `qa_inspector`

### Dashboard (role-specific)

```http
GET /api/v1/fm/dashboard
Authorization: Bearer <token>
```

Returns different KPI blocks for technician vs supervisor vs facility manager.

### Work orders

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/fm/work-orders` | List (query: `status`, `q`) |
| GET | `/api/v1/fm/work-orders/{id}` | Detail + job cards + allowed actions |
| POST | `/api/v1/fm/work-orders/{id}/status` | Update status |
| POST | `/api/v1/fm/work-orders/{id}/assign` | Assign technician |
| POST | `/api/v1/fm/work-orders/{id}/job-cards` | Create job card |

**Update status body:**

```json
{
  "status": "in_progress",
  "execution_percent": 50,
  "notes": "Optional completion notes"
}
```

Allowed transitions depend on role and current state. `GET /api/v1/fm/work-orders/{id}` returns an `actions` array listing statuses the current user may set (e.g. `new`, `assigned`, `in_progress`, `completed`, `on_hold`).

**Assign body:**

```json
{ "technician_id": 42 }
```

**Create job card body:**

```json
{
  "technician_id": 42,
  "description": "Replace filter",
  "scheduled_date": "2026-09-15"
}
```

### Complaints (maintenance requests)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/fm/complaints` | List |
| GET | `/api/v1/fm/complaints/{id}` | Detail |
| POST | `/api/v1/fm/complaints/{id}/action` | verify / approve / reject |

**Action body:**

```json
{
  "action": "approve",
  "reason": "Required when action is reject"
}
```

### Job cards

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/fm/job-cards` | List |
| GET | `/api/v1/fm/job-cards/{id}` | Detail |
| POST | `/api/v1/fm/job-cards/{id}/status` | Update status |

### Technicians list

```http
GET /api/v1/fm/technicians
Authorization: Bearer <token>
```

---

## Tenant Portal mobile API

**Requires** user linked to a `tenants` record (`users.tenant_id`, `tenants.user_id`, or matching email).

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/portal/contracts` | Lease list |
| GET | `/api/v1/portal/contracts/{id}` | Lease detail + documents |
| GET | `/api/v1/portal/payments` | Payments (`q`, `status`, `page`, `per_page`) |
| GET | `/api/v1/portal/payments/{id}` | Payment detail |
| GET | `/api/v1/portal/requests` | Service requests |
| GET | `/api/v1/portal/requests/{id}` | Request detail + messages |
| POST | `/api/v1/portal/requests` | Submit request |
| POST | `/api/v1/portal/requests/{id}/messages` | Add message |
| GET | `/api/v1/portal/documents/{id}/download` | Download file |

**Create request (JSON or multipart with `photo`):**

```json
{
  "title": "AC not cooling",
  "category": "Maintenance",
  "priority": "high",
  "description": "Unit living room AC runs but no cold air.",
  "unit_id": 9536
}
```

---

## Work orders (generic API)

Company-scoped; used by PM/finance apps.

| Method | Path | Body |
|--------|------|------|
| GET | `/api/v1/work-orders` | — |
| GET | `/api/v1/work-orders/{id}` | — |
| POST | `/api/v1/work-orders` | `{ "title", "facility_id", "description?", "type?", "priority?", "estimated_cost?" }` |
| POST | `/api/v1/work-orders/{id}` | Update fields |
| POST | `/api/v1/work-orders/{id}/delete` | — |

---

## Finance API

**Roles:** `super_admin`, `finance_manager`, `finance_user`

| Method | Path | Query |
|--------|------|-------|
| GET | `/api/v1/finance/invoices` | — |
| POST | `/api/v1/finance/invoices` | `{ "facility_id", "subtotal", "issue_date?", "due_date?" }` |
| GET | `/api/v1/finance/trial-balance` | `as_of=YYYY-MM-DD` |
| GET | `/api/v1/finance/reconciliation` | `from`, `to` |

---

## Inspections API

JWT required. Tables must exist (`compliance_inspections_patch.sql`).

| Method | Path | Query params |
|--------|------|--------------|
| GET | `/api/v1/inspections/properties` | `facility_id`, `status`, `frequency` |
| GET | `/api/v1/inspections/properties/{id}` | — (+ `items` array) |
| GET | `/api/v1/inspections/units` | `facility_id`, `type`, `frequency` |
| GET | `/api/v1/inspections/units/{id}` | — |

---

## Public API (no login)

| Method | Path | Body |
|--------|------|------|
| POST | `/api/public/maintenance` | `{ "requester_name", "description", "facility_id?", "category?", "priority?" }` |
| GET | `/api/public/track/{ticket}` | Track ticket status |

---

## Legacy API (deprecated)

Prefer `/api/v1`. Legacy routes proxy to v1 controllers and send:

```
Link: </api/v1/...>; rel="successor-version"
Deprecation: true
```

| Legacy | Replacement |
|--------|-------------|
| POST `/api/legacy/auth/login` | POST `/api/v1/auth/login` |
| POST `/api/legacy/auth/register` | *(v1 has no register — use web admin)* |
| GET `/api/legacy/work-orders` | GET `/api/v1/work-orders` |
| GET `/api/legacy/finance/invoices` | GET `/api/v1/finance/invoices` |

---

## Mobile app source code

| Location | Status |
|----------|--------|
| `github.com/masroohahammed/Yaz_new` | **Backend only** (this repo) — API controllers under `app/Controllers/Api/V1/` |
| `github.com/masroohahammed/fluuter` | Empty placeholder repo (last push 2022) |
| Flutter references | Server stores mobile logs in `app_mobile_logs` (`POST /api/v1/app-log`) |

If you have a separate private Flutter repository, point the app's `baseUrl` to this server's `/api/v1` and use the auth flow above.

## Source files (backend)

| Area | Path |
|------|------|
| Routes | `app/Config/Routes.php` (search `api/v1`) |
| JWT filter | `app/Filters/JwtFilter.php` |
| Auth | `app/Controllers/Api/V1/Auth.php` |
| FM mobile | `app/Controllers/Api/V1/Fm.php` |
| Tenant portal | `app/Controllers/Api/V1/Portal.php` |
| Properties | `app/Controllers/Api/V1/Properties.php` |
| Inspections | `app/Controllers/Api/V1/Inspections.php` |
| Shared ops | `app/Services/ApiOperationsService.php` |

## Quick test (curl)

```bash
BASE="https://your-domain.com/public/api/v1"
TOKEN=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"your-password"}' \
  | jq -r .token)

curl -s "$BASE/auth/me" -H "Authorization: Bearer $TOKEN" | jq
curl -s "$BASE/fm/dashboard" -H "Authorization: Bearer $TOKEN" | jq
```
