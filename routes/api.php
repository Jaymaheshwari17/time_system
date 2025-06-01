<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectDetailController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/dashboard', [AuthController::class, 'dashboard']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);       // List all users
    Route::post('/users', [UserController::class, 'store']);      // Create user
    Route::get('/users/{user}', [UserController::class, 'show']); // Get one user
    Route::put('/users/{user}', [UserController::class, 'update']);// Update user
    Route::delete('/users/{user}', [UserController::class, 'destroy']); // Delete user

    Route::get('/access-rights', [UserController::class, 'getAccessRights']);

    Route::apiResource('projects', ProjectDetailController::class);


});
