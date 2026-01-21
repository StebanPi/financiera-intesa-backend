# Checklist de deploy — API v1

Checklist práctico para poner en producción (o staging) el backend Laravel con API v1.

**📘 Guía de producción:** Para la guía completa de *cómo subir y dejar funcional* en producción (servidor, Nginx, MySQL, SSL, pasos de instalación), ver `docs/GUIA_PRODUCCION.md`.

**📘 Runbook de staging:** Para pasos concretos y comandos exactos en staging, ver `docs/STAGING_RUNBOOK.md`.

**⚡ Automatización:** Puedes usar `composer run post-deploy` para ejecutar los pasos principales automáticamente:
- `php artisan app:release-check`
- `php artisan migrate --force`
- `php artisan storage:link`
- `php artisan optimize`

---

## 0) Validación pre-deploy

- [ ] **Ejecutar validación:** `php artisan app:release-check`
  - Verifica: APP_ENV, APP_DEBUG, APP_KEY, DB conectividad, storage paths, public/storage symlink, health endpoint.
  - Si algún check falla (FAIL), corregir antes de continuar.
  - Si hay warnings (APP_ENV=local, symlink faltante), revisar según el entorno.
  - El comando devuelve exit code 1 si hay errores críticos.

**Nota:** Ejecutar este comando antes de cualquier otro paso del checklist para detectar problemas temprano.

---

## A) App / env

- [ ] **APP_ENV=production** (o `staging` según el entorno).
- [ ] **APP_DEBUG=false** en producción.
- [ ] **APP_KEY** definido (`php artisan key:generate` si se crea desde cero).
- [ ] **LOG_CHANNEL** acorde al entorno (p. ej. `stack` o `daily`); en `config/logging.php`.
- [ ] **storage/logs** con permisos de escritura para el usuario con el que corre el servidor web/PHP.

---

## B) Base de datos

- [ ] **Migraciones:** `php artisan migrate --force` (en producción `--force` evita el prompt).
- [ ] **Seeders:**
  - **RolePermissionSeeder:** solo en entornos **nuevos** (crea roles y permisos). No re-ejecutar en BD ya sembrada para no duplicar.
  - En BD existente con datos: no correr seeders de negocio (AcademicCatalogSeeder, etc.) salvo que se indique para ese despliegue.

---

## C) Storage

- [ ] **Enlace simbólico:** `php artisan storage:link` (enlaza `public/storage` → `storage/app/public`).
- [ ] **Permisos:** `storage/` y `bootstrap/cache/` escribibles por el servidor web.
- [ ] **Foto matrícula:**  
  - Confirmar que `POST /api/v1/matriculas/{cod}/foto` (form-data, clave `foto`) guarda en `storage/app/public` y que `Storage::url()` resuelve bien (p. ej. `/storage/students/...` servido por el web server). Revisar configuración de `filesystems` y que `public/storage` exista tras `storage:link`.

---

## D) PDF y XLSX

### PDF (stream)

- [ ] **GET /api/v1/matriculas/{cod_alumno}/pdf** — ficha de matrícula.  
  - `Content-Type: application/pdf`, `Content-Disposition: inline; filename="matricula-{cod}.pdf"`.
- [ ] **GET /api/v1/financial-receipts/{type}/{id}/pdf** — recibo financiero (`type`: `entry`, `other-entry`, `egreso`, `third`).  
  - `Content-Type: application/pdf`, `Content-Disposition: inline`.
- [ ] **POST /api/v1/attendance-sheet/generate** — planilla de asistencia.  
  - Body: `program_id`, `schedule_id`, `group_id`, `teacher_id`, `module_id`, `fecha_inicio`, `fecha_final`, `fecha_clase`.  
  - `Content-Type: application/pdf`, `Content-Disposition: inline`.

### XLSX (download)

- [ ] **GET /api/v1/accounting/abonos/download?fecha_inicio=...&fecha_fin=...**
- [ ] **GET /api/v1/accounting/otros-ingresos/download**, **total-ingresos/download**, **egresos/download**
- [ ] **GET /api/v1/accounting/arqueo-diario/download?fecha=...**, **informe-semanal/download?fecha=...**, **informe-mensual/download?month_year=...**

Cabeceras esperadas:  
- `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`  
- `Content-Disposition: attachment; filename="<reporte>_<rango>.xlsx"`

---

## E) Auth y seguridad

