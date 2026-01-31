<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public Routes (No Authentication Required)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

