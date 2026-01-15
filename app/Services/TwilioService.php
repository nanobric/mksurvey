<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected Client $client;
    protected string $fromNumber;
    protected string $whatsappFrom;

    public function __construct()
    {
        $sid = config('twilio.account_sid');
        $token = config('twilio.auth_token');

        if ($sid && $token) {
            $this->client = new Client($sid, $token);
        }
        
        $this->fromNumber = config('twilio.from_number') ?? '';
        $this->whatsappFrom = config('twilio.whatsapp_from') ?? '';
    }

    /**
     * Enviar SMS
     */
    public function sendSms(string $to, string $message): array
    {
        if (!isset($this->client)) {
             Log::error('Twilio Credentials not set');
             return ['success' => false, 'error' => 'Credentials not set'];
        }

        try {
            $result = $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);

            return [
                'success' => true,
                'sid' => $result->sid,
                'status' => $result->status,
            ];
        } catch (Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enviar WhatsApp
     */
    public function sendWhatsApp(string $to, string $message): array
    {
        if (!isset($this->client)) {
             Log::error('Twilio Credentials not set');
             return ['success' => false, 'error' => 'Credentials not set'];
        }

        // Ensure recipient has 'whatsapp:' prefix if not present
        if (!str_starts_with($to, 'whatsapp:')) {
            $to = 'whatsapp:' . $to;
        }

        try {
            $result = $this->client->messages->create(
                $to,
                [
                    'from' => $this->whatsappFrom,
                    'body' => $message,
                ]
            );

            return [
                'success' => true,
                'sid' => $result->sid,
                'status' => $result->status,
            ];
        } catch (Exception $e) {
            Log::error('Twilio WhatsApp Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
