# Financiera Intesa — Backend

**API REST** de gestión financiera para instituciones educativas. Desarrollado con Laravel 12.

Este proyecto es únicamente el **backend**; el frontend (web o móvil) se conecta vía API.

## 🚀 Características

### Módulos principales (API)

- **Gestión de Matrículas**: Control del proceso de matrícula estudiantil
  - Registro de estudiantes, pagos y abonos
  - Generación de fichas de matrícula en PDF
  - Subida de fotos

- **Cartera**: Administración de cartera y pagos
  - Seguimiento de deudas e historial de pagos
  - Reportes financieros (JSON y Excel)

- **Recibos**: Recibos de entrada y salida
  - Recibos de ingresos (matrículas, abonos, otros), egresos y terceros
  - Generación en PDF
  - Impresión con impresoras térmicas (EscPos)

- **Contabilidad**: Sistema contable integrado
  - Plan de cuentas (Debe/Haber), conceptos de ingreso y egreso
  - Reportes (abonos, otros ingresos, egresos, arqueo, informes semanal/mensual)
  - Exportación a Excel (XLSX)

- **Terceros**: Gestión de terceros y actividades
  - Registro de terceros, actividades y recibos de terceros

- **Configuración**: Catálogo académico, conceptos, consecutivos, elaboradores, plan de cuentas, roles y permisos

- **Mantenimiento**: Limpieza de datos de prueba, verificación de eliminaciones, exportación de configuración a seeders (vía API)

### Características técnicas del backend

- **API REST** bajo `/api/v1` con respuestas JSON unificadas
- **Autenticación** Bearer con Laravel Sanctum
- **Documentación** OpenAPI/Swagger en `/docs` (cuando `SWAGGER_ENABLED=true`)
- **CORS** configurable (`FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`)
- **Roles y permisos** (Super Admin, Contador, Administrador, etc.)
- **Exportación** a PDF (DomPDF, mPDF) y Excel (PhpSpreadsheet)
- **Health** en `GET /api/v1/health` para load balancers y monitoreo

## 📋 Requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18.x y npm (para compilar assets si se usan vistas Blade)
- MySQL >= 5.7 o MariaDB >= 10.3
- Extensiones PHP: BCMath, Ctype, Curl, Dom, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD o Imagick

## 🔧 Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/financiera-intesa-backend.git
   cd financiera-intesa-backend
   ```

2. **Instalar dependencias de PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Node.js** (si compilarás assets)
   ```bash
   npm install
   ```

4. **Configurar el archivo de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar la base de datos** en `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=financiera_intesa
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

6. **Ejecutar migraciones y seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Compilar assets** (opcional; para producción: `npm run build`)
   ```bash
   npm run dev
   ```

8. **Iniciar el servidor**
   ```bash
   php artisan serve
   ```

   La **API** estará en `http://localhost:8000/api/v1`. Health: `GET /api/v1/health`.

## 📡 API

- **Base:** `http://localhost:8000/api/v1` (en producción, la URL que configures en `APP_URL` + `/api/v1`).
- **Autenticación:** `POST /api/v1/auth/login` con `email` y `password`; la respuesta incluye `data.token`. En las peticiones: `Authorization: Bearer {token}`.
- **Documentación:** Con `SWAGGER_ENABLED=true`, la documentación interactiva está en `/docs` y la especificación OpenAPI en `/docs/openapi.json`.
- **Health:** `GET /api/v1/health` (sin auth) para comprobar que el backend responde.

## 🔐 Roles y Permisos

El sistema incluye un sistema completo de roles y permisos:

### Roles principales

- **Super Admin**: Acceso completo a todos los endpoints
- **Contador**: Acceso a contabilidad y reportes
- **Administrador**: Gestión de usuarios y configuración básica

### Permisos

- `access.core`: Acceso al núcleo del sistema
- Permisos específicos por módulo (matrículas, cartera, recibos, etc.)

## 🛠️ Comandos Artisan Personalizados

### Exportar Configuración a Seeders

Exporta la configuración actual del sistema (conceptos, elaboradores, etc.) a archivos de seeders:

```bash
php artisan settings:export-to-seeders
```

Este comando actualiza los siguientes seeders:
- `ConsecutiveSeeder`
- `DebeSeeder`
- `HaberSeeder`
- `ElaboradoSeeder`
- `ConceptoSeeder`
- `ConceptEntryReceiptSeeder`
- `ConceptDischargeReceiptSeeder`
- `OtherConceptosSeeder`
- `EgresoConceptSeeder`
- `AcademicCatalogSeeder`
- `PasswordPrivilegesSeeder`

## 📦 Seeders Disponibles

