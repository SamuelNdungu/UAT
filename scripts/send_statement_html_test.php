<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\StatementService;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new StatementService();
$customer = $argv[1] ?? 'CUS-0090';
$email = $argv[2] ?? 's2ndungu@gmail.com';

echo "Sending HTML statement for $customer to $email...\n";
$ok = $svc->sendStatementHtml($customer, $email);
echo $ok ? "Sent\n" : "Failed\n";
