# Runbook de Staging

Runbook paso a paso para desplegar el backend en staging. Este documento complementa `docs/STAGING_CHECKLIST.md` y `docs/DEPLOY_CHECKLIST.md` con comandos concretos y verificables.

---

## Prerrequisitos

- Acceso SSH al servidor de staging
- Credenciales de base de datos de staging
- Usuario válido en staging para hacer login y obtener token
- `curl` instalado para smoke checks

---

## 1. Configuración del Entorno

### 1.1. Copiar archivo .env

```bash
# Desde la raíz del proyecto
cp docs/env.staging.example .env
```

### 1.2. Editar .env

Editar `.env` y configurar:
- `APP_ENV=staging`
- `APP_DEBUG=false`
- `APP_KEY` (si no existe, ejecutar `php artisan key:generate`)
- `APP_URL=https://api-staging.ejemplo.com` (URL real de staging)
- `DB_*`: credenciales de base de datos de staging
- `FRONTEND_URL`: URL del frontend de staging (o placeholder)
- Otros valores según `docs/env.staging.example`

**Verificar:** El archivo `.env` está configurado correctamente.

---

## 2. Instalación de Dependencias

```bash
composer install --no-dev --optimize-autoloader
```

**Verificar:** No hay errores durante la instalación.

---

## 3. Validación Pre-Deploy

```bash
php artisan app:release-check
```

**Importante:** Si el comando muestra `FAIL` en algún check, **PARAR** y corregir antes de continuar.

El comando verifica:
- `APP_ENV` no es "local"
- `APP_DEBUG=false`
- `APP_KEY` está definido
- Conexión a base de datos funciona
- Paths de storage existen y son escribibles
- `public/storage` symlink existe
- Endpoint `/api/v1/health` responde correctamente

**Salida esperada:** Todos los checks en `PASS` o `WARNING` (warnings son aceptables si son intencionales).

**Si FAIL:** Corregir el problema y ejecutar `app:release-check` de nuevo.

---

## 4. Migraciones

```bash
php artisan migrate --force
```

**Verificar:** Migraciones ejecutadas sin errores.

**Nota:** Si la BD es nueva y necesita seeders, ejecutar:
```bash
php artisan db:seed --class=RolePermissionSeeder
```

⚠️ **No re-ejecutar seeders en BD ya sembrada** para evitar duplicados.

---

## 5. Storage Link

```bash
php artisan storage:link
```

**Verificar:** El symlink `public/storage` → `storage/app/public` se creó correctamente.

```bash
# Verificar que existe el symlink
ls -la public/storage
```

---

## 6. Optimización

```bash
php artisan optimize
```

O ejecutar individualmente:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Verificar:** Cachés generados sin errores.

**Nota:** Si cambias `.env` después, ejecutar `php artisan config:clear` y luego `php artisan config:cache` de nuevo.

---

## 7. Smoke Checks con curl

Realizar los siguientes checks en orden para verificar que todo funciona correctamente.

### Variables de Entorno (ajustar según tu staging)

```bash
# Definir estas variables según tu entorno
export STAGING_URL="https://api-staging.ejemplo.com"
export STAGING_EMAIL="usuario@ejemplo.com"
export STAGING_PASSWORD="password"
export COD_ALUMNO="COD123456"  # Código de alumno existente en staging
```

### 7.1. Health Endpoint (200)

```bash
curl -s -w "\nHTTP %{http_code}\n" "${STAGING_URL}/api/v1/health" \
  -H "Accept: application/json"
```

**Esperado:**
- `HTTP 200`
- Body JSON con `{"data":{"status":"ok","server_time":"...","app_env":"staging"}}`

**Verificar headers:**
```bash
curl -I "${STAGING_URL}/api/v1/health"
```

Debe incluir: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, etc.

---

### 7.2. GET /api/v1/home sin token (401 JSON)

