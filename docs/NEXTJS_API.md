# Guía API REST para Next.js

Base URL: `https://tu-dominio.com/api/v1` (en local: `http://localhost:8000/api/v1`).

## Headers

En todas las peticiones (salvo login, register, forgot-password, reset-password, verify-email):

```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

## Formato de respuestas

### Éxito

```json
{
  "data": { ... },
  "meta": { "current_page": 1, "per_page": 15, "total": 100 },
  "message": "Opcional"
}
```

### Error

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Los datos enviados no son válidos.",
    "details": { "email": ["El campo email es obligatorio."] }
  }
}
```

Códigos: `VALIDATION_ERROR` (422), `UNAUTHENTICATED` (401), `FORBIDDEN` (403), `NOT_FOUND` (404), `TOO_MANY_REQUESTS` (429), `SERVER_ERROR` (500, incl. `trace_id`).

---

## Estructura y convenciones (backend)

- **Controllers:** `app/Http/Controllers/Api/V1/` — orquestan; no devuelven `view()` ni `redirect()`.
- **Requests:** `app/Http/Requests/Api/V1/` — validación de entrada.
- **Resources:** `app/Http/Resources/V1/` — serialización a JSON.
- **Services:** `app/Services/` — lógica de negocio reutilizable.

Patrón: **Request** (valida) → **Controller** (llama Service si aplica) → **Resource** (serializa) → `ApiResponse::success/error`.

Rutas en `routes/api.php` bajo `prefix('v1')`, agrupadas en: auth, recursos (matrículas, …), admin.

---

## Auth

### POST /api/v1/auth/login

**Body:**
```json
{ "email": "user@example.com", "password": "password" }
```

**200:**
```json
{
  "data": {
    "token": "1|xxx...",
    "user": {
      "id": 1,
      "name": "Usuario",
      "email": "user@example.com",
      "roles": [{"id": 1, "name": "Secretaria", "slug": "secretaria"}],
      "permissions": ["access.core"]
    }
  }
}
```

**401:** `{ "error": { "code": "UNAUTHORIZED", "message": "Credenciales incorrectas.", "details": null } }`

**422:** validación (email/password requeridos).

---

### POST /api/v1/auth/logout

Requiere `Authorization: Bearer {token}`. Revoca el token actual.

**200:**
```json
{ "data": null, "message": "Sesión cerrada." }
```

---

### GET /api/v1/auth/me

Requiere `Authorization: Bearer {token}`.

**200:**
```json
{
  "data": {
    "id": 1,
    "name": "Usuario",
    "email": "user@example.com",
    "email_verified_at": "2024-01-01T00:00:00.000000Z",
    "roles": [{"id": 1, "name": "Secretaria", "slug": "secretaria"}],
    "permissions": ["access.core"]
  }
}
```

**401:** sin token o token inválido.

---

### POST /api/v1/auth/register

**Body:**
```json
{ "name": "Nombre", "email": "user@example.com", "password": "secret", "password_confirmation": "secret" }
```

**201:** `{ "data": { "token": "1|...", "user": { "id", "name", "email", "roles", "permissions" } }, "message": "Usuario registrado." }`

**422:** validación (email duplicado, contraseña no cumple reglas, etc.). Throttle: 5/min.

---

### POST /api/v1/auth/forgot-password

**Body:** `{ "email": "user@example.com" }`

**200:** `{ "data": null, "message": "Si el correo existe, recibirás un enlace para restablecer tu contraseña." }` (siempre 200, no se filtra si existe). Throttle: 5/min.

---

### POST /api/v1/auth/reset-password

**Body:** `{ "token": "...", "email": "user@example.com", "password": "new", "password_confirmation": "new" }`

**200:** `{ "data": null, "message": "Contraseña restablecida correctamente." }`

**422:** `{ "error": { "code": "VALIDATION_ERROR", "message": "...", "details": { "email": ["..."] } } }` (token inválido/expirado, etc.). Throttle: 5/min.

---

### GET /api/v1/auth/verify-email

Enlace firmado: `?id=1&hash=xxx&expires=...&signature=...` (generado con `URL::temporarySignedRoute('api.verification.verify', now()->addMinutes(60), ['id'=>$user->id,'hash'=>sha1($user->email)])`). Requiere `User` con `MustVerifyEmail` para que `resend` y `verify` tengan efecto.

**200:** `{ "data": { "user": ... } o null, "message": "Correo verificado correctamente." }` (o "El correo ya estaba verificado.").

**400:** `{ "error": { "code": "FEATURE_DISABLED", "message": "Verificación de email no está habilitada." } }` si el modelo no implementa `MustVerifyEmail`. **403:** enlace inválido o hash no coincide. Throttle: 5/min.

---

### POST /api/v1/auth/resend-verification

Requiere `Authorization: Bearer {token}`.

**200:** `{ "data": null, "message": "Se ha enviado un nuevo enlace de verificación." }` (o "El correo ya está verificado.").

