# Plan de migración: Laravel monolítico → API REST para Next.js

## 1. Auditoría inicial del proyecto

### 1.1 Módulos principales

| Módulo | Controlador(es) | Permisos/Roles | Descripción |
|--------|-----------------|----------------|-------------|
| **Auth** | Auth\LoginController, Register, Forgot, Reset, Verify, Confirm | guest / auth | Login, registro, recuperar contraseña, verificar email |
| **Usuarios** | Admin\UserController | `permission:users.manage` | CRUD usuarios, asignar roles |
| **Roles y permisos** | Admin\RoleController | `permission:roles.manage` | CRUD roles, asignar permisos |
| **Matrícula** | MatriculaController | `permission:access.core` | CRUD matrículas, ficha PDF, foto |
| **Estudiantes / vista** | viewStudentController | `permission:access.core` | Búsqueda, cartera, abonos, privilegios (sesión) |
| **Costos** | CostController | `permission:access.core` | store, show |
| **Consecutivos** | ConsecutiveController | `permission:access.core` | index, store |
| **Abonos (Entries)** | EntryController | `permission:access.core` | CRUD, PDF, impresión |
| **Otros ingresos** | OtherEntryController | `permission:access.core` | CRUD, PDF, impresión |
| **Recibos financieros** | FinancialReceiptController | `permission:access.core` | Impresión unificada |
| **Ajustes (Setting)** | SettingController | `permission:access.core` | CRUD: concepto, elaborado, haber, debe, otrosConcepto, program, schedule, group, teacher, module, institution |
| **Cartera (Purse)** | PurseController, HistoryPurseController | `permission:access.core` | edit, total, totales, PDF, búsqueda, delete |
| **Sincronización** | SynchronizationController | `permission:access.core` | local-cloud, cloud-local, count |
| **Terceros** | thirdEntryController, ThirdActivityController, ThirdReceiptsController, ConceptDischarge/Entry | `permission:access.core` | Entradas, actividades, recibos, conceptos |
| **Egresos** | EgresoProvider, EgresoConcept, EgresoReceipt | `permission:access.core`; destroy solo `role:super-admin` | Proveedores, conceptos, recibos |
| **Contabilidad** | AccountingController | `permission:access.accounting` | Vistas + descargas Excel |
| **Base inicial** | AccountingController (baseInicial) | `role:super-admin` | Vista y store |
| **Mantenimiento** | MaintenanceController | `role:super-admin` | Herramientas de diagnóstico/reparación |
| **Planillas asistencia** | AcademicManagement\AttendanceSheetController | `permission:access.core` | create, generate |
| **Estudiantes (búsqueda)** | StudentController | `permission:access.core` | search, searchAll |

### 1.2 Rutas actuales

- **`routes/web.php`**: ~340 líneas. Rutas Blade con `auth`, `permission:access.core`, `permission:access.accounting`, `permission:users.manage`, `permission:roles.manage`, `role:super-admin`. Sin prefijo (salvo `admin` y `maintenance`).
- **`routes/api.php`**: Solo `GET /user` con `auth:sanctum` (respuesta `$request->user()`).

Las rutas API se cargan con prefijo `api` automático (Laravel 11/12 en `bootstrap/app.php`), por tanto `/user` es realmente `/api/user`.

### 1.3 Dónde vive la lógica

- **Controladores**: La mayoría de la lógica (validación inline, reglas, redirect/views, consultas) está en controladores. Ej: `MatriculaController` ~550 líneas.
- **Modelos**: Relaciones, `hasPermission`, `hasRole`, `syncRoles`, `assignRole`. Algunos métodos estáticos: `haber::getUnique`, `elaborado::getUnique`, `InstitutionSetting::getSettings`.
- **Servicios**: `AccountingExcelService`, `AccountingReportService`, `CarteraService`, `StudentResolverService`. No se usan de forma general en todos los controladores web.
- **Helpers**: `asset_versioned`, `institution_settings`.
- **Form Requests**: Existen 10 (AttendanceSheet, Concepto, Consecutive, Cost, Debe, Elaborado, Entry, Haber, OtherEntry, OtrosConceptos) pero no en todos los flujos (p. ej. Matricula y User usan `Request::validate` inline).

