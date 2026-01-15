<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job principal que procesa una campaña.
 * 
 * Optimizaciones:
 * - Usa Bus::batch() para tracking de progreso
 * - Solo pasa IDs, no modelos completos
 * - Cursor para memoria eficiente
 * - Generator para chunks enormes
 */
class ProcessCampaignBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 900; // 15 minutos
    public array $backoff = [30, 60, 120];

    protected int $campaignId;
    protected int $chunkSize;

    public function __construct(int $campaignId, int $chunkSize = 1000)
    {
        $this->campaignId = $campaignId;
        $this->chunkSize = $chunkSize;
    }

    public function handle(): void
    {
        $campaign = Campaign::find($this->campaignId);
        
        if (!$campaign) {
            Log::warning("ProcessCampaignBatch: Campaign {$this->campaignId} not found");
            return;
        }

        if ($campaign->status === 'cancelled') {
            Log::info("ProcessCampaignBatch: Campaign {$this->campaignId} was cancelled");
            return;
        }

        Log::info("ProcessCampaignBatch: Starting campaign {$campaign->id} - {$campaign->name}");

        // Marcar como procesando
        $campaign->markAsProcessing();

        // Obtener template content si existe
        $templateContent = null;
        if ($campaign->template_id) {
            $template = \App\Models\Template::find($campaign->template_id);
            $templateContent = $template?->content;
        }

        try {
            // Crear jobs para cada chunk usando generator
            $jobs = $this->createChunkJobs($campaign, $templateContent);

            // Dispatch como batch para tracking
            $batch = Bus::batch($jobs)
                ->name("Campaign {$campaign->id}: {$campaign->name}")
                ->allowFailures()
                ->finally(function (Batch $batch) use ($campaign) {
                    $this->onBatchComplete($batch, $campaign);
                })
                ->dispatch();

            // Guardar batch ID en campaign para tracking
            $campaign->update(['external_id' => $batch->id]);

            Log::info("ProcessCampaignBatch: Batch {$batch->id} created with {$batch->totalJobs} jobs");

        } catch (\Exception $e) {
            Log::error("ProcessCampaignBatch: Error creating batch", [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            $this->release($this->backoff[$this->attempts() - 1] ?? 120);
        }
    }

    /**
     * Generator que crea jobs por chunks sin cargar todo en memoria.
     */
    protected function createChunkJobs(Campaign $campaign, ?string $templateContent): \Generator
    {
        $currentChunk = [];
        $chunkCount = 0;

        // Usar cursor para memoria eficiente
        $query = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->select('id')
            ->orderBy('id');

        foreach ($query->cursor() as $recipient) {
            $currentChunk[] = $recipient->id;

            if (count($currentChunk) >= $this->chunkSize) {
                $chunkCount++;
                yield new SendBatchWorker(
                    $campaign->id,
                    $currentChunk,
                    $campaign->channel,
                    $campaign->route_tier,
                    $templateContent
                );
                
                $currentChunk = [];
            }
        }

        // Último chunk parcial
        if (!empty($currentChunk)) {
            $chunkCount++;
            yield new SendBatchWorker(
                $campaign->id,
                $currentChunk,
                $campaign->channel,
                $campaign->route_tier,
                $templateContent
            );
        }

        Log::info("ProcessCampaignBatch: Created {$chunkCount} chunk jobs for campaign {$campaign->id}");
    }

    /**
     * Callback cuando el batch termina (éxito o fallo).
     */
    protected function onBatchComplete(Batch $batch, Campaign $campaign): void
    {
        $campaign->refresh();

        if ($batch->failedJobs > 0 && $batch->failedJobs === $batch->totalJobs) {
            // Todos los jobs fallaron
            $campaign->markAsFailed("All batch jobs failed");
        } else {
            $campaign->markAsCompleted();
        }

        Log::info("ProcessCampaignBatch: Batch complete", [
            'campaign_id' => $campaign->id,
            'total_jobs' => $batch->totalJobs,
            'failed_jobs' => $batch->failedJobs,
            'status' => $campaign->status,
        ]);

        // TODO: Enviar webhook callback si está configurado
        if ($campaign->callback_url) {
            dispatch(new SendCampaignCallback($campaign));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessCampaignBatch FAILED", [
            'campaign_id' => $this->campaignId,
            'error' => $exception->getMessage(),
        ]);

        $campaign = Campaign::find($this->campaignId);
        $campaign?->markAsFailed("Main job failed: " . $exception->getMessage());
    }
}
