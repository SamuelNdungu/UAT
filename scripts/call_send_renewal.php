<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\StatementService;

$svc = new StatementService();
$result = $svc->sendRenewalNotice('CUS-0090', 's2ndungu@gmail.com', [17]);
var_export($result);
echo PHP_EOL;