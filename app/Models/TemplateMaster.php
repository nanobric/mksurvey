<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateMaster extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'channel',
        'content',
        'structure',
        'editable_fields',
        'variables',
        'preview_image',
        'thumbnail',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'structure' => 'array',
        'editable_fields' => 'array',
        'variables' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function clientTemplates(): HasMany
    {
        return $this->hasMany(ClientTemplate::class, 'master_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Renderizar contenido con las customizaciones del cliente.
     */
    public function render(array $customizations): string
    {
        $content = $this->content;
        
        foreach ($customizations as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        
        return $content;
    }

    /**
     * Obtener icono por categoría.
     */
    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'welcome' => '👋',
            'promo' => '🎉',
            'reminder' => '📅',
            'survey' => '⭐',
            'otp' => '🔐',
            'transactional' => '📦',
            'newsletter' => '📰',
            default => '📝',
        };
    }

    /**
     * Obtener icono por canal.
     */
    public function getChannelIconAttribute(): string
    {
        return match($this->channel) {
            'sms' => '📱',
            'whatsapp' => '💬',
            'email' => '📧',
            default => '📨',
        };
    }
}