```bash
curl -s -w "\nHTTP %{http_code}\n" "${STAGING_URL}/api/v1/home" \
  -H "Accept: application/json"
```

**Esperado:**
- `HTTP 401`
- Body JSON: `{"error":{"code":"UNAUTHENTICATED","message":"No autenticado.","details":null}}`
- **NO debe aparecer** `{"message":"Unauthenticated."}` (ese es el formato antiguo)

---

### 7.3. Login (200) y copiar token

```bash
# Login y guardar respuesta
RESPONSE=$(curl -s -X POST "${STAGING_URL}/api/v1/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"${STAGING_EMAIL}\",\"password\":\"${STAGING_PASSWORD}\"}")

# Verificar HTTP 200 (curl no muestra status por defecto, pero podemos verificar el JSON)
echo "$RESPONSE"

# Extraer token (requiere jq o usar sed/grep)
# Si tienes jq instalado:
TOKEN=$(echo "$RESPONSE" | jq -r '.data.token // empty')

# Si no tienes jq, puedes extraer manualmente o copiar desde la respuesta
# El token está en: {"data":{"token":"1|xxx..."}}
```

**Esperado:**
- `HTTP 200`
- Body JSON con `{"data":{"token":"1|xxx...","user":{...}}}`

**Guardar el token:**
```bash
# Copiar el token manualmente si no tienes jq
# Ejemplo: export TOKEN="1|abc123..."
export TOKEN="1|TU_TOKEN_AQUI"
```

**Alternativa sin variables (reemplazar TOKEN directamente en comandos):**
Si prefieres no usar variables, puedes copiar el token directamente en los comandos siguientes.

---

### 7.4. GET /api/v1/home con token (200)

```bash
curl -s -w "\nHTTP %{http_code}\n" "${STAGING_URL}/api/v1/home" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}"
```

**Esperado:**
- `HTTP 200`
- Body JSON con `{"data":{"message":"..."}}`

---

### 7.5. XLSX: GET /api/v1/accounting/abonos/download

```bash
# Descargar archivo XLSX
curl -L -o abonos.xlsx -w "\nHTTP %{http_code}\n" \
  "${STAGING_URL}/api/v1/accounting/abonos/download?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Authorization: Bearer ${TOKEN}"
```

**Verificar headers:**
```bash
curl -I \
  "${STAGING_URL}/api/v1/accounting/abonos/download?fecha_inicio=2024-01-01&fecha_fin=2024-01-31" \
  -H "Authorization: Bearer ${TOKEN}"
```

**Esperado:**
- `HTTP 200`
- Headers: `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- Headers: `Content-Disposition: attachment; filename="Informe de Abonos ..."`
- Archivo `abonos.xlsx` descargado correctamente

**Verificar que el archivo existe:**
```bash
ls -lh abonos.xlsx
file abonos.xlsx  # Debe mostrar "Microsoft Excel" o similar
```

---

### 7.6. PDF: POST /api/v1/attendance-sheet/generate

```bash
# Generar PDF de planilla de asistencia
curl -o planilla.pdf -w "\nHTTP %{http_code}\n" \
  -X POST "${STAGING_URL}/api/v1/attendance-sheet/generate" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -d '{
    "program_id": 1,
    "schedule_id": 1,
    "group_id": 1,
    "teacher_id": 1,
    "module_id": 1,
    "fecha_inicio": "2024-01-01",
    "fecha_final": "2024-01-31",
    "fecha_clase": "2024-01-15"
  }'
```

**Verificar headers:**
```bash
curl -I -X POST "${STAGING_URL}/api/v1/attendance-sheet/generate" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -d '{
    "program_id": 1,
    "schedule_id": 1,
    "group_id": 1,
    "teacher_id": 1,
    "module_id": 1,
    "fecha_inicio": "2024-01-01",
    "fecha_final": "2024-01-31",
    "fecha_clase": "2024-01-15"
  }'
