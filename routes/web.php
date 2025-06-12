<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelanggaranController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\ViolationController;


Route::get('/', [AuthController::class, 'login'])->name('login');

// Rute login
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'handleLogin']);

// Rute register
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'handleRegister']);

// Group route untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/pemantauan', fn () => view('pemantauan'));
    Route::get('/pelanggaran', [PelanggaranController::class, 'show'])->name('pelanggaran');
    Route::get('/statistik', [ViolationController::class, 'showStats'])->name('statistik');
});

// Rute logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