### 1.4 Riesgos

| Riesgo | Detalle | Mitigación API |
|--------|---------|----------------|
| **Sesiones** | Login `session()->regenerate()`, logout `invalidate`. `CheckPermission` redirige a `login`. | API usará **Sanctum Bearer tokens**; sin sesiones en rutas `/api/*`. |
| **CSRF** | `VerifyCsrfToken` en grupo `web`. | Con Bearer no se usa CSRF en `/api/*`. |
| **Archivos** | Fotos (Matricula), PDFs, Excel. | API devolverá URLs (`Storage::url`) o base64/stream según contrato; uploads con `multipart/form-data`. |
| **Jobs** | No hay `ShouldQueue` ni Jobs en `app`. | Nada que migrar. |
| **Policies / Gates** | `AuthServiceProvider`: `$policies` y `boot` vacíos. | Permisos vía middleware `permission` (slug). En API, mismo middleware devolviendo 403 JSON. |
| **CheckPermission** | Hace `redirect()->route('login')` o `abort(403)`. | Adaptar: si `$request->is('api/*')` o `$request->expectsJson()`, responder `403` JSON estándar. |
| **CheckRole** | `role:super-admin`. Mismo patrón. | Igual: 403 JSON en API. |
| **Sesión en flujos** | `viewStudentController`: `session('permission')` para flujos temporales. | Esos flujos se rediseñarán en Next.js sin depender de sesión; la API no expondrá `session('permission')`. |
| **Conexión `mysql2`** | `MatriculaController::edit` consulta BD externa (opcional). | Servicio/endpoint que encapsule; si falla, devolver datos locales. |
| **TableChangeController** | `StoreDelete` en deletes (auditoría). | Mantener en servicios al eliminar; la API llamará al mismo servicio. |

---

## 2. Diseño de arquitectura API-first

### 2.1 Estructura de carpetas y capas

```
app/
  Http/
    Controllers/
      Api/
        V1/
          AuthController.php
          UserController.php
          MatriculaController.php
    Middleware/
      CheckPermission.php       (adaptado para API)
      CheckRole.php             (adaptado para API, si se usa)
    Requests/
      Api/
        LoginRequest.php
        MatriculaStoreRequest.php
    Resources/
      UserResource.php
      MatriculaResource.php
  Services/
    MatriculaService.php        (nuevo; lógica de store/index/show)
  Support/                      (opcional)
    ApiResponse.php             (helper respuestas estándar)
```

- **Controllers/Api/V1**: Controladores que devuelven solo JSON. Delegan lógica a **Services** o **UseCases**.
- **Requests/Api**: Form Requests para validación de body/query en API.
- **Resources**: API Resources para serialización (User, Matricula, colecciones paginadas).
- **Services**: Lógica de negocio reutilizable (crear matrícula, filtros, etc.). Los controladores web pueden reutilizarlos en una fase posterior.

### 2.2 Versionado

- Prefijo: **`/api/v1`**. En `routes/api.php`: `Route::prefix('v1')->group(...)`. El prefijo `api` lo aplica Laravel, así que la ruta final es `https://dominio.com/api/v1/...`.

### 2.3 Formato estándar de respuesta

**Éxito**

```json
{
  "data": { ... },
  "meta": { "current_page": 1, "per_page": 15, "total": 100 },
  "message": "Opción: mensaje corto"
}
```

- `data`: recurso, colección o `null` si no aplica.
- `meta`: solo en listados paginados (o `request_id` si se implementa).
- `message`: opcional; típico en create/update/delete.

