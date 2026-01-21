# Auditoría API-first: Financiera Intesa (sin código)

Objetivo: migrar a API-first manteniendo web. Este documento es solo plan, riesgos y orden; no incluye implementación.

---

## 1) Módulos principales

| Módulo | Controlador(es) | Permisos/Roles | Tipo |
|--------|-----------------|----------------|------|
| **Auth** | Login, Register, Forgot, Reset, Verify, Confirm | `guest` / `auth` | Core |
| **Usuarios** | Admin\UserController | `permission:users.manage` | Admin |
| **Roles y permisos** | Admin\RoleController | `permission:roles.manage` | Admin |
| **Matrícula** | MatriculaController | `permission:access.core` | Recurso core |
| **Estudiantes / vista** | viewStudentController | `permission:access.core` | Recurso core + integración |
| **Costos** | CostController | `permission:access.core` | Recurso core |
| **Consecutivos** | ConsecutiveController | `permission:access.core` | Config/recursos |
| **Abonos (Entries)** | EntryController | `permission:access.core` | Recurso core |
| **Otros ingresos** | OtherEntryController | `permission:access.core` | Recurso core |
| **Recibos financieros** | FinancialReceiptController | `permission:access.core` | Recurso (impresión) |
| **Ajustes (Setting)** | SettingController | `permission:access.core` | Config (catálogos) |
| **Cartera (Purse)** | PurseController, HistoryPurseController | `permission:access.core` | Recurso core |
| **Sincronización** | SynchronizationController | `permission:access.core` | **Integración** (mysql↔mysql3) |
| **Terceros** | thirdEntry, ThirdActivity, ThirdReceipts, ConceptDischarge/Entry | `permission:access.core` | Recurso + config |
| **Egresos** | EgresoProvider, EgresoConcept, EgresoReceipt | `access.core`; destroy `role:super-admin` | Recurso core |
| **Contabilidad** | AccountingController | `permission:access.accounting` | Reportes + integración |
| **Base inicial** | AccountingController | `role:super-admin` | Config |
| **Mantenimiento** | MaintenanceController | `role:super-admin` | **Integración / operativa** |
| **Planillas asistencia** | AcademicManagement\AttendanceSheetController | `permission:access.core` | Recurso |
| **Estudiantes (búsqueda)** | StudentController | `permission:access.core` | Utilidad |

**Integraciones detectadas:**
- **mysql2**: BD externa (alumno, programa, estado). Usada en viewStudentController, MatriculaController::edit (opcional).
- **mysql3**: Sincronización local↔nube (SynchronizationController). Tablas espejo para sync.
- **Storage/PDF/Excel**: descargas e impresión; no APIs externas, pero sí respuestas binarias/HTML (stream PDF, descarga Excel).

---

## 2) Rutas: `web.php` y `api.php` — candidatas a `/api/v1`

### 2.1 `routes/web.php` (resumen por grupo)

