# Guía de despliegue en producción — Financiera Intesa Backend

Esta guía explica cómo subir y dejar funcional en **producción** el backend Laravel (API + vistas) de Financiera Intesa.

**Documentos relacionados:**
- `DEPLOY_CHECKLIST.md` — Checklist detallado (BD, storage, PDF/XLSX, seguridad, etc.)
- `STAGING_RUNBOOK.md` — Comandos paso a paso y smoke tests (aplicables a prod)
- `PROD_OPTIMIZE.md` — Optimización de caché
- `ENVIRONMENTS.md` — Variables de entorno
- `env.production.example` — Plantilla de `.env` para producción

---

## 1. Requisitos del servidor

| Requisito           | Versión / Notas                                      |
|---------------------|------------------------------------------------------|
| **PHP**             | 8.2 o superior (8.3 recomendado)                     |
| **Extensiones PHP** | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `gd` o `imagick` (para QR/PDF) |
| **MySQL**           | 8.0 o MariaDB 10.6+ (utf8mb4)                        |
| **Node.js**         | 18+ (solo para compilar assets en el deploy)         |
| **Composer**        | 2.x                                                  |
| **Servidor web**    | Nginx + PHP-FPM, o Apache con `mod_rewrite`          |

### Comprobar PHP y extensiones

```bash
php -v
php -m | grep -E 'pdo_mysql|mbstring|openssl|json|curl|gd|dom|fileinfo|bcmath'
```

---

## 2. Opciones de despliegue

Puedes desplegar en:

- **VPS / servidor propio** (Ubuntu, Debian, etc.): Nginx + PHP-FPM + MySQL. Se detalla en esta guía.
- **Shared hosting**: Si soporta PHP 8.2+, Composer y tienes acceso a `.env` y `storage`, los pasos 4–7 siguen siendo válidos; la configuración del servidor web la hace el proveedor.
- **Docker**: El proyecto tiene `docker-compose.yml` con MySQL. Puedes añadir un servicio PHP/Nginx para la app; esta guía se centra en instalación “clásica”.
- **PaaS** (Laravel Forge, Ploi, Railway, etc.): Suelen automatizar gran parte; usa su documentación y adapta el `.env` y los pasos de esta guía.

---

## 3. Preparación previa

### 3.1. Repositorio y acceso al servidor

- Código en Git (GitHub, GitLab, Bitbucket, etc.).
- Acceso SSH al servidor de producción.
- (Opcional) Clave SSH o token para `git clone` en el servidor.

### 3.2. Base de datos de producción

- Crear base de datos MySQL en el servidor o en un servicio gestionado (p. ej. RDS, DigitalOcean Managed DB).
- Usuario con permisos: `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `DROP`, `INDEX`, `ALTER`, `REFERENCES` (o `ALL` para esa BD).
- Anotar: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

### 3.3. Dominio y SSL

- Dominio (p. ej. `api.tudominio.com`) apuntando al servidor (A o CNAME).
- Certificado SSL (Let’s Encrypt con Certbot es lo más habitual).

---

## 4. Instalación en el servidor (paso a paso)

### 4.1. Clonar el repositorio

```bash
cd /var/www
sudo git clone https://github.com/tu-org/financiera-intesa-backend.git
cd financiera-intesa-backend
```

Ajusta la URL del repo. Si usas rama `main`:

```bash
git checkout main
```

### 4.2. Copiar y configurar `.env`

```bash
cp docs/env.production.example .env
nano .env   # o vim, code, etc.
```

Configura al menos:

| Variable       | Descripción |
|----------------|-------------|
| `APP_NAME`     | Ej: `Financiera` |
| `APP_ENV`      | `production` |
| `APP_KEY`      | Generar con `php artisan key:generate` (paso siguiente) |
| `APP_DEBUG`    | `false` |
| `APP_URL`      | URL base del backend, ej: `https://api.tudominio.com` |
| `FRONTEND_URL` | URL del frontend (CORS), ej: `https://app.tudominio.com` |
| `DB_CONNECTION`| `mysql` |
| `DB_HOST`      | Host de MySQL |
| `DB_PORT`      | `3306` |
| `DB_DATABASE`  | Nombre de la BD |
| `DB_USERNAME`  | Usuario MySQL |
| `DB_PASSWORD`  | Contraseña |
| `LOG_CHANNEL`  | `daily` (recomendado) |
| `LOG_LEVEL`    | `warning` o `error` |
| `FILESYSTEM_DRIVER` | `local` (o `s3` si usas S3) |
| `SWAGGER_ENABLED`   | `false` en producción |
| `CACHE_DRIVER` | `file` (o `redis` si lo tienes) |
| `SESSION_DRIVER`    | `file` |
| `QUEUE_CONNECTION`  | `sync` (o `redis`/`database` si usas colas) |

Si usas **mail real** en producción, configura `MAIL_*` según tu SMTP o servicio (SES, Mailgun, etc.). Si no, puedes dejar `MAIL_MAILER=log` temporalmente.

