<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestEmailController;

Route::get('/test-email', [TestEmailController::class, 'sendTestEmail']);