| Grupo | Middleware | Rutas (resumen) | Candidatas API |
|-------|------------|-----------------|----------------|
| Públicas | — | `/`, `/Student`, `login`, `register`, `password/*` (forms + submit) | Solo lógica de **login/register** (no las vistas). |
| Auth (post-login) | `auth` | `logout`, `password/confirm`, `email/verify`, `email/resend` | **logout** (ya en API). `password/confirm`, `email/verify/resend` si el SPA los necesita. |
| Core | `auth`, `permission:access.core` | `/home`, `/Receipts`, `/pdf`, `/synchronization`, `/receipts/third/*` | — (vistas). Acciones de datos: sí. |
| viewStudent | `access.core` | `/view/student/{estado}`, `search`, `/cartera/{id}`, `/student/{id}`, `privileges`, `viewAbonos`, `viewOtros`, `purse/all` | **search**, **cartera**, **show(student)**, **purse/all**. No: `privileges` (sesión), vistas. |
| Matrícula | `access.core` | `index`, `create`, `edit`, `store`, `update`, `destroy`, `showMatricula`, `downloadFicha`, `viewFicha`, `uploadPhoto`, `deletePhoto` | **index, show(edit), store, update, destroy, uploadPhoto, deletePhoto**. PDF: **download/view** como stream o URL. |
| Planillas | `access.core` | `create`, `generate` | **generate** (y opcional **create** si devuelve datos para el formulario). |
| Cost | `access.core` | `store`, `show` | **store**, **show**. |
| Cost (eliminar) | `auth` | `cost/eliminar/{cod_alumno}` | **delete** (y definir `role:super-admin` en API). |
| Consecutive | `access.core` | `index`, `store` | **index**, **store**. |
| Entry | `access.core` | `all`, `get`, `store`, `update`, `destroy`, `print`, `ViewPdf`, `ViewPdfUnitedOther`, `show` | **all, get, store, update, destroy, show**. PDF/print: **stream o link**. |
| OtherEntry | `access.core` | Análogo a Entry | Igual. |
| FinancialReceipt | `access.core` | `print` (type: entry\|other-entry\|egreso\|third) | **print** (stream PDF o URL). |
| Setting | `access.core` | CRUD: concepto, elaborado, haber, debe, otrosConcepto, program, schedule, group, teacher, module, institution | **Todos los CRUD** de catálogos. |
| Purse / History | `access.core` | `edit`, `total`, `totales`, `ViewPdf`, `search`, `delete` | **edit, total, totales, search, delete**. PDF: stream/URL. |
| Synchronization | `access.core` | `local-cloud`, `cloud-local`, `count/local-cloud` | **count** (JSON), **local-cloud**, **cloud-local** (acción; puede seguir siendo operativa poco estándar o solo web). |
| Terceros (entry) | `access.core` | `index`, `store`, `edit`, `update`, `destroy`, `search` | **index, store, show, update, destroy, search**. |
| Terceros (activity) | `access.core` | `list`, `store`, `update`, `destroy` | **index, store, update, destroy**. |
| ThirdReceipts | `access.core` | `index`, `store`, `print`, `destroy` | **index, store, show, print, destroy**. |
| ConceptDischarge/Entry | `access.core` | store, update, destroy | **store, update, destroy**. |
| Student | `access.core` | `search/{name}`, `searchAll/{name}` | **search**, **searchAll**. |
| Egresos (providers) | `access.core` | index, store, update, destroy | **index, store, update, destroy**. |
| Egresos (conceptos) | `access.core` | store, update, destroy | **store, update, destroy**. |
| Egresos (recibos) | `access.core` | index, create, store, edit, print | **index, create, store, show, print**. destroy: `super-admin`. |
| Contabilidad | `access.accounting` | `index`, vistas (abonos, otros-ingresos, …), **download** (Excel), `cashBases`, `investigarAbonos`, `eliminarAbonosProblematicos` | **Descargas Excel** (stream o URL), **cashBases**, **investigar/eliminar** (acciones). Vistas: solo web. |
| Base inicial | `role:super-admin` | `baseInicialView`, `baseInicialStore` | **baseInicial** (GET datos, POST store). |
| Maintenance | `role:super-admin` | Múltiples (investigar, reparar, limpiar, etc.) | Baja prioridad API; pueden seguir solo web o exponer por API interna más adelante. |
| Egreso destroy | `role:super-admin` | `DELETE /egresos/recibos/{noRecibo}` | **destroy** en `/api/v1/egresos/recibos/{noRecibo}`. |
| Admin users | `permission:users.manage` | index, create, store, edit, update, destroy, assignRoles | **index, store, show, update, destroy, assignRoles**. create/edit: datos para formulario. |
| Admin roles | `permission:roles.manage` | index, create, store, edit, update, destroy, assignPermissions | **index, store, show, update, destroy, assignPermissions**. |

### 2.2 `routes/api.php` (estado actual)

Ya existen bajo **`/api/v1`**:

- **Auth:** `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`
- **Matrícula:** `GET /matriculas`, `GET /matriculas/{cod_alumno}`, `POST /matriculas`
- **Admin users:** `GET /admin/users`, `GET /admin/users/{user}`

