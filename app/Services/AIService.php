<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Psr\Http\Message\ResponseInterface;
use App\Services\Traits\HasDataInterrogation;
use App\Models\Customer;

class AIService
{
    // Now using the trait that contains both policy functions
    use HasDataInterrogation; 

    /**
     * Generates a reply using the remote streaming API (called by /ai/stream).
     *
     * @param string $prompt The prompt to send.
     * @return ResponseInterface|null The raw PSR-7 response object, or null on failure.
     */
    public function streamGenerate(string $prompt): ?ResponseInterface
    {
        $remoteApiKey = env('GEMINI_API_KEY');
        $remoteModel = 'gemini-2.5-flash';
        $remoteEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $remoteModel . ':generateContent';

        if (!$remoteApiKey) {
            Log::error('AIService: GEMINI_API_KEY not set for streaming.');
            return null;
        }

        try {
            $streamingEndpoint = str_replace(':generateContent', ':streamGenerateContent', $remoteEndpoint);

            $payload = [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
            ];

            $response = Http::withOptions([
                'stream' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 90,
            ])
            ->post($streamingEndpoint . '?key=' . $remoteApiKey, $payload);

            if (!$response->successful()) {
                Log::error('AIService: Remote streaming failed with status', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }
            
            Log::info('AIService: Remote streaming started successfully.', ['model' => $remoteModel]);
            
            return $response->toPsrResponse();

        } catch (\Exception $e) {
            Log::error('AIService: Remote streaming exception', ['exception' => $e->getMessage()]);
            return null;
        }
    }


    /**
     * Generates a reply using the remote API with Ollama fallbacks (called by /ai/ask).
     * This method supports multi-step function calling.
     */
    public function generate(string $prompt): array
    {
        // 1. Time Budgeting and Configuration
        $startTime = microtime(true);
        $timeLimit = (int) env('AI_REMOTE_TIMEOUT', 30);
        $remoteApiKey = env('GEMINI_API_KEY');
        
        // --- 2. Try Remote API (FUNCTION CALLING LOGIC) ---
        if ($remoteApiKey) {
            $remoteModel = 'gemini-2.5-flash';
            $remoteEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $remoteModel . ':generateContent';

            try {
                $httpTimeout = max(5, (int) floor($timeLimit * 0.8));
                $history = [
                    // Start conversation with the user's prompt
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ];
                
                $finalRaw = null;
                $finalReply = null;

                // Loop for function calling (max 2 iterations: 1 for call, 1 for final response)
                for ($i = 0; $i < 2; $i++) {
                    
                    $payload = [
                        'contents' => $history,
                        // Include the tool declarations (getToolDeclarations from HasInternalFunctions trait)
                        'tools' => $this->getToolDeclarations(), 
                    ];
                    
                    $response = Http::withHeaders(['Content-Type' => 'application/json'])
                        ->timeout($httpTimeout)
                        ->post($remoteEndpoint . '?key=' . $remoteApiKey, $payload);
                        
                    $raw = $response->json();
                    $finalRaw = $raw; // Store the last response for debugging

                    if (!$response->successful() || !isset($raw['candidates'][0]['content']['parts'][0])) {
                        Log::warning('AIService: Remote API failed during step ' . ($i + 1), ['status' => $response->status(), 'body' => $response->body()]);
                        break; 
                    }

                    $candidate = $raw['candidates'][0];
                    $part = $candidate['content']['parts'][0];

                    // --- CHECK 1: IS IT A FUNCTION CALL? ---
                    if (isset($part['functionCall'])) {
                        $functionCall = $part['functionCall'];
                        $functionName = $functionCall['name'];
                        $rawArgs = $functionCall['args'] ?? [];

                        // Normalize args: support JSON string, object, or associative array
                        if (is_string($rawArgs)) {
                            $decodedArgs = json_decode($rawArgs, true) ?: [];
                        } elseif (is_object($rawArgs)) {
                            $decodedArgs = json_decode(json_encode($rawArgs), true) ?: [];
                        } elseif (is_array($rawArgs)) {
                            $decodedArgs = $rawArgs;
                        } else {
                            $decodedArgs = [];
                        }

                        Log::info('AIService: Function call requested.', ['function' => $functionName, 'args' => $decodedArgs]);

                        // Add the model's function request to history
                        $history[] = ['role' => 'model', 'parts' => [['functionCall' => $functionCall]]];

                        // Confirmation flow: when a send function is requested and both customer_code and email_to
                        // are available, ask the user for a quick confirmation before executing. If the model
                        // supplies a 'confirmed' flag (truthy) we'll proceed.
                        try {
                            $needsConfirm = in_array($functionName, ['send_renewal_notice', 'send_statement_of_account']);
                            $hasCustomerCode = false;
                            $hasEmail = false;
                            if (is_array($decodedArgs)) {
                                foreach (['customer_code','customerCode','customer'] as $k) { if (isset($decodedArgs[$k]) && !empty($decodedArgs[$k])) { $hasCustomerCode = true; break; } }
                                foreach (['email_to','emailTo','email','recipient'] as $k) { if (isset($decodedArgs[$k]) && !empty($decodedArgs[$k])) { $hasEmail = true; break; } }
                            }
                            $confirmed = false;
                            if (is_array($decodedArgs) && (isset($decodedArgs['confirmed']) || isset($decodedArgs['confirm']))) {
                                $confirmed = !empty($decodedArgs['confirmed'] ?? $decodedArgs['confirm']);
                            }

                            if ($needsConfirm && $hasCustomerCode && $hasEmail && !$confirmed) {
                                // Build a short confirmation reply and return it (don't execute function yet)
                                $cc = $decodedArgs['customer_code'] ?? ($decodedArgs['customerCode'] ?? ($decodedArgs['customer'] ?? '')); 
                                $em = $decodedArgs['email_to'] ?? ($decodedArgs['emailTo'] ?? ($decodedArgs['email'] ?? ''));
                                $confirmText = "I will send to $cc ($em) — send now?";
                                Log::info('AIService: requesting user confirmation before executing send function', ['function'=>$functionName, 'customer_code'=>$cc, 'email'=>$em]);
                                return ['reply' => $confirmText, 'method' => 'confirm', 'raw' => $decodedArgs];
                            }
                        } catch (\Throwable $_) {
                            // ignore confirmation failures and continue to execution
                        }

                        // --- Autofill: if function expects a customer_code and email missing, look up the customer's email ---
                        try {
                            // Only attempt autofill for our known functions
                            if (in_array($functionName, ['send_renewal_notice', 'send_statement_of_account'])) {
                                $cc = null;
                                // Accept different key casings
                                foreach (['customer_code', 'customerCode', 'customer'] as $k) {
                                    if (isset($decodedArgs[$k]) && !empty($decodedArgs[$k])) { $cc = $decodedArgs[$k]; break; }
                                }
                                $emailKeyExists = false;
                                foreach (['email_to','emailTo','email','recipient'] as $ek) { if (array_key_exists($ek, $decodedArgs)) { $emailKeyExists = true; break; } }
                                if ($cc && !$emailKeyExists) {
                                    $cust = Customer::where('customer_code', $cc)->first();
                                    if ($cust && !empty($cust->email)) {
                                        // prefer email_to key
                                        if (array_key_exists('email_to', $decodedArgs)) {
                                            $decodedArgs['email_to'] = $cust->email;
                                        } else {
                                            $decodedArgs['email_to'] = $cust->email;
                                        }
                                        Log::info('AIService: autofilled customer email for function', ['function'=>$functionName, 'customer_code'=>$cc, 'email'=>$cust->email]);
                                    }
                                }
                            }
                        } catch (\Throwable $ex) {
                            Log::warning('AIService: autofill lookup failed', ['error' => $ex->getMessage()]);
                        }

                        // --- EXECUTE THE FUNCTION LOCALLY ---
                        if (method_exists($this, $functionName)) {
                            try {
                                // Map associative args to positional arguments expected by the PHP method
                                $ref = new \ReflectionMethod($this, $functionName);
                                $positional = [];
                                foreach ($ref->getParameters() as $param) {
                                    $pname = $param->getName(); // e.g., balanceStatus

                                    // direct match
                                    if (array_key_exists($pname, $decodedArgs)) {
                                        $positional[] = $decodedArgs[$pname];
                                        continue;
                                    }

                                    // snake_case match
                                    $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $pname));
                                    if (array_key_exists($snake, $decodedArgs)) {
                                        $positional[] = $decodedArgs[$snake];
                                        continue;
                                    }

                                    // case-insensitive key match
                                    $found = false;
                                    foreach ($decodedArgs as $k => $v) {
                                        if (strtolower($k) === strtolower($pname)) {
                                            $positional[] = $v;
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if ($found) continue;

                                    // fallback to default value or null
                                    if ($param->isDefaultValueAvailable()) {
                                        $positional[] = $param->getDefaultValue();
                                    } else {
                                        $positional[] = null;
                                    }
                                }

                                // Call the local function with mapped positional args.
                                // If mapping produced only nulls but the model provided decodedArgs,
                                // fall back to passing the decodedArgs as a single associative param
                                $allNull = true;
                                foreach ($positional as $pv) {
                                    if ($pv !== null) { $allNull = false; break; }
                                }
                                if ($allNull && !empty($decodedArgs)) {
                                    try {
                                        Log::info('AIService: positional mapping empty, falling back to passing decodedArgs as single param', ['function'=>$functionName, 'decodedArgs'=>$decodedArgs]);
                                    } catch (\Throwable $_) {}
                                    $functionResult = call_user_func([$this, $functionName], $decodedArgs);
                                } else {
                                    $functionResult = call_user_func_array([$this, $functionName], $positional);
                                }

                                // Decode the function result (expected to be JSON string)
                                $functionData = json_decode($functionResult, true);

                                // If decoding failed, make a best-effort fallback
                                if ($functionData === null) {
                                    $functionData = ['raw' => $functionResult];
                                }

                                // Add the function's result to history for the next API call
                                $history[] = [
                                    'role' => 'function',
                                    'parts' => [
                                        'functionResponse' => [
                                            'name' => $functionName,
                                            'response' => $functionData
                                        ]
                                    ]
                                ];


                                    // Prepare a human-friendly reply summarizing the structured data
                                    // Special-case function responses that return a status/message (e.g., send_statement_of_account)
                                    if (isset($functionData['status'])) {
                                        // If function returned status, honor message and include path when present
                                        $status = strtolower((string) $functionData['status']);
                                        $msg = $functionData['message'] ?? ($functionData['summary'] ?? json_encode($functionData));
                                        // Treat 'ok', 'success', 'queued' and 'pending' as non-error statuses
                                        $nonErrorStatuses = ['ok', 'success', 'queued', 'pending'];
                                        $replyText = in_array($status, $nonErrorStatuses) ? $msg : ("Error: " . $msg);
                                        if (!empty($functionData['path'])) {
                                            $replyText .= "\nPath: " . $functionData['path'];
                                        }

                                        return [
                                            'reply' => $replyText,
                                            'method' => 'function',
                                            'raw' => $functionData,
                                        ];
                                    }

                                    $summary = '';
                                    $listLines = [];

                                    if (isset($functionData['summary'])) {
                                        $summary = $functionData['summary'];
                                    }

                                    // Normalize candidate rows: policies vs rows
                                    $rows = [];
                                    if (isset($functionData['policies']) && is_array($functionData['policies'])) {
                                        $rows = $functionData['policies'];
                                    } elseif (isset($functionData['rows']) && is_array($functionData['rows'])) {
                                        $rows = $functionData['rows'];
                                    }

                                    $count = count($rows);
                                    if ($summary === '') {
                                        if ($count === 0) {
                                            $summary = 'There are no policies matching your request.';
                                        } elseif ($count === 1) {
                                            $p = $rows[0];
                                            $summary = "Found 1 policy: " . ($p['file_number'] ?? ($p['fileno'] ?? 'unknown')) . " (" . ($p['customer'] ?? ($p['customer_name'] ?? 'unknown')) . ")";
                                        } else {
                                            $summary = "Found $count policies matching the criteria.";
                                        }
                                    }

                                    // Build a readable list (top N)
                                    if ($count > 0) {
                                        $max = min(10, $count);
                                        for ($ii = 0; $ii < $max; $ii++) {
                                            $r = $rows[$ii];
                                            // friendly extraction of common fields
                                            $file = $r['file_number'] ?? ($r['fileno'] ?? ($r['policy_no'] ?? 'n/a'));
                                            $name = $r['customer'] ?? ($r['customer_name'] ?? (($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')));
                                            $status = $r['status'] ?? 'N/A';
                                            $premium = isset($r['premium']) ? (string)$r['premium'] : (isset($r['amount'] ) ? (string)$r['amount'] : '');
                                            $date = $r['due_date'] ?? ($r['end_date'] ?? ($r['start_date'] ?? ''));

                                            $parts = [];
                                            $parts[] = "#" . ($ii + 1) . ": " . $file;
                                            if ($name) $parts[] = $name;
                                            if ($status) $parts[] = "status: " . $status;
                                            if ($premium !== '') $parts[] = "premium: " . $premium;
                                            if ($date) $parts[] = "date: " . $date;

                                            $listLines[] = implode(' — ', $parts);
                                        }
                                    }

                                    $replyText = $summary;
                                    if (!empty($listLines)) {
                                        $replyText .= "\n\n" . implode("\n", $listLines);
                                    }

                                // Return immediately: we already have an answer from the local function
                                return [
                                    'reply' => $replyText,
                                    'method' => 'function',
                                    'raw' => $functionData,
                                ];
                            } catch (\Throwable $ex) {
                                Log::error('AIService: Exception while executing local function.', ['function' => $functionName, 'error' => $ex->getMessage()]);
                                $finalReply = 'Error: Exception occurred while executing function.';
                                break;
                            }
                        } else {
                            Log::error('AIService: Requested function does not exist.', ['function' => $functionName]);
                            $finalReply = 'Error: Requested function not implemented.';
                            break;
                        }
                    }

                    // --- CHECK 2: IS IT THE FINAL TEXT RESPONSE? ---
                    if (isset($part['text'])) {
                        $finalReply = $part['text'];
                        Log::info('AIService: Remote API success (final text).', ['duration' => microtime(true) - $startTime]);
                        break; // Exit loop, we have the final answer
                    }
                    
                    break; // Fallthrough if response is neither a call nor text (e.g., Blocked)
                } 

                if ($finalReply !== null) {
                    return [
                        'reply' => $finalReply,
                        'method' => 'remote',
                        'raw' => $finalRaw,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('AIService: Remote API exception.', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }
        
        // --- 3. Fallback: Ollama HTTP (If time allows) ---
        $ollamaEndpoint = env('AI_SERVICE_URL', 'http://127.0.0.1:11434/api/generate');
        $model = env('AI_MODEL', 'phi3:mini'); // Reads the tinyllama value from your .env
        $remainingTime = $timeLimit - (microtime(true) - $startTime);

        if ($remainingTime > 3) { // Only try if we have at least 3 seconds left
            try {
                $response = Http::timeout($remainingTime)
                    ->post($ollamaEndpoint, [
                        'model' => $model,
                        'prompt' => $prompt,
                        'stream' => false,
                    ]);

                $raw = $response->json();

                if ($response->successful() && isset($raw['response'])) {
                    Log::info('AIService: Ollama HTTP success.', ['duration' => microtime(true) - $startTime]);
                    return [
                        'reply' => $raw['response'],
                        'method' => 'ollama-http',
                        'raw' => $raw,
                    ];
                }

            } catch (\Exception $e) {
                Log::error('AIService: Ollama HTTP exception.', ['exception' => $e->getMessage()]);
            }
        }

        // --- 4. Fallback: Ollama CLI (If time allows and binary found) ---
        $finder = new ExecutableFinder();
        $ollamaBinary = $finder->find('ollama');
        $remainingTime = $timeLimit - (microtime(true) - $startTime);

        if ($ollamaBinary && $remainingTime > 3) {
            try {
                $process = new Process([$ollamaBinary, 'run', $model, $prompt]);
                $process->setTimeout($remainingTime);
                $process->run();

                if ($process->isSuccessful()) {
                    $reply = trim($process->getOutput());
                    if ($reply) {
                        Log::info('AIService: Ollama CLI success.', ['duration' => microtime(true) - $startTime]);
                        return [
                            'reply' => $reply,
                            'method' => 'ollama-cli',
                            'raw' => $process->getOutput(),
                        ];
                    }
                } else {
                    Log::warning('AIService: Ollama CLI failed.', ['error' => $process->getErrorOutput()]);
                }
            } catch (\Exception $e) {
                Log::error('AIService: Ollama CLI exception.', ['exception' => $e->getMessage()]);
            }
        }

        // 5. Final Fallback
        $finalRaw = isset($raw) ? json_encode($raw) : 'No successful API call or fallback was attempted.';
        return ['reply' => 'I was unable to process that request. The AI service failed to return a response.', 'method' => 'none', 'raw' => $finalRaw];
    }
}
