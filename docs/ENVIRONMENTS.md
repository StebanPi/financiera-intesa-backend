# Entornos (staging / producción)

Documentación de variables de entorno por categoría y reglas para staging y producción. Las plantillas sin secretos están en `docs/env.staging.example` y `docs/env.production.example`. Para la API + Next.js ver también `docs/ENV_API.example`.

---

## 1. Variables por categoría

| Categoría | Variable | Descripción |
|-----------|----------|-------------|
| **App** | `APP_NAME` | Nombre de la aplicación |
| | `APP_ENV` | `local`, `staging`, `production` |
| | `APP_KEY` | Clave de cifrado (base64). `php artisan key:generate` |
| | `APP_URL` | URL base del backend (p. ej. `https://api.ejemplo.com`) |
| | `APP_DEBUG` | `true` o `false`; en prod siempre `false` |
| **Frontend** | `FRONTEND_URL` | URL del frontend. **Requerido en staging y prod.** Usado por CORS para `api/*`. Ejemplo: `https://staging.tu-dominio.com` |
| | `FRONTEND_URLS` | Opcional. Varios orígenes CORS separados por comas: `https://a.com,https://b.com`. Si se define, tiene prioridad; `FRONTEND_URL` se añade a la lista si falta. |
| | `CORS_ALLOWED_ORIGINS` | Opcional. Alternativa a `FRONTEND_URLS`; orígenes separados por comas. Si no se usa `FRONTEND_URLS` ni esto, se usa `FRONTEND_URL`. |
| **DB** | `DB_CONNECTION` | `mysql` (recomendado) |
| | `DB_HOST` | Host de la BD |
| | `DB_PORT` | `3306` para MySQL |
| | `DB_DATABASE` | Nombre de la BD |
| | `DB_USERNAME` | Usuario |
| | `DB_PASSWORD` | Contraseña |
| **Logs** | `LOG_CHANNEL` | `stack`, `single`, `daily`, etc. (`config/logging.php`) |
| | `LOG_LEVEL` | `debug`, `info`, `warning`, `error`, `critical` |
| **Storage** | `FILESYSTEM_DRIVER` | `local` o `public`; si S3: `s3` y variables AWS |
| | `AWS_ACCESS_KEY_ID` | Si `FILESYSTEM_DRIVER=s3` |
| | `AWS_SECRET_ACCESS_KEY` | Si S3 |
| | `AWS_DEFAULT_REGION` | Si S3 |
| | `AWS_BUCKET` | Si S3 |
| | `AWS_URL` | Opcional, URL pública del bucket |
| | `AWS_ENDPOINT` | Opcional, para S3-compatible (MinIO, etc.) |
| | `AWS_USE_PATH_STYLE_ENDPOINT` | Opcional, para algunos S3-compatible |
| **Mail** | `MAIL_MAILER` | `smtp`, `log`, `ses`, `mailgun`, `postmark`, etc. |
| | `MAIL_HOST` | Host SMTP |
| | `MAIL_PORT` | Puerto (587, 465, 25) |
| | `MAIL_USERNAME` | Usuario SMTP |
| | `MAIL_PASSWORD` | Contraseña SMTP |
| | `MAIL_ENCRYPTION` | `tls`, `ssl` o `null` |
| | `MAIL_FROM_ADDRESS` | Remitente |
| | `MAIL_FROM_NAME` | Nombre del remitente |
| **Cache / Queue** | `CACHE_DRIVER` | `file`, `redis`, `memcached`, etc. |
| | `QUEUE_CONNECTION` | `sync`, `database`, `redis`, etc. |
| | `SESSION_DRIVER` | `file`, `database`, `redis`, etc. |
| **Sanctum** | `SANCTUM_STATEFUL_DOMAINS` | Opcional. Dominios con cookies stateful; con API Bearer suele no ser necesario. Si se usa: hosts separados por comas |
| **Swagger** | `SWAGGER_ENABLED` | `true` o `false`. Habilita/deshabilita la documentación Swagger. **En producción debe ser `false` por defecto.** En local/staging puede ser `true`. |
| | `SWAGGER_URL` | URL base para la documentación Swagger. Por defecto: `/docs`. Ajustar según necesidad (p. ej. `/api-docs`). |
| | `L5_SWAGGER_GENERATE_ALWAYS` | `true` o `false`. Si es `true`, regenera la documentación en cada request (solo en desarrollo). Por defecto: `false`. |
| | `L5_FORMAT_TO_USE_FOR_DOCS` | `json` o `yaml`. Formato del archivo de documentación. Por defecto: `json`. |

---

## 2. Reglas

- **No commitear `.env`.** Usar `.env.example` o las plantillas `docs/env.staging.example` y `docs/env.production.example` como referencia; copiar a `.env` y rellenar secretos en el servidor.
- **`APP_KEY` distinto por entorno:** staging y producción deben tener claves distintas. Generar con `php artisan key:generate` en cada despliegue o robar de forma segura.
- **Base de datos distinta y credenciales distintas:** staging y prod usan BD separadas y usuarios distintos.
- **`FRONTEND_URL` requerido en staging y prod:** necesario para CORS en `/api/*`. Ejemplos: staging `https://staging.tu-dominio.com`, prod `https://app.tu-dominio.com`. En local puede ser `http://localhost:3000`.
- **Mail en staging en modo seguro:** usar `MAIL_MAILER=log` para que los correos se escriban en el log y no se envíen; o un sandbox del proveedor (Mailtrap, Mailgun sandbox, etc.). En producción configurar SMTP o servicio real.
- **Swagger deshabilitado en producción:** `SWAGGER_ENABLED=false` en producción por defecto. Solo habilitar en local/staging con `SWAGGER_ENABLED=true`. Si está deshabilitado, las rutas `/docs` y `/docs/openapi.json` devuelven 404.

---

## 3. Plantillas de ejemplo

- **`docs/env.staging.example`** — plantilla para staging (sin secretos). Valores orientativos: `APP_ENV=staging`, `APP_DEBUG=false`, `MAIL_MAILER=log`, etc.
- **`docs/env.production.example`** — plantilla para producción. `APP_ENV=production`, `APP_DEBUG=false`, mail con SMTP o SES.

Para usarlas: copiar la adecuada a la raíz del proyecto como `.env`, sustituir placeholders por valores reales y **nunca** commitear el `.env` con secretos.

---

## 4. Relación con `docs/ENV_API.example`

`docs/ENV_API.example` describe variables específicas para la API y el frontend Next.js (`FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`). Esas variables deben incorporarse al `.env` de cada entorno según la plantilla correspondiente.