**Error**

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Los datos enviados no son válidos.",
    "details": { "email": ["El campo email es obligatorio."] }
  }
}
```

- `code`: `VALIDATION_ERROR`, `UNAUTHORIZED`, `FORBIDDEN`, `NOT_FOUND`, `SERVER_ERROR`, etc.
- `message`: legible.
- `details`: objeto con errores de validación, o `null`.

### 2.4 Paginación, filtros, ordenamiento y búsqueda

- **Paginación**: `?page=1&per_page=15` (máx. `per_page` 100). Respuesta con `meta` al estilo Laravel: `current_page`, `per_page`, `total`, `last_page`, etc.
- **Filtros**: query params con el nombre del campo, p. ej. `?programa=X&horario=Y`.
- **Ordenamiento**: `?sort=nombre_completo&order=asc` (por defecto `order=asc`).
- **Búsqueda**: `?search=texto` para búsqueda en campos habituales (nombre, documento, etc.).

Se documentará por recurso en `NEXTJS_API.md`.

---

## 3. Autenticación para Next.js

### 3.1 Decisión: Sanctum con Bearer tokens

- **Elegido: Sanctum con Bearer tokens** (no cookies SPA).
- **Motivo**: Next.js va en otro origen/dominio (puerto o subdominio distinto). Con cookies SPA haría falta CORS `credentials: true`, `SANCTUM_STATEFUL_DOMAINS`, `sanctum/csrf-cookie` y CSRF en cada petición. Con un frontend totalmente desacoplado, Bearer simplifica: no depende de cookies ni CSRF; Next.js guarda el token (memoria, `localStorage` o cookie httpOnly si se usa BFF) y envía `Authorization: Bearer {token}`.
- **Flujo**:
  1. `POST /api/v1/auth/login` con `email` y `password` → `{ "data": { "token": "...", "user": {...} } }`.
  2. Next.js guarda el token y lo envía en `Authorization: Bearer {token}`.
  3. `POST /api/v1/auth/logout` → revocar token actual (opcional; también se puede revocar en backend al hacer logout en el cliente).
  4. `GET /api/v1/auth/me` → perfil (user + roles/permisos) para rehidratar sesión en el front.

### 3.2 Endpoints de auth

- `POST /api/v1/auth/login` — Login (email, password). Devuelve token + user.
- `POST /api/v1/auth/logout` — Logout (revocar token del request).
- `GET /api/v1/auth/me` — Perfil (auth:sanctum).

Protección: `auth:sanctum` en todas las rutas que requieran autenticación. Permisos con middleware `permission:...` (adaptado a 403 JSON).

### 3.3 CORS

- `config/cors.php`: `allowed_origins` desde `FRONTEND_URL` o lista de dominios (no `*` en producción). `supports_credentials` en `false` con Bearer (no necesario). `paths`: `['api/*', 'sanctum/csrf-cookie']` por si en el futuro se usa csrf-cookie.

---

## 4. Implementación incremental

### 4.1 Fases

1. **Fase 0 – Infraestructura**: `ApiResponse`, Handler para excepciones JSON en `/api/*`, CORS, adaptar `CheckPermission` (y `CheckRole`) para 403 JSON.
2. **Fase 1 – Auth y rutas v1**: `routes/api.php` con prefijo `v1`; `AuthController` (login, logout, me); `LoginRequest`; `UserResource` para `me`.
3. **Fase 2 – Users API**: `Api\V1\UserController` (index, show); `UserResource`; middleware `permission:users.manage` en esas rutas.
4. **Fase 3 – Matrícula API**: `Api\V1\MatriculaController` (index, show, store); `MatriculaResource`, `MatriculaStoreRequest`; `MatriculaService` con lógica de create y filtros; middleware `permission:access.core`.
5. **Fase 4 – Documentación**: `NEXTJS_API.md` con endpoints, ejemplos, códigos. Opción Scribe/OpenAPI en fases posteriores si se requiere.
6. **Fase 5 – Tests**: Feature tests para auth, users, matrícula (sin Blade).
7. **Checklist**: variables `.env`, rate limiting, logs (request id si procede), estrategia de deprecación de rutas web.

### 4.2 Rutas web existentes

- Se mantienen en `web.php` sin cambios. La API coexiste; no se elimina ninguna ruta web en esta iteración.

### 4.3 Manejo global de excepciones

- En `Handler` (o `bootstrap/app.php` `withExceptions`): si la petición es `api/*` o `expectsJson()`, renderizar `ValidationException`, `AuthenticationException`, `AuthorizationException` (403), `ModelNotFoundException` (404) y genéricas con el formato `{ "error": { "code", "message", "details" } }`.

---

## 5. Documentación

- **`NEXTJS_API.md`**: lista de endpoints mínimos para que Next.js arranque (auth + users + matrícula), formato de respuestas, ejemplos de `fetch`/axios, headers (`Authorization: Bearer`, `Accept: application/json`).
- Opción **Scribe** o **OpenAPI** en una fase posterior (revisar compatibilidad con Laravel 12).

---

## 6. Testing

- **Feature tests** (no dependen de Blade):
  - Auth: login 200 + token, 401 credenciales inválidas, 422 validación; `me` 200 con token, 401 sin token; `logout` 204/200.
  - Users: `index` 401 sin auth, 403 sin `users.manage`, 200 con permiso; `show` 200.
  - Matrícula: `index` 401/403/200; `show` 200/404; `store` 422, 201.

Factories/seeders: `UserFactory`; `RolePermissionSeeder` y `User` con roles para permisos. Para matrícula: `MatriculaFactory` si existe o se crearán datos en el test.

---

## 7. Checklist de entrega

- [x] **.env**: Añadir `FRONTEND_URL=http://localhost:3000` (o la URL de Next.js en producción). Opcional: `CORS_ALLOWED_ORIGINS` (lista separada por comas); si no se define, se usa `FRONTEND_URL` en `config/cors.php`. `SANCTUM_STATEFUL_DOMAINS` no es necesario con Bearer.
- [x] **Rate limiting**: `throttle:api` (60/min) aplicado al grupo `api` por defecto; `throttle:5,1` en `POST /api/v1/auth/login`.
- [ ] **Logs**: (TODO) Middleware `RequestId` (X-Request-ID o correlation id) en rutas API y registro en logs. Opcional para una siguiente iteración.
- [ ] **Deprecación**: Estrategia recomendada: mantener rutas web durante 6–12 meses; en rutas web que ya tengan equivalente en API, añadir cabecera `X-Deprecation: true` y `X-Sunset: <fecha>`; documentar en `docs/DEPRECACION_WEB.md` las rutas y la fecha tope.

---

## 8. Archivos a tocar / crear

| Acción | Archivo |
|--------|---------|
| Crear | `app/Support/ApiResponse.php` |
| Modificar | `app/Exceptions/Handler.php` |
| Modificar | `bootstrap/app.php` (exceptions para API si no se centraliza en Handler) |
| Modificar | `app/Http/Middleware/CheckPermission.php` |
| Modificar | `config/cors.php` |
| Crear | `app/Http/Controllers/Api/V1/AuthController.php` |
| Crear | `app/Http/Controllers/Api/V1/UserController.php` |
| Crear | `app/Http/Controllers/Api/V1/MatriculaController.php` |
| Crear | `app/Http/Requests/Api/LoginRequest.php` |
| Crear | `app/Http/Requests/Api/MatriculaStoreRequest.php` |
| Crear | `app/Http/Resources/UserResource.php` |
| Crear | `app/Http/Resources/MatriculaResource.php` |
| Crear | `app/Services/MatriculaService.php` |
| Reescribir | `routes/api.php` (prefijo v1, auth, users, matrícula) |
| Crear | `docs/NEXTJS_API.md` |
| Crear | `tests/Feature/Api/V1/AuthApiTest.php` |
| Crear | `tests/Feature/Api/V1/UserApiTest.php` |
| Crear | `tests/Feature/Api/V1/MatriculaApiTest.php` |
| Revisar | `database/seeders/` (RolePermissionSeeder, DatabaseSeeder) para tests |

---

## 9. Próximos pasos (fuera de esta iteración)

- Más módulos: Entry, OtherEntry, Setting, Purse, Egresos, Contabilidad, etc.
- Refresh token si se requiere (Sanctum con expiración y refresh).
- Scribe u OpenAPI.
- RequestId/correlation id en logs.
- Política de deprecación de rutas web y cabeceras `X-Deprecation`.
