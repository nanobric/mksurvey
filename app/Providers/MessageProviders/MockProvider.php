<?php

namespace App\Providers\MessageProviders;

use App\Contracts\MessageProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Provider de prueba que simula envíos sin conectar a APIs reales.
 * Útil para testing y desarrollo.
 */
class MockProvider implements MessageProviderInterface
{
    private float $successRate;
    private int $delayMs;

    public function __construct(float $successRate = 0.95, int $delayMs = 50)
    {
        $this->successRate = $successRate;
        $this->delayMs = $delayMs;
    }

    public function send(string $to, string $content, array $options = []): array
    {
        // Simular delay de red
        usleep($this->delayMs * 1000);

        // Simular tasa de éxito/fallo
        $success = (mt_rand(1, 100) / 100) <= $this->successRate;

        $result = [
            'success' => $success,
            'sid' => 'MOCK_' . uniqid(),
            'status' => $success ? 'queued' : 'failed',
            'to' => $to,
            'content_length' => strlen($content),
        ];

        if (!$success) {
            $result['error'] = 'Simulated failure for testing';
            $result['error_code'] = 'MOCK_ERROR';
        }

        Log::debug("MockProvider: {$to} -> " . ($success ? 'OK' : 'FAIL'), $result);

        return $result;
    }

    public function getName(): string
    {
        return 'mock';
    }

    public function supportsChannel(string $channel): bool
    {
        return true; // Soporta todos los canales para testing
    }
}
