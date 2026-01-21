# Testing — API y tests

## 1. Requisitos

- **MySQL** en ejecución.
- **Base de datos de test** creada (por ejemplo `financiera_intesa_test`).
- Credenciales de MySQL: `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` cuando no usas los valores por defecto (p. ej. en `.env` o como variables de entorno al ejecutar).

`phpunit.xml` usa SQLite (`:memory:`) por defecto. Las migraciones del proyecto incluyen `MODIFY`/`CHANGE` (sintaxis MySQL), por lo que hay que **sobrescribir** la conexión con MySQL mediante variables de entorno al correr los tests. No se modifica `phpunit.xml`; solo se documenta el override.

---

## 2. Crear la base de datos de test

En MySQL (o cliente):

```sql
CREATE DATABASE financiera_intesa_test;
```

(O el nombre que vayas a usar en `DB_DATABASE`.)

---

## 3. Ejecutar migraciones y tests

Los tests con `RefreshDatabase` ejecutan las migraciones en la BD de test. Asegúrate de que `DB_DATABASE` apunte a la BD de test (no a desarrollo).

### Linux / macOS

```bash
DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test php artisan test tests/Feature/Api/V1/SmokeTest.php
```

Con credenciales explícitas:

```bash
DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test DB_HOST=127.0.0.1 DB_USERNAME=user DB_PASSWORD=pass php artisan test tests/Feature/Api/V1/SmokeTest.php
```

### Windows (PowerShell)

```powershell
$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; php artisan test tests/Feature/Api/V1/SmokeTest.php
```

Con credenciales:

```powershell
$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; $env:DB_USERNAME="user"; $env:DB_PASSWORD="pass"; php artisan test tests/Feature/Api/V1/SmokeTest.php
```

---

## 4. Tests de headers de streams (PDF/XLSX)

El archivo `tests/Feature/Api/V1/StreamsHeadersTest.php` valida que los endpoints de descarga devuelvan los headers correctos:

- **XLSX:** `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `Content-Disposition: attachment; filename="..."`
- **PDF:** `Content-Type: application/pdf`, `Content-Disposition: inline; filename="..."`

### Ejecutar StreamsHeadersTest

#### Linux / macOS

```bash
DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test php artisan test tests/Feature/Api/V1/StreamsHeadersTest.php
```

Con credenciales explícitas:

```bash
DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test DB_HOST=127.0.0.1 DB_USERNAME=user DB_PASSWORD=pass php artisan test tests/Feature/Api/V1/StreamsHeadersTest.php
```

#### Windows (PowerShell)

```powershell
$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; php artisan test tests/Feature/Api/V1/StreamsHeadersTest.php
```

Con credenciales:

```powershell
$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; $env:DB_USERNAME="user"; $env:DB_PASSWORD="pass"; php artisan test tests/Feature/Api/V1/StreamsHeadersTest.php
```

### Tests incluidos

- **test_xlsx_abonos_download_has_headers:** Valida headers del endpoint de descarga de abonos (XLSX).
- **test_pdf_attendance_sheet_has_headers:** Valida headers del endpoint de generación de planilla de asistencia (PDF).

**Nota:** Los tests crean datos mínimos necesarios (Program, Schedule, Group, Teacher, Module) si no existen, para que solo validen los headers sin depender de datos previos.

---

## 5. Troubleshooting

| Problema | Causa probable | Qué hacer |
|----------|----------------|-----------|
| **Unknown database** | La BD de test no existe | Crear con `CREATE DATABASE financiera_intesa_test;` (o el nombre que uses en `DB_DATABASE`). |
| **Access denied** | Usuario, contraseña o host incorrectos | Revisar `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST`. Comprobar que el usuario tenga permisos sobre la BD de test. |
| **Sanctum / `personal_access_tokens`** | Falta la tabla o la migración de Sanctum | Ejecutar migraciones en la BD de test; si usas `RefreshDatabase`, se aplican al correr los tests. Si falla `createToken`, revisar que exista la migración de `personal_access_tokens` (Sanctum). |
| **Roles o permisos** | El seeder de roles/permisos no se ha ejecutado o no existe el rol | Confirmar que `RolePermissionSeeder` corre en el test (p. ej. en `setUp`). Verificar que exista el rol `secretaria` (y que tenga `access.core` si el test lo usa). |
| **Headers de streams no coinciden** | El endpoint no está configurando correctamente los headers | Revisar que `Content-Type` y `Content-Disposition` estén configurados en el controlador. Para `BinaryFileResponse`, usar `$response->headers->set()` en lugar de `->header()`. |