# Análisis Completo: Vista "Matricular Estudiante"

**Ruta original:** `/matricula/create` (Laravel Blade)  
**Ruta destino:** `/dashboard/matriculas/create` (Next.js 16 + App Router)

---

## 1️⃣ ANÁLISIS VISUAL DE LA VISTA

### Layout General

La vista usa un **layout de una sola columna** dentro de un card Bootstrap:

```
┌─────────────────────────────────────────┐
│ Card Header                              │
│ - Título: "Formulario de Inscripción"   │
│ - Botón "Volver" (izquierda)             │
├─────────────────────────────────────────┤
│ Card Body                                │
│ ┌─────────────────────────────────────┐ │
│ │ Formulario (POST /matricula/store)  │ │
│ │                                     │ │
│ │ ┌─ Datos Personales ─────────────┐ │ │
│ │ │ nombre_completo (full width)    │ │ │
│ │ │ numero_documento | tipo_doc     │ │ │
│ │ │ lugar_exp | fecha_nacimiento    │ │ │
│ │ └─────────────────────────────────┘ │ │
│ │                                     │ │
│ │ ┌─ Datos de Residencia ──────────┐ │ │
│ │ │ direccion_barrio (textarea)    │ │ │
│ │ │ ciudad | departamento           │ │ │
│ │ │ correo_gmail                    │ │ │
│ │ └─────────────────────────────────┘ │ │
│ │                                     │ │
│ │ ┌─ Contacto ─────────────────────┐ │ │
│ │ │ telefono_personal | emergencia│ │ │
│ │ └─────────────────────────────────┘ │ │
│ │                                     │ │
│ │ ┌─ Información Adicional ─────────┐ │ │
│ │ │ estado_civil | ocupacion       │ │ │
│ │ │ nivel_formacion | estrato      │ │ │
│ │ │ nivel_sisben | eps              │ │ │
│ │ │ grupo_sanguineo | discapacidad  │ │ │
│ │ │ [tipo_discapacidad] (condicional)│ │
│ │ └─────────────────────────────────┘ │ │
│ │                                     │ │
│ │ ┌─ Información Académica ─────────┐ │ │
│ │ │ programa | sede                 │ │ │
│ │ │ horario | estado_estudiante     │ │ │
│ │ │ semestre | año | grupo | talla  │ │ │
│ │ │ contraseña_plataforma           │ │ │
│ │ └─────────────────────────────────┘ │ │
│ │                                     │ │
│ │ Botones: [Guardar] [Cancelar]      │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

### Jerarquía Visual

1. **Card principal** con header y body
2. **Secciones con títulos** (`<h5>` con iconos Font Awesome):
   - 📋 Datos Personales
   - 📍 Datos de Residencia
   - 📞 Contacto
   - ℹ️ Información Adicional
   - 🎓 Información Académica
3. **Grid Bootstrap** (`row` + `col-md-6` o `col-md-12`):
   - Campos en 2 columnas (50/50) o full width
   - Responsive: en móvil se apilan
4. **Campos agrupados** por sección temática

### Tipos de Inputs

| Tipo | Campos | Notas |
|------|--------|-------|
| **text** | `nombre_completo`, `numero_documento`, `lugar_expedicion_documento`, `ciudad_residencia`, `correo_gmail`, `telefono_personal`, `telefono_emergencia`, `nivel_sisben`, `eps`, `contraseña_plataforma` | Texto libre |
| **email** | `correo_gmail` | Validación HTML5 |
| **date** | `fecha_nacimiento` | Date picker nativo |
| **textarea** | `direccion_barrio`, `discapacidad_descripcion` | 2 filas |
| **select** | `tipo_documento`, `departamento`, `estado_civil`, `ocupacion`, `nivel_formacion`, `estrato`, `grupo_sanguineo`, `tiene_discapacidad`, `tipo_discapacidad`, `programa`, `sede`, `horario`, `estado_estudiante`, `semestre_actual`, `anio`, `numero_grupo`, `talla_uniforme` | Dropdowns estáticos o dinámicos |

### Estados Visuales

#### Estado Vacío (Inicial)
- Todos los campos vacíos
- Selects con opción "Seleccione..."
- `tipo_discapacidad` y `discapacidad_descripcion` **ocultos** (solo se muestran si `tiene_discapacidad == "Sí"`)

#### Estado Loading
- **No hay loading explícito** en la vista Blade
- El submit del formulario es síncrono (POST tradicional)
- En Next.js deberías mostrar spinner en el botón "Guardar" durante el submit

#### Estado Error
- **Modal de errores** (`<x-error-modal>`) se muestra automáticamente si `$errors->any()`
- **Errores por campo**: cada input con error tiene:
  - Clase `is-invalid` en el input
  - `<div class="invalid-feedback">` debajo con el mensaje
  - `aria-invalid="true"`
- **Errores de validación** se muestran en modal Bootstrap rojo con lista
- **Valores preservados**: `old('campo')` mantiene lo que el usuario escribió

#### Estado Success
- **No se muestra en esta vista** (es de creación)
- Después de guardar exitoso: **redirect a `/matricula`** (index) con mensaje flash `success`
- El mensaje se muestra en la vista de listado

### Botones Visibles

1. **"Guardar Ficha de Matrícula"** (botón primario)
   - Tipo: `submit`
   - Icono: `fa-solid fa-save`
   - Acción: envía el formulario POST
   - Estados: normal (siempre habilitado, validación en submit)

2. **"Cancelar"** (botón secundario)
   - Tipo: `<a>` link
   - Icono: `fa-solid fa-times`
   - Acción: `route('matricula.index')` → redirige a listado
   - No tiene estados especiales

3. **"Volver"** (en header del card)
   - Tipo: `<a>` link
   - Icono: `fa-solid fa-arrow-left`
   - Acción: `route('matricula.index')` → redirige a listado

---

## 2️⃣ CAMPOS DEL FORMULARIO

### Datos Personales

| Campo Visual | name | Tipo | Obligatorio | Validaciones | Dependencias |
|--------------|------|------|-------------|--------------|--------------|
| **Nombre Completo** | `nombre_completo` | text | ✅ Sí | `required`, `string`, `max:255` | Ninguna |
| **Número de Documento** | `numero_documento` | text | ✅ Sí | `required`, `string`, `max:255`, `unique:matriculas,numero_documento` | **Genera `cod_alumno` automáticamente** (cod_alumno = numero_documento) |
| **Tipo de Documento** | `tipo_documento` | select | ✅ Sí | `required`, `in:CC,TI,PPT` | Ninguna |
| **Lugar de Expedición** | `lugar_expedicion_documento` | text | ❌ No | `nullable`, `string` | Ninguna |
| **Fecha de Nacimiento** | `fecha_nacimiento` | date | ❌ No | `nullable`, `date` | Ninguna |

### Datos de Residencia

| Campo Visual | name | Tipo | Obligatorio | Validaciones | Dependencias |
|--------------|------|------|-------------|--------------|--------------|
| **Dirección y Barrio** | `direccion_barrio` | textarea | ❌ No | `nullable`, `string` | Ninguna |
| **Ciudad de Residencia** | `ciudad_residencia` | text | ❌ No | `nullable`, `string` | Ninguna |
| **Departamento** | `departamento` | select | ❌ No | `nullable`, `string`, `max:255` | Lista estática (32 departamentos de Colombia + Bogotá D.C.) |
| **Correo de Gmail** | `correo_gmail` | email | ❌ No | `nullable`, `email` | Ninguna |

### Contacto

| Campo Visual | name | Tipo | Obligatorio | Validaciones | Dependencias |
|--------------|------|------|-------------|--------------|--------------|
| **Teléfono Personal** | `telefono_personal` | text | ❌ No | `nullable`, `string` | Ninguna |
| **Teléfono Emergencia** | `telefono_emergencia` | text | ❌ No | `nullable`, `string` | Ninguna |

### Información Adicional

| Campo Visual | name | Tipo | Obligatorio | Validaciones | Dependencias |
|--------------|------|------|-------------|--------------|--------------|
| **Estado Civil** | `estado_civil` | select | ❌ No | `nullable`, `string`, `max:255` | Lista estática: Soltero, Casado, Divorciado, Viudo, Unión Libre, Separado |
| **Ocupación** | `ocupacion` | select | ❌ No | `nullable`, `string`, `max:255` | Lista estática: Estudiante, Empleado, Independiente, Desempleado, Jubilado, Ama de casa, Comerciante, Profesional, Técnico, Obrero, Agricultor, Otro |
| **Nivel de Formación** | `nivel_formacion` | select | ❌ No | `nullable`, `string`, `max:255` | Lista estática: Primaria, Secundaria, Bachiller, Técnico, Tecnólogo, Universitario, Postgrado |
| **Estrato** | `estrato` | select | ❌ No | `nullable`, `integer` | Lista estática: 1, 2, 3, 4, 5, 6 |
| **Nivel del Sisbén** | `nivel_sisben` | text | ❌ No | `nullable`, `string` | Ninguna |
| **EPS** | `eps` | text | ❌ No | `nullable`, `string` | Ninguna |
| **Grupo Sanguíneo** | `grupo_sanguineo` | select | ❌ No | `nullable`, `string` | Lista estática: O+, O-, A+, A-, B+, B-, AB+, AB- |
| **Discapacidad** | `tiene_discapacidad` | select | ❌ No | `nullable`, `in:No,Sí,Prefiero no decir` | **Si = "Sí" → muestra campos de discapacidad** |
| **Tipo de Discapacidad** | `tipo_discapacidad` | select | ⚠️ Condicional | `required_if:tiene_discapacidad,Sí`, `nullable`, `string`, `max:255` | **Solo visible/requerido si `tiene_discapacidad == "Sí"`**. Lista: Física, Visual, Auditiva, Intelectual, Psicosocial, Múltiple, Otra |
| **Descripción Discapacidad** | `discapacidad_descripcion` | textarea | ❌ No | `nullable`, `string` | Solo visible si `tiene_discapacidad == "Sí"` |

### Información Académica

| Campo Visual | name | Tipo | Obligatorio | Validaciones | Dependencias |
|--------------|------|------|-------------|--------------|--------------|
| **Programa** | `programa` | select | ✅ Sí | `required`, `string`, `max:255`, debe existir en `Program` con `active=true` | **Endpoint:** `GET /api/v1/settings/programs?per_page=100` (solo activos) |
| **Sede** | `sede` | select | ✅ Sí | `required`, `in:Barrancabermeja,Aguachica,Virtual` | Lista estática |
| **Horario** | `horario` | select | ✅ Sí | `required`, `string`, `max:255`, debe existir en `Schedule` con `active=true` | **Endpoint:** `GET /api/v1/settings/schedules?per_page=100` (solo activos) |
| **Estado del Estudiante** | `estado_estudiante` | select | ✅ Sí | `required`, `in:Activo,Inactivo,Por Certificar,Certificado,Retirado,Suspendido,Todos` | Lista estática |
| **Semestre Actual** | `semestre_actual` | select | ✅ Sí | `required`, `in:I,II,Ninguno (curso)` | Lista estática |
| **Año** | `anio` | select | ✅ Sí | `required`, `string`, `max:255` | **Lista dinámica:** años desde 2015 hasta año actual + 1 (generado en PHP) |
| **Número de Grupo** | `numero_grupo` | select | ✅ Sí | `required`, `string`, `max:255`, debe existir en `Group` con `active=true` | **Endpoint:** `GET /api/v1/settings/groups?per_page=100` (solo activos) |
| **Talla Uniforme** | `talla_uniforme` | select | ❌ No | `nullable`, `in:XS,S,M,L,XL,XXL,XXXL` | Lista estática |
| **Contraseña Plataforma** | `contraseña_plataforma` | text | ❌ No | `nullable`, `string`, `max:255` | Ninguna |

### Notas Importantes

- **`cod_alumno`**: Se genera automáticamente = `numero_documento`. **No es un campo del formulario**.
- **`anio`**: Se genera dinámicamente en el servidor (2015 hasta año actual + 1). En Next.js puedes generarlo en el cliente o pedirlo al backend.
- **Campos condicionales**: `tipo_discapacidad` y `discapacidad_descripcion` solo se muestran si `tiene_discapacidad == "Sí"`. JavaScript maneja el show/hide.

---

## 3️⃣ FUNCIONALIDAD DE LA VISTA

### Al Cargar la Vista

1. **Controlador `create()`** ejecuta:
   ```php
   $programs = Program::where('active', true)->orderBy('name')->get();
   $schedules = Schedule::where('active', true)->orderBy('name')->get();
   $groups = Group::where('active', true)->orderBy('name')->get();
   ```
   - Consulta programas, horarios y grupos **activos** desde la BD
   - Los pasa a la vista como `$programs`, `schedules`, `groups`

2. **Vista renderiza**:
   - Formulario vacío
   - Selects de `programa`, `horario`, `numero_grupo` se llenan con datos del servidor
   - Select de `anio` se genera en PHP (2015 hasta año actual + 1)
   - Resto de selects son estáticos (hardcodeados en Blade)

3. **JavaScript inicializa**:
   - Listener en `#tiene_discapacidad` para mostrar/ocultar campos de discapacidad
   - Validación del formulario antes de submit
   - Mensaje informativo en `#numero_documento` sobre generación automática de código