- [ ] **Sanctum:** tokens Bearer funcionando (`Authorization: Bearer {token}`). En API stateless no se usa `EnsureFrontendRequestsAreStateful`; la autenticación es por token.
- [ ] **Rate limits:** login (y register, forgot-password, reset-password, verify-email) con throttle 5/min; el resto con `throttle:api` (p. ej. 60/min).
- [ ] **401 en /api/\*:** sin token o token inválido debe devolver `{"error":{"code":"UNAUTHENTICATED","message":"No autenticado.","details":null}}` con 401 (formato unificado vía `ApiResponse::error`).

### Mantenimiento de tokens

- [ ] **Limpiar tokens viejos:** ejecutar periódicamente `php artisan sanctum:prune-tokens --days=30` para borrar tokens antiguos de `personal_access_tokens`.
  - Borra tokens creados hace más de N días (default: 30).
  - Imprime cuántos tokens fueron borrados.
  - **Recomendación:** ejecutar manualmente cada 1-3 meses o configurar un cron job:
    ```bash
    # Ejemplo cron: limpiar tokens de más de 30 días cada domingo a las 2 AM
    0 2 * * 0 cd /path/to/project && php artisan sanctum:prune-tokens --days=30 >> /dev/null 2>&1
    ```
  - Útil para mantener la tabla `personal_access_tokens` limpia y evitar acumulación de tokens no utilizados.

---

## F) Postman (QA rápido)

- [ ] Importar **docs/postman/collection.json** y **docs/postman/environment.json**.
- [ ] Seleccionar el environment y definir `base_url` (p. ej. `https://api.ejemplo.com`).
- [ ] **Auth/Login** con usuario válido → comprobar que el test guarda `token` en el environment.
- [ ] Probar:
  1. **1 JSON:** p. ej. `GET /api/v1/home` con `Authorization: Bearer {{token}}` → 200 y `data.message`.
  2. **1 PDF:** p. ej. `GET /api/v1/matriculas/{cod_alumno}/pdf` → 200, binario PDF.
  3. **1 XLSX:** p. ej. `GET /api/v1/accounting/abonos/download?fecha_inicio=...&fecha_fin=...` → 200, descarga `.xlsx`.

---

## G) Observabilidad

- [ ] **X-Request-Id en respuestas:** todas las respuestas HTTP incluyen el header `X-Request-Id` (UUID único por request).
  - Si el cliente envía `X-Request-Id`, se usa ese valor.
  - Si no se envía, se genera un UUID automáticamente.
  - El mismo ID aparece en `error.trace_id` en respuestas 500.
- [ ] **trace_id en 500:** las respuestas 500 de la API incluyen `error.trace_id` (mismo valor que `X-Request-Id` de la respuesta).
- [ ] **En logs:** buscar por `request_id` en `storage/logs/laravel.log` (o el canal configurado).
  - El `request_id` aparece automáticamente en todos los logs de Laravel gracias a `Log::withContext()`.
  - Formato en logs: `{"request_id":"<uuid>"}` aparece en el contexto JSON de cada línea de log.
  - **Cómo buscar:**
    ```bash
    # Buscar por request_id específico
    grep "request_id.*<uuid-aqui>" storage/logs/laravel.log
    
    # O si el log está en formato JSON (daily stack, etc.):
    grep -E '"request_id":"<uuid-aqui>"' storage/logs/laravel-*.log
    ```
  - Para correlacionar un 500 con el log:
    1. Obtener el `X-Request-Id` del header de respuesta (o `error.trace_id` del body JSON).
    2. Buscar ese UUID en los logs con el comando anterior.
    3. Todos los logs de esa request tendrán el mismo `request_id` en el contexto.

---

## I) Hardening de errores (seguridad)

- [ ] **APP_DEBUG=false:** verificar que `APP_DEBUG=false` en producción/staging (no debe ser `true`).
- [ ] **No stacktrace en JSON:** forzar un 500 (p. ej. endpoint temporal que lance excepción) y verificar que la respuesta JSON **NO** contenga:
  - `error.trace` o `error.trace_id` (solo `error.trace_id` como UUID, no stacktrace)
  - `error.exception` o `error.file` o `error.line`
  - Mensajes de excepción detallados en `error.message` (debe ser genérico: "Error interno del servidor.")
- [ ] **Estructura de error en 500:** verificar que la respuesta sea:
  ```json
  {
    "error": {
      "code": "SERVER_ERROR",
      "message": "Error interno del servidor.",
      "trace_id": "uuid-aqui"
    }
  }
  ```
  Sin campos adicionales con información sensible.
