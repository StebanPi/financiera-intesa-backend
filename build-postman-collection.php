<?php
/**
 * Genera docs/postman/collection.json (Postman v2.1)
 * Ejecutar desde la raíz: php build-postman-collection.php
 */

$base = '{{base_url}}/api/v1';

$hJson = [['key' => 'Accept', 'value' => 'application/json', 'type' => 'text']];
$hAny  = [['key' => 'Accept', 'value' => '*/*', 'type' => 'text']];
$hJsonBody = [['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text']];

$bearer = ['type' => 'bearer', 'bearer' => [['key' => 'token', 'value' => '{{token}}', 'type' => 'string']]];
$noauth = ['type' => 'noauth'];

function req($name, $method, $path, $opts = []) {
    global $hJson, $hAny, $hJsonBody, $bearer, $noauth;
    $auth = $opts['auth'] ?? $bearer;
    $headers = isset($opts['file']) && $opts['file'] ? $hAny : $hJson;
    $body = $opts['body'] ?? null;
    $tests = $opts['tests'] ?? null;

    $r = [
        'name' => $name,
        'request' => [
            'auth' => $auth,
            'method' => $method,
            'header' => array_merge($headers, $body !== null ? $hJsonBody : []),
            'url' => $path,
        ],
        'response' => [],
    ];
    if ($body !== null) {
        $r['request']['body'] = ['mode' => 'raw', 'raw' => is_string($body) ? $body : json_encode($body)];
    }
    if ($tests) {
        $r['event'] = [['listen' => 'test', 'script' => ['exec' => $tests, 'type' => 'text/javascript']]];
    }
    $desc = $opts['description'] ?? null;
    if (!$desc && !empty($opts['file'])) {
        $desc = 'Devuelve archivo (PDF/XLSX). Usar "Send and Download" en Postman para guardar.';
    }
    if ($desc) {
        $r['request']['description'] = $desc;
    }
    return $r;
}

$testOk = [
    "pm.test('Status 2xx', function(){ pm.expect(pm.response.code).to.be.within(200, 204); });",
    "var j=pm.response.json(); pm.expect(j.data !== undefined || j.error !== undefined || j.message !== undefined).to.be.true;"
];
$testLogin = [
    "pm.test('Status 200', function(){ pm.expect(pm.response.code).to.eql(200); });",
    "var j=pm.response.json(); if (j.data && j.data.token) { pm.environment.set('token', j.data.token); }"
];
$test401 = [
    "pm.test('401 UNAUTHENTICATED', function(){ pm.expect(pm.response.code).to.eql(401); var j=pm.response.json(); pm.expect(j.error && j.error.code).to.eql('UNAUTHENTICATED'); });"
];

$items = [];

// ---- Auth ----
$items[] = [
    'name' => 'Auth',
    'item' => [
        req('Login', 'POST', "{$base}/auth/login", ['auth' => $noauth, 'body' => ['email' => 'admin@example.com', 'password' => 'password'], 'tests' => $testLogin]),
        req('Me (sin token - espera 401)', 'GET', "{$base}/auth/me", ['auth' => $noauth, 'tests' => $test401]),
        req('Register', 'POST', "{$base}/auth/register", ['auth' => $noauth, 'body' => ['name' => 'Test', 'email' => 'test@example.com', 'password' => 'password', 'password_confirmation' => 'password'], 'tests' => $testOk]),
        req('Forgot password', 'POST', "{$base}/auth/forgot-password", ['auth' => $noauth, 'body' => ['email' => 'user@example.com'], 'tests' => $testOk]),
        req('Reset password', 'POST', "{$base}/auth/reset-password", ['auth' => $noauth, 'body' => ['email' => 'user@example.com', 'password' => 'newpassword', 'password_confirmation' => 'newpassword', 'token' => 'TOKEN_FROM_EMAIL'], 'tests' => $testOk]),
        req('Verify email', 'GET', "{$base}/auth/verify-email?id=1&hash=abc&expires=1&signature=xyz", ['auth' => $noauth, 'tests' => $testOk]),
        req('Resend verification', 'POST', "{$base}/auth/resend-verification", ['body' => ['email' => 'user@example.com'], 'tests' => $testOk]),
        req('Logout', 'POST', "{$base}/auth/logout", ['tests' => $testOk]),
        req('Me', 'GET', "{$base}/auth/me", ['tests' => $testOk]),
    ],
];

