# Endpoints API para la vista /matricula

Resumen de **todos los endpoints de la API v1** necesarios para hacer funcional el módulo de matrícula: listado (`/matricula`), crear (`/matricula/create`), ver estudiante (`/matricula/estudiante/{cod_alumno}`) y editar ficha (`/matricula/ficha/{cod_alumno}`).

**Base URL:** `https://tu-dominio.com/api/v1`  
**Autenticación:** Todas las rutas (salvo `health` y `auth/*`) requieren `Authorization: Bearer {token}` y permiso `access.core`. Los catálogos `settings/*` requieren además `settings.manage`.

---

## 1. Autenticación

| Método | Endpoint | Uso |
|--------|----------|-----|
| `POST` | `/auth/login` | Login (email, password) → token |
| `GET`  | `/auth/me`    | Usuario actual (para validar sesión) |
| `POST` | `/auth/logout`| Cerrar sesión |

---

## 2. Matrículas (CRUD + foto + PDF)

| Método | Endpoint | Uso en /matricula |
|--------|----------|-------------------|
| `GET`    | `/matriculas?search=&programa=&horario=&tipo_documento=&per_page=15` | **Index**: listar y filtrar matrículas |
| `GET`    | `/matriculas/{cod_alumno}` | **Show / Ficha**: ver una matrícula |
| `POST`   | `/matriculas` | **Create**: matricular nuevo estudiante |
| `PUT` / `PATCH` | `/matriculas/{cod_alumno}` | **Ficha**: actualizar datos |
| `DELETE` | `/matriculas/{cod_alumno}?confirmar_cascada=1` | **Index**: eliminar (opcional cascada) |
| `GET`    | `/matriculas/{cod_alumno}/foto` | **Show / Ficha**: obtener foto del estudiante (stream imagen) |
| `POST`   | `/matriculas/{cod_alumno}/foto` | **Ficha**: subir foto (multipart, campo `foto`) |
| `GET`    | `/matriculas/{cod_alumno}/pdf` | **Ficha**: stream PDF ficha de matrícula |

**Nota:** `cod_alumno` suele ser el número de documento. Sin `confirmar_cascada`, el `DELETE` puede devolver 409 si hay abonos/cuotas y pedir confirmación.  
**Foto:** El campo `photo_url` en la respuesta de `GET /matriculas/{cod_alumno}` apunta a `GET /matriculas/{cod_alumno}/foto` (endpoint de la API) para evitar problemas con URLs relativas en producción.

---

## 3. Catálogos (create + ficha)

Usados en **create** y **ficha** para programas, horarios y grupos. Requieren `permission:settings.manage`.

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET` | `/settings/programs`   | Programas (select programa) |
| `GET` | `/settings/schedules` | Horarios (select horario) |
| `GET` | `/settings/groups`    | Grupos (select numero_grupo) |

Opcional `?per_page=15`. Respuesta paginada en `data` y metadatos en `meta`.

---

## 4. Costos (vista estudiante)

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET`  | `/costs?cod_alumno={cod}&per_page=15` | Listar costos del estudiante |
| `GET`  | `/costs/{id}` | Ver un costo (ej. primer semestre) |
| `POST` | `/costs` | Configurar costos (payload: `cod_alumno`, `semestres`) |
| `PUT` / `PATCH` | `/costs/{id}` | Actualizar costo |
| `DELETE` | `/costs/{id}` | Eliminar costo |

En la vista estudiante se usa el **primer** costo (`costs[0]`) para mostrar valores y para cartera/abonos.

---

## 5. Cartera (purses) – vista estudiante

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET` | `/purses?cod_alumno={cod}&per_page=15` | Listar cuotas del estudiante |
| `GET` | `/purses/totales?cod_alumno={cod}` o `?id_cost={id}` | Saldo a favor, pendiente, etc. |
| `GET` | `/purses/cartera?cod_alumno={cod}` o `?id_cost={id}` | Cartera completa (cuotas + totales) |
| `GET` | `/purses/{id}` | Detalle de una cartera |
| `GET` | `/purses/{id}/history` | Historial de una cartera |

`cod_alumno` o `id_cost` es obligatorio en `totales` y `cartera`.

---

## 6. Abonos (entries) – vista estudiante

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET`  | `/entries?cod_alumno={cod}&id_cost=&from=&to=` | Listar abonos del estudiante |
| `POST` | `/entries` | Registrar abono |
| `GET`  | `/entries/{id}` | Ver abono |
| `DELETE` | `/entries/{id}` | Eliminar abono |

---

