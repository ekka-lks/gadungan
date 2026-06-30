<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/sensory', [DashboardController::class, 'sensory'])->name('sensory');
    Route::get('/rendaman', [DashboardController::class, 'rendaman'])->name('rendaman');
    Route::get('/devices/{device}/data', [DashboardController::class, 'getDeviceData']);
    Route::post('/devices', [DashboardController::class, 'storeDevice'])->name('devices.store');
});
