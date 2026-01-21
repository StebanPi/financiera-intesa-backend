# Pruebas manuales (curl) — respuestas JSON estándar API

Base: `http://localhost:8000` (o tu `APP_URL`). Todas las peticiones a `/api/*` deben devolver JSON (nunca HTML ni redirect).

---

## CORS (preflight OPTIONS)

Comprobar que CORS está bien configurado para `/api/*`. La petición OPTIONS con `Origin` y `Access-Control-Request-Method` simula el preflight del navegador.

```bash
curl -i -X OPTIONS "http://localhost:8000/api/v1/home" \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET"
```

**Headers CORS esperados en la respuesta** (si `Origin: http://localhost:3000` está en `allowed_origins`, p. ej. por `FRONTEND_URL` o por defecto en local):

- **`Access-Control-Allow-Origin: http://localhost:3000`** — el origen debe coincidir con el enviado (nunca `*` en nuestra config).
- **`Access-Control-Allow-Methods`** — GET, POST, PUT, PATCH, DELETE, etc. (o el método solicitado).
- **`Access-Control-Allow-Headers`** — `*` o la lista que permita `Authorization`, `Content-Type`, etc.

En respuestas GET/POST que incluyan `Content-Disposition` (p. ej. descargas PDF/XLSX), debe aparecer también:

- **`Access-Control-Expose-Headers: Content-Disposition`** — para que el cliente pueda leer ese header.

En local con `FRONTEND_URL=http://localhost:3000` (o sin definirlo, que usa ese por defecto), el `Origin: http://localhost:3000` debe ser aceptado. Para probar con otro origen, definir `FRONTEND_URL` o `FRONTEND_URLS` en `.env` y usar ese valor en `-H "Origin: ..."`.

---

## Health (sin auth)

**GET /api/v1/health** — endpoint para infraestructura (load balancer, k8s, monitoreo). No requiere `Authorization`.

```bash
curl -i "http://localhost:8000/api/v1/health"
```

Esperado: `HTTP 200` y cuerpo:

```json
{
  "data": {
    "status": "ok",
    "server_time": "2025-01-15T12:00:00.000000Z",
    "app_env": "local"
  },
  "message": "OK"
}
```

**Security headers:** en la respuesta deben aparecer (y en el resto de rutas web y API):

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`

---

## 401 UNAUTHENTICATED

**Sin token en ruta protegida**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/auth/me" \
  -H "Accept: application/json"
```

Esperado: `HTTP 401` y cuerpo:

```json
{
  "error": {
    "code": "UNAUTHENTICATED",
    "message": "No autenticado.",
    "details": null
  }
}
```

**Token inválido o expirado**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/matriculas" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer token-invalido"
```

Esperado: `HTTP 401` y cuerpo con `"error": { "code": "UNAUTHENTICATED", "message": "No autenticado.", "details": null }` (mismo formato que «Sin token»).

---

## 403 FORBIDDEN

Usuario autenticado **sin** el permiso `users.manage` llamando a `/admin/users` (p. ej. rol `secretaria` que solo tiene `access.core`).

1. Obtener token con ese usuario:
   ```bash
   curl -s -X POST "http://localhost:8000/api/v1/auth/login" \
     -H "Accept: application/json" -H "Content-Type: application/json" \
     -d '{"email":"secretaria@ejemplo.com","password":"password"}'
   ```
2. Usar el `data.token` en:

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer AQUI_EL_TOKEN"
```

Esperado: `HTTP 403` y cuerpo:

```json
{
  "error": {
    "code": "FORBIDDEN",
    "message": "No tienes permiso para acceder a esta sección.",
    "details": null
  }
}
```

---

## 422 VALIDATION_ERROR

**Login sin email**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"password":"abc"}'
```

Esperado: `HTTP 422` y cuerpo con `"code": "VALIDATION_ERROR"` y `"details"` con errores por campo, por ejemplo:

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Los datos enviados no son válidos.",
    "details": {
      "email": ["El campo email es obligatorio."]
    }
  }
}
```

**Matrícula con campos requeridos faltantes**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/matriculas" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_VALIDO_ACCESS_CORE" \
  -d '{"nombre_completo":"X"}'
```

Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"` y `"details"` con los campos que fallan.

---

## 404 NOT_FOUND