### 4.3. Instalar dependencias PHP

```bash
composer install --no-dev --optimize-autoloader
```

`--no-dev` evita instalar paquetes de desarrollo; `--optimize-autoloader` mejora el rendimiento.

### 4.4. Generar APP_KEY (si no lo hiciste antes)

```bash
php artisan key:generate
```

### 4.5. Compilar assets (CSS/JS con Vite)

```bash
npm ci
npm run build
```

Esto genera los archivos en `public/build`. En producción no hace falta `npm run dev`.

### 4.6. Validación pre-despliegue

```bash
php artisan app:release-check
```

Si hay **FAIL**, hay que corregir antes de seguir. Warnings (p. ej. `public/storage`) se resuelven en los siguientes pasos.

### 4.7. Migraciones

```bash
php artisan migrate --force
```

`--force` evita el aviso en producción.

**Si la BD es nueva** y necesitas roles/permisos:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

No re-ejecutes seeders de negocio en una BD ya usada para no duplicar datos.

### 4.8. Enlace simbólico de storage

```bash
php artisan storage:link
```

Comprueba:

```bash
ls -la public/storage
# Debe apuntar a ../storage/app/public
```

### 4.9. Permisos

El usuario del servidor web (p. ej. `www-data`) debe poder escribir en `storage` y `bootstrap/cache`:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

Ajusta `www-data` si tu servidor usa otro usuario (p. ej. `nginx`, `apache`).

### 4.10. Documentación Swagger/OpenAPI (opcional)

Si quieres tener la documentación interactiva en `/docs` en producción:

1. **En `.env`:**
   ```
   SWAGGER_ENABLED=true
   SWAGGER_URL=/docs
   L5_SWAGGER_GENERATE_ALWAYS=false
   ```

2. **Generar el archivo OpenAPI** (una vez por despliegue o al cambiar anotaciones):
   ```bash
   php artisan l5-swagger:generate
   ```
   Esto crea o actualiza `storage/api-docs/openapi.json`. La UI en `/docs` y el JSON en `/docs/openapi.json` leen ese archivo.

3. **Permisos:** el usuario del servidor web debe poder escribir en `storage/api-docs` (el comando crea la carpeta si no existe).

Si `SWAGGER_ENABLED=false`, las rutas `/docs` y `/docs/openapi.json` responden 404. Con `L5_SWAGGER_GENERATE_ALWAYS=false` (recomendado en prod) **no** se regenera en cada request; solo al ejecutar `l5-swagger:generate`.

### 4.11. Optimización para producción

```bash
php artisan optimize
```

Equivale a `config:cache`, `route:cache` y `view:cache`. Después de cambiar `.env` debes ejecutar:

```bash
php artisan config:clear
php artisan config:cache
```

---

## 5. Configuración del servidor web

El **document root** debe ser la carpeta `public` del proyecto. Ejemplo: `/var/www/financiera-intesa-backend/public`.

### 5.1. Nginx + PHP-FPM

