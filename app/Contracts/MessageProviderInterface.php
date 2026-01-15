<?php

namespace App\Contracts;

/**
 * Interface para proveedores de mensajería.
 * Permite implementar Strategy Pattern para múltiples providers.
 */
interface MessageProviderInterface
{
    /**
     * Enviar un mensaje a un destinatario.
     *
     * @param string $to Número de teléfono en formato E.164
     * @param string $content Contenido del mensaje
     * @param array $options Opciones adicionales (template vars, media, etc)
     * @return array ['success' => bool, 'sid' => string|null, 'status' => string, 'error' => string|null]
     */
    public function send(string $to, string $content, array $options = []): array;

    /**
     * Obtener el nombre del provider.
     */
    public function getName(): string;

    /**
     * Verificar si el provider soporta un canal específico.
     */
    public function supportsChannel(string $channel): bool;

    /**
     * Obtener los canales soportados por el provider.
     * @return array<string>
     */
    public function getSupportedChannels(): array;
}