**400:** `FEATURE_DISABLED` si el modelo no implementa `MustVerifyEmail`. **401:** sin token. Throttle: 5/min.

---

### Refresh y expiración

No hay `POST /auth/refresh`. Los tokens Sanctum no expiran por defecto (`sanctum.expiration` null). Ante expiración (si se configura): el cliente debe hacer login de nuevo.

---

## Users (Admin)

Requiere `permission:users.manage`.

### GET /api/v1/admin/users

**Query:** `?per_page=15&page=1`

**200:**
```json
{
  "data": [{ "id": 1, "name": "...", "email": "...", "roles": [...] }],
  "meta": { "current_page": 1, "per_page": 15, "total": 50, "last_page": 4 }
}
```

### GET /api/v1/admin/users/{id}

**200:** `{ "data": { "id": 1, "name": "...", "email": "...", "roles": [...], "permissions": [...] } }`

**404:** usuario no encontrado.

---

## Roles y permisos (Admin)

Requiere `permission:roles.manage`. Todas las rutas usan `Authorization: Bearer {token}`.

### GET /api/v1/admin/permissions

Lista todos los permisos (sin paginar). Solo lectura.

**200:**
```json
{
  "data": [
    { "id": 1, "name": "Acceso al Sistema", "slug": "access.core", "description": "..." },
    { "id": 2, "name": "Gestionar Roles", "slug": "roles.manage", "description": "..." }
  ]
}
```

**401** sin token. **403** sin `roles.manage`.

---

### GET /api/v1/admin/roles

Lista roles con sus `permissions`.

