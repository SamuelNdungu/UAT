<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\AiController;
use App\Services\AIService;
use Illuminate\Http\Request;

$service = new AIService();
$controller = new AiController($service);

$request = Request::create('/ai/ask', 'POST', ['prompt' => 'how many policies does Charles Okwi have?']);

try {
	$response = $controller->generate($request);
	echo "Response class: " . get_class($response) . "\n";
	if (method_exists($response, 'getStatusCode')) {
		echo "Status: " . $response->getStatusCode() . "\n";
	}
	if (method_exists($response, 'getContent')) {
		echo "Body:\n" . $response->getContent() . "\n";
	} else {
		var_dump($response);
	}
} catch (\Throwable $ex) {
	echo "Exception: " . $ex->getMessage() . "\n";
}

exit(0);
