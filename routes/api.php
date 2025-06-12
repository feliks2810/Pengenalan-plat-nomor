<?php

use App\Http\Controllers\Api\ViolationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/violations', [ViolationController::class, 'store']);
    Route::get('/violations', [ViolationController::class, 'index']);
});