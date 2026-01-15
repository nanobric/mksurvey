<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Verificar campañas programadas y pausadas cada minuto
Schedule::command('campaign:check-pauses')->everyMinute();

// Resetear contadores de uso mensualmente (primer día del mes a las 00:00)
Schedule::command('subscriptions:reset-usage')->monthlyOn(1, '00:00');