**200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Secretari@",
      "slug": "secretaria",
      "description": "...",
      "permissions": [{ "id": 1, "name": "Acceso al Sistema", "slug": "access.core", "description": "..." }]
    }
  ]
}
```

---

### POST /api/v1/admin/roles

**Body:** `{ "name": "Nuevo rol", "slug": "nuevo-rol", "description": "Opcional" }`

**201:** `{ "data": { "id": 5, "name": "...", "slug": "...", "description": "...", "permissions": [] }, "message": "Rol creado." }`

**422:** `name`/`slug` requeridos, `slug` o `name` duplicados.

---

### GET /api/v1/admin/roles/{id}

**200:** `{ "data": { "id": 1, "name": "...", "slug": "...", "description": "...", "permissions": [...] } }`

**404:** rol no encontrado.

---

### PUT/PATCH /api/v1/admin/roles/{id}

**Body (todos opcionales en PATCH):** `{ "name": "...", "slug": "...", "description": "..." }`

**200:** `{ "data": { ... }, "message": "Rol actualizado." }`

**422:** `slug` duplicado (ignorando el actual).

---

### DELETE /api/v1/admin/roles/{id}

**200:** `{ "data": null, "message": "Rol eliminado." }`

**404:** rol no encontrado.

---

### POST /api/v1/admin/users/{id}/roles

Sincroniza los roles del usuario. Reemplaza todos los roles actuales.

**Body:** `{ "roles": ["secretaria", "contador"] }` — array de **slugs** de roles existentes. `[]` deja al usuario sin roles.

**200:** `{ "data": { "id": 1, "name": "...", "email": "...", "roles": [...], "permissions": [...] }, "message": "Roles del usuario sincronizados." }`

**422:** si algún slug en `roles` no existe. **404:** usuario no encontrado.

---

### POST /api/v1/admin/roles/{id}/permissions

Sincroniza los permisos del rol. Reemplaza todos los permisos actuales.

**Body:** `{ "permissions": ["access.core", "roles.manage"] }` — array de **slugs** de permisos existentes. `[]` deja al rol sin permisos.

**200:** `{ "data": { "id": 1, "name": "...", "slug": "...", "description": "...", "permissions": [...] }, "message": "Permisos del rol sincronizados." }`

**422:** si algún slug en `permissions` no existe. **404:** rol no encontrado.

---

## Inicio (Home)

Requiere `auth:sanctum` y `permission:access.core`.

### GET /api/v1/home

Dashboard / Inicio. Respuesta JSON con `message` y `server_time`. No depende de Blade.

**200:**
```json
{
  "data": {
    "message": "Bienvenido",
    "server_time": "2024-06-15T10:30:00.000000Z"
  }
}
```

**401:** sin token. **403:** sin `access.core`.

---

## Mantenimiento (Maintenance)

Requiere `auth:sanctum` y `permission:access.core`. Solo estadísticas del panel (sin acciones destructivas).

### GET /api/v1/maintenance

Devuelve los mismos counts que la vista web `maintenance.index`: entries, other_entries, costs, matriculas, purses, history_purses, third_receipts, egreso_receipts, egreso_providers, third_entries, third_activities, cash_bases, initial_balances.

**200:**
```json
{
  "data": {
    "stats": {
      "entries": 100,
      "other_entries": 20,
      "costs": 80,
      "matriculas": 75,
      "purses": 450,
      "history_purses": 1200,
      "third_receipts": 30,
      "egreso_receipts": 45,
      "egreso_providers": 5,
      "third_entries": 25,
      "third_activities": 3,
      "cash_bases": 90,
      "initial_balances": 1
    }
  },
  "message": "OK"
}
```

**401:** sin token. **403:** sin `access.core`.

---

## Planilla de Asistencia

Requiere `auth:sanctum` y `permission:access.core`.

### POST /api/v1/attendance-sheet/generate

Genera el PDF de la planilla de asistencia. Reutiliza la lógica y vista del controlador web. Respuesta: **stream PDF** (no JSON).

**Body (JSON):**
```json
{
  "program_id": 1,
  "schedule_id": 1,
  "group_id": 1,
  "teacher_id": 1,
  "module_id": 1,
  "fecha_inicio": "2024-01-01",
  "fecha_final": "2024-01-31",
  "fecha_clase": "2024-01-15"
}
```

- `program_id`: required, exists:programs,id  
- `schedule_id`: required, exists:schedules,id  
- `group_id`: required, exists:groups,id  
- `teacher_id`: required, exists:teachers,id  
- `module_id`: required, exists:modules,id  
- `fecha_inicio`: required, date  
- `fecha_final`: required, date, after_or_equal:fecha_inicio  
- `fecha_clase`: required, date; debe estar dentro del rango [fecha_inicio, fecha_final]

**200:** stream PDF. Cabeceras: `Content-Type: application/pdf`, `Content-Disposition: inline; filename="planilla_asistencia_<programa>_<grupo>_<fecha_clase>.pdf"`.

**422:** `VALIDATION_ERROR` con `details` por campo (ej. fecha_clase fuera de rango, ids inexistentes).

**500:** `SERVER_ERROR` con `trace_id` si falla la generación del PDF.

---

## Contabilidad (JSON y Excel)

Requiere `auth:sanctum` y `permission:access.core`. Contrato JSON para reportes: `{ "data": { "rows": [...], "totals": {...}, "params": {...} }, "meta": {...}, "message": "OK" }`. Los downloads Excel devuelven stream binario (no JSON).

### GET /api/v1/accounting

Resumen del dashboard: base del día (CashBase). Si no hay base para hoy, `today_base` es `null`.

**200:**
```json
{
  "data": {
    "today_base": { "fecha": "2024-01-15", "base_efectivo": 100.5, "base_banco": 200.0 }
  },
  "message": "OK"
}
```
O `"today_base": null` si no existe base para la fecha actual.

---

### GET /api/v1/accounting/abonos

**Query:** `?fecha_inicio=YYYY-MM-DD&fecha_fin=YYYY-MM-DD` (ambos opcionales; si se envía uno, el otro es obligatorio. Si se omiten, se usa todo el rango).

**200:** `data.rows` (lista de abonos, con `programa`), `data.totals` (`total`, `total_rows`, `is_partial`), `data.params` (`fecha_inicio`, `fecha_fin`). **422:** `fecha_fin` &lt; `fecha_inicio` o solo uno de los dos.

---

### GET /api/v1/accounting/otros-ingresos

**Query:** `?fecha_inicio=&fecha_fin=` (mismas reglas que abonos).

**200:** `data.rows`, `data.totals`, `data.params`. Estructura análoga.

---

### GET /api/v1/accounting/total-ingresos

**Query:** `?fecha_inicio=&fecha_fin=` (mismas reglas).

**200:** `data.rows` (entradas planas con `suma` acumulada), `data.totals`, `data.params`.

---

### GET /api/v1/accounting/egresos

**Query:** `?fecha_inicio=&fecha_fin=` (mismas reglas).

**200:** `data.rows` (egresos con `suma` acumulada), `data.totals`, `data.params`.

---

### GET /api/v1/accounting/arqueo-diario

**Query:** `?fecha=YYYY-MM-DD` (obligatorio).

**200:** `data.rows` (array de días con `fecha`, `base_efectivo`, `base_banco`, `movements`, `saldo_efectivo`, `saldo_banco`), `data.totals`, `data.params`.

**422:** `VALIDATION_ERROR` con `details.missing_dates` si faltan bases diarias para el rango.

---

### GET /api/v1/accounting/informe-semanal

**Query:** `?fecha=YYYY-MM-DD` (obligatorio). Usa la semana (lunes–domingo) que contiene esa fecha.

**200:** `data.rows`, `data.totals` (resumen: `total_ing_efectivo`, `total_ing_banco`, `total_egr_efectivo`, `total_egr_banco`, `saldo_final_efectivo`, `saldo_final_banco`, `saldo_final_total`), `data.params` (`fecha`, `startDate`, `endDate`).

**422:** `VALIDATION_ERROR` si no está configurada la base inicial.

---

### GET /api/v1/accounting/informe-mensual

**Query:** `?month_year=YYYY-MM` **o** `?mes=1&anio=2024`. Debe proporcionarse uno de los dos formatos.

**200:** `data.rows`, `data.totals` (igual estructura que informe-semanal), `data.params` (`mes`, `anio`, `startDate`, `endDate`).

**422:** `VALIDATION_ERROR` si falta base inicial; o si no se envía `month_year` ni (`mes` y `anio`).

---

### Downloads Excel (stream xlsx)

Los 7 endpoints siguientes devuelven **archivo xlsx** (no JSON). Cabeceras:  
`Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`  
`Content-Disposition: attachment; filename="<reporte>_<rango>.xlsx"`

Validación: **DateRangeRequiredRequest** (fecha_inicio y fecha_fin obligatorios) para los 4 primeros; **SingleDateRequest** (fecha obligatorio) para arqueo-diario e informe-semanal; **MonthYearRequest** (month_year o mes+anio) para informe-mensual.

Errores: **422** JSON `VALIDATION_ERROR` si params inválidos, `missing_dates` (arqueo-diario) o `base inicial` (informe-semanal/mensual). **500** JSON `SERVER_ERROR` con `trace_id` si falla la generación.

| Endpoint | Query | Filename ejemplo |
|----------|-------|------------------|
| `GET /api/v1/accounting/abonos/download` | `fecha_inicio`, `fecha_fin` (req) | `Informe de Abonos 2024-01-01 a 2024-01-31.xlsx` |
| `GET /api/v1/accounting/otros-ingresos/download` | idem | `Informe de Otros Ingresos 2024-01-01 a 2024-01-31.xlsx` |
| `GET /api/v1/accounting/total-ingresos/download` | idem | `Informe Total Ingresos 2024-01-01 a 2024-01-31.xlsx` |
| `GET /api/v1/accounting/egresos/download` | idem | `Informe Total Egresos 2024-01-01 a 2024-01-31.xlsx` |
| `GET /api/v1/accounting/arqueo-diario/download` | `fecha` (req) | `ARQUEO DIARIO 2024-01-15 a 2024-01-15.xlsx` |
| `GET /api/v1/accounting/informe-semanal/download` | `fecha` (req) | `INFORME SEMANAL 2024-01-15 a 2024-01-21.xlsx` |
| `GET /api/v1/accounting/informe-mensual/download` | `month_year=YYYY-MM` o `mes`+`anio` | `INFORME MENSUAL 2024-01-01 a 2024-01-31.xlsx` |

Ejemplo para guardar:

```bash
curl -L -o abonos.xlsx "http://localhost:8000/api/v1/accounting/abonos/download?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Authorization: Bearer TOKEN"
```

---

## Matrículas

Requiere `permission:access.core`.

### GET /api/v1/matriculas

**Query:** `?search=...&programa=...&horario=...&tipo_documento=CC|TI|PPT&sort=nombre_completo&order=asc&page=1&per_page=15`

**200:**
```json
{
  "data": [{ "id": 1, "cod_alumno": "123", "nombre_completo": "...", ... }],
  "meta": { "current_page": 1, "per_page": 15, "total": 100, "last_page": 7 }
}
```

### GET /api/v1/matriculas/{cod_alumno}

**200:** `{ "data": { "id": 1, "cod_alumno": "123", "nombre_completo": "...", "photo_url": "/storage/...", ... } }`

**404:** matrícula no encontrada.

### POST /api/v1/matriculas

**Body (campos requeridos):**
```json
{
  "nombre_completo": "Juan Pérez",
  "numero_documento": "12345678",
  "tipo_documento": "CC",
  "programa": "Nombre del programa (debe existir en ajustes)",
  "sede": "Barrancabermeja|Aguachica|Virtual",
  "estado_estudiante": "Activo|Inactivo|Por Certificar|Certificado|Retirado|Suspendido|Todos",
  "horario": "Nombre del horario (de ajustes)",
  "semestre_actual": "I|II|Ninguno (curso)",
  "anio": "2024",
  "numero_grupo": "Nombre del grupo (de ajustes)"
}
```

Opcionales: `departamento`, `estado_civil`, `ocupacion`, `nivel_formacion`, `tiene_discapacidad`, `tipo_discapacidad`, `talla_uniforme`, `contraseña_plataforma`, `correo_gmail`, `telefono_personal`, etc. (ver modelo `Matricula`).

**201:**
```json
{ "data": { "id": 1, "cod_alumno": "12345678", ... }, "message": "Matrícula creada. Código: 12345678" }
```

**422:** validación (p. ej. `numero_documento` duplicado, `programa`/`horario`/`numero_grupo` no válidos).

### PUT/PATCH /api/v1/matriculas/{cod_alumno}

Mismos campos que store, todos opcionales (`sometimes`).

**200:** `{ "data": { ... }, "message": "Matrícula actualizada." }`  
**404:** no encontrada. **422:** validación.

### DELETE /api/v1/matriculas/{cod_alumno}

**200:** `{ "data": null, "message": "Eliminado." }`  
**422:** si tiene datos relacionados (abonos, otros ingresos, cuotas): `{ "error": { "code": "VALIDATION_ERROR", "details": { "confirmar_cascada": ["... Use ?confirmar_cascada=1 para eliminar en cascada."] } } }`. Reenviar con **`?confirmar_cascada=1`** para eliminar en cascada. **404:** no encontrada.

### POST /api/v1/matriculas/{cod_alumno}/foto

**Content-Type:** `multipart/form-data`. Campo **`foto`**: `required|image|mimes:jpeg,jpg,png,webp|max:2048` (KB).

**200:** `{ "data": { "url": "storage/students/...", "path": "students/...", "mime": "image/jpeg", "size": 12345 }, "message": "Foto subida." }`  
**422:** validación (foto requerida, no image, o >2MB). **404:** matrícula no encontrada.

### GET /api/v1/matriculas/{cod_alumno}/pdf

Devuelve **stream PDF** (ficha de matrícula):  
- **Content-Type:** `application/pdf`  
- **Content-Disposition:** `inline; filename="matricula-{cod_alumno}.pdf"`

**Consumir desde Next.js (guardar archivo):**
```bash
curl -o out.pdf -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/v1/matriculas/12345678/pdf"
```

**200:** cuerpo binario PDF. **404:** matrícula no encontrada. **500:** error en generación (JSON con `SERVER_ERROR` y `trace_id` si el Handler captura).

---

## Costs (cuotas/costos por matrícula)

Requiere `permission:access.core`. Un cost por `cod_alumno` (unique). Al crear se generan las cuotas (Purses).

### GET /api/v1/costs

**Query:** `?cod_alumno=...&per_page=15&page=1`

**200:** `{ "data": [ { "id", "cod_alumno", "valor_semestre", "numero_semestre", "valor_total_semestre", "descuento", "valor_neto", "saldo_financiar", "periodo", "numero_cuotas", "valor_cuotas", "fecha_pago", "detalles" } ], "meta": { "current_page", "per_page", "total", "last_page" } }`

### POST /api/v1/costs

**Body:** `cod_alumno` (req, exists:matriculas, unique:costs), `valor_semestre`, `numero_semestre`, `descuento`, `periodo`, `numero_cuotas`, `fecha_pago` (req). Opcionales/autocalculados: `valor_total_semestre`, `valor_neto`, `saldo_financiar`, `valor_cuotas`, `detalles`. Se auto-calculan si se omiten (igual que web).

**201:** `{ "data": { ... }, "message": "Costo creado." }`  
**422:** `cod_alumno` ya tiene costo, o no existe en matriculas.

### GET /api/v1/costs/{id}

**200:** `{ "data": { ... } }`  
**404:** no encontrado.

### PUT/PATCH /api/v1/costs/{id}

**Body:** `valor_semestre`, `numero_semestre`, `valor_total_semestre`, `descuento`, `valor_neto`, `saldo_financiar`, `periodo`, `numero_cuotas`, `valor_cuotas`, `fecha_pago`, `detalles` (todos opcionales). Al cambiar `numero_cuotas` o `fecha_pago` se recalculan las cuotas (Purses).

**200:** `{ "data": { ... }, "message": "Costo actualizado." }`  
**404/422:** estándar.

### DELETE /api/v1/costs/{id}

**200:** `{ "data": null, "message": "Eliminado." }`  
**422:** `{ "error": { "code": "VALIDATION_ERROR", "details": { "id": ["No se puede eliminar el costo: tiene N abonos y M otros ingresos asociados."] } } }` si hay Entry u OtherEntry.

---

## Consecutives (consecutivos de recibos)

Requiere `permission:access.core`. Tipos: `entry`, `discharge` (uno por tipo).

### GET /api/v1/consecutives

**200:** `{ "data": [ { "id", "type", "num_start", "num_current" } ] }` (lista, sin paginar).

### POST /api/v1/consecutives

**Body:** `type` (req, `entry`|`discharge`, unique), `num_start` (req, integer, min:0). Se crea con `num_current = num_start`.

**201:** `{ "data": { ... }, "message": "Consecutivo creado." }`  
**422:** ya existe consecutivo para ese `type`.

### GET /api/v1/consecutives/{id}

**200:** `{ "data": { "id", "type", "num_start", "num_current" } }`  
**404:** no encontrado.

### PUT/PATCH /api/v1/consecutives/{id}

**Body:** `num_start`, `num_current` (opcionales, integer, min:0).

**200:** `{ "data": { ... }, "message": "Consecutivo actualizado." }`

---

## Purses (cuotas por alumno)

Requiere `permission:access.core`. Solo lectura. `cod_alumno` se resuelve vía `Cost`.

### GET /api/v1/purses

**Query:** `cod_alumno` (obligatorio), `per_page`, `page`. Si falta `cod_alumno` → **422 VALIDATION_ERROR**.

**200:** `{ "data": [ { "id", "id_cost", "cod_alumno", "fecha_pago", "estado", "cuota", "abonado", "comentario" } ], "meta": { "current_page", "per_page", "total", "last_page" } }`. Si no hay cost para ese `cod_alumno`, `data` vacío.

### GET /api/v1/purses/{id}

**200:** `{ "data": { ... } }`  
**404:** no encontrado.

### GET /api/v1/purses/{id}/history

Historial de cambios del purse (HistoryPurse), paginado.

**200:** `{ "data": [ { "id", "id_purse", "fecha_pago", "estado", "cuota", "abonado", "comentario" } ], "meta": { ... } }`

---

## Entries (abonos / ingresos)

Requiere `permission:access.core`. Al crear (POST) se asigna `no_recibo` con consecutivo `entry` en transacción con `lockForUpdate()`. Si no existe consecutivo type=entry → **422** "Falta configurar el consecutivo de tipo 'entry'.". Cuando `concepto != 2` se consume número; si `concepto == 2` no se usa consecutivo. Si el cost no tenía cuotas, se crean (Purses). `forma`: Efectivo, Bancos o Consignación (Consignación se guarda como Bancos).

### GET /api/v1/entries

**Query:** `cod_alumno`, `id_cost`, `from` (YYYY-MM-DD), `to` (YYYY-MM-DD), `per_page`, `page` (todos opcionales).

**200:** `{ "data": [ { "id", "id_cost", "concepto", "descripcion", "no_recibo", "fecha_recibo", "valor", "elaborado_por", "debe", "haber", "forma" } ], "meta": { ... } }`

### POST /api/v1/entries

**Body:** `id_cost` (req, exists:costs), `concepto` (req, exists:conceptos), `descripcion`, `fecha_recibo`, `valor`, `elaborado_por` (exists:elaborados), `debe` (exists:debes), `haber` (exists:habers), `forma` (opcional, Efectivo|Bancos|Consignación). `no_recibo` se asigna automáticamente (consecutivo).

**201:** `{ "data": { ..., "no_recibo": 123 }, "message": "Abono creado." }`  
**422:** validación; o `"consecutive": ["Falta configurar el consecutivo de tipo \"entry\"..."]` si no existe.

### GET /api/v1/entries/{id}

**200:** `{ "data": { ... } }`  
**404:** no encontrado.

### DELETE /api/v1/entries/{id}

Revierte solo el registro (el web no revierte purse/history). **200:** `{ "data": null, "message": "Eliminado." }`  
**404:** no encontrado.

---

## Other-Entries (otros ingresos)

Requiere `permission:access.core`. Siempre usa consecutivo `entry` con `lockForUpdate()` para `no_recibo`. No afecta cartera (Purses). `concepto` en `otros_conceptos`.

### GET /api/v1/other-entries

**Query:** `cod_alumno`, `id_cost`, `from`, `to`, `per_page`, `page` (opcionales).

**200:** `{ "data": [ { "id", "id_cost", "concepto", "descripcion", "no_recibo", "fecha_recibo", "valor", "elaborado_por", "debe", "haber", "forma" } ], "meta": { ... } }`

### POST /api/v1/other-entries

**Body:** `id_cost`, `concepto` (exists:otros_conceptos), `descripcion`, `fecha_recibo`, `valor`, `elaborado_por`, `debe`, `haber`, `forma` (opcional). `no_recibo` se asigna automáticamente.

**201:** `{ "data": { ..., "no_recibo": 124 }, "message": "Otro ingreso creado." }`  
**422:** validación o consecutivo no configurado.

### GET /api/v1/other-entries/{id}

**200:** `{ "data": { ... } }`  
**404:** no encontrado.

### DELETE /api/v1/other-entries/{id}

**200:** `{ "data": null, "message": "Eliminado." }`  
**404:** no encontrado.

---

## Egresos: providers, discharge-concepts, discharges

Requiere `permission:access.core`. Los **discharges** (recibos de egreso) usan consecutivo `type=discharge` con `lockForUpdate()` al crear. Si no existe → **422** `details.consecutive`.

### Providers (proveedores de egreso)

- **GET /api/v1/providers** — listado paginado. **POST** body: `cedula` (opc), `nombre` (req), `direccion`, `telefono`. **GET /api/v1/providers/{id}**. **PUT/PATCH /api/v1/providers/{id}**. **DELETE /api/v1/providers/{id}**.

### Discharge-concepts (conceptos de egreso)

- **GET /api/v1/discharge-concepts** — listado. **POST** body: `nombre` (req), `descripcion`, `state`, `debe` (req, exists:debes), `haber` (req, exists:habers). **GET/PUT/DELETE /api/v1/discharge-concepts/{id}**.

### Discharges (recibos de egreso)

- **GET /api/v1/discharges** — query: `proveedor_id`, `from`, `to`, `per_page`. **POST** body: `fecha_recibo`, `proveedor_id`, `forma` (Efectivo|Bancos), `concepto` (exists:egreso_concepts; se usan su debe/haber), `descripcion`, `valor`, `elaborado_por`. `no_recibo` se asigna con consecutivo **discharge** (transacción+lock). **201** `data.no_recibo`. **422** si falta consecutivo discharge o concepto sin debe/haber. **GET /api/v1/discharges/{id}**. **DELETE /api/v1/discharges/{id}**.

---

## Terceros: third-entries, third-activities, third-receipts, concept-entry, concept-discharge

Requiere `permission:access.core`. **Third-receipts** usa consecutivo `type=entry` (lock) al crear. Solo se gestionan recibos `type=entry`.

### Third-entries (terceros)

- **GET /api/v1/third-entries**. **POST** body: `cedula` (req, unique), `nombre`, `direccion`, `telefono`, `actividad` (exists:third_activities), `mas`. **GET/PUT/DELETE /api/v1/third-entries/{id}**. **DELETE** 422 si tiene recibos.

### Third-activities (actividades de terceros)

- **GET /api/v1/third-activities**. **POST** body: `nombre`. **GET/PUT/DELETE /api/v1/third-activities/{id}**. **DELETE** 422 si hay third-entries con esa actividad.

### Third-receipts (recibos de terceros, type=entry)

- **GET /api/v1/third-receipts** — query: `third`, `from`, `to`. **POST** body: `third` (exists:third_entries), `concepto` (exists:concept_entry_receipts), `detalles`, `valor`, `debe`, `haber`, `elaborado_por`, `forma`, `fecha_recibo`. `no_recibo` se asigna con consecutivo **entry** (lock). **422** si falta consecutivo entry. **GET/DELETE /api/v1/third-receipts/{id}**.

### Concept-entry-receipts / concept-discharge-receipts

- **GET/POST/GET/{id}/PUT/DELETE /api/v1/concept-entry-receipts** — body: `name`, `debe`, `haber`, `state` (opc).  
- **GET/POST/GET/{id}/PUT/DELETE /api/v1/concept-discharge-receipts** — mismos campos. Usados por terceros (entry/discharge).

---

## Financial-receipts (datos y PDF por type+id)

Requiere `permission:access.core`. **type:** `entry` \| `other-entry` \| `egreso` \| `third`. **id:** id del recibo en su tabla.

### GET /api/v1/financial-receipts/{type}/{id}

Devuelve **JSON** con los datos del recibo (consecutivo, fecha, valor, concepto, descripción y campos por tipo: estudiante, proveedor, tercero, forma, etc.).

**200:** `{ "data": { "consecutivo", "fecha", "valor", "concepto", "descripcion", ... } }`. **404:** no encontrado. **422:** type inválido.

### GET /api/v1/financial-receipts/{type}/{id}/pdf

Stream **PDF** del recibo (formato térmico 80mm). Query: `?paper=76|80`, `?offset=8`.

- **Content-Type:** `application/pdf`  
- **Content-Disposition:** `inline; filename="financial-receipt-{type}-{id}.pdf"`

**Ejemplo curl (guardar PDF):**
```bash
curl -o out.pdf -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/v1/financial-receipts/egreso/1/pdf"
```

**200:** cuerpo PDF. **404:** recibo no encontrado. **422:** type inválido.

---

## Settings / Catálogos

Requiere `permission:settings.manage`. Rutas bajo `/api/v1/settings`. Recurso no permitido en whitelist → **404 NOT_FOUND**.

**Catálogos (whitelist):** `programs`, `schedules`, `groups`, `teachers`, `modules`, `conceptos`, `elaborados`, `habers`, `debes`, `otros-conceptos`.  
**Singleton:** `institution` (solo GET y PUT, sin `/{id}`).

### GET /api/v1/settings/{resource}

Lista paginada. **Query:** `?per_page=15&page=1`.

**200:** `{ "data": [ { "id", ... } ], "meta": { "current_page", "per_page", "total", "last_page" } }`  
**404:** `{ "error": { "code": "NOT_FOUND", "message": "Recurso no encontrado." } }` si `{resource}` no está en whitelist.

### POST /api/v1/settings/{resource}

**Body según recurso:**

| resource        | Campos (store)                                                                 |
|-----------------|---------------------------------------------------------------------------------|
| programs        | `name` (req), `code`, `active`                                                 |
| schedules       | `name` (req), `active`                                                         |
| groups          | `name` (req), `active`                                                         |
| teachers        | `name` (req), `active`                                                         |
| modules         | `name` (req), `code`, `active`                                                 |
| conceptos       | `nombre`, `estado`, `orderTable`, `consecutivo` (todos req)                    |
| elaborados      | `nombre`, `estado` (req)                                                       |
| habers          | `cuenta`, `nombre` (req)                                                       |
| debes           | `cuenta`, `nombre` (req)                                                       |
| otros-conceptos | `nombre`, `estado`, `debe` (id, exists:debes), `haber` (id, exists:habers) (req)|

**201:** `{ "data": { "id", ... }, "message": "Creado." }`  
**422:** `VALIDATION_ERROR` con `details` por campo. **404:** recurso no en whitelist.

### GET /api/v1/settings/{resource}/{id}

**200:** `{ "data": { "id", ... } }`  
**404:** recurso no en whitelist o id inexistente.

### PUT/PATCH /api/v1/settings/{resource}/{id}

Mismos campos que store, todos opcionales (`sometimes`/`nullable`).

**200:** `{ "data": { ... }, "message": "Actualizado." }`  
**422:** validación. **404:** recurso o id.

### DELETE /api/v1/settings/{resource}/{id}

**200:** `{ "data": null, "message": "Eliminado." }`  
**422:** `{ "error": { "code": "VALIDATION_ERROR", "message": "No se puede eliminar... porque está siendo usado..." } }` si está en uso.  
**404:** recurso o id.

### GET /api/v1/settings/institution

Configuración de institución (singleton). **200:** `{ "data": { "id", "name", "logo_path", "institucion_subtitulo", "sede", "nit", "address", "phone", "telefono2", "telefono3", "email", "website", "footer_licencia_texto", "footer_ciudad", "footer_mostrar_ubicacion_fecha", "footer_firma" } }`.

### PUT /api/v1/settings/institution

**Body:** todos opcionales: `name`, `logo_path`, `institucion_subtitulo`, `sede`, `nit`, `address`, `phone`, `telefono2`, `telefono3`, `email`, `website`, `footer_licencia_texto`, `footer_ciudad`, `footer_mostrar_ubicacion_fecha`, `footer_firma`. (Subida de archivo `logo` no expuesta en API; usar `logo_path` si se gestiona por otro medio.)

**200:** `{ "data": { ... }, "message": "Configuración actualizada." }`.

**Permiso `settings.manage`:** ejecutar `php artisan db:seed --class=RolePermissionSeeder` para crearlo y asignarlo a super-admin si no existe.

---

## Ejemplo en Next.js (fetch)

```ts
const API = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';

