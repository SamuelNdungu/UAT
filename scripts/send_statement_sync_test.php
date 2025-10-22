<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\StatementService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

// Bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new StatementService();
$customerCode = $argv[1] ?? 'CUS-0090';
$email = $argv[2] ?? 's2ndungu@gmail.com';

try {
    echo "Generating PDF for $customerCode...\n";
    $pdf = $svc->generatePdfForCustomer($customerCode);
    if (!$pdf) {
        echo "PDF generation failed\n";
        exit(1);
    }

    echo "Attaching and sending to $email...\n";
    $ok = $svc->attachPdfToCustomer($customerCode, $pdf, true, $email);
    echo $ok ? "Sent/Attached OK\n" : "Attach/send failed\n";
} catch (\Throwable $ex) {
    echo "Exception: " . $ex->getMessage() . "\n";
}
