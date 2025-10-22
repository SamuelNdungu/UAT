<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AIService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
// NEW IMPORTS
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller; // Ensure Controller is imported

class AiController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        return view('ai.ai');
    }

    public function status()
    {
        $endpoint = env('AI_SERVICE_URL', 'http://127.0.0.1:11434/');
        try {
            $r = \Illuminate\Support\Facades\Http::timeout(3)->get($endpoint);
            if ($r->successful()) {
                return response()->json(['ok' => true, 'endpoint' => $endpoint]);
            }
        } catch (\Exception $e) {
            // ignore
        }
        return response()->json(['ok' => false, 'endpoint' => $endpoint], 503);
    }
    
    // RENAMED from 'ask' to 'generate' for clarity, keeping original logic
    public function generate(Request $request) 
    {
        // 🚀 IMPROVEMENT: Increase execution limit to prevent PHP timeouts (e.g., 120 seconds)
        set_time_limit(120); 

        $data = $request->validate([
            'prompt' => 'required|string',
            'confirm_token' => 'nullable|string'
        ]);

        Log::info('AI request received', ['prompt_snippet' => substr($data['prompt'], 0, 200)]);

        try {
            // Server-side confirm token enforcement:
            $prompt = $data['prompt'];
            $token = $data['confirm_token'] ?? null;

            // Detect potentially destructive prompts (simple heuristic)
            $needsConfirm = preg_match('/send\s+statement/i', $prompt);

            if ($needsConfirm && empty($token)) {
                // Issue a short-lived confirm token and store a hash of the prompt
                $confirmToken = Str::random(40);
                $promptHash = sha1($prompt);
                Cache::put('ai_confirm_' . $confirmToken, $promptHash, now()->addMinutes(5));

                return response()->json([
                    'confirm_required' => true,
                    'confirm_token' => $confirmToken,
                    'message' => 'Please confirm generation and sending of the statement. Use the confirmation button to proceed.'
                ]);
            }

            if (!empty($token)) {
                $cacheKey = 'ai_confirm_' . $token;
                $stored = Cache::get($cacheKey);
                if (!$stored || $stored !== sha1($prompt)) {
                    return response()->json(['error' => 'Invalid or expired confirmation token. Please submit again to request a new confirmation.'], 400);
                }
                // Consume the token
                Cache::forget($cacheKey);
            }

            // Execute AI generate now that confirmation (when needed) is satisfied
            $res = $this->aiService->generate($prompt);

            if (!is_array($res) || !array_key_exists('reply', $res)) {
                Log::error('AIService returned unexpected result', ['result' => $res]);
                return response()->json(['error' => 'AI returned unexpected response'], 502);
            }

            $reply = (string) $res['reply'];
            $method = $res['method'] ?? 'unknown';

            if ($reply === '') {
                Log::error('AIService returned empty reply', ['method' => $method]);
                $raw_error = $res['raw'] ?? 'AI returned empty response'; 
                return response()->json(['error' => $raw_error], 502);
            }

            Log::info('AI reply generated', ['len' => strlen($reply), 'method' => $method]);

            $out = ['reply' => $reply, 'method' => $method];
            if (config('app.env') !== 'production') {
                $out['raw'] = $res['raw'] ?? null; 
            }

            return response()->json($out);
        } catch (\Exception $e) {
            Log::error('AI handler exception', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to generate AI reply', 'message' => $e->getMessage()], 500);
        }
    }
    
    // 🚀 NEW STREAMING METHOD
    public function stream(Request $request): StreamedResponse
    {
        // Turn off PHP time limit for streaming as generation time is unknown
        set_time_limit(0); 

        $prompt = $request->input('prompt');
        if (!$prompt) {
            return response()->stream(function () {
                echo '{"error": "Prompt is required."}' . "\n";
            }, 400, ['Content-Type' => 'application/json']);
        }
        
        Log::info('AI streaming request received', ['prompt_snippet' => substr($prompt, 0, 200)]);

        $psrResponse = $this->aiService->streamGenerate($prompt);

        if (!$psrResponse) {
             // Handle case where service failed before streaming started
             return response()->stream(function () {
                 echo '{"reply": "Error: AI service failed to connect or API key missing."}' . "\n";
             }, 503, ['Content-Type' => 'application/json']);
        }

        return new StreamedResponse(function () use ($psrResponse) {
            
            // Set output headers to prevent buffering by the webserver
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no'); // For Nginx

            $stream = $psrResponse->getBody();

            while (!$stream->eof()) {
                // Read a chunk from the API stream
                $chunk = $stream->read(4096); 
                
                // --- Decode and Extract the Text ---
                // Gemini streaming returns NDJSON. We split, parse, and extract the text.
                $lines = preg_split('/\r?\n/', $chunk, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($lines as $line) {
                    $decoded = json_decode(trim($line), true);
                    
                    // Check the expected path for text in a streaming response
                    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                        $replyChunk = $decoded['candidates'][0]['content']['parts'][0]['text'];
                        
                        // Output the raw text chunk to the client
                        echo $replyChunk; 
                        
                        // Force flush the output buffer to send the data now
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }
                }
            }

            // Optional: send a final marker if your frontend expects one
            // echo "\n[DONE]";
            
        }, 200, [
            // Using application/octet-stream is generally safer than text/event-stream
            // when streaming raw text chunks like this, but either can work.
            'Content-Type' => 'application/octet-stream', 
        ]);
    }
}