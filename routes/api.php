<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaPaymentController;

Route::post('/mpesa/initiate', [MpesaPaymentController::class, 'initiateMpesaPayment']);
Route::post('/mpesa/callback', [MpesaPaymentController::class, 'handleMpesaCallback'])->name('mpesa.callback');