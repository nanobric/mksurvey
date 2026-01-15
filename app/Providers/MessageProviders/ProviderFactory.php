<?php

namespace App\Providers\MessageProviders;

use App\Contracts\MessageProviderInterface;
use InvalidArgumentException;

/**
 * Factory para crear instancias de providers según el canal y tier.
 */
class ProviderFactory
{
    /**
     * Mapa de providers disponibles por route_tier.
     * Expandible para agregar más providers en el futuro.
     */
    protected static array $providers = [
        'mock' => [
            'sms' => MockProvider::class,
            'whatsapp' => MockProvider::class,
            'email' => MockProvider::class,
        ],
        'sandbox' => [
            'sms' => TwilioProvider::class,
            'whatsapp' => TwilioProvider::class,
        ],
        'official' => [
            'sms' => TwilioProvider::class,
            'whatsapp' => TwilioProvider::class,
            // Futuro: 'whatsapp' => WhatsAppBusinessProvider::class,
        ],
        'secondary' => [
            'sms' => TwilioProvider::class,
            // Futuro: 'sms' => SmsSecondaryProvider::class,
        ],
    ];

    /**
     * Crear instancia del provider apropiado.
     *
     * @param string $channel Canal de envío (sms, whatsapp)
     * @param string $routeTier Tier de ruteo (sandbox, official, secondary)
     * @return MessageProviderInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $channel, string $routeTier = 'sandbox'): MessageProviderInterface
    {
        $tier = self::$providers[$routeTier] ?? self::$providers['sandbox'];
        
        if (!isset($tier[$channel])) {
            throw new InvalidArgumentException(
                "No provider configured for channel '{$channel}' with tier '{$routeTier}'"
            );
        }

        $providerClass = $tier[$channel];
        $provider = new $providerClass($channel);

        if (!$provider instanceof MessageProviderInterface) {
            throw new InvalidArgumentException(
                "Provider class must implement MessageProviderInterface"
            );
        }

        return $provider;
    }

    /**
     * Registrar un nuevo provider para un canal y tier específico.
     */
    public static function register(string $routeTier, string $channel, string $providerClass): void
    {
        if (!isset(self::$providers[$routeTier])) {
            self::$providers[$routeTier] = [];
        }
        
        self::$providers[$routeTier][$channel] = $providerClass;
    }

    /**
     * Obtener todos los tiers disponibles.
     */
    public static function getAvailableTiers(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Obtener canales disponibles para un tier.
     */
    public static function getChannelsForTier(string $routeTier): array
    {
        return array_keys(self::$providers[$routeTier] ?? []);
    }
}