**Matrícula inexistente** (ModelNotFoundException)

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/matriculas/CODIGO_QUE_NO_EXISTE" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_VALIDO"
```

Esperado: `HTTP 404`:

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Recurso no encontrado.",
    "details": null
  }
}
```

**Ruta no definida** (NotFoundHttpException)

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/ruta-inexistente" \
  -H "Accept: application/json"
```

Esperado: `HTTP 404`, `"code": "NOT_FOUND"`.

---

## Inicio (Home)

Requiere `Authorization: Bearer TOKEN` con `permission:access.core`.

### GET /api/v1/home — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/home" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": { "message": "Bienvenido", "server_time": "..." }`.

---

## Mantenimiento (Maintenance)

Requiere `Authorization: Bearer TOKEN` con `permission:access.core`.

### GET /api/v1/maintenance — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/maintenance" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": { "stats": { "entries", "other_entries", "costs", "matriculas", "purses", "history_purses", "third_receipts", "egreso_receipts", "egreso_providers", "third_entries", "third_activities", "cash_bases", "initial_balances" } }`, `"message": "OK"`.

---

## Planilla de Asistencia

Requiere `Authorization: Bearer TOKEN` con `permission:access.core`. Sustituir los IDs por `program_id`, `schedule_id`, `group_id`, `teacher_id`, `module_id` existentes (programs, schedules, groups, teachers, modules).

### POST /api/v1/attendance-sheet/generate — 200 (stream PDF)

Guardar PDF en `out.pdf`:

```bash
curl -o out.pdf -w "HTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/attendance-sheet/generate" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"program_id":1,"schedule_id":1,"group_id":1,"teacher_id":1,"module_id":1,"fecha_inicio":"2024-01-01","fecha_final":"2024-01-31","fecha_clase":"2024-01-15"}'
```

Esperado: `HTTP 200`, contenido binario PDF en `out.pdf`. Cabeceras: `Content-Type: application/pdf`, `Content-Disposition: inline; filename="planilla_asistencia_....pdf"`.

### POST /api/v1/attendance-sheet/generate — 422 (validación)

**fecha_clase fuera de rango:**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/attendance-sheet/generate" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"program_id":1,"schedule_id":1,"group_id":1,"teacher_id":1,"module_id":1,"fecha_inicio":"2024-01-01","fecha_final":"2024-01-31","fecha_clase":"2024-02-15"}'
```

Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details": { "fecha_clase": ["La fecha de clase debe estar dentro del rango de fechas de inicio y final."] }`.

**Campos requeridos faltantes:**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/attendance-sheet/generate" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{}'
```

Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details"` con errores por cada campo requerido.

---

## Contabilidad (JSON)

Requiere `Authorization: Bearer TOKEN` con `permission:access.core`. Sustituir `TOKEN` y fechas por valores válidos.

### GET /api/v1/accounting — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/accounting" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": { "today_base": { "fecha", "base_efectivo", "base_banco" } o null }`, `"message": "OK"`.

### GET /api/v1/accounting/abonos — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/abonos?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": { "rows": [...], "totals": { "total", "total_rows", "is_partial" }, "params": { "fecha_inicio", "fecha_fin" } }`.

### GET /api/v1/accounting/otros-ingresos — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/otros-ingresos?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

### GET /api/v1/accounting/total-ingresos — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/total-ingresos?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

### GET /api/v1/accounting/egresos — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/egresos?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

### GET /api/v1/accounting/arqueo-diario — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/arqueo-diario?fecha=2024-01-15" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": { "rows", "totals", "params" }`. Si faltan bases diarias: `HTTP 422`, `"details": { "missing_dates": [...] }`.

### GET /api/v1/accounting/informe-semanal — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/informe-semanal?fecha=2024-01-15" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

### GET /api/v1/accounting/informe-mensual — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/informe-mensual?month_year=2024-01" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

Alternativa: `?mes=1&anio=2024`. Si falta base inicial: `HTTP 422`, `"code": "VALIDATION_ERROR"`.

### GET /api/v1/accounting/abonos — 422 (solo fecha_inicio)

```bash
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/abonos?fecha_inicio=2024-01-01" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details": { "fecha_fin": ["fecha_fin es obligatorio cuando se envía fecha_inicio."] }`.

---

### Contabilidad: downloads Excel (stream xlsx)

