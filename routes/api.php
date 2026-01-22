<?php

use App\Http\Controllers\Api\V1\AccountingApiController;
use App\Http\Controllers\Api\V1\AttendanceSheetController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ConceptDischargeReceiptController;
use App\Http\Controllers\Api\V1\ConceptEntryReceiptController;
use App\Http\Controllers\Api\V1\ConsecutiveController;
use App\Http\Controllers\Api\V1\CostController;
use App\Http\Controllers\Api\V1\DischargeConceptController;
use App\Http\Controllers\Api\V1\DischargeController;
use App\Http\Controllers\Api\V1\EntryController;
use App\Http\Controllers\Api\V1\FinancialReceiptController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\MaintenanceApiController;
use App\Http\Controllers\Api\V1\MatriculaController;
use App\Http\Controllers\Api\V1\OtherEntryController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\PurseController;
use App\Http\Controllers\Api\V1\ThirdActivityController;
use App\Http\Controllers\Api\V1\ThirdEntryController;
use App\Http\Controllers\Api\V1\ThirdReceiptController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
|
| Prefijo /api lo aplica el framework. Todo bajo Route::prefix('v1').
| throttle:api (60/min) aplica al grupo api por defecto.
| Los controladores en App\Http\Controllers\Api\V1 no devuelven views ni redirects.
|
*/

