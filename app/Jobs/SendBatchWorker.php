<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Providers\MessageProviders\ProviderFactory;
use App\Services\CircuitBreaker;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Sub-job optimizado que procesa un chunk de recipients.
 * 
 * Optimizaciones:
 * - Circuit Breaker para APIs externas
 * - Retry inteligente (release vs fail)
 * - Bulk updates a BD
 * - Memory-efficient processing
 */
class SendBatchWorker implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 300;
    public array $backoff = [10, 30, 60, 120, 300]; // Backoff exponencial

    protected int $campaignId;
    protected array $recipientIds;
    protected ?string $templateContent;
    protected string $channel;
    protected string $routeTier;

    // Batch updates para eficiencia
    protected array $pendingUpdates = [];
    protected int $batchUpdateSize = 100;

    public function __construct(
        int $campaignId,
        array $recipientIds,
        string $channel,
        string $routeTier,
        ?string $templateContent = null
    ) {
        // Solo IDs, no el modelo completo (evita serialización pesada)
        $this->campaignId = $campaignId;
        $this->recipientIds = $recipientIds;
        $this->channel = $channel;
        $this->routeTier = $routeTier;
        $this->templateContent = $templateContent;
    }

    public function handle(): void
    {
        // Si el batch fue cancelado, salir
        if ($this->batch()?->cancelled()) {
            return;
        }

        $circuitBreaker = new CircuitBreaker("provider:{$this->channel}:{$this->routeTier}");

        // Circuit Breaker check
        if (!$circuitBreaker->isAvailable()) {
            Log::warning("SendBatchWorker: Circuit breaker ABIERTO, re-encolando", [
                'campaign_id' => $this->campaignId,
            ]);
            // Re-encolar con delay
            $this->release(30);
            return;
        }

        try {
            $provider = ProviderFactory::make($this->channel, $this->routeTier);
            $campaign = Campaign::find($this->campaignId);

            if (!$campaign || $campaign->status === 'cancelled') {
                return;
            }

            // Cargar variables globales una sola vez
            $globalVars = $campaign->content_vars['global'] ?? [];

            // Procesar recipients usando cursor para memoria eficiente
            CampaignRecipient::whereIn('id', $this->recipientIds)
                ->where('status', 'pending')
                ->cursor()
                ->each(function ($recipient) use ($provider, $circuitBreaker, $globalVars) {
                    $this->processRecipient($recipient, $provider, $circuitBreaker, $globalVars);
                });

            // Flush pending updates
            $this->flushPendingUpdates();

            // Registrar éxito en circuit breaker
            $circuitBreaker->recordSuccess();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Error de conexión: reintentar
            Log::warning("SendBatchWorker: Error de conexión, reintentando", [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);
            $circuitBreaker->recordFailure();
            $this->release($this->backoff[$this->attempts() - 1] ?? 300);

        } catch (\Exception $e) {
            // Error de lógica: fallar fatalmente
            Log::error("SendBatchWorker: Error fatal de lógica", [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    protected function processRecipient(
        CampaignRecipient $recipient,
        $provider,
        CircuitBreaker $circuitBreaker,
        array $globalVars
    ): void {
        // Rate limiting: 50 MPS
        $this->waitForRateLimit();

        try {
            $content = $this->prepareContent($recipient, $globalVars);

            $result = $provider->send($recipient->to, $content, [
                'campaign_id' => $this->campaignId,
                'tracking_id' => $recipient->tracking_id,
            ]);

            if ($result['success']) {
                $this->queueUpdate($recipient->id, [
                    'status' => 'sent',
                    'provider_sid' => $result['sid'] ?? '',
                    'provider_status' => $result['status'] ?? 'queued',
                    'sent_at' => now(),
                ]);
            } else {
                $this->queueUpdate($recipient->id, [
                    'status' => 'failed',
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);
                $circuitBreaker->recordFailure();
            }

        } catch (\Exception $e) {
            $this->queueUpdate($recipient->id, [
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);
            $circuitBreaker->recordFailure();
        }
    }

    protected function waitForRateLimit(): void
    {
        $key = "send-rate:{$this->campaignId}";
        
        while (!RateLimiter::attempt($key, 50, fn() => true, 1)) {
            usleep(20000); // 20ms
        }
    }

    protected function prepareContent(CampaignRecipient $recipient, array $globalVars): string
    {
        $content = $this->templateContent ?? '';

        // Variables globales
        foreach ($globalVars as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        // Variables del recipient
        foreach ($recipient->params ?? [] as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        return $content;
    }

    /**
     * Encolar update para batch processing.
     */
    protected function queueUpdate(int $recipientId, array $data): void
    {
        $this->pendingUpdates[$recipientId] = $data;

        if (count($this->pendingUpdates) >= $this->batchUpdateSize) {
            $this->flushPendingUpdates();
        }
    }

    /**
     * Ejecutar updates pendientes en batch.
     */
    protected function flushPendingUpdates(): void
    {
        if (empty($this->pendingUpdates)) {
            return;
        }

        // Agrupar por status para updates masivos
        $byStatus = [];
        foreach ($this->pendingUpdates as $id => $data) {
            $status = $data['status'];
            if (!isset($byStatus[$status])) {
                $byStatus[$status] = ['ids' => [], 'data' => $data];
            }
            $byStatus[$status]['ids'][] = $id;
        }

        // Ejecutar updates en transacción
        DB::transaction(function () use ($byStatus) {
            foreach ($byStatus as $status => $group) {
                CampaignRecipient::whereIn('id', $group['ids'])
                    ->update([
                        'status' => $status,
                        'sent_at' => $group['data']['sent_at'] ?? null,
                        'provider_sid' => $group['data']['provider_sid'] ?? null,
                        'provider_status' => $group['data']['provider_status'] ?? null,
                        'error_message' => $group['data']['error_message'] ?? null,
                    ]);

                // Actualizar contadores de campaña
                $count = count($group['ids']);
                if ($status === 'sent') {
                    Campaign::where('id', $this->campaignId)->increment('sent_count', $count);
                } elseif ($status === 'failed') {
                    Campaign::where('id', $this->campaignId)->increment('failed_count', $count);
                }
            }
        });

        Log::debug("SendBatchWorker: Flush " . count($this->pendingUpdates) . " updates");
        $this->pendingUpdates = [];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendBatchWorker FAILED", [
            'campaign_id' => $this->campaignId,
            'recipients' => count($this->recipientIds),
            'error' => $exception->getMessage(),
        ]);

        // Marcar recipients como fallidos
        CampaignRecipient::whereIn('id', $this->recipientIds)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'error_message' => 'Job failed: ' . substr($exception->getMessage(), 0, 200),
            ]);
    }
}