Requiere `Authorization: Bearer TOKEN` con `permission:access.core`. Respuesta: archivo xlsx. Cabeceras: `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `Content-Disposition: attachment; filename="<reporte>_<rango>.xlsx"`. Si falla: 422 JSON (missing_dates, base inicial, params) o 500 con trace_id.

#### GET /api/v1/accounting/abonos/download — 200

```bash
curl -L -o abonos.xlsx -w "HTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/abonos/download?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, archivo `abonos.xlsx`. Sin `fecha_inicio` o `fecha_fin`: `HTTP 422`.

#### GET /api/v1/accounting/arqueo-diario/download — 200

```bash
curl -L -o arqueo.xlsx -w "HTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/arqueo-diario/download?fecha=2024-01-15" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, archivo `arqueo.xlsx`. Si faltan bases diarias: `HTTP 422`, `"details": { "missing_dates": ["2024-01-15", ...] }`.

#### GET /api/v1/accounting/informe-mensual/download — 200

```bash
curl -L -o informe.xlsx -w "HTTP %{http_code}\n" "http://localhost:8000/api/v1/accounting/informe-mensual/download?month_year=2024-01" \
  -H "Authorization: Bearer TOKEN"
```

Alternativa: `?mes=1&anio=2024`. Si falta base inicial: `HTTP 422`, `"code": "VALIDATION_ERROR"`.

Otros: `otros-ingresos/download`, `total-ingresos/download`, `egresos/download` (fecha_inicio, fecha_fin); `informe-semanal/download` (fecha). Mismo patrón.

---

## Matrículas: update, delete, foto, pdf

Requiere `Authorization: Bearer TOKEN` con `permission:access.core`. Sustituir `COD_ALUMNO` por un `cod_alumno` existente.

### PUT /api/v1/matriculas/{cod_alumno} — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" -X PUT "http://localhost:8000/api/v1/matriculas/COD_ALUMNO" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"nombre_completo":"Nombre Actualizado","estado_estudiante":"Activo"}'
```

Esperado: `HTTP 200`, `"data": { ... }`, `"message": "Matrícula actualizada."`.

### DELETE /api/v1/matriculas/{cod_alumno} — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/matriculas/COD_ALUMNO" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": null`, `"message": "Eliminado."`.  
Si tiene datos relacionados: `HTTP 422`; repetir con `?confirmar_cascada=1` para eliminar en cascada:

```bash
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/matriculas/COD_ALUMNO?confirmar_cascada=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

### POST /api/v1/matriculas/{cod_alumno}/foto — 200

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/matriculas/COD_ALUMNO/foto" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -F "foto=@/ruta/a/una-imagen.jpg"
```

Esperado: `HTTP 200`, `"data": { "url", "path", "mime", "size" }`, `"message": "Foto subida."`.

### POST /api/v1/matriculas/{cod_alumno}/foto — 422 (sin foto)

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/matriculas/COD_ALUMNO/foto" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details": { "foto": ["..."] }`.

### GET /api/v1/matriculas/{cod_alumno}/pdf — 200 (stream)

Guardar PDF en `out.pdf`:

```bash
curl -o out.pdf -w "HTTP %{http_code}\n" "http://localhost:8000/api/v1/matriculas/COD_ALUMNO/pdf" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, contenido binario PDF en `out.pdf`. Cabeceras: `Content-Type: application/pdf`, `Content-Disposition: inline; filename="matricula-COD_ALUMNO.pdf"`.

---

## Costs

Requiere `permission:access.core`. Sustituir `COD_ALUMNO` por un `cod_alumno` existente en matriculas.

### POST /api/v1/costs — crear

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/costs" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"cod_alumno":"COD_ALUMNO","valor_semestre":1000000,"numero_semestre":1,"descuento":0,"periodo":"2024-1","numero_cuotas":6,"fecha_pago":"2024-01-15"}'
```

Esperado: `HTTP 201`, `"data": { "id", "cod_alumno", "valor_semestre", ... }`, `"message": "Costo creado."`. **422** si `cod_alumno` ya tiene costo o no existe en matriculas.

### GET /api/v1/costs?cod_alumno=... — listar por cod_alumno

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/costs?cod_alumno=COD_ALUMNO&per_page=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": [ ... ]`, `"meta": { "current_page", "per_page", "total", "last_page" }`.

### PUT /api/v1/costs/{id} — actualizar

```bash
curl -s -w "\nHTTP %{http_code}\n" -X PUT "http://localhost:8000/api/v1/costs/1" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"numero_cuotas":12,"valor_cuotas":150000}'
```

