<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShortenUrlController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public redirect route (no authentication required)
Route::get('/redirect/{shortCode}', [ShortenUrlController::class, 'redirect']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/shorten', [ShortenUrlController::class, 'shortenUrl']);
});
