<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Job para enviar callback al finalizar una campaña.
 */
class SendCampaignCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public array $backoff = [60, 300, 900];

    protected int $campaignId;

    public function __construct(Campaign $campaign)
    {
        $this->campaignId = $campaign->id;
    }

    public function handle(): void
    {
        $campaign = Campaign::find($this->campaignId);

        if (!$campaign || !$campaign->callback_url) {
            return;
        }

        try {
            $payload = [
                'event' => 'campaign.completed',
                'campaign_id' => $campaign->id,
                'request_id' => $campaign->request_id,
                'external_id' => $campaign->external_id,
                'status' => $campaign->status,
                'stats' => [
                    'total' => $campaign->total_recipients,
                    'sent' => $campaign->sent_count,
                    'delivered' => $campaign->delivered_count,
                    'failed' => $campaign->failed_count,
                ],
                'completed_at' => $campaign->completed_at?->toIso8601String(),
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Secret' => config('app.webhook_secret'),
                ])
                ->post($campaign->callback_url, $payload);

            if ($response->failed()) {
                throw new \Exception("Callback failed with status {$response->status()}");
            }

            Log::info("SendCampaignCallback: Success", [
                'campaign_id' => $campaign->id,
                'url' => $campaign->callback_url,
            ]);

        } catch (\Exception $e) {
            Log::warning("SendCampaignCallback: Failed", [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            $this->release($this->backoff[$this->attempts() - 1] ?? 900);
        }
    }
}