El sistema incluye los siguientes seeders para la configuración inicial:

- `RolePermissionSeeder`: Roles y permisos del sistema
- `ConsecutiveSeeder`: Consecutivos de documentos
- `DebeSeeder`: Cuentas de debe (plan de cuentas)
- `HaberSeeder`: Cuentas de haber (plan de cuentas)
- `ElaboradoSeeder`: Elaboradores de documentos
- `ConceptoSeeder`: Conceptos generales
- `ConceptEntryReceiptSeeder`: Conceptos de recibos de ingreso
- `ConceptDischargeReceiptSeeder`: Conceptos de recibos de egreso
- `OtherConceptosSeeder`: Otros conceptos
- `EgresoConceptSeeder`: Conceptos de egreso
- `AcademicCatalogSeeder`: Catálogo académico
- `PasswordPrivilegesSeeder`: Privilegios de contraseña

> **Nota**: Los seeders `ThirdActivitySeeder` y `ThirdEntrySeeder` están vacíos por defecto. Las actividades y terceros se crean vía API.

## 🔧 Mantenimiento

Endpoints de mantenimiento (requieren rol Super Admin, vía API):

- **Limpiar Todos los Datos**: Elimina datos de prueba y restaura la configuración desde los seeders
- **Verificación de Eliminaciones**: Comprueba registros eliminados que quedaron como huérfanos
- **Eliminación Física**: Elimina físicamente los registros huérfanos

> **⚠️ Advertencia**: Estas operaciones son irreversibles. Haz un respaldo antes de usarlas.

## 🛠️ Tecnologías

- **Backend**: Laravel 12
- **API**: REST bajo `/api/v1`, Laravel Sanctum (Bearer), documentación OpenAPI (l5-swagger, Swagger UI en `/docs`)
- **Base de datos**: MySQL
- **PDF**: DomPDF, mPDF
- **Excel**: PhpOffice/PhpSpreadsheet
- **Impresión térmica**: EscPos-PHP
- **QR**: SimpleSoftwareIO/simple-qrcode
- **Build (assets)**: Vite

## 📁 Estructura

```
financiera-intesa-backend/
├── app/
│   ├── Console/Commands/      # Comandos Artisan (app:release-check, sanctum:prune-tokens, etc.)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/        # Controladores de la API REST
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/              # Servicios de negocio
│   └── View/Components/
├── database/migrations/       # Migraciones
├── database/seeders/          # Seeders
├── routes/
│   ├── api.php                # Rutas de la API (/api/v1)
│   └── web.php                # Rutas web (p. ej. /docs para Swagger)
├── resources/views/           # Vistas Blade (p. ej. Swagger)
└── public/                    # Document root del servidor
```

## 🚀 Despliegue en producción

Para subir y dejar el backend funcional en producción (servidor, Nginx/Apache, MySQL, SSL, cron, etc.) sigue la guía:

- **[docs/GUIA_PRODUCCION.md](docs/GUIA_PRODUCCION.md)** — Guía completa de despliegue en producción

Documentos adicionales en `docs/`:
- `DEPLOY_CHECKLIST.md` — Checklist detallado (BD, storage, PDF/XLSX, seguridad)
- `STAGING_RUNBOOK.md` — Comandos paso a paso y smoke tests
- `env.production.example` — Plantilla de `.env` para producción

## 🔐 Seguridad

- No subas el archivo `.env` al repositorio
- Asegúrate de cambiar la `APP_KEY` en producción
- Cambia las credenciales del usuario superadmin por defecto
- Configura correctamente los permisos de archivos y directorios
- Usa HTTPS en producción
- Implementa políticas de contraseñas seguras
- Revisa regularmente los logs de seguridad

## 🐛 Solución de Problemas

### Error: "Cannot truncate a table referenced in a foreign key constraint"

Este error puede ocurrir al limpiar datos. El sistema maneja esto automáticamente deshabilitando temporalmente las verificaciones de claves foráneas.

### El usuario superadmin no tiene ID 1

El seeder está configurado para asegurar que el usuario superadmin siempre tenga ID 1. Si esto no ocurre, ejecuta:

```bash
php artisan db:seed --class=DatabaseSeeder
```

### Los seeders no actualizan la configuración

Usa el comando personalizado para exportar la configuración actual:

```bash
php artisan settings:export-to-seeders
```

## 📝 Licencia

Este proyecto es software propietario. Todos los derechos reservados.

## 👥 Contribuidores

- Steban Fabian Pineda Aguilera

## 📞 Soporte

Para soporte, envía un email a pinedasteban13@gmail.com o abre un issue en el repositorio.

---

Desarrollado con ❤️ usando Laravel
