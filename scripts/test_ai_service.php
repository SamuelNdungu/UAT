<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
// Boot the application minimally
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AIService;

$service = new AIService();
$prompt = $argv[1] ?? 'What is the purpose of an insurance policy renewal?';
echo "Prompt: $prompt\n";
 $res = $service->generate($prompt);
echo "--- RESULT ---\n";
if (is_array($res)) {
	echo "method: " . ($res['method'] ?? 'n/a') . "\n";
	echo "reply:\n" . ($res['reply'] ?? '') . "\n\n";
	echo "raw (first 800 chars):\n" . substr((string)($res['raw'] ?? ''), 0, 800) . "\n";
} else {
	echo "unexpected result: ";
	var_export($res);
	echo "\n";
}
echo "--- END ---\n";