### Al Cambiar Selects

- **`tiene_discapacidad`**: Si cambia a "Sí" → muestra `tipo_discapacidad` y `discapacidad_descripcion`. Si cambia a otro valor → oculta y limpia esos campos.
- **Resto de selects**: No tienen dependencias entre sí. No hay cascadas (ej: programa → horario).

### Al Enviar el Formulario

1. **POST a `/matricula/store`** (ruta web) o **POST `/api/v1/matriculas`** (API)
2. **Validación en backend**:
   - `MatriculaStoreRequest` valida todos los campos
   - Verifica que `programa`, `horario`, `numero_grupo` existan y estén activos
   - Verifica que `numero_documento` sea único
3. **Si hay errores de validación**:
   - Laravel devuelve `422` (API) o redirect back con `$errors` (web)
   - Los errores se muestran:
     - **Modal** con lista de errores (si hay múltiples)
     - **Por campo** con `is-invalid` y mensaje debajo
   - Los valores se preservan con `old()`
4. **Si es exitoso**:
   - **Backend genera `cod_alumno`** = `numero_documento`
   - **Backend busca ID disponible** (reutiliza IDs eliminados, prioriza ID 1)
   - **Inserta en BD** con `DB::table('matriculas')->insert()`
   - **Web**: redirect a `/matricula` con mensaje `success`
   - **API**: devuelve `201` con `MatriculaResource`

