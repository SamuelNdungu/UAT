<?php
// Small runner to generate a statement PDF for testing
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $customerCode = $argv[1] ?? 'CUS-03869';
    $svc = $app->make(App\Services\StatementService::class);
    $path = $svc->generatePdfForCustomer($customerCode);
    echo "RESULT:" . ($path ?? 'NULL') . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
