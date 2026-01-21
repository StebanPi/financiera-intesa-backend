# Checklist de staging

Checklist para dejar **staging** listo (solo backend API, sin frontend). Complementa `docs/DEPLOY_CHECKLIST.md` y `docs/ENVIRONMENTS.md`.

**📘 Runbook detallado:** Para pasos concretos y comandos exactos, ver `docs/STAGING_RUNBOOK.md`.

**⚡ Automatización:** Puedes usar `composer run post-deploy` para ejecutar los pasos principales automáticamente:
- `php artisan app:release-check`
- `php artisan migrate --force`
- `php artisan storage:link`
- `php artisan optimize`

---

## 0. Validación pre-deploy

- [ ] **Ejecutar validación:** `php artisan app:release-check`
  - Verifica: APP_ENV, APP_DEBUG, APP_KEY, DB conectividad, storage paths, public/storage symlink, health endpoint.
  - Si algún check falla (FAIL), corregir antes de continuar.
  - Si hay warnings (APP_ENV=local, symlink faltante), revisar según el entorno.
  - El comando devuelve exit code 1 si hay errores críticos.

**Nota:** Ejecutar este comando antes de cualquier otro paso del checklist para detectar problemas temprano.

---

## 1. Config / env

- [ ] `.env` creado a partir de `docs/env.staging.example` (o equivalente), con secretos rellenados.
- [ ] `APP_ENV=staging`, `APP_DEBUG=false` (o `true` solo si se acepta para depurar).
- [ ] `APP_KEY` definido y distinto al de producción.
- [ ] `APP_URL` con la URL pública de la API en staging (p. ej. `https://api-staging.ejemplo.com`).
- [ ] `FRONTEND_URL` apuntando al frontend de staging si existe; si no hay frontend, puede quedar un valor placeholder (CORS usará esto o `CORS_ALLOWED_ORIGINS`).
- [ ] `DB_*`: base de datos y credenciales **de staging**, distintas a producción.
- [ ] `LOG_CHANNEL` y `LOG_LEVEL` adecuados (p. ej. `daily` y `debug`).
- [ ] `MAIL_MAILER=log` (o sandbox) para no enviar correos reales.

---

## 2. Migraciones

- [ ] `php artisan migrate --force` ejecutado correctamente.
- [ ] Si la BD es nueva: `php artisan db:seed --class=RolePermissionSeeder` (solo una vez). No re-ejecutar en BD ya sembrada.

---

## 3. Storage: link y permisos

- [ ] `php artisan storage:link` ejecutado (`public/storage` → `storage/app/public`).
- [ ] Permisos de escritura en `storage/` y `bootstrap/cache/` para el usuario del servidor web.

---

## 4. PDF (stream)

Probar que los PDF se sirven con `Content-Type: application/pdf` y `Content-Disposition` adecuados:

- [ ] **GET /api/v1/matriculas/{cod_alumno}/pdf** con `Authorization: Bearer {token}` → 200, PDF descargable/visualizable.
- [ ] **GET /api/v1/financial-receipts/{type}/{id}/pdf** (p. ej. `type=entry`, `id` válido) → 200, PDF.
- [ ] **POST /api/v1/attendance-sheet/generate** con body JSON (`program_id`, `schedule_id`, `group_id`, `teacher_id`, `module_id`, `fecha_inicio`, `fecha_final`, `fecha_clase`) → 200, PDF.

---

## 5. XLSX (download)

- [ ] **GET /api/v1/accounting/abonos/download?fecha_inicio=YYYY-MM-DD&fecha_fin=YYYY-MM-DD** con token → 200, archivo `.xlsx` con `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` y `Content-Disposition: attachment`.
- [ ] (Opcional) Probar otro reporte, p. ej. `arqueo-diario/download?fecha=...` o `informe-mensual/download?month_year=YYYY-MM`.

---

## 6. 401 unificado

- [ ] **GET /api/v1/home** (o `/api/v1/matriculas`, `/api/v1/accounting`) **sin** cabecera `Authorization` → 401 con cuerpo:
  ```json
  {"error":{"code":"UNAUTHENTICATED","message":"No autenticado.","details":null}}
  ```
- [ ] No debe aparecer `{"message":"Unauthenticated."}`.

---

## 7. Postman: importar y 3 pruebas

- [ ] Importar **docs/postman/collection.json** y **docs/postman/environment.json**.
- [ ] En el environment, definir `base_url` con la URL de staging (p. ej. `https://api-staging.ejemplo.com`).
- [ ] **Auth/Login**: ejecutar con usuario válido de staging; el test debe guardar `token` en el environment.
- [ ] Ejecutar en este orden:
  1. **1 JSON:** `GET /api/v1/home` con `Authorization: Bearer {{token}}` → 200, `data.message` presente.
  2. **1 PDF:** p. ej. `GET /api/v1/matriculas/{cod_alumno}/pdf` → 200, binario PDF.
  3. **1 XLSX:** p. ej. `GET /api/v1/accounting/abonos/download?fecha_inicio=...&fecha_fin=...` → 200, descarga `.xlsx`.

---

## 8. Logs y trace_id en 500

- [ ] Provocar un 500 controlado (p. ej. forzar una excepción en un endpoint de prueba o usar un `id` inexistente en un contexto que lance 500). Comprobar que la respuesta incluye `error.trace_id`.
- [ ] Opcional: enviar `X-Request-Id: test-123` en la petición y verificar que `error.trace_id` es `test-123`.
- [ ] Buscar en `storage/logs/laravel.log` (o el canal configurado) la excepción correspondiente; el `trace_id` o la fecha/hora permiten correlacionar con el 500.

---

## 9. Optimización de caché (staging/prod)

**Importante:** Ejecutar estos comandos en staging/prod para mejorar el rendimiento. En desarrollo, NO ejecutar (o limpiar después).

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
- Verificar que `/api/v1/health` sigue funcionando (ver sección 10).
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

## 10. Health y security headers

- [ ] **GET /api/v1/health** (sin token) → 200, `data.status` = `"ok"`, `data.server_time` (ISO8601), `data.app_env`.
  - Verificar que funciona **después de ejecutar `php artisan route:cache`** (si aplica).
- [ ] **Headers de seguridad:** `curl -i http://localhost:8000/api/v1/health` (o la URL de staging) y comprobar: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: geolocation=(), microphone=(), camera=()`.