- [ ] **Códigos de error correctos:** verificar que los endpoints devuelven códigos de error estándar:
  - `401` → `error.code: "UNAUTHENTICATED"`
  - `403` → `error.code: "FORBIDDEN"`
  - `404` → `error.code: "NOT_FOUND"`
  - `422` → `error.code: "VALIDATION_ERROR"` con `error.details`
  - `500` → `error.code: "SERVER_ERROR"` con `error.trace_id` y sin información sensible

**Test automatizado:** ejecutar `DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test APP_DEBUG=false php artisan test tests/Feature/Api/V1/ErrorHardeningTest.php` para verificar automáticamente que no se expone información sensible.

---

## J) Optimización de caché (producción)

**Importante:** Ejecutar estos comandos en producción para mejorar el rendimiento. En desarrollo, NO ejecutar (o limpiar después).

### Comandos de optimización

```bash
# Generar cachés de configuración, rutas y vistas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# O ejecutar todo junto (equivalente a los 3 anteriores)
php artisan optimize
```

**Qué hace cada comando:**
- `config:cache` — Caché de configuración (`bootstrap/cache/config.php`). En producción, las variables `.env` se leen una vez y se cachean.
- `route:cache` — Caché de rutas (`bootstrap/cache/routes-v7.php`). Acelera el enrutamiento.
- `view:cache` — Compila y cachea todas las vistas Blade para renderizado más rápido.
- `optimize` — Ejecuta `config:cache`, `route:cache` y `view:cache` en un solo comando.

**Después de optimizar:**
- Verificar que `/api/v1/health` sigue funcionando (ver sección "Cache sanity check").
- Si cambias variables `.env`, ejecutar `php artisan config:clear` y luego `php artisan config:cache` de nuevo.

### Revertir (si necesitas hacer cambios)

```bash
# Limpiar cachés individuales
php artisan config:clear
php artisan route:clear
php artisan view:clear

# O limpiar todo junto
php artisan optimize:clear
```

**Cuándo limpiar:**
- Después de cambiar variables en `.env` → `config:clear` y `config:cache`.
- Después de agregar/modificar rutas en `routes/api.php` o `routes/web.php` → `route:clear` y `route:cache`.
- Después de modificar vistas Blade → `view:clear` y `view:cache`.
- Si no estás seguro, usar `optimize:clear` y luego `optimize`.

**Nota:** En desarrollo, normalmente NO se usa caché (o se limpia con `optimize:clear`). Solo en staging/prod.

---

## K) Cache sanity check

Después de ejecutar `php artisan optimize` (o comandos de caché individuales), verificar que la aplicación sigue funcionando correctamente.

### Verificaciones básicas

1. **Health endpoint:**
   ```bash
   curl -i "https://api.ejemplo.com/api/v1/health"
   ```
   Esperado: `HTTP 200` y JSON con `data.status = "ok"`.

2. **Si falla `/api/v1/health` después de `route:cache`:**
   ```bash
   # Limpiar caché de rutas y regenerar
   php artisan route:clear
   php artisan route:cache
   ```
   Verificar nuevamente. Si sigue fallando, revisar errores en logs.

3. **Si cambiaste variables `.env` y no se reflejan:**
   ```bash
   # Limpiar y regenerar caché de configuración
   php artisan config:clear
   php artisan config:cache
   ```

4. **Verificar que la API responde correctamente:**
   - `GET /api/v1/health` → 200
   - `POST /api/v1/auth/login` (si aplica) → 200 o 401 según credenciales

**Comandos rápidos:**
```bash
# Verificar que los archivos de caché existen
ls -la bootstrap/cache/config.php bootstrap/cache/routes-v7.php

# Si necesitas limpiar todo y empezar de nuevo
php artisan optimize:clear
php artisan optimize
```

---

## H) Health y security headers

- [ ] **GET /api/v1/health** (sin auth) → 200 y JSON: `{ "data": { "status": "ok", "server_time": "<ISO8601>", "app_env": "staging"|"production" }, "message": "OK" }`. Para load balancer, k8s, monitoreo.
  - **Verificar que funciona después de `php artisan route:cache`** (ver sección "Cache sanity check").
- [ ] **Security headers** en respuestas (web y API): comprobar con `curl -i` que existan `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: geolocation=(), microphone=(), camera=()`. No se modifica `Content-Disposition` en descargas.
