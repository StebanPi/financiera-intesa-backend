<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Aumentar tiempo de ejecución para evitar timeouts
set_time_limit(120);
ini_set('max_execution_time', 120);

// Suprimir advertencias de deprecación de PHP 8.1+ relacionadas con tipos nullable implícitos
// Estas advertencias provienen de dependencias en vendor/ que aún no están actualizadas
error_reporting(error_reporting() & ~E_DEPRECATED & ~E_USER_DEPRECATED);

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Load Helper Functions
|--------------------------------------------------------------------------
|
| Load custom helper functions that are used throughout the application.
|
*/

require_once __DIR__.'/../app/helpers.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
