<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'request_id',
        'external_id',
        'name',
        'template_id',
        'channel',
        'route_tier',
        'priority',
        'validity_seconds',
        'status',
        'scheduled_at',
        'deadline_at',
        'timezone',
        'on_timeout_policy',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'content_vars',
        'tags',
        'callback_url',
        'authorization_pin',
        'temp_file_path',
        'client_id',
    ];

    protected $casts = [
        'content_vars' => 'array',
        'tags' => 'array',
        'scheduled_at' => 'datetime',
        'deadline_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function pendingRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class)->where('status', 'pending');
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailedCount(): void
    {
        $this->increment('failed_count');
    }

    public function incrementDeliveredCount(): void
    {
        $this->increment('delivered_count');
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