No están todavía en API (candidatas): Roles (admin), Setting (catálogos), Cost, Consecutive, Entry, OtherEntry, Purse/History, Terceros (entry, activity, receipts, concept-discharge/entry), Egresos (providers, conceptos, recibos), Student (search), viewStudent (search, cartera, show — sin `privileges` por sesión), Contabilidad (downloads, cashBases, etc.), Base inicial, planillas, FinancialReceipt print, Matrícula (update, destroy, photo, PDF).

---

## 3) Middleware relevantes

| Middleware | Dónde | Uso en API |
|------------|-------|------------|
| **auth** | `Authenticate`; redirect a `login` si no `expectsJson` | En API: `auth:sanctum`. `Authenticate::redirectTo` ya devuelve `null` si `expectsJson`. |
| **auth:sanctum** | Rutas API | Token Bearer o cookie SPA. Para API-first con frontend separado: **Bearer** (Sin `EnsureFrontendRequestsAreStateful` en `api`). |
| **guest** | `RedirectIfAuthenticated`; redirect a `/home` si autenticado | No se usa en rutas API. Para `login`/`register` en API no aplica `guest` (no hay “form”). |
| **permission:{slug}** | `CheckPermission` | **En uso en API.** Si `api/*` o `expectsJson`: 401/403 JSON. Si no: redirect `login` o `abort(403)`. |
| **role:{slug}** | `CheckRole` | Igual: ya devuelve 401/403 JSON en `api/*` o `expectsJson`. |
| **CSRF** | `VerifyCsrfToken` en grupo `web` | **No aplica a `api`**. Las rutas `api` no llevan `web`; con Bearer no hace falta CSRF. |
| **Sesión** | `StartSession`, `ShareErrorsFromSession` en `web` | **Solo web.** La API no debe depender de sesión. |
| **throttle** | `throttle:api` (60/min) en grupo api; `throttle:5,1` en login | En uso. Mantener y extender en login/registro si se expone. |
| **signed** | `email/verify/{id}/{hash}` | Si se lleva verify a API, habría que validar firma y devolver JSON; el enlace puede seguir abriendo web o SPA. |

Resumen: para API, la pila relevante es `auth:sanctum` + `permission`/`role` + `throttle`. CSRF y sesión solo web.

---

## 4) Riesgos y orden de migración por impacto

### 4.1 Riesgos

