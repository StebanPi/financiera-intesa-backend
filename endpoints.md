# API Endpoints Documentation

Base URL: `/api/v1`

## Authentication

| Method | Endpoint       | Description                  | Auth Required |
| ------ | -------------- | ---------------------------- | ------------- |
| POST   | `/auth/login`  | Login user (email, password) | No            |
| POST   | `/auth/logout` | Logout user                  | Yes           |
| GET    | `/auth/me`     | Get current user details     | Yes           |

## Users

**Permissions Required:** `users.manage`

| Method    | Endpoint                  | Description            |
| --------- | ------------------------- | ---------------------- |
| GET       | `/admin/users`            | List users (paginated) |
| POST      | `/admin/users`            | Create user            |
| GET       | `/admin/users/{id}`       | Get user details       |
| PUT/PATCH | `/admin/users/{id}`       | Update user            |
| DELETE    | `/admin/users/{id}`       | Delete user            |
| POST      | `/admin/users/{id}/roles` | Sync user roles        |

## Roles & Permissions

**Permissions Required:** `roles.manage`

| Method    | Endpoint                        | Description           |
| --------- | ------------------------------- | --------------------- |
| GET       | `/admin/roles`                  | List roles            |
| POST      | `/admin/roles`                  | Create role           |
| GET       | `/admin/roles/{id}`             | Get role details      |
| PUT/PATCH | `/admin/roles/{id}`             | Update role           |
| DELETE    | `/admin/roles/{id}`             | Delete role           |
| GET       | `/admin/permissions`            | List all permissions  |
| POST      | `/admin/roles/{id}/permissions` | Sync role permissions |

## Enrollments (Matrículas)

**Permissions Required:** `access.core` (typically)

| Method    | Endpoint                        | Description                                                                      |
| --------- | ------------------------------- | -------------------------------------------------------------------------------- |
| GET       | `/matriculas`                   | List enrollments (filters: search, programa, horario, etc.)                      |
| POST      | `/matriculas`                   | Create enrollment                                                                |
| GET       | `/matriculas/{cod_alumno}`      | Get enrollment details                                                           |
| PUT/PATCH | `/matriculas/{cod_alumno}`      | Update enrollment                                                                |
| DELETE    | `/matriculas/{cod_alumno}`      | Delete enrollment (query param: `confirmar_cascada=1`)                           |
| GET       | `/matriculas/form-data`         | **[NEW]** Get form options (types, sedes, statuses usually needed for dropdowns) |
| POST      | `/matriculas/{cod_alumno}/foto` | Upload student photo                                                             |
| GET       | `/matriculas/{cod_alumno}/foto` | Get student photo (public)                                                       |
| GET       | `/matriculas/{cod_alumno}/pdf`  | Stream enrollment PDF                                                            |

## Catalogs (Settings)

**Permissions Required:** `settings.manage`

| Method    | Endpoint                    | Description                                            |
| --------- | --------------------------- | ------------------------------------------------------ |
| GET       | `/settings/{resource}`      | List catalog items (programs, schedules, groups, etc.) |
| POST      | `/settings/{resource}`      | Create catalog item                                    |
| GET       | `/settings/{resource}/{id}` | Get catalog item                                       |
| PUT/PATCH | `/settings/{resource}/{id}` | Update catalog item                                    |
| DELETE    | `/settings/{resource}/{id}` | Delete catalog item                                    |

**Resources:** `programs`, `schedules`, `groups`, `teachers`, `modules`, `conceptos`, `elaborados`, `habers`, `debes`, `otros-conceptos`.

## Institution & Configuration

**Permissions Required:** `settings.manage`

| Method | Endpoint                | Description                                |
| ------ | ----------------------- | ------------------------------------------ |
| GET    | `/settings/institution` | Get institution settings (name, NIT, etc.) |
| PUT    | `/settings/institution` | Update institution settings                |

## Financial Settings

**Permissions Required:** `access.core`

### Costs (Centros de Costo)

| Method    | Endpoint      | Description      |
| --------- | ------------- | ---------------- |
| GET       | `/costs`      | List costs       |
| POST      | `/costs`      | Create cost      |
| GET       | `/costs/{id}` | Get cost details |
| PUT/PATCH | `/costs/{id}` | Update cost      |
| DELETE    | `/costs/{id}` | Delete cost      |

### Consecutives

| Method    | Endpoint             | Description             |
| --------- | -------------------- | ----------------------- |
| GET       | `/consecutives`      | List consecutives       |
| POST      | `/consecutives`      | Create consecutive      |
| GET       | `/consecutives/{id}` | Get consecutive details |
| PUT/PATCH | `/consecutives/{id}` | Update consecutive      |

