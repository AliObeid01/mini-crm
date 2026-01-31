<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use Illuminate\Support\Facades\Route;

// Public Routes 
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Protected Routes

Route::middleware('auth:sanctum')->group(function () {

 Route::apiResource('departments', DepartmentController::class);

});