| Riesgo | Dónde | Efecto | Mitigación |
|--------|-------|--------|------------|
| **Redirects** | Login, Register, Forgot, Reset, Verify, Confirm, logout; `CheckPermission`/`CheckRole`; `RedirectIfAuthenticated` | En API hay que devolver **solo JSON** (401/403, 422, etc.). | Auth web sigue con redirects. API: ya no redirect en `CheckPermission`/`CheckRole` ni en `Handler` para `api/*`. |
| **Sesión** | Login (`session()->regenerate`), Logout (`invalidate`, `regenerateToken`), `viewStudentController::privileges` (`session('permission')`), `viewAbonos`/`viewOtros` (leen `session('permission')`) | La API no debe usar sesión. `privileges`/`viewAbonos`/`viewOtros` son flujos estadoful. | API: auth con **Sanctum Bearer** (ya). `privileges`/viewAbonos/viewOtros: **rediseñar en frontend** (ej. paso de contraseña en el mismo request o flujo en 2 pasos vía API sin sesión). No exponer `session('permission')`. |
| **Responses HTML** | Casi todos los controladores: `return view(...)`, `redirect()->with(...)`, PDF `stream()`, Excel download, `errors.403` | API debe devolver JSON (y para archivos: JSON con URL, o `Content-Disposition` con stream manteniendo `Accept: application/json` opcional). | Separar: web = view/redirect; API = `ApiResponse::success/error` y para files: contrato claro (URL, base64, o stream con headers). |
| **Excepciones** | `abort(403)`, `ValidationException`, `ModelNotFoundException`, `AuthenticationException`, errores 500 | En web: vistas o páginas Laravel. En API: formato `{ error: { code, message, details } }`. | `Handler` con `renderable` para `api/*`/`expectsJson` ya unifica. Revisar que no queden `abort()` que rendericen HTML en rutas API. |
| **BD externa mysql2** | viewStudentController (index, show, carteraTable, viewAbonos, viewOtros), MatriculaController::edit (opcional) | Si mysql2 cae, esas rutas fallan. | API: encapsular en servicio; si mysql2 no responde, devolver error JSON o datos parciales (solo mysql principal). Documentar. |
| **Sincronización mysql↔mysql3** | SynchronizationController | Lógica operativa y pesada; `redirect` con `with('success')`. | `count_local_cloud` es fácil (JSON). `transfer_*`: pueden quedar solo web o exponer como “acción” API que devuelva JSON al final. |
| **PDF / Excel / streams** | Entry, OtherEntry, Purse, FinancialReceipt, Matricula (ficha), Accounting (Excel) | Contenido binario. | Definir contrato: URL firmada temporal, o endpoint que con `Accept: application/json` devuelva `{ "url": "..." }` y con `Accept: application/pdf` (o sin Accept) el stream. O siempre JSON con link. |
| **Contador → /home** | `CheckPermission`: si contador e `is('home')` → `redirect(accounting.index)` | Comportamiento web. | En API, `GET /home` no tiene sentido; `/me` o similares no redirigen. Nada que hacer en API. |
| **TableChangeController (auditoría)** | Llamadas en deletes (Entry, OtherEntry, Matricula, etc.) | La lógica de borrado debe seguir registrando. | Servicios/acciones de delete usados por API y web; la API no debe saltarse `StoreDelete`. |
| **Validación en controladores** | Muchos con `$request->validate()` inline; algunos con Form Request | Duplicación o incoherencia si API y web divergen. | Unificar en Form Requests o reglas reutilizables; API puede tener `Api\*Request` que comparta reglas con web donde aplique. |

### 4.2 Orden de migración por impacto (candidatas que faltan en `/api/v1`)

Alto impacto (core, usados por un futuro SPA desde día 1):

1. **Auth complementario**: `register` (POST), si el SPA registra usuarios; `password/email` (reset link), `password/reset` (reset); opcional `email/verify` y `email/resend`.  
2. **Roles (admin)**: CRUD + `assignPermissions` — necesario para administración en SPA.  
3. **Setting (catálogos)**: al menos **program, schedule, group** (y si el SPA los usa: concepto, elaborado, haber, debe, otrosConcepto, teacher, module, institution). Sin esto, formularios (matrícula, costos, etc.) no se rellenan.  
4. **Matrícula ampliado**: `update`, `destroy`, `uploadPhoto`, `deletePhoto`; `downloadFicha`/`viewFicha` (PDF) según contrato.  
5. **Cost**: `store`, `show` — necesario para flujo matrícula → costos → abonos.  
6. **Consecutive**: `index`, `store` — usados antes de crear entries/recibos.  
7. **Entry** y **OtherEntry**: `all`, `get`, `store`, `update`, `destroy`, `show`; PDF/print como stream o URL.  
8. **Purse / HistoryPurse**: `edit`, `total`, `totales`, `search`, `delete`; PDF si aplica.  
9. **Student**: `search`, `searchAll` — usado en búsquedas globales.

Impacto medio (imprescindibles más adelante, pero se puede posponer):

10. **Terceros**: thirdEntry (CRUD + search), ThirdActivity (CRUD), ThirdReceipts (CRUD + print), ConceptDischarge/Entry (CRUD).  
11. **Egresos**: providers, conceptos, recibos (CRUD + print); destroy con `role:super-admin`.  
12. **FinancialReceipt**: `print` unificado (stream o URL).  
13. **viewStudent “datos” (sin privileges)**: `search`, `cartera` (o equivalente con Purse/History), `show` — si se mantiene como agregación; si se reemplaza por Entry/Purse/Student, depende del diseño del SPA.  
14. **Planillas asistencia**: `generate` (y `create` si devuelve datos para el form).

Impacto menor o más complejo:

