<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\TechnicianMissionApiController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionTimeTrackingController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'STEG mobile API is running.',
        ]);
    });

    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout']);
        Route::get('/me', [MobileAuthController::class, 'me']);

        Route::get('/missions/active', [TechnicianMissionApiController::class, 'active']);
        Route::get('/missions/completed', [TechnicianMissionApiController::class, 'completed']);
        Route::get('/missions/{mission}', [TechnicianMissionApiController::class, 'show']);
        Route::get('/notifications', [TechnicianMissionApiController::class, 'notifications']);

        Route::post('/missions/{mission}/time-log', [MissionTimeTrackingController::class, 'log']);
        Route::post('/missions/{mission}/verify-qr', [MissionController::class, 'verifyQr']);
    });
});
