<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);

// Protected routes (Harus bawa token Bearer)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute API untuk Manajemen User
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);

    // Rute API Leave Request (Manajemen Status)
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
    Route::post('/leave-requests/{id}/cancel', [LeaveRequestController::class, 'cancel']);
    Route::delete('/leave-requests/{id}', [LeaveRequestController::class, 'destroy']);

    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::post('/impersonate/{id}', [AuthController::class, 'impersonate']);
    // Tambahkan juga route untuk mengambil balance user saat ini (untuk dropdown form)
    Route::get('/my-balances', [UserController::class, 'myBalances']);

});
