<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'rfc',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'api_token',
        'api_token_expires_at',
        'status',
        'expected_monthly_volume',
        'volume_tier',
        'trial_ends_at',
        'industry',
        'website',
        'notes',
    ];

    protected $casts = [
        'api_token_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    protected $hidden = [
        'api_token',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    public function users(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Generar nuevo API token.
     */
    public function generateApiToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'api_token' => hash('sha256', $token),
            'api_token_expires_at' => now()->addYear(),
        ]);
        return $token; // Devolver token sin hash (solo visible una vez)
    }

    /**
     * Verificar si el cliente está activo.
     */
    public function isActive(): bool
    {
        if ($this->status === 'active') {
            return true;
        }

        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Obtener plan actual.
     */
    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }

    /**
     * Verificar límites de uso.
     */
    public function canSend(string $channel, int $count = 1): bool
    {
        $subscription = $this->activeSubscription;
        if (!$subscription) {
            return false;
        }

        return $subscription->canUse($channel, $count);
    }
}
