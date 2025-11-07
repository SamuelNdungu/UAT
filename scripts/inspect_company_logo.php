<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$company = App\Models\CompanyData::first();
if (!$company) { echo "No company data found\n"; exit(0); }
$logo = $company->logo_path ?? '(null)';
echo "company->logo_path: $logo\n";
$norm = $logo ? ltrim($logo, '/\\') : '';
$full = $norm ? storage_path('app/public/'.$norm) : '(none)';
echo "resolved filesystem path: $full\n";
if ($norm && file_exists($full)) {
    echo "exists: yes\n";
    echo "filesize: " . filesize($full) . " bytes\n";
} else {
    echo "exists: no\n";
}
$public = $norm ? public_path('storage/'.$norm) : '(none)';
echo "public path (storage URL): $public\n";
