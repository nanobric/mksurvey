<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'channel',
        'content',
        'components',
        'variables',
        'media_url',
        'media_type',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
        'components' => 'array',
    ];

    /**
     * Detectar variables en el contenido.
     */
    public function detectVariables(): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->content, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Renderizar contenido con variables.
     */
    public function render(array $data): string
    {
        $content = $this->content;
        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        return $content;
    }

    /**
     * Scope para templates activos.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope por canal.
     */
    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
}

