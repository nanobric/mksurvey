<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $fillable = [
        'client_id',
        'method',
        'endpoint',
        'ip_address',
        'user_agent',
        'request_headers',
        'request_body',
        'response_status',
        'response_body',
        'duration_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_body' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Crear log desde request.
     */
    public static function logRequest($request, $response = null, $startTime = null): self
    {
        $duration = $startTime ? (microtime(true) - $startTime) * 1000 : null;
        
        return static::create([
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_headers' => collect($request->headers->all())->except(['authorization'])->toArray(),
            'request_body' => $request->except(['password', 'api_token', 'authorization_pin']),
            'response_status' => $response?->getStatusCode(),
            'response_body' => $response ? json_decode($response->getContent(), true) : null,
            'duration_ms' => $duration,
            'status' => $response && $response->getStatusCode() < 400 ? 'success' : 'error',
        ]);
    }
}