Esperado: `HTTP 200`, `"data": { ... }`, `"message": "Costo actualizado."`.

### DELETE /api/v1/costs/{id} — 200 y 422

**200** (sin abonos/otros ingresos):
```bash
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/costs/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```
Esperado: `HTTP 200`, `"message": "Eliminado."`.

**422** (con abonos u otros ingresos):
```bash
# Usar id de un cost que tenga Entry o OtherEntry
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/costs/ID_CON_ABONOS" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```
Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details": { "id": ["No se puede eliminar el costo: tiene N abonos y M otros ingresos asociados."] }`.

---

## Consecutives

Requiere `permission:access.core`.

### GET /api/v1/consecutives — listar

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/consecutives" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": [ { "id", "type", "num_start", "num_current" }, ... ]`.

### GET /api/v1/consecutives/{id}

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/consecutives/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": { "id", "type", "num_start", "num_current" }`.

### PUT /api/v1/consecutives/{id} — actualizar

```bash
curl -s -w "\nHTTP %{http_code}\n" -X PUT "http://localhost:8000/api/v1/consecutives/1" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"num_start":100,"num_current":105}'
```

Esperado: `HTTP 200`, `"data": { ... }`, `"message": "Consecutivo actualizado."`.

---

## Purses

Requiere `permission:access.core`. `cod_alumno` obligatorio en index (422 si falta).

### GET /api/v1/purses?cod_alumno=COD — listar por alumno

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/purses?cod_alumno=COD_ALUMNO&per_page=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": [ { "id", "id_cost", "cod_alumno", "fecha_pago", "estado", "cuota", "abonado", "comentario" } ]`, `"meta"`. **422** si se omite `cod_alumno`.

### GET /api/v1/purses/{id}/history

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/purses/1/history?per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"data": [ { "id", "id_purse", "fecha_pago", "estado", "cuota", "abonado", "comentario" } ]`, `"meta"`.

---

## Entries (abonos)

Requiere `permission:access.core`. Al crear se asigna `no_recibo` con consecutivo (lockForUpdate). Asegurar que exista consecutivo type=entry (GET/PUT /consecutives).

### POST /api/v1/entries — crear (asigna no_recibo incremental)

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/entries" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"id_cost":1,"concepto":1,"descripcion":"Abono matrícula","fecha_recibo":"2024-01-15","valor":500000,"elaborado_por":1,"debe":1,"haber":1,"forma":"Efectivo"}'
```

Esperado: `HTTP 201`, `"data": { "id", "no_recibo": N, ... }`, `"message": "Abono creado."`. **422** si falta consecutivo: `"consecutive": ["Falta configurar el consecutivo de tipo \"entry\"..."]`.

### DELETE /api/v1/entries/{id} — eliminar

```bash
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/entries/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"message": "Eliminado."`.

---

## Other-Entries (otros ingresos)

Requiere `permission:access.core`. También asigna `no_recibo` con consecutivo type=entry (lockForUpdate).

### POST /api/v1/other-entries — crear

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/other-entries" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"id_cost":1,"concepto":1,"descripcion":"Otro ingreso","fecha_recibo":"2024-01-15","valor":100000,"elaborado_por":1,"debe":1,"haber":1,"forma":"Efectivo"}'
```

Esperado: `HTTP 201`, `"data": { "id", "no_recibo": N, ... }`, `"message": "Otro ingreso creado."`. **422** si no existe consecutivo type=entry.

### DELETE /api/v1/other-entries/{id}

```bash
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/other-entries/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

Esperado: `HTTP 200`, `"message": "Eliminado."`.

---

## Egresos: providers, discharge-concepts, discharges

### Providers — CRUD

```bash
# POST crear
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/providers" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"nombre":"Proveedor API","cedula":"123","direccion":"Calle 1","telefono":"3001111"}'
# 201, "Proveedor creado."

# GET listar
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/providers?per_page=5" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"

# PUT actualizar
curl -s -w "\nHTTP %{http_code}\n" -X PUT "http://localhost:8000/api/v1/providers/1" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"nombre":"Proveedor Actualizado"}'

# DELETE
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/providers/1" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

### Discharges — crear (no_recibo incremental, consecutivo discharge)

Configurar antes consecutivo type=discharge (GET/PUT /consecutives). Tener al menos: 1 provider, 1 discharge-concept con debe/haber, 1 elaborado.

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/discharges" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"fecha_recibo":"2024-01-20","proveedor_id":1,"forma":"Efectivo","concepto":1,"descripcion":"Pago","valor":100000,"elaborado_por":1}'
```

