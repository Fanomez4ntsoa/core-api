<?php

use App\Modules\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Auth public
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées
Route::middleware('auth:api')->group(function () {
    Route::get('/me',           [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});