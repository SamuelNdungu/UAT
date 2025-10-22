<?php
require __DIR__ . "/../vendor/autoload.php";
putenv('APP_ENV=local');
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::select('select id, payload, available_at, created_at from jobs order by id desc limit 10');
echo json_encode($rows, JSON_PRETTY_PRINT);