Ejemplo de sitio (ej: `/etc/nginx/sites-available/financiera-api`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.tudominio.com;
    root /var/www/financiera-intesa-backend/public;

    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ajusta:
- `server_name`
- `root`
- `fastcgi_pass` (ruta del socket de PHP-FPM según tu PHP: `php8.2-fpm`, `php8.3-fpm`, etc.).

Activar y recargar Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/financiera-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5.2. Apache

Asegúrate de tener `mod_rewrite` habilitado. En el `VirtualHost`:

```apache
<VirtualHost *:80>
    ServerName api.tudominio.com
    DocumentRoot /var/www/financiera-intesa-backend/public

    <Directory /var/www/financiera-intesa-backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/financiera-api-error.log
    CustomLog ${APACHE_LOG_DIR}/financiera-api-access.log combined
</VirtualHost>
```

El `.htaccess` de Laravel en `public/` se encarga del rewrite.

### 5.3. PHP-FPM

Revisa que el pool de PHP-FPM tenga valores razonables (p. ej. `pm.max_children`, `request_terminate_timeout` para PDFs/Excel que pueden tardar). Ajusta según RAM y carga.

---

## 6. SSL (HTTPS)

Con Certbot (Let’s Encrypt) en Nginx:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.tudominio.com
```

Certbot configura el redirect HTTP→HTTPS y el certificado. Renovación automática suele estar en un cron de `certbot`.

Después de activar HTTPS, verifica que `APP_URL` en `.env` use `https://`:

```
APP_URL=https://api.tudominio.com
```

---

## 7. Cron (tareas programadas)

Laravel necesita un cron para colas, programación y (opcional) limpieza de tokens.

```bash
sudo crontab -u www-data -e
```

Añade:

```
* * * * * cd /var/www/financiera-intesa-backend && php artisan schedule:run >> /dev/null 2>&1
```

(O el usuario que ejecute PHP en tu entorno.)

**Limpieza de tokens Sanctum** (recomendado, p. ej. domingos 2:00):

```
0 2 * * 0 cd /var/www/financiera-intesa-backend && php artisan sanctum:prune-tokens --days=30 >> /dev/null 2>&1
```

Si usas `schedule:run`, puedes definir este comando en `app/Console/Kernel.php` en lugar de cron directo.

---

## 8. Verificación en producción

### 8.1. Health

```bash
curl -s https://api.tudominio.com/api/v1/health
```

Esperado: HTTP 200 y JSON con `"data":{"status":"ok", ...}`.

### 8.2. Login y ruta protegida

```bash
# Login
curl -s -X POST "https://api.tudominio.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"TU_EMAIL","password":"TU_PASSWORD"}'
```

Debes recibir `data.token`. Luego:

```bash
curl -s "https://api.tudominio.com/api/v1/home" \
  -H "Authorization: Bearer EL_TOKEN" \
  -H "Accept: application/json"
```

Esperado: 200 y `data.message`.

### 8.3. PDF y XLSX

- **PDF matrícula:**  
  `GET /api/v1/matriculas/{cod_alumno}/pdf` con `Authorization: Bearer TOKEN` → 200, `Content-Type: application/pdf`.
- **XLSX abonos:**  
  `GET /api/v1/accounting/abonos/download?fecha_inicio=...&fecha_fin=...` con `Authorization: Bearer TOKEN` → 200, archivo `.xlsx`.

Puedes usar los ejemplos de `docs/STAGING_RUNBOOK.md` (sección 7) cambiando la URL a la de producción.

### 8.4. CORS y frontend

Comprueba que el frontend (`FRONTEND_URL`) puede llamar a la API sin errores CORS. Si usas otros orígenes, define `CORS_ALLOWED_ORIGINS` o `FRONTEND_URLS` según `docs/ENVIRONMENTS.md`.

---

## 9. Resumen de comandos por despliegue

Para un **nuevo despliegue** o después de `git pull`:

```bash
cd /var/www/financiera-intesa-backend

git pull origin main

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan app:release-check
php artisan migrate --force
php artisan storage:link
# Si SWAGGER_ENABLED=true: php artisan l5-swagger:generate
php artisan optimize
```

Si cambias **solo `.env`**:

```bash
php artisan config:clear
php artisan config:cache
```

Si cambias **rutas**:

```bash
php artisan route:clear
php artisan route:cache
```

Si algo falla tras optimizar:

```bash
php artisan optimize:clear
# Corregir y luego:
php artisan optimize
```

---

## 10. Mantenimiento y buenas prácticas

- **Logs:** Revisar `storage/logs/` (p. ej. `laravel-YYYY-MM-DD.log` con `LOG_CHANNEL=daily`). Rotar/archivar si crecen mucho.
- **Backups:** Hacer backups de la BD y, si aplica, de `storage/app` (archivos subidos, fotos, etc.).
- **Actualizaciones:** Revisar `composer update` y cambios en `composer.lock` en un entorno de staging antes de producción.
- **Seguridad:** Mantener `APP_DEBUG=false`, `SWAGGER_ENABLED=false` en producción. No commitear `.env`.
- **Errores 500:** El cuerpo de la respuesta no debe mostrar trazas; usa `trace_id` y busca en logs por `request_id` (ver `DEPLOY_CHECKLIST.md` sección G e I).

---

## 11. Referencias rápidas

| Documento | Uso |
|-----------|-----|
| `docs/DEPLOY_CHECKLIST.md` | Checklist completo: BD, storage, PDF/XLSX, auth, hardening, headers, caché |
| `docs/STAGING_RUNBOOK.md` | Comandos detallados y smoke tests (health, login, PDF, XLSX, problemas comunes) |
| `docs/PROD_OPTIMIZE.md` | Detalle de `optimize`, `optimize:clear` y cuándo limpiar caché |
| `docs/ENVIRONMENTS.md` | Variables de entorno por categoría |
| `docs/env.production.example` | Plantilla de `.env` para producción |

---

## 12. Checklist final antes de dar por cerrado el deploy

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` generado
- [ ] `.env` con `DB_*`, `APP_URL`, `FRONTEND_URL` correctos
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm run build` ejecutado
- [ ] `php artisan app:release-check` sin FAIL
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link` y `public/storage` existe
- [ ] Permisos en `storage/` y `bootstrap/cache/` para el usuario del servidor web
- [ ] `php artisan optimize`
- [ ] Document root del servidor web = `.../public`
- [ ] HTTPS (SSL) configurado y `APP_URL` con `https://`
- [ ] Cron para `schedule:run` (y opcional `sanctum:prune-tokens`)
- [ ] `GET /api/v1/health` → 200
- [ ] Login → token; `GET /api/v1/home` con token → 200
- [ ] Al menos un PDF y un XLSX probados
- [ ] CORS correcto para el frontend
- [ ] `SWAGGER_ENABLED=false` en producción (o si está `true`: `php artisan l5-swagger:generate` y `GET /docs` → 200)
