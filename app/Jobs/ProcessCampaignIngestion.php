<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

/**
 * Job de ingesta que usa JsonMachine para streaming de JSON masivos.
 * 
 * Procesa archivos JSON de hasta 600k recipients sin cargar todo en memoria.
 * Usa streaming + bulk inserts en chunks de 1000.
 */
final class ProcessCampaignIngestion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $timeout = 0; // Sin límite para ingestas largas
    public int $maxExceptions = 2;
    public array $backoff = [30, 60, 120];

    public function __construct(
        private readonly int $campaignId,
        private readonly string $filePath,
    ) {}

    public function handle(): void
    {
        $campaign = Campaign::find($this->campaignId);

        if (!$campaign) {
            Log::warning("ProcessCampaignIngestion: Campaign {$this->campaignId} not found");
            $this->cleanup();
            return;
        }

        if ($campaign->status === 'cancelled') {
            Log::info("ProcessCampaignIngestion: Campaign {$this->campaignId} was cancelled");
            $this->cleanup();
            return;
        }

        Log::info("ProcessCampaignIngestion: Starting", [
            'campaign_id' => $this->campaignId,
            'file' => $this->filePath,
        ]);

        try {
            // Stream y procesar recipients
            $result = $this->streamInsertRecipients($campaign);

            // Actualizar campaign con total real
            $campaign->update([
                'status' => 'received',
                'total_recipients' => $result['inserted'],
                'temp_file_path' => null, // Limpiar referencia
            ]);

            // Cleanup temp file
            $this->cleanup();

            Log::info("ProcessCampaignIngestion: Complete", [
                'campaign_id' => $this->campaignId,
                'inserted' => $result['inserted'],
                'skipped' => $result['skipped'],
            ]);

            // Dispatch job de procesamiento si hay recipients
            if ($result['inserted'] > 0) {
                ProcessCampaignBatch::dispatch($this->campaignId);
            } else {
                $campaign->update(['status' => 'completed']);
            }

        } catch (\Throwable $e) {
            Log::error("ProcessCampaignIngestion: Failed", [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $campaign->update(['status' => 'ingestion_failed']);
            
            // Re-throw para retry
            throw $e;
        }
    }

    /**
     * Stream JSON con JsonMachine e insertar en chunks.
     */
    private function streamInsertRecipients(Campaign $campaign): array
    {
        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("File not found: {$this->filePath}");
        }

        // JsonMachine stream - NO carga todo en memoria
        $recipients = Items::fromFile(
            $this->filePath,
            [
                'pointer' => '/recipients',
                'decoder' => new ExtJsonDecoder(assoc: true),
            ]
        );

        $batch = [];
        $batchSize = 1000;
        $totalInserted = 0;
        $totalSkipped = 0;
        $now = now();

        foreach ($recipients as $recipient) {
            // Validación inline (E.164)
            if (!isset($recipient['to']) || !preg_match('/^\+[1-9]\d{1,14}$/', $recipient['to'])) {
                $totalSkipped++;
                continue;
            }

            $batch[] = [
                'campaign_id' => $campaign->id,
                'to' => $recipient['to'],
                'tracking_id' => $recipient['tracking_id'] ?? null,
                'params' => isset($recipient['params']) ? json_encode($recipient['params']) : null,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                $this->insertBatch($batch);
                $totalInserted += count($batch);
                $batch = [];

                // Log cada 50k para monitoreo
                if ($totalInserted % 50000 === 0) {
                    Log::info("ProcessCampaignIngestion: Progress", [
                        'campaign_id' => $campaign->id,
                        'inserted' => $totalInserted,
                    ]);
                }
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            $this->insertBatch($batch);
            $totalInserted += count($batch);
        }

        return [
            'inserted' => $totalInserted,
            'skipped' => $totalSkipped,
        ];
    }

    /**
     * Insert batch en transacción.
     */
    private function insertBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            DB::table('campaign_recipients')->insert($batch);
        });
    }

    /**
     * Eliminar archivo temporal.
     */
    private function cleanup(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
            Log::debug("ProcessCampaignIngestion: Cleaned up {$this->filePath}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessCampaignIngestion FAILED", [
            'campaign_id' => $this->campaignId,
            'error' => $exception->getMessage(),
        ]);

        $this->cleanup();

        Campaign::where('id', $this->campaignId)
            ->update(['status' => 'ingestion_failed']);
    }
}
