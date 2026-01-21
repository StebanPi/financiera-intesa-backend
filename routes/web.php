<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Swagger Documentation Routes
Route::middleware([\App\Http\Middleware\EnsureSwaggerEnabled::class])->group(function () {
    Route::get('/docs', [\App\Http\Controllers\SwaggerDocsController::class, 'index'])->name('docs.ui');
    Route::get('/docs/openapi.json', [\App\Http\Controllers\SwaggerDocsController::class, 'spec'])->name('docs.spec');
});

Route::get('/Student', function () {
    return view('auth.login');
});
Route::get('/Receipts', function () {
    return view('receipts.index');
})->middleware(['auth', 'permission:access.core']);

// Rutas deshabilitadas - Vistas ocultas
// Route::get('/abonos/', function () {
//     return view('viewStudent.abonos.index');
// })->name('abonos');

// Route::get('/cartera/', function () {
//     return view('viewStudent.cartera.index');
// })->name('cartera');

// Route::get('otros/abonos/', function () {
//     return view('viewStudent.otrosAbonos.index');
// })->name('otros.abonos');

// Route::get('financiera/', function () {
//     return view('viewStudent.financiera.index');
// })->name('financiera');

Route::get('/receipts/third/entry/', function () {
    return view('third.thirdEntryReceipts');
})->middleware(['auth', 'permission:access.core'])->name('third.receipts.entry');

Route::get('/receipts/third/entry/{id}', [App\Http\Controllers\ThirdReceiptsController::class, 'redireccionarEntry'])->middleware(['auth', 'permission:access.core'])->name('third.receipts.entry.edit');

// Ruta deshabilitada - Vista oculta
// Route::get('/receipts/third/discharge/', function () {
//     return view('third.thirdDischargeReceipts');
// })->name('third.receipts.discharge');

// Route::get('/receipts/third/discharge/{id}', [App\Http\Controllers\ThirdReceiptsController::class, 'redireccionarDischarge'])->name('third.receipts.discharge.edit');

