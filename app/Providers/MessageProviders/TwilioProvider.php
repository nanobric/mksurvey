<?php

namespace App\Providers\MessageProviders;

use App\Contracts\MessageProviderInterface;
use App\Services\TwilioService;

/**
 * Provider de Twilio para SMS y WhatsApp.
 */
class TwilioProvider implements MessageProviderInterface
{
    protected TwilioService $twilioService;
    protected string $channel;

    public function __construct(string $channel = 'sms')
    {
        $this->twilioService = app(TwilioService::class);
        $this->channel = $channel;
    }

    public function send(string $to, string $content, array $options = []): array
    {
        if ($this->channel === 'whatsapp') {
            return $this->twilioService->sendWhatsApp($to, $content);
        }
        
        return $this->twilioService->sendSms($to, $content);
    }

    public function getName(): string
    {
        return 'twilio';
    }

    public function supportsChannel(string $channel): bool
    {
        return in_array($channel, $this->getSupportedChannels());
    }

    public function getSupportedChannels(): array
    {
        return ['sms', 'whatsapp'];
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }
}
