<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'client_id',
        'plan_id',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'status',
        'sms_used',
        'whatsapp_used',
        'email_used',
        'campaigns_used',
        'usage_resets_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'usage_resets_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Verificar si puede usar X cantidad de un canal.
     */
    public function canUse(string $channel, int $count = 1): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $usedField = "{$channel}_used";
        $limitField = "monthly_{$channel}_limit";

        $used = $this->$usedField ?? 0;
        $limit = $this->plan->$limitField ?? 0;

        // 0 = ilimitado
        if ($limit === 0) {
            return true;
        }

        return ($used + $count) <= $limit;
    }

    /**
     * Incrementar uso de un canal.
     */
    public function incrementUsage(string $channel, int $count = 1): void
    {
        $usedField = "{$channel}_used";
        $this->increment($usedField, $count);
    }

    /**
     * Resetear contadores de uso (llamado mensualmente).
     */
    public function resetUsage(): void
    {
        $this->update([
            'sms_used' => 0,
            'whatsapp_used' => 0,
            'email_used' => 0,
            'campaigns_used' => 0,
            'usage_resets_at' => now()->addMonth(),
        ]);
    }

    /**
     * Obtener porcentaje de uso de un canal.
     */
    public function usagePercentage(string $channel): float
    {
        $usedField = "{$channel}_used";
        $limitField = "monthly_{$channel}_limit";

        $used = $this->$usedField ?? 0;
        $limit = $this->plan->$limitField ?? 0;

        if ($limit === 0) {
            return 0; // Ilimitado
        }

        return round(($used / $limit) * 100, 2);
    }

    /**
     * Verificar si la suscripción está activa.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Cancelar suscripción.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
