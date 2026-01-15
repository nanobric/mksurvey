<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCampaignIngestion;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Controller optimizado para 600k+ recipients.
 * 
 * Flujo:
 * 1. Validación mínima síncrona (solo headers)
 * 2. Guardar JSON raw a disco
 * 3. Crear campaign en status "ingesting"
 * 4. Dispatch job de ingesta
 * 5. Respuesta 202 inmediata (< 100ms)
 */
final class CampaignController extends Controller
{
    /**
     * POST /api/v1/campaigns/send
     * 
     * Recibe JSON masivo, lo guarda a disco y responde inmediatamente.
     */
    public function send(Request $request): JsonResponse
    {
        try {
            // 1. Validación MÍNIMA síncrona (NO validamos recipients aquí)
            $request->validate([
                'request_header' => 'required|array',
                'request_header.api_token' => 'required|string',
                'request_header.request_id' => 'required|string|max:100',
                'campaign' => 'required|array',
                'campaign.name' => 'required|string|max:255',
                'routing' => 'required|array',
                'routing.channel' => 'required|in:sms,whatsapp,email',
            ]);

            $requestId = $request->input('request_header.request_id');

            // 2. Idempotencia check
            $cacheKey = "campaign_request:{$requestId}";
            if (cache()->has($cacheKey)) {
                $existingCampaignId = cache()->get($cacheKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud duplicada',
                    'data' => ['existing_campaign_id' => $existingCampaignId],
                ], 409);
            }

            // 3. Guardar JSON raw a disco (sin parsear recipients)
            $campaignsDir = storage_path('app/campaigns');
            if (!is_dir($campaignsDir)) {
                mkdir($campaignsDir, 0755, true);
            }
            
            $tempPath = $campaignsDir . '/' . Str::uuid() . '.json';
            file_put_contents($tempPath, $request->getContent());

            // 4. Crear campaign en status "ingesting"
            $campaign = Campaign::create([
                'request_id' => $requestId,
                'external_id' => $request->input('campaign.external_id'),
                'name' => $request->input('campaign.name'),
                'template_id' => $request->input('campaign.template_id'),
                'channel' => $request->input('routing.channel'),
                'route_tier' => $request->input('routing.route_tier', 'sandbox'),
                'priority' => $request->input('routing.priority', 'normal'),
                'validity_seconds' => $request->input('routing.validity_seconds', 3600),
                'scheduled_at' => $request->input('scheduling.send_at'),
                'timezone' => $request->input('scheduling.timezone', 'America/Mexico_City'),
                'on_timeout_policy' => $request->input('scheduling.on_timeout_policy', 'resume'),
                'content_vars' => $request->input('content_vars'),
                'tags' => $request->input('campaign.tags'),
                'callback_url' => $request->input('reporting.callback_url'),
                'authorization_pin' => $request->input('security_billing.authorization_pin'),
                'temp_file_path' => $tempPath,
                'total_recipients' => 0, // Se actualiza después de ingesta
                'status' => 'ingesting',
            ]);

            // 5. Marcar como procesado para idempotencia (24h)
            cache()->put($cacheKey, $campaign->id, now()->addHours(24));

            // 6. Dispatch job de ingesta (async)
            ProcessCampaignIngestion::dispatch($campaign->id, $tempPath);

            Log::info("CampaignController: Campaign {$campaign->id} queued for ingestion", [
                'request_id' => $requestId,
                'file' => $tempPath,
            ]);

            // 7. Respuesta inmediata 202 Accepted
            return response()->json([
                'success' => true,
                'message' => 'Campaña recibida, procesando recipients en background',
                'data' => [
                    'campaign_id' => $campaign->id,
                    'request_id' => $requestId,
                    'status' => 'ingesting',
                    'status_url' => url("/api/v1/campaigns/{$campaign->id}/status"),
                ],
            ], 202);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error("CampaignController: Error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la campaña',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * GET /api/v1/campaigns/{campaign}/status
     */
    public function status(Campaign $campaign): JsonResponse
    {
        $progressPercentage = 0;
        if ($campaign->total_recipients > 0) {
            $processed = $campaign->sent_count + $campaign->failed_count;
            $progressPercentage = round(($processed / $campaign->total_recipients) * 100, 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'campaign_id' => $campaign->id,
                'request_id' => $campaign->request_id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'total_recipients' => $campaign->total_recipients,
                'sent_count' => $campaign->sent_count,
                'delivered_count' => $campaign->delivered_count,
                'failed_count' => $campaign->failed_count,
                'progress_percentage' => $progressPercentage,
                'started_at' => $campaign->started_at?->toIso8601String(),
                'completed_at' => $campaign->completed_at?->toIso8601String(),
            ],
        ]);
    }
}
