<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\ApiLogController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('templates', App\Http\Controllers\TemplateController::class);
    Route::resource('messages', App\Http\Controllers\MessageController::class);

    // Client Templates (personalización)
    Route::get('client-templates/gallery', [App\Http\Controllers\ClientTemplateController::class, 'gallery'])->name('client-templates.gallery');
    Route::get('client-templates/my-templates', [App\Http\Controllers\ClientTemplateController::class, 'myTemplates'])->name('client-templates.my-templates');
    Route::get('client-templates/{master}/customize', [App\Http\Controllers\ClientTemplateController::class, 'customize'])->name('client-templates.customize');
    Route::post('client-templates/preview/{master}', [App\Http\Controllers\ClientTemplateController::class, 'preview'])->name('client-templates.preview');
    Route::resource('client-templates', App\Http\Controllers\ClientTemplateController::class)->except(['index', 'create']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        // Usuarios Admin
        Route::resource('users', UserController::class);
        Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::post('users/{user}/avatar', [UserController::class, 'updateAvatar'])->name('users.avatar');
        Route::post('users/{user}/capture-avatar', [UserController::class, 'captureAvatar'])->name('users.capture-avatar');

        // Clientes CRM
        Route::resource('clients', ClientController::class);
        Route::post('clients/{client}/generate-token', [ClientController::class, 'generateToken'])->name('clients.generate-token');

        // Planes
        Route::resource('plans', PlanController::class)->except(['show']);

        // Campañas (Monitoreo)
        Route::get('campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/{campaign}', [AdminCampaignController::class, 'show'])->name('campaigns.show');
        Route::post('campaigns/{campaign}/pause', [AdminCampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{campaign}/resume', [AdminCampaignController::class, 'resume'])->name('campaigns.resume');
        Route::post('campaigns/{campaign}/cancel', [AdminCampaignController::class, 'cancel'])->name('campaigns.cancel');

        // API Logs (Monitor)
        Route::get('api-logs', [ApiLogController::class, 'index'])->name('api-logs.index');
        Route::get('api-logs/{apiLog}', [ApiLogController::class, 'show'])->name('api-logs.show');
        Route::post('api-logs/cleanup', [ApiLogController::class, 'cleanup'])->name('api-logs.cleanup');

        // Template Masters
        Route::resource('template-masters', \App\Http\Controllers\Admin\TemplateMasterController::class);
    });
});

