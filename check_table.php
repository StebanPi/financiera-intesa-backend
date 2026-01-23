<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
foreach(DB::select('DESCRIBE conceptos') as $c) {
    echo $c->Field . ' (' . $c->Type . ') ' . ($c->Null == 'NO' ? 'NOT NULL' : 'NULL') . PHP_EOL;
}