// Login y guardar token (ej. en estado, cookie o localStorage)
const res = await fetch(`${API}/auth/login`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  body: JSON.stringify({ email, password }),
});
const { data } = await res.json();
// data.token, data.user

// Peticiones autenticadas
const list = await fetch(`${API}/matriculas?per_page=10`, {
  headers: {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
  },
});
```

## CORS y .env

En el backend (Laravel) configurar:

- `FRONTEND_URL=http://localhost:3000` o la URL del Next.js en producción.
- `CORS_ALLOWED_ORIGINS` (opcional): si se usa, lista separada por comas; si no, se usa `FRONTEND_URL`.

Con Bearer no hace falta `supports_credentials` ni `sanctum/csrf-cookie` para estas rutas.

## Rate limiting

- **5/min por IP:** `login`, `register`, `forgot-password`, `reset-password`, `verify-email`, `resend-verification`.
- **60/min:** resto de la API (por usuario o IP).

## Tests

Ejecutar con MySQL (o MariaDB). Algunas migraciones usan SQL específico de MySQL (`MODIFY`/`CHANGE`) y fallan con SQLite:

```bash
DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test php artisan test tests/Feature/Api/V1/
```

## Variables de entorno recomendadas (Next.js)

Recomendado separar origin y versión para evitar duplicaciones:

