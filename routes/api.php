<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\ApiAuthMiddleware::class])->group(function () {
    Route::get('/servers', [\App\Http\Controllers\Api\ServerApiController::class, 'index']);
    Route::get('/servers/{server}', [\App\Http\Controllers\Api\ServerApiController::class, 'show']);
    Route::post('/servers/{server}/start', [\App\Http\Controllers\Api\ServerApiController::class, 'start']);
    Route::post('/servers/{server}/stop', [\App\Http\Controllers\Api\ServerApiController::class, 'stop']);
    Route::post('/servers/{server}/restart', [\App\Http\Controllers\Api\ServerApiController::class, 'restart']);
    Route::post('/servers/{server}/rcon', [\App\Http\Controllers\Api\ServerApiController::class, 'rcon']);
});
