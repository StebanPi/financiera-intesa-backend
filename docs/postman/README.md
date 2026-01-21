# Postman — Financiera Intesa API v1

Colección y environment para probar la API v1 desde Postman.

## Archivos

- **collection.json** — Colección Postman v2.1 con todas las rutas de la API v1.
- **environment.json** — Variables de entorno: `base_url`, `token`, `cod_alumno`, `id`, `resource`, `fecha_inicio`, `fecha_fin`, etc.

## Cómo importar

1. Abrir Postman.
2. **Importar la colección:**  
   `Import` → `Upload Files` → seleccionar `docs/postman/collection.json`.
3. **Importar el environment:**  
   `Import` → `Upload Files` → seleccionar `docs/postman/environment.json`.
4. En la esquina superior derecha, en el selector de entorno, elegir **"Financiera Intesa API v1"**.

## Primer request: obtener token (Login)

Casi todos los endpoints exigen `Authorization: Bearer {{token}}`. Para obtener el token:

1. Ir a la carpeta **Auth**.
2. Ejecutar **"Login"**.
3. Ajustar el body si hace falta:
   - `email`: usuario existente (ej. `admin@example.com`).
   - `password`: contraseña correcta.
4. Si la respuesta es **200**, el test de la pestaña **Tests** guarda el token en la variable de entorno `token`.  
   A partir de ahí, el resto de requests que usan `{{token}}` lo tomarán del environment.

## Variables de entorno

| Variable       | Uso                                                                 | Ejemplo     |
|----------------|---------------------------------------------------------------------|-------------|
| `base_url`     | Origen de la API (sin barra final)                                  | `http://localhost:8000` |
| `token`        | Bearer token; se rellena al hacer **Login** con éxito               | (vacío por defecto)     |
| `cod_alumno`   | Para matriculas, PDF matrícula, upload foto                         | `987654321` |
| `id`           | IDs numéricos (costs, purses, entries, consecutives, etc.)          | `1`         |
| `id_cost`      | Opcional; para algunos bodies                                       | `1`         |
| `resource`     | Catálogo en Settings: `programs`, `schedules`, `groups`, etc.       | `programs`  |
| `fecha_inicio` | Reportes contables por rango                                        | `2024-01-01`|
| `fecha_fin`    | Reportes contables por rango                                        | `2024-01-31`|
| `fecha`        | Arqueo diario, informe semanal                                      | `2024-01-15`|
| `month_year`   | Informe mensual                                                     | `2024-01`   |
| `role`         | Admin roles                                                         | `1`         |
| `user`         | Admin users                                                         | `1`         |
| `type`         | Financial receipts: `entry`, `other-entry`, `egreso`, `third`       | `entry`     |
| `program_id`   | ID de programa (para planilla de asistencia)                       | `1`         |
| `schedule_id`  | ID de horario (para planilla de asistencia)                        | `1`         |
| `group_id`     | ID de grupo (para planilla de asistencia)                          | `1`         |
| `teacher_id`   | ID de docente (para planilla de asistencia)                        | `1`         |
| `module_id`    | ID de módulo (para planilla de asistencia)                         | `1`         |
| `fecha_clase`  | Fecha de clase (para planilla de asistencia)                       | `2024-01-15`|
| `entry_id`     | ID de entry creado (se guarda automáticamente)                     | (vacío por defecto) |

## Endpoints que devuelven archivo (PDF / XLSX)

- **Attendance Sheet** → Planilla PDF (generate)
- **Matriculas** → PDF, **Upload foto** (form-data, clave `foto`)
- **Financial Receipts** → PDF (stream)
- **Accounting** → Todos los `.../download` (XLSX)

Para guardar el archivo en disco: en Postman usar **"Send and Download"** (junto a **Send**).  
En esos requests no se fuerza `Accept: application/json`; el `Accept` es `*/*`.

## Tests incluidos

- **Login:** si hay 200 y `data.token`, se hace `pm.environment.set("token", data.token)`.
- **Me (sin token - espera 401):** comprueba `error.code === "UNAUTHENTICATED"`.
- **Resto de JSON:** comprueban código 2xx y que exista `data`, `error` o `message`.

## Happy Path (QA)

La carpeta **"Happy Path (QA)"** contiene un flujo automatizado para realizar QA del backend de forma repetible sin frontend. Incluye requests que prueban endpoints clave con validaciones automáticas.

### Variables necesarias

Antes de ejecutar el "Happy Path (QA)", configura las siguientes variables en el environment:

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `program_id` | ID de un programa existente en la BD | `1` |
| `schedule_id` | ID de un horario existente | `1` |
| `group_id` | ID de un grupo existente | `1` |
| `teacher_id` | ID de un docente existente | `1` |
| `module_id` | ID de un módulo existente | `1` |
| `id_cost` | ID de un cost (cartera) existente | `1` |
| `concepto` | ID de concepto (1 o 2) | `1` |
| `elaborado_por` | ID de elaborado existente | `1` |
| `debe` | ID de debe existente | `1` |
| `haber` | ID de haber existente | `1` |

Las siguientes variables tienen valores por defecto que puedes ajustar:

- `fecha_inicio`, `fecha_fin`, `fecha_clase`, `fecha_recibo`: fechas en formato `YYYY-MM-DD`
- `descripcion`: descripción del entry de prueba
- `valor`: valor numérico del entry
- `forma`: `Efectivo`, `Bancos` o `Consignación`

### Flujo del Happy Path

El orden de los requests en la carpeta es:

1. **Auth/Login** — Obtiene el token y lo guarda en `{{token}}`
   - Test: status 200
   - Test: guarda token en environment

2. **Home** — Verifica endpoint protegido básico
   - Test: status 200
   - Test: `data.message` existe

3. **Health** — Verifica endpoint de salud (sin auth)
   - Test: status 200
   - Test: `data.status === "ok"`

4. **Accounting XLSX** — Descarga de Excel de abonos
   - Test: status 200
   - Test: header `Content-Type` contiene `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
   - Test: header `Content-Disposition` contiene `attachment`

5. **Attendance-sheet PDF** — Genera PDF de planilla de asistencia
   - Test: status 200
   - Test: header `Content-Type` contiene `application/pdf`
   - Test: header `Content-Disposition` contiene `inline`

6. **Entries/Create** — Crea un entry de prueba
   - Test: status 201
   - Test: guarda `entry_id` en environment
   - Test: `data.no_recibo` existe (si `concepto != 2`)

7. **Entries/Delete** — Elimina el entry creado
   - Test: status 200
   - Test: `message` contiene "Eliminado"

### Cómo ejecutar con Collection Runner

1. Abre Postman.
2. Selecciona la carpeta **"Happy Path (QA)"** en la colección.
3. Haz clic en **"Run"** (o ve a `View` → `Show Postman Console`).
4. En el Collection Runner:
   - Verifica que el environment correcto esté seleccionado.
   - Configura las variables necesarias si faltan.
   - Haz clic en **"Run Happy Path (QA)"**.
5. Revisa los resultados:
   - Los tests automáticos muestran PASS/FAIL para cada request.
   - Si un request falla, revisa el mensaje de error y las variables.

**Nota:** Los requests de descarga (XLSX/PDF) no descargan el archivo automáticamente en el Runner, pero los tests validan los headers correctamente. Para descargar manualmente, usa "Send and Download" en esos requests individuales.

## Regenerar la colección

Si se añaden o cambian rutas en `routes/api.php`:

```bash
php build-postman-collection.php
```

El script está en la raíz del proyecto y sobrescribe `docs/postman/collection.json`.