### Manejo de Errores

- **Errores de validación**: se muestran en modal y por campo
- **Error de duplicado**: `numero_documento` ya existe → error específico en ese campo
- **Error de programa/horario/grupo inválido**: mensaje personalizado indicando que no está activo o no existe

### Redirección Post-Éxito

- **Web**: `redirect()->route('matricula.index')->with('success', '...')`
- **API**: `201 Created` con JSON `{ "data": {...}, "message": "Matrícula creada." }`

---

## 4️⃣ ENDPOINTS UTILIZADOS

### Endpoints para Cargar Datos Iniciales

| Método | URL | Cuándo se ejecuta | Qué devuelve | Consumido por |
|--------|-----|-------------------|--------------|---------------|
| **GET** | `/api/v1/settings/programs?per_page=100` | Al cargar la vista | Lista paginada de programas activos. Formato: `{ "data": [{ "id": 1, "name": "Técnico en Sistemas", "code": "TS001", "active": true }, ...], "meta": {...} }` | Select `programa` |
| **GET** | `/api/v1/settings/schedules?per_page=100` | Al cargar la vista | Lista paginada de horarios activos. Formato: `{ "data": [{ "id": 1, "name": "Diurno", "active": true }, ...], "meta": {...} }` | Select `horario` |
| **GET** | `/api/v1/settings/groups?per_page=100` | Al cargar la vista | Lista paginada de grupos activos. Formato: `{ "data": [{ "id": 1, "name": "101", "active": true }, ...], "meta": {...} }` | Select `numero_grupo` |