Route::prefix('v1')->group(function () {

    // ---- 0) Health (sin auth, para infra: load balancer, k8s, monitoreo) ----
    Route::get('health', [HealthController::class, 'index']);

    // ---- 0.5) Foto de matrícula (pública, sin auth, para que funcione en <img> tags) ----
    Route::get('matriculas/{cod_alumno}/foto', [MatriculaController::class, 'getFoto'])->where('cod_alumno', '[A-Za-z0-9\-]+');

    // ---- 1) Auth: login, register, forgot, reset, verify, resend (throttle en sensibles), logout, me ----
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
        Route::get('verify-email', [AuthController::class, 'verifyEmail'])->middleware(['signed', 'throttle:5,1'])->name('api.verification.verify');
        Route::post('resend-verification', [AuthController::class, 'resendVerification'])->middleware(['auth:sanctum', 'throttle:5,1']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // ---- 2) Recursos: matrículas, costs (auth:sanctum + permission:access.core) ----
    Route::middleware(['auth:sanctum', 'permission:access.core'])->group(function () {
        Route::get('home', [HomeController::class, 'index']);
        Route::post('attendance-sheet/generate', [AttendanceSheetController::class, 'generate']);
        Route::get('maintenance', [MaintenanceApiController::class, 'index']);
        // Contabilidad (JSON): index + reportes. Excel download en B2.
        Route::get('accounting', [AccountingApiController::class, 'index']);
        Route::get('accounting/abonos', [AccountingApiController::class, 'abonos']);
        Route::get('accounting/otros-ingresos', [AccountingApiController::class, 'otrosIngresos']);
        Route::get('accounting/total-ingresos', [AccountingApiController::class, 'totalIngresos']);
        Route::get('accounting/egresos', [AccountingApiController::class, 'egresos']);
        Route::get('accounting/arqueo-diario', [AccountingApiController::class, 'arqueoDiario']);
        Route::get('accounting/informe-semanal', [AccountingApiController::class, 'informeSemanal']);
        Route::get('accounting/informe-mensual', [AccountingApiController::class, 'informeMensual']);
        Route::get('accounting/abonos/download', [AccountingApiController::class, 'abonosDownload']);
        Route::get('accounting/otros-ingresos/download', [AccountingApiController::class, 'otrosIngresosDownload']);
        Route::get('accounting/total-ingresos/download', [AccountingApiController::class, 'totalIngresosDownload']);
        Route::get('accounting/egresos/download', [AccountingApiController::class, 'egresosDownload']);
        Route::get('accounting/arqueo-diario/download', [AccountingApiController::class, 'arqueoDiarioDownload']);
        Route::get('accounting/informe-semanal/download', [AccountingApiController::class, 'informeSemanalDownload']);
        Route::get('accounting/informe-mensual/download', [AccountingApiController::class, 'informeMensualDownload']);
        Route::get('costs', [CostController::class, 'index']);
        Route::post('costs', [CostController::class, 'store']);
        Route::get('costs/{id}', [CostController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'costs/{id}', [CostController::class, 'update'])->whereNumber('id');
        Route::delete('costs/{id}', [CostController::class, 'destroy'])->whereNumber('id');
        Route::get('consecutives', [ConsecutiveController::class, 'index']);
        Route::post('consecutives', [ConsecutiveController::class, 'store']);
        Route::get('consecutives/{id}', [ConsecutiveController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'consecutives/{id}', [ConsecutiveController::class, 'update'])->whereNumber('id');
        Route::get('purses', [PurseController::class, 'index']);
        Route::get('purses/totales', [PurseController::class, 'totales']);
        Route::get('purses/cartera', [PurseController::class, 'cartera']);
        Route::get('purses/{id}/history', [PurseController::class, 'history'])->whereNumber('id');
        Route::get('purses/{id}', [PurseController::class, 'show'])->whereNumber('id');
        Route::get('entries', [EntryController::class, 'index']);
        Route::post('entries', [EntryController::class, 'store']);
        Route::get('entries/{id}', [EntryController::class, 'show'])->whereNumber('id');
        Route::delete('entries/{id}', [EntryController::class, 'destroy'])->whereNumber('id');
        Route::get('other-entries', [OtherEntryController::class, 'index']);
        Route::post('other-entries', [OtherEntryController::class, 'store']);
        Route::get('other-entries/{id}', [OtherEntryController::class, 'show'])->whereNumber('id');
        Route::delete('other-entries/{id}', [OtherEntryController::class, 'destroy'])->whereNumber('id');
        // Egresos: proveedores, conceptos de egreso, recibos (consecutivo discharge)
        Route::get('providers', [ProviderController::class, 'index']);
        Route::post('providers', [ProviderController::class, 'store']);
        Route::get('providers/{id}', [ProviderController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'providers/{id}', [ProviderController::class, 'update'])->whereNumber('id');
        Route::delete('providers/{id}', [ProviderController::class, 'destroy'])->whereNumber('id');
        Route::get('discharge-concepts', [DischargeConceptController::class, 'index']);
        Route::post('discharge-concepts', [DischargeConceptController::class, 'store']);
        Route::get('discharge-concepts/{id}', [DischargeConceptController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'discharge-concepts/{id}', [DischargeConceptController::class, 'update'])->whereNumber('id');
        Route::delete('discharge-concepts/{id}', [DischargeConceptController::class, 'destroy'])->whereNumber('id');
        Route::get('discharges', [DischargeController::class, 'index']);
        Route::post('discharges', [DischargeController::class, 'store']);
        Route::get('discharges/{id}', [DischargeController::class, 'show'])->whereNumber('id');
        Route::delete('discharges/{id}', [DischargeController::class, 'destroy'])->whereNumber('id');
        // Terceros: entries, activities, receipts (consecutivo entry), concept-entry, concept-discharge
        Route::get('third-entries', [ThirdEntryController::class, 'index']);
        Route::post('third-entries', [ThirdEntryController::class, 'store']);
        Route::get('third-entries/{id}', [ThirdEntryController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'third-entries/{id}', [ThirdEntryController::class, 'update'])->whereNumber('id');
        Route::delete('third-entries/{id}', [ThirdEntryController::class, 'destroy'])->whereNumber('id');
        Route::get('third-activities', [ThirdActivityController::class, 'index']);
        Route::post('third-activities', [ThirdActivityController::class, 'store']);
        Route::get('third-activities/{id}', [ThirdActivityController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'third-activities/{id}', [ThirdActivityController::class, 'update'])->whereNumber('id');
        Route::delete('third-activities/{id}', [ThirdActivityController::class, 'destroy'])->whereNumber('id');
        Route::get('third-receipts', [ThirdReceiptController::class, 'index']);
        Route::post('third-receipts', [ThirdReceiptController::class, 'store']);
        Route::get('third-receipts/{id}', [ThirdReceiptController::class, 'show'])->whereNumber('id');
        Route::delete('third-receipts/{id}', [ThirdReceiptController::class, 'destroy'])->whereNumber('id');
        Route::get('concept-entry-receipts', [ConceptEntryReceiptController::class, 'index']);
        Route::post('concept-entry-receipts', [ConceptEntryReceiptController::class, 'store']);
        Route::get('concept-entry-receipts/{id}', [ConceptEntryReceiptController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'concept-entry-receipts/{id}', [ConceptEntryReceiptController::class, 'update'])->whereNumber('id');
        Route::delete('concept-entry-receipts/{id}', [ConceptEntryReceiptController::class, 'destroy'])->whereNumber('id');
        Route::get('concept-discharge-receipts', [ConceptDischargeReceiptController::class, 'index']);
        Route::post('concept-discharge-receipts', [ConceptDischargeReceiptController::class, 'store']);
        Route::get('concept-discharge-receipts/{id}', [ConceptDischargeReceiptController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], 'concept-discharge-receipts/{id}', [ConceptDischargeReceiptController::class, 'update'])->whereNumber('id');
        Route::delete('concept-discharge-receipts/{id}', [ConceptDischargeReceiptController::class, 'destroy'])->whereNumber('id');
        // Financial receipt: datos JSON y stream PDF por type+id (entry|other-entry|egreso|third)
        Route::get('financial-receipts/{type}/{id}/pdf', [FinancialReceiptController::class, 'streamPdf'])->where('type', 'entry|other-entry|egreso|third')->whereNumber('id');
        Route::get('financial-receipts/{type}/{id}', [FinancialReceiptController::class, 'show'])->where('type', 'entry|other-entry|egreso|third')->whereNumber('id');
        // Foto pública (sin auth) para que funcione en <img> tags
        Route::get('matriculas/{cod_alumno}/foto', [MatriculaController::class, 'getFoto'])->where('cod_alumno', '[A-Za-z0-9\-]+');
        Route::get('matriculas', [MatriculaController::class, 'index']);
        Route::get('matriculas/{cod_alumno}/pdf', [MatriculaController::class, 'streamPdf'])->where('cod_alumno', '[A-Za-z0-9\-]+');
        Route::post('matriculas/{cod_alumno}/foto', [MatriculaController::class, 'uploadFoto'])->where('cod_alumno', '[A-Za-z0-9\-]+');
        Route::get('matriculas/{cod_alumno}', [MatriculaController::class, 'show'])->where('cod_alumno', '[A-Za-z0-9\-]+');
        Route::post('matriculas', [MatriculaController::class, 'store']);
        Route::match(['put', 'patch'], 'matriculas/{cod_alumno}', [MatriculaController::class, 'update'])->where('cod_alumno', '[A-Za-z0-9\-]+');
        Route::delete('matriculas/{cod_alumno}', [MatriculaController::class, 'destroy'])->where('cod_alumno', '[A-Za-z0-9\-]+');
    });

    // ---- 3) Admin: users (auth:sanctum + permission:users.manage) ----
    Route::middleware(['auth:sanctum', 'permission:users.manage'])->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
    });

    // ---- 4) Admin: roles, permissions, asignación (auth:sanctum + permission:roles.manage) ----
    Route::middleware(['auth:sanctum', 'permission:roles.manage'])->prefix('admin')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('admin.roles.permissions.sync');
        Route::apiResource('roles', RoleController::class);
        Route::post('users/{user}/roles', [UserRoleController::class, 'syncRoles'])->name('admin.users.roles.sync');
    });

    // ---- 5) Settings / Catálogos (auth:sanctum + permission:settings.manage). Whitelist en CatalogController. ----
    Route::middleware(['auth:sanctum', 'permission:settings.manage'])->prefix('settings')->group(function () {
        Route::get('institution', [CatalogController::class, 'showInstitution']);
        Route::put('institution', [CatalogController::class, 'updateInstitution']);
        Route::get('{resource}', [CatalogController::class, 'index']);
        Route::post('{resource}', [CatalogController::class, 'store']);
        Route::get('{resource}/{id}', [CatalogController::class, 'show'])->whereNumber('id');
        Route::match(['put', 'patch'], '{resource}/{id}', [CatalogController::class, 'update'])->whereNumber('id');
        Route::delete('{resource}/{id}', [CatalogController::class, 'destroy'])->whereNumber('id');
    });
});
