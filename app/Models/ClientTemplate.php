<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientTemplate extends Model
{
    protected $fillable = [
        'client_id',
        'master_id',
        'name',
        'customizations',
        'media_url',
        'status',
    ];

    protected $casts = [
        'customizations' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(TemplateMaster::class, 'master_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'client_template_id');
    }

    /**
     * Obtener el contenido renderizado con las customizaciones.
     */
    public function getRenderedContentAttribute(): string
    {
        return $this->master->render($this->customizations ?? []);
    }

    /**
     * Obtener el canal del master.
     */
    public function getChannelAttribute(): string
    {
        return $this->master->channel;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