**Nota:** En la vista Blade, estos datos vienen del controlador `create()`, no de API. En Next.js deberás llamarlos desde el cliente o servidor.

### Endpoint para Guardar Matrícula

| Método | URL | Cuándo se ejecuta | Body (JSON) | Respuesta Exitosa | Respuesta Error |
|--------|-----|-------------------|-------------|-------------------|-----------------|
| **POST** | `/api/v1/matriculas` | Al hacer submit del formulario | Todos los campos del formulario (ver `MatriculaStoreRequest`) | `201 Created`: `{ "data": { "id": 1, "cod_alumno": "12345678", "nombre_completo": "...", ... }, "message": "Matrícula creada." }` | `422 Validation Error`: `{ "error": { "code": "VALIDATION_ERROR", "message": "Los datos enviados no son válidos.", "details": { "nombre_completo": ["El nombre completo es obligatorio."], ... } } }` o `401` si no autenticado |

**Campos requeridos en el body:**
```json
{
  "nombre_completo": "Juan Pérez",
  "numero_documento": "1234567890",
  "tipo_documento": "CC",
  "programa": "Técnico en Sistemas",
  "sede": "Barrancabermeja",
  "estado_estudiante": "Activo",
  "horario": "Diurno",
  "semestre_actual": "I",
  "anio": "2024",
  "numero_grupo": "101",
  // ... resto opcionales
}
```

