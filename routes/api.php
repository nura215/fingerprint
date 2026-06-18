<?php

use App\Http\Controllers\Api\DeviceApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('device.token')->group(function () {
    Route::post('/device/status', [DeviceApiController::class, 'status']);
    Route::post('/device/scan', [DeviceApiController::class, 'scan']);
    Route::post('/device/door-log', [DeviceApiController::class, 'doorLog']);
});






