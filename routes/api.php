<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\WebhookController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/notify', [App\Http\Controllers\Api\NotificationController::class, 'send']);
});

/*
|--------------------------------------------------------------------------
| API v1 Routes - Campaigns
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Campaign endpoints
    Route::post('campaigns/send', [CampaignController::class, 'send']);
    Route::get('campaigns/{campaign}/status', [CampaignController::class, 'status']);
    
    // Webhooks (sin autenticación - Twilio los llama directamente)
    Route::prefix('webhooks/twilio')->group(function () {
        Route::post('status', [WebhookController::class, 'twilioStatus']);
        Route::post('inbound', [WebhookController::class, 'twilioInbound']);
    });
});
