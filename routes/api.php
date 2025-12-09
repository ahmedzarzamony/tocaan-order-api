<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:api')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh-token', [AuthController::class, 'refresh']);

    Route::get('payments', [PaymentController::class, 'listPayments']);
    Route::post('payments', [PaymentController::class, 'payOrder']);

    Route::resource('orders', OrderController::class)->except(['create', 'edit']);

});