```

**Esperado:**
- `HTTP 200`
- Headers: `Content-Type: application/pdf`
- Headers: `Content-Disposition: inline; filename="planilla_asistencia_....pdf"`
- Archivo `planilla.pdf` generado correctamente

**Verificar que el archivo existe:**
```bash
ls -lh planilla.pdf
file planilla.pdf  # Debe mostrar "PDF document"
```

---

### 7.7. PDF Matrícula: GET /api/v1/matriculas/{cod}/pdf

```bash
# Generar PDF de matrícula
curl -o matricula.pdf -w "\nHTTP %{http_code}\n" \
  "${STAGING_URL}/api/v1/matriculas/${COD_ALUMNO}/pdf" \
  -H "Authorization: Bearer ${TOKEN}"
```

**Verificar headers:**
```bash
curl -I \
  "${STAGING_URL}/api/v1/matriculas/${COD_ALUMNO}/pdf" \
  -H "Authorization: Bearer ${TOKEN}"
```

**Esperado:**
- `HTTP 200`
- Headers: `Content-Type: application/pdf`
- Headers: `Content-Disposition: inline; filename="matricula-${COD_ALUMNO}.pdf"`
- Archivo `matricula.pdf` generado correctamente

**Verificar que el archivo existe:**
```bash
ls -lh matricula.pdf
file matricula.pdf  # Debe mostrar "PDF document"
```

---

## 8. Verificación de Logs

### 8.1. Buscar por X-Request-Id

Si una request incluye el header `X-Request-Id`, buscar en los logs:

```bash
# Buscar en logs recientes
grep "request_id" storage/logs/laravel.log | tail -20

# O si usas log daily:
grep "request_id" storage/logs/laravel-$(date +%Y-%m-%d).log
```

**Formato esperado:** `{"request_id":"uuid-aqui"}` aparece en el contexto JSON de cada línea de log.

### 8.2. Buscar por trace_id

Si hay un error 500, buscar por `trace_id`:

```bash
# Buscar trace_id en logs
grep "trace_id" storage/logs/laravel.log | tail -20

# O buscar por el UUID específico
grep "TRACE-UUID-AQUI" storage/logs/laravel.log
```

**Nota:** El `trace_id` en la respuesta JSON del error 500 debe coincidir con el `request_id` en los logs.

### 8.3. Ver logs recientes

```bash
# Ver últimas 50 líneas
tail -50 storage/logs/laravel.log

# O si usas daily logs:
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## 9. Problemas Comunes y Soluciones

### 9.1. Permisos de Storage

**Problema:** No se pueden escribir archivos en `storage/` o `bootstrap/cache/`.

**Solución:**
```bash
# Ajustar permisos (ajustar usuario/grupo según tu servidor)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**Verificar:**
```bash
ls -la storage/
ls -la bootstrap/cache/
```

---

### 9.2. Storage Link No Funciona

**Problema:** El symlink `public/storage` no existe o está roto.

**Solución:**
```bash
# Eliminar symlink si existe y está roto
rm -f public/storage

