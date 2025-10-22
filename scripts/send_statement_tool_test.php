<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Traits\HasDataInterrogation;

class Tester {
    use HasDataInterrogation;
}

$t = new Tester();
$result = $t->send_statement_of_account('CUS-00093', true, 's2ndungu@gmail.com');
echo $result . PHP_EOL;