Esperado: **201** `"data": { "no_recibo": N, ... }`, `"message": "Egreso creado."`.

**422 cuando falta consecutivo discharge:** si no existe consecutivo type=discharge:

```json
{ "error": { "code": "VALIDATION_ERROR", "details": { "consecutive": ["Falta configurar el consecutivo de tipo \"discharge\"..."] } } }
```

### Financial-receipts — show y PDF

```bash
# JSON (type: entry|other-entry|egreso|third)
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/financial-receipts/egreso/1" \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"

# PDF stream (guardar)
curl -o fr.pdf -H "Authorization: Bearer TOKEN" "http://localhost:8000/api/v1/financial-receipts/egreso/1/pdf"
```

Esperado: **200** JSON con `consecutivo`, `fecha`, `valor`, `concepto`, etc.; o PDF binario con `Content-Type: application/pdf`.

---

## 429 TOO_MANY_REQUESTS

`POST /api/v1/auth/login` tiene `throttle:5,1` (5 por minuto). Hacer **6 o más** peticiones en menos de 1 minuto:

```bash
for i in {1..7}; do
  curl -s -w " -> %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/login" \
    -H "Accept: application/json" -H "Content-Type: application/json" \
    -d '{"email":"a@a.com","password":"x"}'
done
```

Esperado: las primeras 5 con 401 o 422; a partir de la 6.ª, `HTTP 429`:

```json
{
  "error": {
    "code": "TOO_MANY_REQUESTS",
    "message": "Demasiadas peticiones.",
    "details": null
  }
}
```

---

## 500 SERVER_ERROR y trace_id

Provocar un error interno (solo en entorno de pruebas). Opciones:

**A) Ruta de prueba que lance**

En `routes/api.php` (solo para pruebas, quitar después):

```php
Route::get('v1/test-500', fn () => throw new \Exception('test 500'));
```

Luego:

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/test-500" \
  -H "Accept: application/json"
```

**B) Pasar X-Request-Id**

Si se provoca un 500 de cualquier forma, enviando cabecera para comprobar que se reutiliza como `trace_id`:

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/test-500" \
  -H "Accept: application/json" \
  -H "X-Request-Id: mi-id-123"
```

Esperado: `HTTP 500` y cuerpo con `trace_id` y `code`:

```json
{
  "error": {
    "code": "SERVER_ERROR",
    "message": "test 500",
    "details": { "exception": "...", "file": "...", "line": ... },
    "trace_id": "mi-id-123"
  }
}
```

Con `APP_DEBUG=false`, `message` genérico y `details` `null`; `trace_id` debe seguir presente.

---

## Auth: register, forgot, reset, verify, resend

### POST /api/v1/auth/register — 201

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/register" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Nuevo User","email":"nuevo@ejemplo.com","password":"Password1!","password_confirmation":"Password1!"}'
```

Esperado: `HTTP 201`, `"data": { "token": "...", "user": { ... } }`, `"message": "Usuario registrado."`

### POST /api/v1/auth/register — 422 (email duplicado)

```bash
# Registrar una vez, luego repetir con el mismo email
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/register" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"X","email":"existente@ejemplo.com","password":"Password1!","password_confirmation":"Password1!"}'
```

Esperado (si `existente@ejemplo.com` ya existe): `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details": { "email": ["..."] }`

---

### POST /api/v1/auth/forgot-password — 200 siempre

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/forgot-password" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"cualquiera@ejemplo.com"}'
```

Esperado: `HTTP 200`, `"message": "Si el correo existe, recibirás un enlace..."` (mismo cuerpo aunque el email no exista).

---

### POST /api/v1/auth/reset-password — 200 y 422

**200** (token y email válidos, desde el enlace del correo de reset):

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/reset-password" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"token":"TOKEN_DEL_EMAIL","email":"user@ejemplo.com","password":"NuevaPass1!","password_confirmation":"NuevaPass1!"}'
```

Esperado: `HTTP 200`, `"message": "Contraseña restablecida correctamente."`