## Accounting (Contabilidad)

**Permissions Required:** `access.core`

### Reports & Summaries

| Method | Endpoint                      | Description                  |
| ------ | ----------------------------- | ---------------------------- |
| GET    | `/accounting`                 | General accounting summary   |
| GET    | `/accounting/abonos`          | List abonos (entries) report |
| GET    | `/accounting/otros-ingresos`  | List other entries report    |
| GET    | `/accounting/total-ingresos`  | Total income report          |
| GET    | `/accounting/egresos`         | Expenses report              |
| GET    | `/accounting/arqueo-diario`   | Daily cash count             |
| GET    | `/accounting/informe-semanal` | Weekly report                |
| GET    | `/accounting/informe-mensual` | Monthly report               |

### Report Downloads (Excel/PDF)

All accounting endpoints above have a corresponding `/download` endpoint that exports the data.

- `/accounting/abonos/download`
- `/accounting/otros-ingresos/download`
- `/accounting/total-ingresos/download`
- `/accounting/egresos/download`
- `/accounting/arqueo-diario/download`
- `/accounting/informe-semanal/download`
- `/accounting/informe-mensual/download`

## Transactions

**Permissions Required:** `access.core`

### Entries (Abonos)

| Method | Endpoint        | Description       |
| ------ | --------------- | ----------------- |
| GET    | `/entries`      | List entries      |
| POST   | `/entries`      | Create entry      |
| GET    | `/entries/{id}` | Get entry details |
| DELETE | `/entries/{id}` | Delete entry      |

### Other Entries (Otros Ingresos)

| Method | Endpoint              | Description             |
| ------ | --------------------- | ----------------------- |
| GET    | `/other-entries`      | List other entries      |
| POST   | `/other-entries`      | Create other entry      |
| GET    | `/other-entries/{id}` | Get other entry details |
| DELETE | `/other-entries/{id}` | Delete other entry      |

### Discharges (Egresos) & Providers

| Method    | Endpoint                   | Description              |
| --------- | -------------------------- | ------------------------ |
| GET       | `/providers`               | List providers           |
| POST      | `/providers`               | Create provider          |
| PUT/PATCH | `/providers/{id}`          | Update provider          |
| DELETE    | `/providers/{id}`          | Delete provider          |
| GET       | `/discharge-concepts`      | List discharge concepts  |
| POST      | `/discharge-concepts`      | Create discharge concept |
| PUT/PATCH | `/discharge-concepts/{id}` | Update discharge concept |
| DELETE    | `/discharge-concepts/{id}` | Delete discharge concept |
| GET       | `/discharges`              | List discharges          |
| POST      | `/discharges`              | Create discharge         |
| GET       | `/discharges/{id}`         | Get discharge details    |
| DELETE    | `/discharges/{id}`         | Delete discharge         |

### Third Parties (Terceros)

| Method    | Endpoint                 | Description                 |
| --------- | ------------------------ | --------------------------- |
| GET       | `/third-entries`         | List third party entries    |
| POST      | `/third-entries`         | Create third party entry    |
| PUT/PATCH | `/third-entries/{id}`    | Update third party entry    |
| DELETE    | `/third-entries/{id}`    | Delete third party entry    |
| GET       | `/third-activities`      | List third party activities |
| POST      | `/third-activities`      | Create third party activity |
| PUT/PATCH | `/third-activities/{id}` | Update third party activity |
| DELETE    | `/third-activities/{id}` | Delete third party activity |
| GET       | `/third-receipts`        | List third party receipts   |

### Financial Receipts (PDFs)

| Method | Endpoint                              | Description             |
| ------ | ------------------------------------- | ----------------------- |
| GET    | `/financial-receipts/{type}/{id}`     | Get receipt data (JSON) |
| GET    | `/financial-receipts/{type}/{id}/pdf` | Stream receipt PDF      |

**Types:** `entry`, `other-entry`, `egreso`, `third`.

## Purse (Cartera)

**Permissions Required:** `access.core`

| Method | Endpoint               | Description                     |
| ------ | ---------------------- | ------------------------------- |
| GET    | `/purses`              | List student debts/purses       |
| GET    | `/purses/totales`      | Get total debts summary         |
| GET    | `/purses/cartera`      | Get detailed portfolio          |
| GET    | `/purses/{id}`         | Get specific purse details      |
| GET    | `/purses/{id}/history` | Get payment history for a purse |