# Crear symlink de nuevo
php artisan storage:link
```

**Verificar:**
```bash
ls -la public/storage
# Debe mostrar: public/storage -> ../storage/app/public
```

---

### 9.3. Error de Conexión a Base de Datos

**Problema:** `app:release-check` falla en el check de DB conectividad.

**Solución:**
1. Verificar credenciales en `.env`:
   ```bash
   grep "^DB_" .env
   ```

2. Probar conexión manualmente:
   ```bash
   php artisan tinker
   # Dentro de tinker:
   DB::select('SELECT 1');
   ```

3. Verificar que el servidor MySQL/MariaDB está corriendo:
   ```bash
   systemctl status mysql
   # O
   systemctl status mariadb
   ```

4. Verificar firewall/red: que el servidor puede alcanzar el host de BD.

---

### 9.4. Error CORS

**Problema:** Requests desde el frontend fallan con error CORS.

**Solución:**
1. Verificar `FRONTEND_URL` en `.env`:
   ```bash
   grep "^FRONTEND_URL" .env
   ```

2. Verificar `CORS_ALLOWED_ORIGINS` si está definido:
   ```bash
   grep "^CORS_ALLOWED_ORIGINS" .env
   ```

3. Limpiar caché de configuración:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

4. Verificar que el frontend está enviando las headers correctas:
   - `Origin` header debe coincidir con `FRONTEND_URL` o estar en `CORS_ALLOWED_ORIGINS`

---

### 9.5. Health Endpoint No Responde

**Problema:** `/api/v1/health` devuelve 404 o error.

**Solución:**
1. Verificar que las rutas están cacheadas:
   ```bash
   php artisan route:list | grep health
   ```

2. Si no aparece, limpiar y re-cachear rutas:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

3. Verificar que el endpoint funciona sin cache:
   ```bash
   php artisan route:clear
   curl -s "${STAGING_URL}/api/v1/health"
   php artisan route:cache
   ```

---

### 9.6. Tokens No Funcionan (401)

**Problema:** Login funciona pero requests autenticadas devuelven 401.

**Solución:**
1. Verificar que el token está completo y correcto:
   ```bash
   # El token debe incluir el prefijo "1|"
   echo "$TOKEN"
   ```

2. Verificar que el header `Authorization` está bien formado:
   ```bash
   # Debe ser: Authorization: Bearer 1|xxx...
   curl -v "${STAGING_URL}/api/v1/home" \
     -H "Authorization: Bearer ${TOKEN}"
   ```

3. Verificar que Sanctum está configurado correctamente:
   ```bash
   grep "SANCTUM" .env
   ```

---

### 9.7. PDF/XLSX No Se Generan

**Problema:** Requests a endpoints de PDF/XLSX devuelven error.

**Solución:**
1. Verificar permisos de storage (ver sección 9.1)
2. Verificar que storage:link existe (ver sección 9.2)
3. Verificar logs para ver el error específico:
   ```bash
   tail -50 storage/logs/laravel.log
   ```
4. Probar con usuario que tenga los permisos correctos (p. ej. `access.core`)

---

## 10. Rollback Rápido

Si algo sale mal y necesitas revertir los cambios de optimización:

```bash
php artisan optimize:clear
```

Esto limpia todos los cachés:
- `config:clear`
- `route:clear`
- `view:clear`
- `event:clear`

**Nota:** Después de hacer cambios en `.env` o rutas, ejecutar `optimize:clear` antes de continuar desarrollando en local.

---

## 11. Automatización con Script

Para automatizar el despliegue, puedes usar el script de composer:

```bash
composer run post-deploy
```

Este script ejecuta:
1. `php artisan app:release-check`
2. `php artisan migrate --force`
3. `php artisan storage:link`
4. `php artisan optimize`

**Ver también:** `docs/PROD_OPTIMIZE.md` para más detalles sobre optimización.

---

## Checklist Final

Antes de considerar el despliegue completo:

- [ ] `app:release-check` pasa todos los checks
- [ ] Migraciones ejecutadas
- [ ] `storage:link` creado
- [ ] Optimización ejecutada
- [ ] Health endpoint responde 200
- [ ] Login funciona y devuelve token
- [ ] Request autenticada funciona (GET /api/v1/home)
- [ ] Endpoint XLSX funciona y devuelve archivo correcto
- [ ] Endpoint PDF planilla funciona y devuelve PDF correcto
- [ ] Endpoint PDF matrícula funciona y devuelve PDF correcto
- [ ] Logs funcionan correctamente
- [ ] Permisos de storage correctos

---

## Referencias

- `docs/STAGING_CHECKLIST.md` — Checklist detallado de staging
- `docs/DEPLOY_CHECKLIST.md` — Checklist general de deploy
- `docs/PROD_OPTIMIZE.md` — Documentación de optimización
- `docs/API_TESTS_CURL.md` — Más ejemplos de tests con curl
