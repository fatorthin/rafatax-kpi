<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - Authentication
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - Requires authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/revoke-all-tokens', [AuthController::class, 'revokeAll']);

    // CaseProject API Routes
    Route::apiResource('case-projects', CaseProjectController::class);
});
