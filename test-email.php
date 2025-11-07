<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Policy;
use Illuminate\Support\Facades\Mail;

// Find the policy
$policy = Policy::where('fileno', 'FN--2022')->first();

if (!$policy) {
    die("Policy not found\n");
}

// Get customer email
try {
    $customerEmail = $policy->customer->email ?? null;
    
    if (!$customerEmail) {
        die("No email found for customer with ID: " . $policy->customer_id . "\n");
    }

    // Send test email
    Mail::raw('This is a test renewal notice for policy ' . $policy->fileno, function($message) use ($customerEmail, $policy) {
        $message->to($customerEmail)
                ->subject('Test Renewal Notice - ' . $policy->fileno);
    });

    echo "Test email sent to: " . $customerEmail . "\n";
    echo "Check storage/logs/laravel.log for email details\n";

} catch (\Exception $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
