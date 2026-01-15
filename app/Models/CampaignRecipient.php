<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id',
        'to',
        'tracking_id',
        'params',
        'status',
        'provider_sid',
        'provider_status',
        'error_message',
        'error_code',
        'queued_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'params' => 'array',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function markAsSent(string $providerSid, string $providerStatus = 'queued'): void
    {
        $this->update([
            'status' => 'sent',
            'provider_sid' => $providerSid,
            'provider_status' => $providerStatus,
            'sent_at' => now(),
        ]);
        
        $this->campaign->incrementSentCount();
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'provider_status' => 'delivered',
            'delivered_at' => now(),
        ]);
        
        $this->campaign->incrementDeliveredCount();
    }

    public function markAsFailed(string $errorMessage, string $errorCode = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'failed_at' => now(),
        ]);
        
        $this->campaign->incrementFailedCount();
    }

    public function updateFromWebhook(string $status, array $data = []): void
    {
        $updateData = ['provider_status' => $status];

        switch ($status) {
            case 'delivered':
                $updateData['status'] = 'delivered';
                $updateData['delivered_at'] = now();
                $this->campaign->incrementDeliveredCount();
                break;
            case 'read':
                $updateData['status'] = 'read';
                $updateData['read_at'] = now();
                break;
            case 'failed':
            case 'undelivered':
                $updateData['status'] = 'failed';
                $updateData['error_message'] = $data['error_message'] ?? null;
                $updateData['error_code'] = $data['error_code'] ?? null;
                $updateData['failed_at'] = now();
                break;
        }

        $this->update($updateData);
    }
}