15. **Contabilidad**: downloads Excel, `cashBases`, `investigarAbonos`, `eliminarAbonosProblematicos` — muchos son reportes/acciones batch.  
16. **Base inicial** y **Maintenance** — solo `super-admin`; pueden ser las últimas o quedar solo web.  
17. **Synchronization**: `count` (fácil); `transfer_local_cloud` / `transfer_cloud_local` — decidir si se exponen como API o solo web.

---

## 5) Plan por fases

**Fase 0 — Infraestructura (hecha en gran parte)**  
- Formato estándar de respuesta: `ApiResponse::success` / `ApiResponse::error`.  
- `Handler`: `renderable` para `api/*` / `expectsJson` (Validation, Auth, Authz, ModelNotFound, HttpException, 500).  
- `bootstrap/app.php`: 403 en web → vista; en API → Handler.  
- `CheckPermission` y `CheckRole`: 401/403 JSON en `api/*` o `expectsJson`.  
- CORS: `allowed_origins` desde env.  
- `routes/api.php`: prefijo `v1`, `throttle:api`, `throttle:5,1` en login.  

Archivos a revisar/tocar: `app/Support/ApiResponse.php`, `app/Exceptions/Handler.php`, `bootstrap/app.php`, `app/Http/Middleware/CheckPermission.php`, `app/Http/Middleware/CheckRole.php`, `config/cors.php`, `routes/api.php`.

---

**Fase 1 — Auth (parcialmente hecha)**  
- Hecho: `login`, `logout`, `me` (Sanctum Bearer).  
- Pendiente (si el SPA lo necesita): `register` (POST), `password/email`, `password/reset` (POST con token), `email/verify` (GET signed), `email/resend`.  

Archivos: `app/Http/Controllers/Api/V1/AuthController.php` (o `Auth\*`), `app/Http/Requests/Api/LoginRequest.php` (y `RegisterRequest`, etc. si se añaden), `routes/api.php`.  
Decisión: **Sanctum con Bearer** (frontend en otro origen; sin cookies SPA ni CSRF). JWT no necesario.

---

**Fase 2 — Admin: Users y Roles**  
- Users: ya `index`, `show`. Falta: `store`, `update`, `destroy`, `assignRoles`.  
- Roles: `index`, `store`, `show`, `update`, `destroy`, `assignPermissions`.  

Archivos: `app/Http/Controllers/Api/V1/UserController.php`, nuevo `RoleController` en `Api/V1`, `app/Http/Resources/UserResource.php` (y `RoleResource`, `PermissionResource` si se usan), `app/Http/Requests/Api/UserStoreRequest` etc., `routes/api.php`.

---

**Fase 3 — Catálogos (Setting)**  
- CRUD: program, schedule, group (prioridad); luego concepto, elaborado, haber, debe, otrosConcepto, teacher, module; por último institution.  
- Reutilizar o extraer lógica de `SettingController` a servicios; API como capa delgada.  

Archivos: `app/Http/Controllers/Api/V1/SettingController.php` (o por recurso: `ProgramController`, etc.), `app/Http/Resources/*`, `app/Http/Requests/Api/*`, `app/Services` si se extrae lógica, `routes/api.php`.

---

**Fase 4 — Matrícula completo**  
- Hecho: `index`, `show`, `store`.  
- Falta: `update`, `destroy`, `uploadPhoto`, `deletePhoto`; `downloadFicha` / `viewFicha` (contrato PDF).  

Archivos: `app/Http/Controllers/Api/V1/MatriculaController.php`, `app/Http/Requests/Api/MatriculaUpdateRequest`, `app/Services/MatriculaService.php`, `routes/api.php`.

---

**Fase 5 — Cost, Consecutive, Student**  
- Cost: `store`, `show` (+ `eliminar` con `role:super-admin` si se unifica).  
- Consecutive: `index`, `store`.  
- Student: `search`, `searchAll`.  

Archivos: `app/Http/Controllers/Api/V1/CostController.php`, `ConsecutiveController`, `StudentController`; Requests, Resources; `routes/api.php`.

---

