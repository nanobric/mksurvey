<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Enviar Email Genérico
     */
    public function send(string $to, string $subject, string $content): array
    {
        try {
            Mail::raw($content, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Email Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
