<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaPaymentController;
use App\Http\Controllers\AiController;

// MPesa routes
Route::post('/mpesa/initiate', [MpesaPaymentController::class, 'initiateMpesaPayment']);
Route::post('/mpesa/callback', [MpesaPaymentController::class, 'handleMpesaCallback'])->name('mpesa.callback');

// AI routes
Route::middleware('api')->group(function () {
    Route::post('/ai/ask', [AiController::class, 'generate']);
    Route::post('/ai/stream', [AiController::class, 'stream']);
});