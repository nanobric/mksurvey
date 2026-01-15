<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/notify', [App\Http\Controllers\Api\NotificationController::class, 'send']);
});
