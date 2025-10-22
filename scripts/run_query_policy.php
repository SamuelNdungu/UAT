<?php
// Simple runner to test query_policy_data
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Traits\HasDataInterrogation;

// Create a small object that uses the trait
class Tester { use HasDataInterrogation; }

$tester = new Tester();

// Use reflection to call the protected method from the trait
$ref = new ReflectionClass($tester);
$method = $ref->getMethod('query_policy_data');
$method->setAccessible(true);

// Run the query for Charles Okwi
$result = $method->invokeArgs($tester, [null, 'Charles Okwi', null, null]);

echo "Result:\n" . $result . "\n";