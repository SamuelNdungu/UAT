<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\StatementService;

$svc = new StatementService();
$path = $svc->generatePdfForCustomer('CUS-00093');
if ($path) {
    echo "Generated PDF: " . $path . "\n";
} else {
    echo "Failed to generate PDF. Check logs.\n";
}
