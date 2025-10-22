<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\StatementService;
use Illuminate\Support\Facades\Storage;

$svc = new StatementService();
$customer = 'CUS-00093';

// find latest PDF for this customer
$files = Storage::disk('public')->files('statements');
$latest = null;
foreach ($files as $f) {
    if (strpos($f, $customer) !== false) {
        if ($latest === null || filemtime(storage_path('app/public/' . $f)) > filemtime(storage_path('app/public/' . $latest))) {
            $latest = $f;
        }
    }
}

if (!$latest) {
    echo "No statement PDFs found for $customer\n";
    exit(1);
}

$pdfPublic = 'storage/' . $latest;

echo "Attaching $pdfPublic to customer $customer and sending email...\n";
$ok = $svc->attachPdfToCustomer($customer, $pdfPublic, true, 's2ndungu@gmail.com');
if ($ok) echo "Attached and email queued/sent successfully.\n"; else echo "Failed to attach/send. Check logs.\n";

exit(0);