## 7. Otros ingresos (other-entries) – vista estudiante

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET`  | `/other-entries?cod_alumno={cod}&id_cost=&from=&to=` | Listar otros ingresos |
| `POST` | `/other-entries` | Registrar otro ingreso |
| `GET`  | `/other-entries/{id}` | Ver otro ingreso |
| `DELETE` | `/other-entries/{id}` | Eliminar otro ingreso |

---

## 8. Recibos financieros (PDF)

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET` | `/financial-receipts/entry/{id}/pdf` | PDF recibo de abono |
| `GET` | `/financial-receipts/other-entry/{id}/pdf` | PDF recibo otro ingreso |
| `GET` | `/financial-receipts/entry/{id}` | Datos JSON del recibo (abono) |
| `GET` | `/financial-receipts/other-entry/{id}` | Datos JSON del recibo (otro ingreso) |

Opcionales: `?paper=76|80`, `?offset=8`.  
En la vista web, “Pagos” usa un **PDF unificado** (abonos + otros ingresos por `id_cost`). Ese endpoint **no existe en la API**; solo hay PDF por tipo (entry / other-entry). Para “Pagos” unificado habría que añadir un endpoint específico o construir el PDF en el frontend.

---

## 9. Consecutivos

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET` | `/consecutives` | Listar consecutivos (p. ej. tipo `entry` para abonos) |
| `GET` | `/consecutives/{id}` | Ver uno |
| `POST` | `/consecutives` | Crear |
| `PUT` / `PATCH` | `/consecutives/{id}` | Actualizar |

Se usan al registrar abonos/otros ingresos (consecutivo de recibo).

---

## 10. Catálogos para modales de abonos (vista estudiante)

Para los formularios de abonos y otros ingresos en la vista estudiante:

| Método | Endpoint | Uso |
|--------|----------|-----|
| `GET` | `/settings/conceptos`      | Conceptos de abono |
| `GET` | `/settings/elaborados`     | Elaborado por |
| `GET` | `/settings/habers`         | Haber |
| `GET` | `/settings/debes`          | Debe |
| `GET` | `/settings/otros-conceptos`| Otros conceptos |

Requieren `permission:settings.manage`.

---

## 11. Resumen por vista (Next.js)

### 11.1. `/enrollment` (Listado de Matrículas)

**Vista:** Página principal que muestra una tabla con todas las matrículas.

**Endpoints necesarios:**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `GET /api/v1/matriculas?search=&programa=&horario=&tipo_documento=&per_page=15` | **Al cargar la página** y al aplicar filtros | Parámetros opcionales: `search` (búsqueda general), `programa`, `horario`, `tipo_documento`, `per_page` (default 15). Respuesta paginada. |
| `DELETE /api/v1/matriculas/{cod_alumno}` | **Al eliminar una matrícula** (botón eliminar) | Si hay abonos/cuotas relacionados, puede devolver `409` con mensaje. Opcional: `?confirmar_cascada=1` para forzar eliminación en cascada. |

**Flujo típico:**
1. Cargar página → `GET /matriculas?per_page=25`
2. Usuario busca/filtra → `GET /matriculas?search=Juan&programa=Auxiliar...`
3. Usuario elimina → `DELETE /matriculas/12345678` (con confirmación previa)

---

### 11.2. `/enrollment/create` (Crear Nueva Matrícula)

**Vista:** Formulario para matricular un nuevo estudiante.

**Endpoints necesarios:**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `GET /api/v1/settings/programs` | **Al cargar la página** | Llenar select "Programa". Requiere `settings.manage`. |
| `GET /api/v1/settings/schedules` | **Al cargar la página** | Llenar select "Horario". Requiere `settings.manage`. |
| `GET /api/v1/settings/groups` | **Al cargar la página** | Llenar select "Número de Grupo". Requiere `settings.manage`. |
| `POST /api/v1/matriculas` | **Al enviar el formulario** (submit) | Payload: ver `MatriculaStoreRequest` (nombre_completo, numero_documento, tipo_documento, programa, sede, horario, etc.). Respuesta `201` con `MatriculaResource`. |

**Flujo típico:**
1. Cargar página → Fetch paralelo de `programs`, `schedules`, `groups`
2. Usuario completa formulario
3. Submit → `POST /matriculas` con todos los campos
4. Si éxito → Redirect a `/enrollment` o `/enrollment/wallet/{cod_alumno}`

**Nota:** El `cod_alumno` se genera automáticamente (suele ser igual a `numero_documento`).

---

### 11.3. `/enrollment/wallet/{cod}` (Vista Estudiante / Cartera)

**Vista:** Dashboard completo del estudiante con información financiera (costos, cartera, abonos, otros ingresos).

**Endpoints necesarios (carga inicial):**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `GET /api/v1/matriculas/{cod_alumno}` | **Al cargar la página** | Datos básicos del estudiante (nombre, documento, programa, etc.). Incluye `photo_url` que apunta a `GET /matriculas/{cod_alumno}/foto` |
| `GET /api/v1/matriculas/{cod_alumno}/foto` | **Al mostrar la foto** | Obtener foto del estudiante (stream imagen binaria). Usar este endpoint directamente en `<img src={photo_url}>` |
| `GET /api/v1/costs?cod_alumno={cod}` | **Al cargar la página** | Lista de costos del estudiante. Usar el **primer** costo (`costs[0]`) como `id_cost` principal. |
| `GET /api/v1/purses/totales?cod_alumno={cod}` | **Al cargar la página** | Totales: `saldo_a_favor`, `saldo_pendiente`, `total_abonado`, etc. |
| `GET /api/v1/purses/cartera?cod_alumno={cod}` | **Al cargar la página** | Cartera completa: todas las cuotas con estados, fechas, valores. |
| `GET /api/v1/entries?cod_alumno={cod}` | **Al cargar la página** | Lista de abonos (pagos) del estudiante. |
| `GET /api/v1/other-entries?cod_alumno={cod}` | **Al cargar la página** | Lista de otros ingresos del estudiante. |
| `GET /api/v1/consecutives` | **Al cargar la página** (opcional) | Para mostrar consecutivos disponibles al crear abonos. Filtrar por `type: 'entry'`. |

**Endpoints para catálogos (modales de formularios):**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `GET /api/v1/settings/conceptos` | **Al abrir modal "Registrar Abono"** | Conceptos para el select de concepto. Requiere `settings.manage`. |
| `GET /api/v1/settings/elaborados` | **Al abrir modal "Registrar Abono"** | Elaborado por (select). Requiere `settings.manage`. |
| `GET /api/v1/settings/habers` | **Al abrir modal "Registrar Abono"** | Haber (select). Requiere `settings.manage`. |
| `GET /api/v1/settings/debes` | **Al abrir modal "Registrar Abono"** | Debe (select). Requiere `settings.manage`. |
| `GET /api/v1/settings/otros-conceptos` | **Al abrir modal "Registrar Otro Ingreso"** | Otros conceptos. Requiere `settings.manage`. |

**Endpoints para acciones (POST/DELETE):**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `POST /api/v1/costs` | **Al configurar/actualizar costos** (modal "Configurar Costos") | Payload: `{ cod_alumno: string, semestres: array }`. Sincroniza costos por semestre. |
| `POST /api/v1/entries` | **Al registrar un abono** (submit modal) | Payload: ver `EntryStoreRequest` (cod_alumno, id_cost, valor, concepto, fecha, etc.). |
| `POST /api/v1/other-entries` | **Al registrar otro ingreso** (submit modal) | Payload: ver `OtherEntryStoreRequest`. |
| `DELETE /api/v1/entries/{id}` | **Al eliminar un abono** (botón eliminar) | Elimina el abono específico. |
| `DELETE /api/v1/other-entries/{id}` | **Al eliminar otro ingreso** (botón eliminar) | Elimina el otro ingreso específico. |

**Endpoints para PDFs:**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `GET /api/v1/financial-receipts/entry/{id}/pdf` | **Al descargar PDF de un abono** | Stream PDF. Opcionales: `?paper=76|80`, `?offset=8`. |
| `GET /api/v1/financial-receipts/other-entry/{id}/pdf` | **Al descargar PDF de otro ingreso** | Stream PDF. Opcionales: `?paper=76|80`, `?offset=8`. |

**Flujo típico:**
1. Cargar página → Fetch paralelo de: `matriculas/{cod}`, `costs?cod_alumno=`, `purses/totales`, `purses/cartera`, `entries?cod_alumno=`, `other-entries?cod_alumno=`
2. Usuario abre modal "Registrar Abono" → Fetch de catálogos: `conceptos`, `elaborados`, `habers`, `debes`
3. Usuario registra abono → `POST /entries` → Refrescar lista de entries
4. Usuario descarga PDF → `GET /financial-receipts/entry/{id}/pdf` (abrir en nueva pestaña o descargar)

**Nota importante:** El PDF "Pagos" unificado (todos los pagos por `id_cost`) **no existe en la API**. Solo hay PDFs individuales por tipo (entry / other-entry). Si necesitas ese PDF unificado, tendrías que:
- Crear un endpoint nuevo en el backend, o
- Construir el PDF en el frontend combinando los datos de entries + other-entries.

---

### 11.4. `/enrollment/file/{cod}` (Editar Ficha de Matrícula)

**Vista:** Formulario para editar los datos de la ficha de matrícula del estudiante.

**Endpoints necesarios (carga inicial):**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `GET /api/v1/matriculas/{cod_alumno}` | **Al cargar la página** | Cargar datos actuales del estudiante para prellenar el formulario. Incluye `photo_url` para mostrar la foto actual. |
| `GET /api/v1/matriculas/{cod_alumno}/foto` | **Al mostrar la foto** | Obtener foto del estudiante (stream imagen). Usar en `<img src={photo_url}>` o directamente este endpoint. |
| `GET /api/v1/settings/programs` | **Al cargar la página** | Llenar select "Programa". Requiere `settings.manage`. |
| `GET /api/v1/settings/schedules` | **Al cargar la página** | Llenar select "Horario". Requiere `settings.manage`. |
| `GET /api/v1/settings/groups` | **Al cargar la página** | Llenar select "Número de Grupo". Requiere `settings.manage`. |

**Endpoints para acciones:**

| Endpoint | Cuándo se usa | Notas |
|----------|---------------|-------|
| `PUT` o `PATCH /api/v1/matriculas/{cod_alumno}` | **Al guardar cambios** (submit formulario) | Payload: campos actualizados (nombre_completo, programa, horario, etc.). Ver `MatriculaUpdateRequest`. |
| `POST /api/v1/matriculas/{cod_alumno}/foto` | **Al subir foto** (input file change) | Multipart form-data, campo `foto` (image). Respuesta incluye `photo_url` actualizado. |
| `GET /api/v1/matriculas/{cod_alumno}/pdf` | **Al descargar/ver PDF** (botón "Ver PDF" o "Descargar PDF") | Stream PDF de la ficha de matrícula. Se puede abrir en nueva pestaña o descargar. |

**Flujo típico:**
1. Cargar página → Fetch paralelo de: `matriculas/{cod}`, `programs`, `schedules`, `groups`
2. Prellenar formulario con datos de `matriculas/{cod}`
3. Usuario edita campos
4. Usuario sube foto → `POST /matriculas/{cod}/foto` (puede ser independiente del submit)
5. Usuario guarda → `PUT /matriculas/{cod}` → Redirect o mostrar mensaje de éxito
6. Usuario descarga PDF → `GET /matriculas/{cod}/pdf` (abrir en nueva pestaña)

---

### Resumen rápido por vista

| Vista Next.js | Endpoints principales (GET inicial) | Endpoints de acción (POST/PUT/DELETE) |
|---------------|--------------------------------------|----------------------------------------|
| `/enrollment` | `GET /matriculas` (con filtros) | `DELETE /matriculas/{cod}` |
| `/enrollment/create` | `GET /settings/programs`, `schedules`, `groups` | `POST /matriculas` |
| `/enrollment/wallet/{cod}` | `GET /matriculas/{cod}`, `GET /costs?cod_alumno=`, `GET /purses/totales`, `GET /purses/cartera`, `GET /entries?cod_alumno=`, `GET /other-entries?cod_alumno=`, `GET /consecutives`, `GET /settings/conceptos`, `elaborados`, `habers`, `debes`, `otros-conceptos` | `POST /costs`, `POST /entries`, `POST /other-entries`, `DELETE /entries/{id}`, `DELETE /other-entries/{id}`, `GET /financial-receipts/{type}/{id}/pdf` |
| `/enrollment/file/{cod}` | `GET /matriculas/{cod}`, `GET /settings/programs`, `schedules`, `groups` | `PUT /matriculas/{cod}`, `POST /matriculas/{cod}/foto`, `GET /matriculas/{cod}/pdf` |

---

## 12. Sin equivalente en API (solo web)

- **PDF “Pagos” unificado** (abonos + otros ingresos por `id_cost`): ruta web `entry.ViewPdfUnitedOther`. En API solo existen PDFs por tipo (entry / other-entry).
- **PDF cuota (cartera)**: `purse.Viewpdfc` en web. No hay `GET /purses/{id}/pdf` en la API.
- **Editar cuota (cartera)**: `POST /purse/edit` en web (FormPurseEdit). La API solo tiene GET de carteras; no hay `PUT /purses/{id}`.
- **Eliminar todos los costos de un estudiante**: `POST /cost/eliminar/{cod_alumno}` (web, solo auth). No hay equivalente en API.

Si quieres parity total con la web, habría que exponer estos casos como nuevos endpoints.

---

## 13. Ejemplo de flujo para “Ver estudiante”

1. `GET /auth/me` (validar token).
2. `GET /matriculas/{cod_alumno}` → datos del estudiante.
3. `GET /costs?cod_alumno={cod}` → costos; usar el primero como `id_cost` si existe.
4. `GET /purses/totales?cod_alumno={cod}` o `?id_cost={id}` → totales (saldo a favor, pendiente).
5. `GET /purses/cartera?cod_alumno={cod}` → cuotas y detalle.
6. `GET /entries?cod_alumno={cod}` y `GET /other-entries?cod_alumno={cod}` → abonos y otros ingresos.
7. Para cada recibo: `GET /financial-receipts/entry/{id}/pdf` o `other-entry/{id}/pdf`.

Con esto tienes todos los endpoints de la API necesarios para hacer funcional la vista `/matricula` y sus subvistas.