### Endpoints NO Usados en Esta Vista (pero relacionados)

- `GET /api/v1/matriculas` - Listar matrículas (usado en index, no en create)
- `GET /api/v1/matriculas/{cod_alumno}` - Ver matrícula (usado en show/edit)
- `PUT /api/v1/matriculas/{cod_alumno}` - Actualizar (usado en edit)
- `DELETE /api/v1/matriculas/{cod_alumno}` - Eliminar (usado en index)
- `POST /api/v1/matriculas/{cod_alumno}/foto` - Subir foto (usado en edit, no en create)
- `GET /api/v1/matriculas/{cod_alumno}/pdf` - Ver PDF (usado en show/edit)

---

## 5️⃣ BOTONES Y ACCIONES

### Botón "Guardar Ficha de Matrícula"

- **Texto**: "Guardar Ficha de Matrícula"
- **Icono**: `fa-solid fa-save`
- **Tipo**: `submit` (envía el formulario)
- **Acción exacta**:
  1. JavaScript valida campos requeridos antes de submit
  2. Si hay errores → previene submit, muestra `is-invalid`, scroll al primer error
  3. Si pasa validación → POST a `/matricula/store` (web) o `/api/v1/matriculas` (API)
- **Endpoint**: `POST /api/v1/matriculas` (con Bearer token)
- **Redirección posterior**:
  - **Web**: redirect a `/matricula` (index) con mensaje flash
  - **API**: devuelve `201` con JSON (no hay redirect, el frontend maneja la navegación)
- **Estados**:
  - **Normal**: habilitado
  - **Loading**: no hay en Blade (en Next.js deberías deshabilitar y mostrar spinner)
  - **Disabled**: no aplica (validación previa al submit)

### Botón "Cancelar"

- **Texto**: "Cancelar"
- **Icono**: `fa-solid fa-times`
- **Tipo**: `<a>` link
- **Acción exacta**: `route('matricula.index')` → navega a `/matricula` (listado)
- **Endpoint**: ninguno (navegación interna)
- **Redirección**: `/matricula` (listado de matrículas)
- **Estados**: siempre habilitado

### Botón "Volver" (header)

- **Texto**: "Volver"
- **Icono**: `fa-solid fa-arrow-left`
- **Tipo**: `<a>` link
- **Acción exacta**: `route('matricula.index')` → navega a `/matricula`
- **Endpoint**: ninguno
- **Redirección**: `/matricula`
- **Estados**: siempre habilitado

---

## 6️⃣ MANEJO DE ERRORES (Laravel)

