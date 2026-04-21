<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('entries');
print_r($cols);
$cols2 = Illuminate\Support\Facades\Schema::getColumnListing('other_entries');
print_r($cols2);
