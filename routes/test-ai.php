<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/test-ai', function () {
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . env('GEMINI_API_KEY'), [
            'contents' => [
                ['parts' => [['text' => 'Hello, how are you?']]]
            ]
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'response' => $response->json()
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'API request failed',
            'response' => $response->body()
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
