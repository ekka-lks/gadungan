<?php

use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

Route::post('/sensor-logs', [SensorController::class, 'store']);
