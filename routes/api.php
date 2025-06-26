<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ViolationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/violations', [ViolationController::class, 'index'])->name('violations.index');
    Route::get('/violations/stats', [ViolationController::class, 'stats'])->name('violations.stats');
    Route::get('/violations/recent', [ViolationController::class, 'recent'])->name('violations.recent');
    Route::post('/violations', [ViolationController::class, 'store'])->name('violations.store');
});