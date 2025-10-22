<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new App\Services\StatementService();
$ok = $svc->sendRenewalNotice('CUS-0090', 's2ndungu@gmail.com');
var_export($ok);
