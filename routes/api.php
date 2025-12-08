<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;




Route::middleware('guest:api')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('logout',   [AuthController::class, 'logout']); 
    Route::post('refresh-token',   [AuthController::class, 'refresh']); 

    

});
