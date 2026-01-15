<?php

namespace App\Jobs;

use App\Models\CampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para procesar actualizaciones de webhooks de Twilio.
 * Cola: high priority para procesamiento rápido.
 */
class ProcessWebhookUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    protected array $webhookData;

    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
        $this->onQueue('high'); // Cola de alta prioridad
    }

    public function handle(): void
    {
        $sid = $this->webhookData['MessageSid'] ?? $this->webhookData['SmsSid'] ?? null;
        $status = $this->webhookData['MessageStatus'] ?? $this->webhookData['SmsStatus'] ?? null;

        if (!$sid || !$status) {
            Log::warning('ProcessWebhookUpdate: Missing SID or Status', $this->webhookData);
            return;
        }

        // Buscar recipient por provider_sid
        $recipient = CampaignRecipient::where('provider_sid', $sid)->first();

        if (!$recipient) {
            Log::debug("ProcessWebhookUpdate: No recipient found for SID {$sid}");
            return;
        }

        // Mapear status de Twilio a nuestro status
        $mappedStatus = $this->mapTwilioStatus($status);

        // Actualizar recipient
        $recipient->updateFromWebhook($mappedStatus, [
            'error_code' => $this->webhookData['ErrorCode'] ?? null,
            'error_message' => $this->webhookData['ErrorMessage'] ?? null,
        ]);

        Log::debug("ProcessWebhookUpdate: Updated recipient {$recipient->id} to status {$mappedStatus}");
    }

    protected function mapTwilioStatus(string $twilioStatus): string
    {
        return match (strtolower($twilioStatus)) {
            'queued', 'sending' => 'queued',
            'sent' => 'sent',
            'delivered' => 'delivered',
            'read' => 'read',
            'failed', 'undelivered' => 'failed',
            default => 'sent',
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessWebhookUpdate FAILED: {$exception->getMessage()}", [
            'webhook_data' => $this->webhookData,
        ]);
    }
}