**422** (token inválido o expirado):

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/reset-password" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"token":"token-invalido","email":"a@a.com","password":"X","password_confirmation":"X"}'
```

Esperado: `HTTP 422`, `"code": "VALIDATION_ERROR"`, `"details": { "email": ["..."] }`

---

### GET /api/v1/auth/verify-email

Requiere URL firmada: `?id=1&hash=xxx&expires=...&signature=...`. Se obtiene del correo de verificación o generando con:

`URL::temporarySignedRoute('api.verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => hash('sha1', $user->email)])`

```bash
# Sustituir por la URL firmada real (desde el email o tinker)
curl -s -w "\nHTTP %{http_code}\n" "http://localhost:8000/api/v1/auth/verify-email?id=1&hash=XXX&expires=XXX&signature=XXX" \
  -H "Accept: application/json"
```

Esperado: `200` y `"message": "Correo verificado correctamente."` (o "El correo ya estaba verificado."). Sin `MustVerifyEmail` en `User`: `400`, `"code": "FEATURE_DISABLED"`.

---

### POST /api/v1/auth/resend-verification — 200

Requiere `Authorization: Bearer {token}`.

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/auth/resend-verification" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN"
```

Esperado: `200`, `"message": "Se ha enviado un nuevo enlace de verificación."` o "El correo ya está verificado."  
Sin `MustVerifyEmail` en `User`: `400`, `"code": "FEATURE_DISABLED"`.

---

## Admin: roles y permisos

Requiere **`permission:roles.manage`** (p. ej. usuario con rol `super-admin`). Obtener token:

```bash
curl -s -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"superadmin@ejemplo.com","password":"password"}'
# Usar data.token como TOKEN_ROLES en los ejemplos siguientes.
```

Todos estos endpoints devuelven **401** (sin token), **403** (sin `roles.manage`) o **422** (validación) con el formato estándar `{ "error": { "code": "...", "message": "...", "details": ... } }`.

### GET /api/v1/admin/permissions — listar permisos

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/admin/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_ROLES"
```

Esperado: `HTTP 200`, `"data": [ { "id", "name", "slug", "description" }, ... ]`.

### GET /api/v1/admin/roles — listar roles

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/admin/roles" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_ROLES"
```

Esperado: `HTTP 200`, `"data": [ { "id", "name", "slug", "description", "permissions": [...] }, ... ]`.

### POST /api/v1/admin/roles — crear rol

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/admin/roles" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_ROLES" \
  -d '{"name":"Rol de prueba","slug":"rol-prueba","description":"Solo para tests"}'
```

Esperado: `HTTP 201`, `"data": { "id", "name", "slug", "description", "permissions": [] }`, `"message": "Rol creado."`.  
**422** si `slug` o `name` duplicados: `"code": "VALIDATION_ERROR"`, `"details": { "slug": ["Ya existe un rol con ese slug."] }`.

### POST /api/v1/admin/roles/{id}/permissions — sync permisos al rol

```bash
# Asignar access.core y roles.manage al rol id=5 (ajustar id y slugs según tu BD)
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/admin/roles/5/permissions" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_ROLES" \
  -d '{"permissions":["access.core","roles.manage"]}'
```

Esperado: `HTTP 200`, `"data": { "id", "name", "slug", "permissions": [...] }`, `"message": "Permisos del rol sincronizados."`.  
**422** si algún slug no existe: `"details": { "permissions.0": ["Uno o más permisos no existen."] }` (o similar).

### POST /api/v1/admin/users/{id}/roles — sync roles al usuario

```bash
# Asignar secretaria y contador al usuario id=2 (ajustar id y slugs según tu BD)
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/admin/users/2/roles" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_ROLES" \
  -d '{"roles":["secretaria","contador"]}'
```

Esperado: `HTTP 200`, `"data": { "id", "name", "email", "roles": [...], "permissions": [...] }`, `"message": "Roles del usuario sincronizados."`.  
**422** si algún slug no existe. **404** si el usuario no existe.

### 403 en /admin/roles sin roles.manage

