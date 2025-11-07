<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customerCode = $argv[1] ?? 'CUS-03869';
$company = App\Models\CompanyData::first();
$customer = App\Models\Customer::where('customer_code', $customerCode)->first();
$policies = App\Models\Policy::where('customer_code', $customerCode)->orderBy('start_date', 'desc')->get()->map(function($p){
    return [
        'file_number' => $p->fileno,
        'policy_no' => $p->policy_no ?? $p->policy_no,
        'coverage' => $p->coverage,
        'start_date' => $p->start_date,
        'end_date' => $p->end_date,
        'premium' => $p->premium,
        'paid_amount' => $p->paid_amount ?? 0,
        'outstanding' => $p->outstanding_amount ?? $p->balance ?? 0,
        'status' => $p->status,
    ];
})->toArray();

$data = [
    'company' => $company,
    'company_logo_local' => null,
    'customer' => $customer,
    'policies' => $policies,
    'generated_at' => now()->toDateTimeString(),
];
$html = View::make('statements.statement', $data)->render();
$save = storage_path('app/public/debug_statement.html');
file_put_contents($save, $html);
echo "WROTE: $save\n";
echo "--- First 800 chars of rendered HTML ---\n";
echo substr($html, 0, 800) . "\n";
// show any <img tags nearby
if (preg_match_all('/<img[^>]+>/i', $html, $matches)) {
    echo "Found " . count($matches[0]) . " <img> tags:\n";
    foreach ($matches[0] as $m) echo $m . "\n";
} else {
    echo "No <img> tags found in rendered HTML.\n";
}