### Cómo Laravel Devuelve Errores de Validación

#### En Web (Blade)
- **Redirect back** con `$errors` en sesión
- **Estructura**: `$errors` es un objeto `MessageBag` con:
  - `$errors->all()` → array de todos los mensajes
  - `$errors->get('campo')` → array de errores de ese campo
  - `$errors->has('campo')` → boolean
- **Preservación de datos**: `old('campo')` mantiene el valor enviado

#### En API (JSON)
- **Status**: `422 Unprocessable Entity`
- **Estructura**:
  ```json
  {
    "error": {
      "code": "VALIDATION_ERROR",
      "message": "Los datos enviados no son válidos.",
      "details": {
        "nombre_completo": ["El nombre completo es obligatorio."],
        "numero_documento": ["Este número de documento ya está registrado."],
        "programa": ["El programa seleccionado no es válido o no está activo."]
      }
    }
  }
  ```

### Qué Campos Muestran Error

- **Todos los campos con validación fallida** muestran:
  - Clase `is-invalid` en el input/select
  - `<div class="invalid-feedback">` debajo con el mensaje
  - `aria-invalid="true"` para accesibilidad

### Mensajes Comunes

| Campo | Mensaje de Error |
|-------|------------------|
| `nombre_completo` | "El nombre completo es obligatorio." |
| `numero_documento` | "El número de documento es obligatorio." / "Este número de documento ya está registrado." |
| `tipo_documento` | "El tipo de documento es obligatorio." / "El tipo de documento debe ser CC, TI o PPT." |
| `programa` | "El programa es obligatorio." / "El programa seleccionado no es válido o no está activo." |
| `sede` | "La sede es obligatoria." / "La sede seleccionada no es válida." |
| `horario` | "El horario es obligatorio." / "El horario seleccionado no es válido o no está activo." |
| `numero_grupo` | "El número de grupo es obligatorio." / "El grupo seleccionado no es válido o no está activo." |
| `tipo_discapacidad` | "Debe indicar el tipo de discapacidad." (solo si `tiene_discapacidad == "Sí"`) |

### Errores Generales vs Errores por Campo

- **Errores por campo**: se muestran debajo de cada input con `invalid-feedback`
- **Errores generales**: se muestran en el **modal `<x-error-modal>`** que aparece automáticamente si `$errors->any()`
- **En API**: todos los errores van en `error.details` como objeto clave-valor

---

## 7️⃣ MAPEO A NEXT.JS (TEÓRICO)

### Server Components vs Client Components

#### Server Components (SSR/SSG)
- **Layout base** (`app/dashboard/layout.tsx`): sidebar, estructura general
- **Página wrapper** (`app/dashboard/matriculas/create/page.tsx`): puede ser Server Component que renderiza el Client Component del formulario
- **Carga inicial de catálogos** (opcional): puedes hacer `fetch` en Server Component y pasar datos como props, pero es más común cargarlos en el cliente con TanStack Query

#### Client Components (Interactivos)
- **Formulario completo** (`components/matriculas/CreateMatriculaForm.tsx`): debe ser Client Component porque:
  - Usa `react-hook-form` (hooks)
  - Maneja estado de campos condicionales (discapacidad)
  - Validación en tiempo real
  - Submit con estado loading
- **Selects dinámicos**: pueden ser Client Components que usan TanStack Query para cargar datos

### Qué Debe Usar TanStack Query

- **`useQuery`** para:
  - `GET /api/v1/settings/programs` → llenar select de programas
  - `GET /api/v1/settings/schedules` → llenar select de horarios
  - `GET /api/v1/settings/groups` → llenar select de grupos
- **`useMutation`** para:
  - `POST /api/v1/matriculas` → crear matrícula
  - Con `onSuccess`: redirigir a `/dashboard/matriculas` con toast de éxito
  - Con `onError`: mostrar errores de validación

### Qué Debe Usar react-hook-form

- **Todo el formulario**:
  - `useForm()` con `defaultValues` vacíos
  - `register()` para cada campo
  - `handleSubmit()` para el submit
  - `formState.errors` para mostrar errores por campo
  - `watch('tiene_discapacidad')` para mostrar/ocultar campos de discapacidad condicionalmente

