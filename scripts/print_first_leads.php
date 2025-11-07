<?php
// Boot the Laravel app and print first few Lead records (normalized attributes)
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Carbon\Carbon;

$leads = Lead::orderBy('id', 'desc')->limit(5)->get();
if ($leads->isEmpty()) {
    echo "No leads found in database.\n";
    exit(0);
}

foreach ($leads as $lead) {
    $name = $lead->lead_type === 'Corporate' ? ($lead->corporate_name ?? $lead->company_name) : trim((($lead->first_name ?? '') . ' ' . ($lead->last_name ?? '')));
    $follow = $lead->follow_up_date ? Carbon::parse($lead->follow_up_date)->format('Y-m-d') : '';
    echo "ID: {$lead->id}\n";
    echo "Lead Type: " . ($lead->lead_type ?? 'N/A') . "\n";
    echo "Name: " . ($name ?: 'N/A') . "\n";
    echo "Email: " . ($lead->email ?? $lead->email_address ?? 'N/A') . "\n";
    echo "Mobile: " . ($lead->mobile ?? $lead->phone ?? 'N/A') . "\n";
    echo "Policy Type: " . ($lead->policy_type ?? 'N/A') . "\n";
    echo "Lead Source: " . ($lead->lead_source ?? 'N/A') . "\n";
    echo "Follow-up Date: " . ($follow) . "\n";
    echo str_repeat('-', 40) . "\n";
}
