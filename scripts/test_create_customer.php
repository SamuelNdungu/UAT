<?php
// Test script: attempt to create a Customer using LeadsController-equivalent data
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;

try {
    $data = [
        'customer_code' => 'CUS-99999',
        'customer_type' => 'Corporate',
        'first_name' => null,
        'last_name' => null,
        'corporate_name' => 'TestCorp Ltd',
        'contact_person' => 'Test Contact',
        'email' => 'test@example.com',
        'phone' => '0700000000',
        'status' => true,
        'user_id' => 1,
    ];

    $c = Customer::create($data);
    echo "Created customer id: " . ($c->id ?? 'NULL') . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