### Qué Debe Usar Zod

- **Schema de validación** que refleje `MatriculaStoreRequest`:
  ```typescript
  const matriculaSchema = z.object({
    nombre_completo: z.string().min(1, "El nombre completo es obligatorio.").max(255),
    numero_documento: z.string().min(1, "El número de documento es obligatorio.").max(255),
    tipo_documento: z.enum(["CC", "TI", "PPT"]),
    programa: z.string().min(1, "El programa es obligatorio."),
    // ... resto de campos
    tipo_discapacidad: z.string().optional().refine(
      (val) => {
        // Si tiene_discapacidad es "Sí", entonces tipo_discapacidad es requerido
        // Esto se maneja con watch() en el form
      }
    )
  });
  ```
- **Resolver**: `zodResolver(matriculaSchema)` en `useForm()`

### Estados a Manejar en el Frontend

1. **Loading de catálogos**:
   - `isLoading` de `useQuery` para programs, schedules, groups
   - Mostrar skeleton o spinner en los selects mientras cargan

2. **Estado del formulario**:
   - `isSubmitting` de `useMutation` → deshabilitar botón "Guardar" y mostrar spinner
   - `errors` de `formState` → mostrar errores por campo
   - `isDirty` → opcional: mostrar advertencia si el usuario intenta salir sin guardar

3. **Campos condicionales**:
   - `watch('tiene_discapacidad')` → mostrar/ocultar `tipo_discapacidad` y `discapacidad_descripcion`
   - Si cambia a "Sí" → hacer `tipo_discapacidad` requerido
   - Si cambia a otro valor → limpiar y ocultar

4. **Errores de validación del backend**:
   - En `onError` de `useMutation`, si es `422`:
     - Extraer `error.response.data.error.details`
     - Usar `setError()` de react-hook-form para setear errores por campo
     - Mostrar toast/modal con lista de errores generales (si hay)

5. **Éxito**:
   - En `onSuccess` de `useMutation`:
     - Mostrar toast de éxito
     - Redirigir a `/dashboard/matriculas` con `router.push()`

### Estructura de Archivos Sugerida (Next.js)

```
app/
  dashboard/
    matriculas/
      create/
        page.tsx                    # Server Component (opcional) o wrapper
components/
  matriculas/
    CreateMatriculaForm.tsx         # Client Component (formulario completo)
    MatriculaFormFields.tsx         # Componentes de campos (opcional, para modularizar)
hooks/
  useMatriculaCatalogs.ts          # Custom hook con useQuery para programs/schedules/groups
  useCreateMatricula.ts             # Custom hook con useMutation para POST
lib/
  validations/
    matricula.schema.ts             # Zod schema
  api/
    matricula.ts                    # Funciones Axios para endpoints
```

---

## RESUMEN EJECUTIVO

### Endpoints Clave
- **Cargar datos**: `GET /api/v1/settings/{programs|schedules|groups}?per_page=100`
- **Guardar**: `POST /api/v1/matriculas` (con Bearer token)

### Campos Críticos
- **Obligatorios**: `nombre_completo`, `numero_documento`, `tipo_documento`, `programa`, `sede`, `estado_estudiante`, `horario`, `semestre_actual`, `anio`, `numero_grupo`
- **Condicional**: `tipo_discapacidad` (solo si `tiene_discapacidad == "Sí"`)
- **Auto-generado**: `cod_alumno` = `numero_documento` (backend)

### Lógica Especial
- **Reutilización de IDs**: el backend busca el primer ID disponible (reutiliza eliminados)
- **Validación dinámica**: programa, horario y grupo deben existir y estar activos en sus respectivas tablas
- **Campos condicionales**: discapacidad se muestra/oculta con JavaScript

### Flujo Completo
1. Cargar catálogos (programs, schedules, groups) → llenar selects
2. Usuario completa formulario
3. Validación frontend (Zod) + validación backend (Laravel)
4. Si éxito → crear matrícula, redirigir a listado
5. Si error → mostrar errores por campo + modal general