**Fase 6 — Entry, OtherEntry, Purse, HistoryPurse**  
- Entry y OtherEntry: CRUD + show; PDF/print (stream o URL).  
- Purse: edit, total, totales, search, delete; PDF.  
- HistoryPurse: search, delete.  
- Reutilizar `CarteraService`, `AccountingReportService` donde aplique; `TableChangeController::StoreDelete` en deletes.  

Archivos: `Api/V1/EntryController`, `OtherEntryController`, `PurseController`, `HistoryPurseController`; Form Requests; Resources; `routes/api.php`.

---

**Fase 7 — Terceros y Egresos**  
- Terceros: thirdEntry, ThirdActivity, ThirdReceipts, ConceptDischarge, ConceptEntry.  
- Egresos: providers, conceptos, recibos (CRUD + print); destroy egreso con `role:super-admin`.  
- FinancialReceipt: `print` unificado.  

Archivos: controladores en `Api/V1`, Requests, Resources; `routes/api.php`.

---

**Fase 8 — viewStudent (solo datos), Planillas, Contabilidad, Base inicial, Sync**  
- viewStudent: solo endpoints de datos (search, cartera, show) sin `privileges`/sesión; o sustituir por agregaciones sobre Entry/Purse/Student.  
- Planillas: `generate` (y `create` si devuelve datos).  
- Contabilidad: downloads Excel, cashBases, investigar/eliminar abonos.  
- Base inicial: GET + POST.  
- Sync: `count`; opcional `transfer_*`.  

Archivos: `Api/V1` para los controladores implicados; `routes/api.php`.  
Decisiones: qué hacer con `privileges`/viewAbonos/viewOtros (rediseño en SPA); si Sync `transfer_*` se expone en API o solo web.

---

**Fase 9 — Maintenance (opcional)**  
- Herramientas `super-admin`. Pueden quedar solo web o exponer algunas por API interna.  
- Archivos: `Api/V1/MaintenanceController` o equivalente; `routes/api.php`.

---

## 6) Archivos a tocar primero (prioridad)

- **Fase 0 (ya mayormente hecha):**  
  - `app/Support/ApiResponse.php`  
  - `app/Exceptions/Handler.php`  
  - `bootstrap/app.php`  
  - `app/Http/Middleware/CheckPermission.php`, `CheckRole.php`  
  - `config/cors.php`  
  - `routes/api.php`  

- **Fase 1:**  
  - `app/Http/Controllers/Api/V1/AuthController.php`  
  - `app/Http/Requests/Api/LoginRequest.php` (y Register, etc. si se añaden)  
  - `routes/api.php`  

- **Fase 2:**  
  - `app/Http/Controllers/Admin/UserController.php` (referencia)  
  - `app/Http/Controllers/Admin/RoleController.php` (referencia)  
  - Nuevos: `Api/V1/UserController` (ampliar), `Api/V1/RoleController`  
  - `app/Http/Resources/UserResource.php` (existente); `RoleResource`, `PermissionResource`  
  - `app/Http/Requests/Api/UserStoreRequest`, etc.  
  - `routes/api.php`  

- **Fase 3:**  
  - `app/Http/Controllers/SettingController.php` (referencia)  
  - Nuevos controladores/recursos/requests en `Api/V1` para catálogos  
  - `routes/api.php`  

- **Fase 4:**  
  - `app/Http/Controllers/Api/V1/MatriculaController.php` (existente; ampliar)  
  - `app/Services/MatriculaService.php` (existente)  
  - `app/Http/Requests/Api/MatriculaUpdateRequest`, etc.  
  - `routes/api.php`  

A partir de Fase 5, el patrón se repite: `Api/V1/*Controller`, `Requests`, `Resources`, `Services` si se extrae lógica, y `routes/api.php`.

---

## 7) Notas de riesgos y decisiones

- **Sanctum Bearer vs JWT**  
  - **Elegido: Sanctum con Bearer.**  
  - Motivo: frontend en otro dominio/puerto; evitar CSRF y `EnsureFrontendRequestsAreStateful`; integración sencilla; sin librerías JWT.  
  - Si en el futuro se necesitan refresh tokens o expiración estricta, se puede:  
    - usar `sanctum.expiration` + flujo de refresh en un endpoint propio, o  
    - valorar JWT (p. ej. `tymon/jwt-auth`) en una fase posterior.