Route::get('/pdf', function () {
    return view('layouts.pdf');
})->middleware(['auth', 'permission:access.core']);

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
    Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('password/confirm', [App\Http\Controllers\Auth\ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [App\Http\Controllers\Auth\ConfirmPasswordController::class, 'confirm']);
    Route::get('email/verify', [App\Http\Controllers\Auth\VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/resend', [App\Http\Controllers\Auth\VerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->middleware(['auth', 'permission:access.core'])->name('home');

Route::middleware(['auth', 'permission:access.core'])->group(function () {
    Route::controller(App\Http\Controllers\viewStudentController::class)->group(function(){
    Route::get('/view/student/{estado}','index')->name('view.student.index');
    Route::post('/view/student/search','search')->name('view.student.search');
    Route::get('/cartera/{id}', 'carteraTable')->name('cartera.index');
    Route::get('/student/{id}','show')->name('student.view');
    Route::post('/privileges/post/','privileges')->name('post.privileges');
    Route::get('/login/privileges/{no}/{route}','loginPrivilegies')->name('login.privileges');; 
    Route::get('/abono/privileges/admin/{no}','viewAbonos' )->name('show.admin');
    Route::get('/otros/privileges/admin/{no}','viewOtros' )->name('otros.admin');
    Route::post('/purse/all/','cartera')->name('purse.all');
    });

    Route::controller(App\Http\Controllers\MatriculaController::class)->group(function(){
    Route::get('/matricula','index')->name('matricula.index');
    Route::get('/matricula/create','create')->name('matricula.create');
    Route::get('/matricula/ficha/{cod_alumno}','edit')->name('matricula.ficha');
    Route::get('/matricula/ficha/{cod_alumno}/descargar','downloadFicha')->name('matricula.ficha.download');
    Route::get('/matricula/ficha/{cod_alumno}/ver','viewFicha')->name('matricula.ficha.view');
    Route::get('/matricula/estudiante/{cod_alumno}','showMatricula')->name('matricula.estudiante');
    Route::post('/matricula/store','store')->name('matricula.store');
    Route::put('/matricula/update/{cod_alumno}','update')->name('matricula.update');
    Route::delete('/matricula/delete/{cod_alumno}','destroy')->name('matricula.destroy');
    Route::post('/matricula/{cod_alumno}/foto','uploadPhoto')->name('matricula.foto.upload');
    Route::delete('/matricula/{cod_alumno}/foto','deletePhoto')->name('matricula.foto.delete');
    });

    Route::controller(App\Http\Controllers\AcademicManagement\AttendanceSheetController::class)->prefix('gestion-academica/planillas/asistencia')->name('gestion-academica.planillas.asistencia.')->group(function(){
        Route::get('/','create')->name('create');
        Route::post('/','generate')->name('generate');
    });

    Route::controller(App\Http\Controllers\CostController::class)->group(function(){
    Route::post('/cost/store','store')->name('cost.store');
    Route::get('/financiera/{id}','show' )->name('cost.show');
    });    

    // Ruta para eliminar costos de un estudiante - Solo Super Admin
    Route::middleware(['auth'])->group(function(){
        Route::post('/cost/eliminar/{cod_alumno}', [App\Http\Controllers\CostController::class, 'eliminarCostosEstudiante'])->name('cost.eliminar-estudiante');
    });    

    Route::controller(App\Http\Controllers\ConsecutiveController::class)->group(function(){
    Route::get('/consecutive','index')->name('consecutive.index');
    Route::post('/consecutive/store','store')->name('consecutive.store');
    });    

    Route::controller(App\Http\Controllers\EntryController::class)->group(function(){
    Route::post('/entry/all','all')->name('entry.all');
    Route::get('/entry/get/{id}','get')->name('entry.get');
    Route::post('/entry/store','store')->name('entry.store');
    Route::post('/entry/update/{id}','update')->name('entry.update');
    Route::post('/entry/destroy/{id}','destroy')->name('entry.destroy');
    Route::get('/entry/print/{id}','print')->name('entry.print');
    Route::get('/entry/pdf/{id}','ViewPdf')->name('entry.Viewpdf');
    Route::get('/entry/pdfUnited/{id}','ViewPdfUnitedOther')->name('entry.ViewPdfUnitedOther');
    Route::get('/abonos/{id}','show')->name('entry.show');
    }); 

    Route::controller(App\Http\Controllers\OtherEntryController::class)->group(function(){
    Route::post('/other/all','all')->name('other.entry.all');
    Route::get('/other/entry/get/{id}','get')->name('other.entry.get');
    Route::post('/other/entry/store','store')->name('other.entry.store');
    Route::post('/other/entry/update/{id}','update')->name('other.entry.update');
    Route::post('/other/entry/destroy/{id}','destroy')->name('other.entry.destroy');
    Route::get('/other/entry/print/{id}','print')->name('other.entry.print');
    Route::get('/other/entry/pdf/{id}','ViewPdf')->name('other.entry.Viewpdf');
    Route::get('/otros/abonos/{id}','show')->name('other.entry.show');
    }); 

    // Ruta unificada para impresión de recibos financieros
    Route::get('/finanzas/recibos/{type}/{id}/print', [App\Http\Controllers\FinancialReceiptController::class, 'print'])
        ->name('financial.receipt.print')
        ->where('type', 'entry|other-entry|egreso|third');

    Route::controller(App\Http\Controllers\SettingController::class)->group(function(){
    Route::get('/setting','index')->name('setting.index');
    Route::post('/setting/concepto/store','StoreConcepto')->name('concepto.store');
    Route::delete('/setting/concepto/{id}','destroyConcepto')->name('concepto.destroy');
    Route::post('/setting/elaborado/store','StoreElaborado')->name('elaborado.store');
    Route::delete('/setting/elaborado/{id}','destroyElaborado')->name('elaborado.destroy');
    Route::post('/setting/haber/store','StoreHaber')->name('haber.store');
    Route::delete('/setting/haber/{id}','destroyHaber')->name('haber.destroy');
    Route::post('/setting/debe/store','StoreDebe')->name('debe.store');
    Route::delete('/setting/debe/{id}','destroyDebe')->name('debe.destroy');
    Route::post('/setting/OtrosConcepto/store','StoreOtrosConcepto')->name('otrosConceptos.store');
    Route::delete('/setting/otros-concepto/{id}','destroyOtrosConcepto')->name('otrosConceptos.destroy');
    Route::post('/setting/program/store','storeProgram')->name('program.store');
    Route::delete('/setting/program/{id}','destroyProgram')->name('program.destroy');
    Route::post('/setting/schedule/store','storeSchedule')->name('schedule.store');
    Route::delete('/setting/schedule/{id}','destroySchedule')->name('schedule.destroy');
    Route::post('/setting/group/store','storeGroup')->name('group.store');
    Route::delete('/setting/group/{id}','destroyGroup')->name('group.destroy');
    Route::post('/setting/teacher/store','storeTeacher')->name('teacher.store');
    Route::delete('/setting/teacher/{id}','destroyTeacher')->name('teacher.destroy');
    Route::post('/setting/module/store','storeModule')->name('module.store');
    Route::delete('/setting/module/{id}','destroyModule')->name('module.destroy');
    Route::post('/setting/institution/store','storeInstitution')->name('institution.store');
    }); 

    Route::controller(App\Http\Controllers\PurseController::class)->group(function(){
    Route::post('/purse/edit/','edit')->name('purse.edit');
    Route::post('/purse/total/','total')->name('purse.total');
    Route::post('/purse/totales/','totales')->name('purse.totales');
    Route::get('/purse/pdf/{id}','ViewPdf')->name('purse.Viewpdfc');
    });

    Route::controller(App\Http\Controllers\HistoryPurseController::class)->group(function(){
    Route::post('/history/search/','search')->name('purse.history');
    Route::post('/history/delete/','delete')->name('history.delete');
    }); 

    Route::controller(App\Http\Controllers\thirdEntryController::class)->group(function(){
    Route::get('/third/entry/','index')->name('third.entry');
    Route::post('/third/entry/add','store')->name('third.entry.add');
    Route::get('/third/entry/edit/{id}','edit')->name('third.entry.edit');
    Route::post('/third/entry/update/{id}','update')->name('third.entry.update');
    Route::delete('/third/entry/delete/{id}','destroy')->name('third.entry.delete');
    Route::get('/third/search/{name}', 'search')->name('third.search');
    });

    Route::controller(App\Http\Controllers\ThirdActivityController::class)->group(function(){
    Route::get('/third/activity/','list')->name('third.activity');
    Route::post('/third/activity/add','store')->name('third.activity.add');
    Route::post('/third/activity/update/{id}','update')->name('third.activity.update');
    Route::delete('/third/activity/delete/{id}','destroy')->name('third.activity.delete');
    });

    Route::controller(App\Http\Controllers\ThirdReceiptsController::class)->group(function(){
    Route::get('/third/receipts','index')->name('third.receipts.index');
    Route::post('/receipts/store','store')->name('receipts.store');
    Route::get('/third/receipts/print/{id}','print')->name('third.receipts.print');
    Route::delete('/third/receipts/delete/{id}','destroy')->name('third.receipts.delete');
    });

    Route::controller(App\Http\Controllers\ConceptDischargeReceiptController::class)->group(function(){
    Route::post('/third/concept-discharge/add','store')->name('third.concept.discharge.add');
    Route::post('/third/concept-discharge/update/{id}','update')->name('third.concept.discharge.update');
    Route::post('/third/concept-discharge/delete/{id}','destroy')->name('third.concept.discharge.delete');
    });

    Route::controller(App\Http\Controllers\ConceptEntryReceiptController::class)->group(function(){
    Route::post('/third/concept-entry/add','store')->name('third.concept.entry.add');
    Route::post('/third/concept-entry/update/{id}','update')->name('third.concept.entry.update');
    Route::post('/third/concept-entry/delete/{id}','destroy')->name('third.concept.entry.delete');
    });

    Route::controller(App\Http\Controllers\StudentController::class)->group(function(){
    Route::get('/student/search/{name}','search')->name('student.search.consult');
    Route::get('/student/search/all/{name}','searchAll')->name('student.searchAll.consult');
    });

    Route::controller(App\Http\Controllers\EgresoProviderController::class)->group(function(){
        Route::get('/egresos/proveedores','index')->name('egreso.providers.index');
        Route::post('/egresos/proveedores','store')->name('egreso.providers.store');
        Route::put('/egresos/proveedores/{id}','update')->name('egreso.providers.update');
        Route::delete('/egresos/proveedores/{id}','destroy')->name('egreso.providers.destroy');
    });

    Route::controller(App\Http\Controllers\EgresoConceptController::class)->group(function(){
        Route::post('/egresos/conceptos','store')->name('egreso.concepts.store');
        Route::put('/egresos/conceptos/{id}','update')->name('egreso.concepts.update');
        Route::delete('/egresos/conceptos/{id}','destroy')->name('egreso.concepts.destroy');
    });

    Route::controller(App\Http\Controllers\EgresoReceiptController::class)->group(function(){
        Route::get('/egresos/recibos','index')->name('egreso.receipts.index');
        Route::get('/egresos/recibos/create','create')->name('egreso.receipts.create');
        Route::post('/egresos/recibos','store')->name('egreso.receipts.store');
        Route::get('/egresos/recibos/{noRecibo}','edit')->name('egreso.receipts.edit');
        Route::get('/egresos/recibos/print/{id}','print')->name('egreso.receipts.print');
    });
});

// Rutas de Contabilidad - Requieren permission:access.accounting
Route::middleware(['auth', 'permission:access.accounting'])->group(function () {
    Route::controller(App\Http\Controllers\AccountingController::class)->group(function(){
    Route::get('/contabilidad','index')->name('accounting.index');
    
    // Rutas de vistas (preview)
    Route::get('/contabilidad/abonos','abonosView')->name('accounting.abonos');
    Route::get('/contabilidad/otros-ingresos','otrosIngresosView')->name('accounting.otros-ingresos');
    Route::get('/contabilidad/total-ingresos','totalIngresosView')->name('accounting.total-ingresos');
    Route::get('/contabilidad/egresos','totalEgresosView')->name('accounting.total-egresos');
    Route::get('/contabilidad/arqueo-diario','arqueoDiarioView')->name('accounting.arqueo-diario');
    Route::get('/contabilidad/informe-semanal','informeSemanalView')->name('accounting.informe-semanal');
    Route::get('/contabilidad/informe-mensual','informeMensualView')->name('accounting.informe-mensual');
    
    // Rutas de descarga (Excel)
    Route::get('/contabilidad/abonos/download','abonosDownload')->name('accounting.abonos.download');
    Route::get('/contabilidad/otros-ingresos/download','otrosIngresosDownload')->name('accounting.otros-ingresos.download');
    Route::get('/contabilidad/total-ingresos/download','totalIngresosDownload')->name('accounting.total-ingresos.download');
    Route::get('/contabilidad/egresos/download','totalEgresosDownload')->name('accounting.total-egresos.download');
    Route::get('/contabilidad/arqueo-diario/download','arqueoDiarioDownload')->name('accounting.arqueo-diario.download');
    Route::get('/contabilidad/informe-semanal/download','informeSemanalDownload')->name('accounting.informe-semanal.download');
    Route::get('/contabilidad/informe-mensual/download','informeMensualDownload')->name('accounting.informe-mensual.download');
    
    // Otras rutas
    Route::match(['get', 'post'], '/contabilidad/bases-diarias','cashBases')->name('accounting.cash-bases');
    Route::match(['get', 'post'], '/contabilidad/investigar-abonos','investigarAbonos')->name('accounting.investigar-abonos');
    Route::match(['get', 'post'], '/contabilidad/eliminar-abonos-problematicos','eliminarAbonosProblematicos')->name('accounting.eliminar-abonos-problematicos');
    });
});

// Rutas de Base Inicial - Solo Super Admin
Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::controller(App\Http\Controllers\AccountingController::class)->group(function(){
        Route::get('/contabilidad/base-inicial', 'baseInicialView')->name('accounting.base-inicial');
        Route::post('/contabilidad/base-inicial', 'baseInicialStore')->name('accounting.base-inicial.store');
    });

    // Rutas de Herramientas de Mantenimiento - Solo Super Admin
    Route::controller(App\Http\Controllers\MaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function(){
        Route::get('/', 'index')->name('index');
        Route::get('/investigar-abonos', 'investigarAbonosProblematicos')->name('investigar-abonos');
        Route::post('/eliminar-abonos', 'eliminarAbonosProblematicos')->name('eliminar-abonos');
        Route::get('/investigar-matriculas', 'investigarMatriculasProblematicas')->name('investigar-matriculas');
        Route::post('/reparar-matriculas', 'repararMatriculasProblematicas')->name('reparar-matriculas');
        Route::post('/limpiar-datos-prueba', 'limpiarDatosPrueba')->name('limpiar-datos-prueba');
        Route::get('/get-stats-datos-prueba', 'getStatsDatosPrueba')->name('get-stats-datos-prueba');
        Route::get('/revisar-duplicados', 'revisarDuplicados')->name('revisar-duplicados');
        Route::post('/limpiar-duplicados', 'limpiarDuplicados')->name('limpiar-duplicados');
        Route::post('/generar-planilla-asistencia-prueba', 'generarPlanillaAsistenciaPrueba')->name('generar-planilla-asistencia-prueba');
        Route::get('/obtener-vista-previa-datos', 'obtenerVistaPreviaDatos')->name('obtener-vista-previa-datos');
        Route::post('/limpiar-tabla-especifica', 'limpiarTablaEspecifica')->name('limpiar-tabla-especifica');
        Route::get('/verificar-eliminaciones', 'verificarEliminaciones')->name('verificar-eliminaciones');
        Route::post('/forzar-eliminacion-fisica', 'forzarEliminacionFisica')->name('forzar-eliminacion-fisica');
        Route::get('/get-stats-costos', 'getStatsCostos')->name('get-stats-costos');
        Route::post('/eliminar-costos', 'eliminarCostos')->name('eliminar-costos');
    });

    // Ruta para eliminar recibos de egreso - Solo Super Admin
    Route::controller(App\Http\Controllers\EgresoReceiptController::class)->group(function(){
        Route::delete('/egresos/recibos/{noRecibo}','destroy')->name('egreso.receipts.destroy');
    });
});

// Rutas de Administración - Requieren permisos específicos
Route::middleware(['auth', 'permission:users.manage'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/roles', [App\Http\Controllers\Admin\UserController::class, 'assignRoles'])->name('users.assign-roles');
});

Route::middleware(['auth', 'permission:roles.manage'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/{role}/permissions', [App\Http\Controllers\Admin\RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
});


