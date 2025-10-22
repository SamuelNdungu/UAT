<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Traits\HasDataInterrogation;

class DBTester { use HasDataInterrogation; }

$tester = new DBTester();
$ref = new ReflectionClass($tester);
$m = $ref->getMethod('query_database');
$m->setAccessible(true);

$params = [
    'table_name' => 'policies',
    'search' => 'Charles Okwi',
    'limit' => 10
];

$result = $m->invokeArgs($tester, [$params]);

echo $result . "\n";
