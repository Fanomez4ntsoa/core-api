<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Identity\Controllers\IdentityController;
use App\Modules\User\Controllers\UserController;
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

    // Module User
    Route::put('/user/profile',          [UserController::class, 'updateProfile']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);

    // Module Identity
    Route::get('/verification/status',  [IdentityController::class, 'status']);
    Route::post('/verification/submit', [IdentityController::class, 'submit']);
});