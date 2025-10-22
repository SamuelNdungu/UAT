<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Customer::where('customer_code', 'CUS-00093')->first();
if (!$c) { echo "Customer not found\n"; exit(1); }

echo "Customer dump:\n";
var_dump(['id' => $c->id, 'customer_code' => $c->customer_code, 'documents' => $c->documents]);