- **Sesión y `privileges` / viewAbonos / viewOtros**  
  - `session('permission')` en viewStudent no tiene equivalente directo en API.  
  - Opciones: (a) Endpoint `POST /privileges/verify` con password, que devuelva un token corto o flag en JSON (y el SPA lo guarda en memoria); (b) Incluir la contraseña de privilegios en el request que edita el abono/otro (si el negocio lo admite).  
  - Decisión: no replicar `session('permission')` en la API; rediseñar el flujo en el SPA y, si hace falta, un endpoint específico sin sesión.

- **PDF y Excel**  
  - Opción A: JSON con `url` (link firmado temporal o ruta pública con token).  
  - Opción B: Mismo endpoint: si `Accept: application/json` → `{ "url": "..." }`; si `Accept: application/pdf` o sin Accept → stream.  
  - Opción C: Siempre stream; el SPA usa `window.open` o `fetch` con `response.blob()`.  
  - Decisión recomendada: **A o B** para simplificar SPA y cacheo; documentar en contrato API.

- **mysql2 / mysql3**  
  - mysql2: fallos deben traducirse en 503 o 200 con datos parciales/vacíos y un flag; no romper el request.  
  - mysql3 / Sync: `transfer_*` son operaciones largas; evaluar timeout, colas (Job) o dejarlas solo en web.  
  - Documentar en API que algunos datos (estudiantes desde mysql2) son opcionales o pueden no estar disponibles.

- **`role:super-admin`**  
  - En API se aplica igual que en web; `CheckRole` ya devuelve 403 JSON en `api/*`.  
  - Rutas que usan `role:super-admin`: base-inicial, maintenance, `cost/eliminar`, `egresos/recibos` destroy.  
  - Mantener el mismo middleware en los equivalentes `/api/v1/...`.

- **Tests**  
  - Los tests de API usan `RefreshDatabase`; migraciones con `MODIFY`/`CHANGE` (MySQL) fallan con SQLite.  
  - Ejecutar tests con MySQL (p. ej. `DB_CONNECTION=mysql DB_DATABASE=... php artisan test tests/Feature/Api/`).  
  - Documentar en README o en `docs/NEXTJS_API.md`.

- **Deprecación web**  
  - Mantener web sin cambios hasta que el SPA cubra los flujos.  
  - Después: cabeceras `X-Deprecation`, `X-Sunset` en rutas web que tengan equivalente en `/api/v1`, y documento de deprecación con fechas.

---

## 8) Resumen

- **Módulos:** Auth, Users/Roles, Matrícula, Cost, Entry, OtherEntry, Purse, Setting, Terceros, Egresos, Contabilidad, Sync, Maintenance, Planillas, Student, viewStudent (parcial); integraciones: mysql2, mysql3, PDF/Excel.  
- **Rutas:** web ~100+; api ya tiene auth, matrícula (parcial), users (parcial). Candidatas: el resto de recursos y acciones listadas en §2.  
- **Middleware:** `auth`/`auth:sanctum`, `permission`, `role`, `throttle`; CSRF y sesión solo web.  
- **Riesgos:** redirects, sesión (`privileges`/viewAbonos/viewOtros), HTML/PDF/Excel, excepciones, mysql2/mysql3, TableChange.  
- **Orden:** Fase 0 (infra) → 1 (auth) → 2 (users+roles) → 3 (setting) → 4 (matrícula completo) → 5 (cost, consecutive, student) → 6 (entry, other, purse, history) → 7 (terceros, egresos) → 8 (viewStudent datos, planillas, contabilidad, base-inicial, sync) → 9 (maintenance, opcional).  
- **Decisiones:** Sanctum Bearer (no JWT); no sesión en API; rediseño de `privileges`/viewAbonos/viewOtros en SPA; contrato claro para PDF/Excel (URL o stream); mysql2/mysql3 encapsulados y documentados.
