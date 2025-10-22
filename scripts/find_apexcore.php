<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Traits\HasDataInterrogation;

class Finder { use HasDataInterrogation; }

$finder = new Finder();
$ref = new ReflectionClass($finder);

// Call query_database to search customers for ApexCore
$q = $ref->getMethod('query_database');
$q->setAccessible(true);
$params = [
    'table_name' => 'customers',
    'search' => 'ApexCore',
    'limit' => 50
];

echo "Searching customers for 'ApexCore'...\n";
$customersJson = $q->invokeArgs($finder, [$params]);
echo $customersJson . "\n\n";

// If we got a customer, try to find related policies by customer_code or corporate_name
$data = json_decode($customersJson, true);
$rows = $data['rows'] ?? $data['customers'] ?? [];
if (count($rows) > 0) {
    // try each matching customer to find policies
    foreach ($rows as $r) {
        $custCode = $r['customer_code'] ?? null;
        $corp = $r['corporate_name'] ?? null;
        echo "Found customer: " . ($r['id'] ?? 'n/a') . " - " . ($corp ?? ($r['first_name'] . ' ' . ($r['last_name'] ?? ''))) . "\n";
        if ($custCode) {
            $pParams = ['table_name' => 'policies', 'filters' => ['customer_code' => $custCode], 'limit' => 50];
            echo "Searching policies for customer_code=$custCode...\n";
            $policiesJson = $q->invokeArgs($finder, [$pParams]);
            echo $policiesJson . "\n\n";
        } else {
            // fallback: search policies by corporate name or customer name
            if ($corp) {
                $pParams = ['table_name' => 'policies', 'search' => $corp, 'limit' => 50];
                echo "Searching policies for corporate_name=$corp...\n";
                $policiesJson = $q->invokeArgs($finder, [$pParams]);
                echo $policiesJson . "\n\n";
            }
        }
    }
} else {
    echo "No customers matched 'ApexCore'. You can try searching a different term (e.g., company name or customer code).\n";
}

exit(0);
