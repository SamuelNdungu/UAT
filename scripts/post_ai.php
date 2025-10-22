<?php
// Simple script to POST to the app's /ai/ask route using the app's HTTP kernel
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Build a request to /ai/ask
$request = Illuminate\Http\Request::create('/ai/ask', 'POST', [
    'prompt' => 'how many policies does Charles Okwi have?'
]);

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Body:\n" . $response->getContent() . "\n";

$kernel->terminate($request, $response);
