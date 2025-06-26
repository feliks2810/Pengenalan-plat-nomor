<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\Api\ViolationController;

// Rute autentikasi
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'handleLogin']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'handleRegister']);

// Rute untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/pemantauan', fn () => view('pemantauan'))->name('pemantauan.index');
    Route::get('/pelanggaran', [PelanggaranController::class, 'index'])->name('pelanggaran.index');
    Route::get('/pelanggaran/{id}', [PelanggaranController::class, 'show'])->name('pelanggaran.show');
    Route::get('/statistik', [ViolationController::class, 'showStats'])->name('statistik.index');
});

// Rute logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');