<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Small wrapper to call the protected trait method
class FauxCaller {
    use App\Services\Traits\HasDataInterrogation;

    public function call(array $params) {
        return $this->send_renewal_notice($params);
    }
}

$sample = 'send policy renewal notice to CUS-00129 Charles Okwi on email s2ndungu@gmail.com';
$f = new FauxCaller();
$result = $f->call(['text' => $sample]);
// Ensure output is newline-terminated for console readability
echo $result . PHP_EOL;
