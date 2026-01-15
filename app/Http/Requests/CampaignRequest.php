<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Validar api_token contra clients API tokens
        return true;
    }

    public function rules(): array
    {
        return [
            // Request Header
            'request_header' => 'required|array',
            'request_header.api_token' => 'required|string',
            'request_header.request_id' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    // Idempotencia: verificar que request_id no existe en cache (24h)
                    $cacheKey = "campaign_request:{$value}";
                    if (Cache::has($cacheKey)) {
                        $fail('Este request_id ya fue procesado. Solicitud duplicada.');
                    }
                },
            ],
            'request_header.is_test' => 'boolean',
            'request_header.version' => 'string',

            // Campaign
            'campaign' => 'required|array',
            'campaign.name' => 'required|string|max:255',
            'campaign.external_id' => 'nullable|string|max:100',
            'campaign.template_id' => 'nullable|string|exists:templates,id',
            'campaign.tags' => 'nullable|array',
            'campaign.tags.*' => 'string|max:50',

            // Routing
            'routing' => 'required|array',
            'routing.channel' => ['required', Rule::in(['sms', 'whatsapp', 'email'])],
            'routing.route_tier' => ['nullable', Rule::in(['sandbox', 'official', 'secondary'])],
            'routing.priority' => ['nullable', Rule::in(['low', 'normal', 'high'])],
            'routing.validity_seconds' => 'nullable|integer|min:60|max:86400',

            // Scheduling
            'scheduling' => 'nullable|array',
            'scheduling.send_at' => 'nullable|date|after:now',
            'scheduling.timezone' => 'nullable|string|timezone',
            'scheduling.on_timeout_policy' => ['nullable', Rule::in(['resume', 'cancel'])],

            // Content Variables
            'content_vars' => 'nullable|array',
            'content_vars.global' => 'nullable|array',
            'content_vars.track_links' => 'nullable|boolean',

            // Recipients - Array de destinatarios
            'recipients' => 'required|array|min:1',
            'recipients.*.to' => [
                'required',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/', // Formato E.164
            ],
            'recipients.*.tracking_id' => 'nullable|string|max:100',
            'recipients.*.params' => 'nullable|array',

            // Security & Billing
            'security_billing' => 'nullable|array',
            'security_billing.authorization_pin' => 'nullable|string|max:10',

            // Reporting
            'reporting' => 'nullable|array',
            'reporting.callback_url' => 'nullable|url',
        ];
    }

    public function messages(): array
    {
        return [
            'recipients.*.to.regex' => 'El número :input no tiene formato E.164 válido (ej: +526561234567)',
            'campaign.template_id.exists' => 'El template especificado no existe',
            'scheduling.send_at.after' => 'La fecha de envío debe ser futura',
        ];
    }

    /**
     * Después de validación exitosa, guardar request_id en cache para idempotencia.
     */
    protected function passedValidation(): void
    {
        $requestId = $this->input('request_header.request_id');
        Cache::put("campaign_request:{$requestId}", true, now()->addHours(24));
    }

    /**
     * Obtener datos estructurados para crear la campaña.
     */
    public function toCampaignData(): array
    {
        return [
            'request_id' => $this->input('request_header.request_id'),
            'external_id' => $this->input('campaign.external_id'),
            'name' => $this->input('campaign.name'),
            'template_id' => $this->input('campaign.template_id'),
            'channel' => $this->input('routing.channel'),
            'route_tier' => $this->input('routing.route_tier', 'sandbox'),
            'priority' => $this->input('routing.priority', 'normal'),
            'validity_seconds' => $this->input('routing.validity_seconds', 3600),
            'scheduled_at' => $this->input('scheduling.send_at'),
            'timezone' => $this->input('scheduling.timezone', 'America/Mexico_City'),
            'on_timeout_policy' => $this->input('scheduling.on_timeout_policy', 'resume'),
            'content_vars' => $this->input('content_vars'),
            'tags' => $this->input('campaign.tags'),
            'callback_url' => $this->input('reporting.callback_url'),
            'authorization_pin' => $this->input('security_billing.authorization_pin'),
            'total_recipients' => count($this->input('recipients', [])),
            'status' => 'received',
        ];
    }
}
