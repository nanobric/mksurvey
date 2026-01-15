<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Circuit Breaker Pattern para APIs externas.
 * 
 * Protege contra cascadas de errores cuando una API externa falla.
 * Estados: CLOSED (normal), OPEN (bloqueado), HALF_OPEN (probando)
 */
class CircuitBreaker
{
    protected string $serviceName;
    protected int $failureThreshold;
    protected int $recoveryTime;
    protected int $sampleWindow;

    public function __construct(
        string $serviceName,
        int $failureThreshold = 10,  // Errores antes de abrir
        int $recoveryTime = 60,      // Segundos antes de probar
        int $sampleWindow = 60       // Ventana de tiempo para contar errores
    ) {
        $this->serviceName = $serviceName;
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTime = $recoveryTime;
        $this->sampleWindow = $sampleWindow;
    }

    /**
     * Verificar si se puede ejecutar la operación.
     */
    public function isAvailable(): bool
    {
        $state = $this->getState();

        if ($state === 'CLOSED') {
            return true;
        }

        if ($state === 'OPEN') {
            // Verificar si ya pasó el tiempo de recuperación
            $openedAt = Redis::get($this->key('opened_at'));
            if ($openedAt && (time() - $openedAt) >= $this->recoveryTime) {
                $this->setState('HALF_OPEN');
                return true; // Permitir una solicitud de prueba
            }
            return false;
        }

        // HALF_OPEN: permitir solicitud de prueba
        return true;
    }

    /**
     * Registrar un éxito.
     */
    public function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === 'HALF_OPEN') {
            // Éxito en half-open, cerrar circuito
            $this->reset();
            Log::info("CircuitBreaker [{$this->serviceName}]: Cerrado después de recovery exitoso");
        }

        // Decrementar contador de errores
        Redis::decr($this->key('failures'));
        if ((int) Redis::get($this->key('failures')) < 0) {
            Redis::set($this->key('failures'), 0);
        }
    }

    /**
     * Registrar un fallo.
     */
    public function recordFailure(): void
    {
        $state = $this->getState();

        if ($state === 'HALF_OPEN') {
            // Fallo en half-open, volver a abrir
            $this->trip();
            return;
        }

        // Incrementar contador de fallos
        $failures = Redis::incr($this->key('failures'));
        Redis::expire($this->key('failures'), $this->sampleWindow);

        if ($failures >= $this->failureThreshold) {
            $this->trip();
        }
    }

    /**
     * Abrir el circuito (bloquear).
     */
    protected function trip(): void
    {
        $this->setState('OPEN');
        Redis::set($this->key('opened_at'), time());
        Redis::expire($this->key('opened_at'), $this->recoveryTime + 60);
        
        Log::warning("CircuitBreaker [{$this->serviceName}]: ABIERTO - Demasiados errores");
    }

    /**
     * Resetear el circuito.
     */
    public function reset(): void
    {
        Redis::del([
            $this->key('state'),
            $this->key('failures'),
            $this->key('opened_at'),
        ]);
    }

    protected function getState(): string
    {
        return Redis::get($this->key('state')) ?? 'CLOSED';
    }

    protected function setState(string $state): void
    {
        Redis::set($this->key('state'), $state);
        Redis::expire($this->key('state'), $this->recoveryTime + 120);
    }

    protected function key(string $suffix): string
    {
        return "circuit_breaker:{$this->serviceName}:{$suffix}";
    }

    /**
     * Obtener métricas actuales.
     */
    public function getMetrics(): array
    {
        return [
            'service' => $this->serviceName,
            'state' => $this->getState(),
            'failures' => (int) Redis::get($this->key('failures')),
            'threshold' => $this->failureThreshold,
        ];
    }
}