Usuario con `users.manage` pero sin `roles.manage` (p. ej. `admin` por defecto):

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/admin/roles" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_SOLO_USERS_MANAGE"
```

Esperado: `HTTP 403`, `"error": { "code": "FORBIDDEN", "message": "No tienes permiso para acceder a esta sección.", "details": null }`.

---

## Settings / Catálogos

Requiere **`permission:settings.manage`**. Crear permiso si falta: `php artisan db:seed --class=RolePermissionSeeder`. Obtener token con usuario que tenga `settings.manage` (p. ej. super-admin):

```bash
curl -s -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"superadmin@ejemplo.com","password":"password"}'
# Usar data.token como TOKEN_SETTINGS.
```

Recursos en whitelist: `programs`, `schedules`, `groups`, `teachers`, `modules`, `conceptos`, `elaborados`, `habers`, `debes`, `otros-conceptos`. Recurso inexistente → **404** `"Recurso no encontrado."`.

### GET /api/v1/settings/programs — listar un catálogo

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/settings/programs?per_page=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS"
```

Esperado: `HTTP 200`, `"data": [ { "id", "name", "code", "active" }, ... ]`, `"meta": { "current_page", "per_page", "total", "last_page" }`.

### POST /api/v1/settings/programs — crear

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/settings/programs" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS" \
  -d '{"name":"Programa Test API","code":"PT-01","active":true}'
```

Esperado: `HTTP 201`, `"data": { "id", "name", "code", "active" }`, `"message": "Creado."`.

### 422 por campo requerido (POST programs sin name)

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/api/v1/settings/programs" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS" \
  -d '{"code":"X"}'
```

Esperado: `HTTP 422`, `"error": { "code": "VALIDATION_ERROR", "message": "Los datos enviados no son válidos.", "details": { "name": ["The name field is required."] } }` (o mensaje equivalente).

### PUT /api/v1/settings/programs/{id} — actualizar

```bash
curl -s -w "\nHTTP %{http_code}\n" -X PUT "http://localhost:8000/api/v1/settings/programs/1" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS" \
  -d '{"name":"Programa Actualizado","active":false}'
```

Esperado: `HTTP 200`, `"data": { "id", "name", "code", "active" }`, `"message": "Actualizado."`.

### DELETE /api/v1/settings/programs/{id} — eliminar

```bash
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "http://localhost:8000/api/v1/settings/programs/99" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS"
```

Esperado: `HTTP 200`, `"data": null`, `"message": "Eliminado."` (o **404** si id no existe). Si el programa está en uso en matrículas: **422** `"No se puede eliminar el programa porque está siendo usado en matrículas."`.

### 403 sin settings.manage

Usuario con token pero sin `settings.manage` (p. ej. `secretaria`):

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/settings/programs" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_SIN_SETTINGS_MANAGE"
```

Esperado: `HTTP 403`, `"error": { "code": "FORBIDDEN", "message": "No tienes permiso para acceder a esta sección.", "details": null }`.

### 404 recurso no en whitelist

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/settings/recurso-inventado" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS"
```

Esperado: `HTTP 404`, `"error": { "code": "NOT_FOUND", "message": "Recurso no encontrado.", "details": null }`.

### GET /api/v1/settings/institution

```bash
curl -s -w "\nHTTP %{http_code}\n" -X GET "http://localhost:8000/api/v1/settings/institution" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_SETTINGS"
```

Esperado: `HTTP 200`, `"data": { "id", "name", "logo_path", "institucion_subtitulo", ... }`.

---

## Comprobar que web no se rompe

**Login web (debe seguir haciendo redirect, no JSON)**

```bash
curl -s -w "\nHTTP %{http_code}\n" -X POST "http://localhost:8000/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"a@a.com","password":"x"}' \
  -L
```

Esperado: 302/200 y HTML (o redirect a login), **no** un JSON `{ "error": { "code": "VALIDATION_ERROR", ... } }` como si fuera API.  
(Si se envía `Accept: application/json` a `/login`, Laravel puede tratar la petición como JSON; en uso normal el navegador no envía ese header y se mantiene el flujo web.)

---

## Resumen de códigos

| HTTP | code              | Origen típico                          |
|------|-------------------|----------------------------------------|
| 401  | UNAUTHENTICATED   | Sin token, token inválido, AuthenticationException |
| 403  | FORBIDDEN         | Sin permiso/rol, AuthorizationException o abort(403) |
| 404  | NOT_FOUND         | ModelNotFoundException, NotFoundHttpException       |
| 422  | VALIDATION_ERROR  | ValidationException, `details` por campo            |
| 429  | TOO_MANY_REQUESTS | TooManyRequestsHttpException (throttle)             |
| 500  | SERVER_ERROR      | Cualquier Throwable no manejada, con `trace_id`     |