- NEXT_PUBLIC_API_ORIGIN=http://localhost:8000
- NEXT_PUBLIC_API_PREFIX=/api/v1

Ejemplo:

```ts
const ORIGIN = process.env.NEXT_PUBLIC_API_ORIGIN ?? 'http://localhost:8000';
const PREFIX = process.env.NEXT_PUBLIC_API_PREFIX ?? '/api/v1';
export const API_BASE = `${ORIGIN}${PREFIX}`;
```

---

## Postman

En `docs/postman/` hay una colección y un environment listos para importar:

- **collection.json** — Todas las rutas API v1 agrupadas (Auth, Home, Maintenance, Attendance Sheet, Accounting, Consecutives, Matriculas, Costs, Purses, Entries, Other-Entries, Egresos, Terceros, Financial Receipts, Settings, Admin).
- **environment.json** — `base_url` (p. ej. `http://localhost:8000`), `token`, `cod_alumno`, `id`, `fecha_inicio`, `fecha_fin`, etc.

**Importar:** Postman → Import → subir `collection.json` y `environment.json` → elegir el environment "Financiera Intesa API v1".

**Primer request:** **Auth → Login**. Con email y contraseña correctos, la respuesta 200 incluye `data.token`; el test de la pestaña Tests lo guarda en la variable `token`. El resto de requests usan `Authorization: Bearer {{token}}`.

Ver `docs/postman/README.md` para variables, endpoints con PDF/XLSX ("Send and Download") y cómo regenerar la colección.
