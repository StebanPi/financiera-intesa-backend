<?php

use App\Services\MatriculaService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(MatriculaService::class);
$cod = '1085096469'; // The code from the logs
$data = $service->getFullEnrollmentData($cod, 'Barrancabermeja');

echo "Total entries: " . count($data['entries']) . "\n";
echo "Entries IDs: " . implode(', ', array_column($data['entries']->toArray(), 'id')) . "\n";
echo "Total other_entries: " . count($data['other_entries']) . "\n";