// ---- Home ----
$items[] = ['name' => 'Home', 'item' => [req('Home', 'GET', "{$base}/home", ['tests' => $testOk])]];

// ---- Maintenance ----
$items[] = ['name' => 'Maintenance', 'item' => [req('Maintenance (stats)', 'GET', "{$base}/maintenance", ['tests' => $testOk])]];

// ---- Attendance Sheet ----
$items[] = [
    'name' => 'Attendance Sheet',
    'item' => [
        req('Planilla PDF (generate)', 'POST', "{$base}/attendance-sheet/generate", [
            'body' => ['program_id' => 1, 'schedule_id' => 1, 'group_id' => 1, 'teacher_id' => 1, 'module_id' => 1, 'fecha_inicio' => '2024-01-01', 'fecha_final' => '2024-01-31', 'fecha_clase' => '2024-01-15'],
            'file' => true,
        ]),
    ],
];

// ---- Accounting ----
$acc = [
    req('Accounting index', 'GET', "{$base}/accounting", ['tests' => $testOk]),
    req('Abonos (JSON)', 'GET', "{$base}/accounting/abonos?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['tests' => $testOk]),
    req('Abonos (download XLSX)', 'GET', "{$base}/accounting/abonos/download?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['file' => true]),
    req('Otros ingresos (JSON)', 'GET', "{$base}/accounting/otros-ingresos?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['tests' => $testOk]),
    req('Otros ingresos (download XLSX)', 'GET', "{$base}/accounting/otros-ingresos/download?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['file' => true]),
    req('Total ingresos (JSON)', 'GET', "{$base}/accounting/total-ingresos?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['tests' => $testOk]),
    req('Total ingresos (download XLSX)', 'GET', "{$base}/accounting/total-ingresos/download?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['file' => true]),
    req('Egresos (JSON)', 'GET', "{$base}/accounting/egresos?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['tests' => $testOk]),
    req('Egresos (download XLSX)', 'GET', "{$base}/accounting/egresos/download?fecha_inicio={{fecha_inicio}}&fecha_fin={{fecha_fin}}", ['file' => true]),
    req('Arqueo diario (JSON)', 'GET', "{$base}/accounting/arqueo-diario?fecha={{fecha}}", ['tests' => $testOk]),
    req('Arqueo diario (download XLSX)', 'GET', "{$base}/accounting/arqueo-diario/download?fecha={{fecha}}", ['file' => true]),
    req('Informe semanal (JSON)', 'GET', "{$base}/accounting/informe-semanal?fecha={{fecha}}", ['tests' => $testOk]),
    req('Informe semanal (download XLSX)', 'GET', "{$base}/accounting/informe-semanal/download?fecha={{fecha}}", ['file' => true]),
    req('Informe mensual (JSON)', 'GET', "{$base}/accounting/informe-mensual?month_year={{month_year}}", ['tests' => $testOk]),
    req('Informe mensual (download XLSX)', 'GET', "{$base}/accounting/informe-mensual/download?month_year={{month_year}}", ['file' => true]),
];
$items[] = ['name' => 'Accounting', 'item' => $acc];

// ---- Consecutives ----
$items[] = [
    'name' => 'Consecutives',
    'item' => [
        req('List', 'GET', "{$base}/consecutives", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/consecutives", ['body' => ['type' => 'entry', 'num_start' => 1, 'num_current' => 1], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/consecutives/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/consecutives/{{id}}", ['body' => ['num_current' => 2], 'tests' => $testOk]),
    ],
];

// ---- Matriculas ----
$matriculaCreate = ['nombre_completo' => 'Estudiante Test', 'numero_documento' => '987654321', 'tipo_documento' => 'CC', 'programa' => 'Auxiliar de Primera Infancia', 'sede' => 'Barrancabermeja', 'estado_estudiante' => 'Activo', 'horario' => 'Diurno', 'semestre_actual' => 'I', 'anio' => '2024', 'numero_grupo' => '1A'];
$items[] = [
    'name' => 'Matriculas',
    'item' => [
        req('List', 'GET', "{$base}/matriculas", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/matriculas", ['body' => $matriculaCreate, 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/matriculas/{{cod_alumno}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/matriculas/{{cod_alumno}}", ['body' => ['nombre_completo' => 'Estudiante Actualizado'], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/matriculas/{{cod_alumno}}", ['tests' => $testOk]),
        req('PDF', 'GET', "{$base}/matriculas/{{cod_alumno}}/pdf", ['file' => true]),
        req('Upload foto', 'POST', "{$base}/matriculas/{{cod_alumno}}/foto", [
            'body' => null,
            'description' => 'Body: form-data, clave "foto", tipo File. Seleccionar una imagen (jpeg, jpg, png, webp, max 2MB).',
            'tests' => $testOk,
        ]),
    ],
];

// ---- Costs ----
$items[] = [
    'name' => 'Costs',
    'item' => [
        req('List', 'GET', "{$base}/costs", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/costs", ['body' => ['cod_alumno' => 'EST001', 'id_concepto' => 1, 'valor' => 100000], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/costs/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/costs/{{id}}", ['body' => ['valor' => 120000], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/costs/{{id}}", ['tests' => $testOk]),
    ],
];

// ---- Purses ----
$items[] = [
    'name' => 'Purses',
    'item' => [
        req('List', 'GET', "{$base}/purses", ['tests' => $testOk]),
        req('Show', 'GET', "{$base}/purses/{{id}}", ['tests' => $testOk]),
        req('History', 'GET', "{$base}/purses/{{id}}/history", ['tests' => $testOk]),
    ],
];

// ---- Entries ----
$items[] = [
    'name' => 'Entries',
    'item' => [
        req('List', 'GET', "{$base}/entries", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/entries", ['body' => ['id_cost' => 1, 'id_purse' => 1, 'valor' => 50000, 'fecha_recibo' => '2024-01-15'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/entries/{{id}}", ['tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/entries/{{id}}", ['tests' => $testOk]),
    ],
];

// ---- Other-Entries ----
$items[] = [
    'name' => 'Other-Entries',
    'item' => [
        req('List', 'GET', "{$base}/other-entries", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/other-entries", ['body' => ['id_otros_conceptos' => 1, 'valor' => 25000, 'fecha_recibo' => '2024-01-15', 'descripcion' => 'Otro'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/other-entries/{{id}}", ['tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/other-entries/{{id}}", ['tests' => $testOk]),
    ],
];

// ---- Egresos ----
$items[] = [
    'name' => 'Egresos - Providers',
    'item' => [
        req('List', 'GET', "{$base}/providers", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/providers", ['body' => ['nombre' => 'Proveedor 1', 'nit' => '900123'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/providers/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/providers/{{id}}", ['body' => ['nombre' => 'Proveedor 1 Updated'], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/providers/{{id}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Egresos - Discharge concepts',
    'item' => [
        req('List', 'GET', "{$base}/discharge-concepts", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/discharge-concepts", ['body' => ['nombre' => 'Concepto 1', 'state' => true], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/discharge-concepts/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/discharge-concepts/{{id}}", ['body' => ['nombre' => 'Concepto 1 Upd'], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/discharge-concepts/{{id}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Egresos - Discharges',
    'item' => [
        req('List', 'GET', "{$base}/discharges", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/discharges", ['body' => ['id_egreso_concept' => 1, 'id_egreso_provider' => 1, 'valor' => 50000, 'fecha' => '2024-01-15', 'descripcion' => 'Egreso'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/discharges/{{id}}", ['tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/discharges/{{id}}", ['tests' => $testOk]),
    ],
];

// ---- Terceros ----
$items[] = [
    'name' => 'Terceros - Third entries',
    'item' => [
        req('List', 'GET', "{$base}/third-entries", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/third-entries", ['body' => ['cedula' => '123', 'nombre' => 'Tercero', 'actividad' => 1], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/third-entries/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/third-entries/{{id}}", ['body' => ['nombre' => 'Tercero Upd'], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/third-entries/{{id}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Terceros - Third activities',
    'item' => [
        req('List', 'GET', "{$base}/third-activities", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/third-activities", ['body' => ['nombre' => 'Actividad 1'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/third-activities/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/third-activities/{{id}}", ['body' => ['nombre' => 'Actividad 1 Upd'], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/third-activities/{{id}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Terceros - Third receipts',
    'item' => [
        req('List', 'GET', "{$base}/third-receipts", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/third-receipts", ['body' => ['third' => 1, 'concepto' => 'Pago', 'valor' => 30000, 'fecha' => '2024-01-15', 'forma' => 'Efectivo'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/third-receipts/{{id}}", ['tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/third-receipts/{{id}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Terceros - Concept entry receipts',
    'item' => [
        req('List', 'GET', "{$base}/concept-entry-receipts", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/concept-entry-receipts", ['body' => ['id_third' => 1, 'id_concepto_entry' => 1, 'valor' => 10000, 'fecha' => '2024-01-15'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/concept-entry-receipts/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/concept-entry-receipts/{{id}}", ['body' => ['valor' => 12000], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/concept-entry-receipts/{{id}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Terceros - Concept discharge receipts',
    'item' => [
        req('List', 'GET', "{$base}/concept-discharge-receipts", ['tests' => $testOk]),
        req('Create', 'POST', "{$base}/concept-discharge-receipts", ['body' => ['id_third' => 1, 'id_concepto_discharge' => 1, 'valor' => 8000, 'fecha' => '2024-01-15'], 'tests' => $testOk]),
        req('Show', 'GET', "{$base}/concept-discharge-receipts/{{id}}", ['tests' => $testOk]),
        req('Update', 'PATCH', "{$base}/concept-discharge-receipts/{{id}}", ['body' => ['valor' => 9000], 'tests' => $testOk]),
        req('Delete', 'DELETE', "{$base}/concept-discharge-receipts/{{id}}", ['tests' => $testOk]),
    ],
];

// ---- Financial Receipts ----
$items[] = [
    'name' => 'Financial Receipts',
    'item' => [
        req('Show JSON', 'GET', "{$base}/financial-receipts/{{type}}/{{id}}", ['tests' => $testOk]),
        req('PDF (stream)', 'GET', "{$base}/financial-receipts/{{type}}/{{id}}/pdf", ['file' => true]),
    ],
];

// ---- Settings ----
$items[] = [
    'name' => 'Settings',
    'item' => [
        req('Institution (get)', 'GET', "{$base}/settings/institution", ['tests' => $testOk]),
        req('Institution (update)', 'PUT', "{$base}/settings/institution", ['body' => ['nombre' => 'Inst', 'nit' => '900'], 'tests' => $testOk]),
        req('Catalog index', 'GET', "{$base}/settings/{{resource}}", ['tests' => $testOk]),
        req('Catalog create', 'POST', "{$base}/settings/{{resource}}", ['body' => ['nombre' => 'Item'], 'tests' => $testOk]),
        req('Catalog show', 'GET', "{$base}/settings/{{resource}}/{{id}}", ['tests' => $testOk]),
        req('Catalog update', 'PATCH', "{$base}/settings/{{resource}}/{{id}}", ['body' => ['nombre' => 'Item Upd'], 'tests' => $testOk]),
        req('Catalog delete', 'DELETE', "{$base}/settings/{{resource}}/{{id}}", ['tests' => $testOk]),
    ],
];

// ---- Admin ----
$items[] = [
    'name' => 'Admin - Users',
    'item' => [
        req('List users', 'GET', "{$base}/admin/users", ['tests' => $testOk]),
        req('Show user', 'GET', "{$base}/admin/users/{{user}}", ['tests' => $testOk]),
    ],
];
$items[] = [
    'name' => 'Admin - Roles & Permissions',
    'item' => [
        req('Permissions', 'GET', "{$base}/admin/permissions", ['tests' => $testOk]),
        req('Roles list', 'GET', "{$base}/admin/roles", ['tests' => $testOk]),
        req('Role create', 'POST', "{$base}/admin/roles", ['body' => ['name' => 'Rol', 'slug' => 'rol'], 'tests' => $testOk]),
        req('Role show', 'GET', "{$base}/admin/roles/{{role}}", ['tests' => $testOk]),
        req('Role update', 'PATCH', "{$base}/admin/roles/{{role}}", ['body' => ['name' => 'Rol Upd'], 'tests' => $testOk]),
        req('Role delete', 'DELETE', "{$base}/admin/roles/{{role}}", ['tests' => $testOk]),
        req('Role sync permissions', 'POST', "{$base}/admin/roles/{{role}}/permissions", ['body' => ['permissions' => ['access.core']], 'tests' => $testOk]),
        req('User sync roles', 'POST', "{$base}/admin/users/{{user}}/roles", ['body' => ['roles' => ['secretaria']], 'tests' => $testOk]),
    ],
];

// ---- Build collection ----
$coll = [
    'info' => [
        'name' => 'Financiera Intesa API v1',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'auth' => $bearer,
    'item' => $items,
];

$dir = __DIR__ . '/docs/postman';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$path = $dir . '/collection.json';
file_put_contents($path, json_encode($coll, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Generado: $path\n";
