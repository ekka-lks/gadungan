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
    Route::get('/process', [DashboardController::class, 'process'])->name('process');
    Route::post('/process/update-stage', [DashboardController::class, 'updateStage'])->name('process.updateStage');
    Route::post('/process/assign-sensor', [DashboardController::class, 'assignSensor'])->name('process.assignSensor');
    Route::get('/devices/{device}/data', [DashboardController::class, 'getDeviceData']);
    Route::post('/devices', [DashboardController::class, 'storeDevice'])->name('devices.store');
});
