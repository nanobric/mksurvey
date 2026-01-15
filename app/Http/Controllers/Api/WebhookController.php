<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * POST /api/v1/webhooks/twilio/status
     * 
     * Endpoint fire-and-forget para recibir status updates de Twilio.
     * IMPORTANTE: Responder 200 inmediatamente, procesar en background.
     */
    public function twilioStatus(Request $request): Response
    {
        // Log para debugging
        Log::debug('TwilioWebhook received', $request->all());

        // Despachar job y responder inmediatamente
        ProcessWebhookUpdate::dispatch($request->all());

        // Twilio espera 200 OK rápido
        return response('', 200);
    }

    /**
     * POST /api/v1/webhooks/twilio/inbound
     * 
     * Endpoint para recibir mensajes entrantes (respuestas de usuarios).
     */
    public function twilioInbound(Request $request): Response
    {
        Log::info('TwilioInbound received', [
            'from' => $request->input('From'),
            'body' => $request->input('Body'),
        ]);

        // TODO: Procesar mensajes entrantes si es necesario
        // Por ejemplo: respuestas a encuestas, opt-outs, etc.

        return response('', 200);
    }
}
