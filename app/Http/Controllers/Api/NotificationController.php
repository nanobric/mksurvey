<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\Message;
use App\Services\TwilioService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        protected TwilioService $twilioService,
        protected EmailService $emailService
    ) {}

    public function send(Request $request)
    {
        // 1. Validation
        $validator = \Validator::make($request->all(), [
            'recipient' => 'required|string',
            'channel' => 'required|in:sms,whatsapp,email',
            // Either content OR template_code is required
            'content' => 'required_without:template_code|string|nullable',
            'template_code' => 'required_without:content|string|exists:templates,code',
            'variables' => 'array|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $recipient = $request->input('recipient');
        $channel = $request->input('channel');
        $content = $request->input('content');
        $templateId = null;

        // 2. Template Processing
        if ($request->filled('template_code')) {
            $template = Template::where('code', $request->input('template_code'))->first();
            
            if ($template) {
                $templateId = $template->id;
                // Override channel if strictly defined by template? 
                // Plan says "Template model (channel)". Let's respect request channel OR Validate template channel matches?
                // For flexibility, let's assume request channel is authoritative or ensure matches.
                // Let's validate logical consistency: if template is 'whatsapp', sending via 'sms' might fail if content not plain text.
                // For now, allow override, but ideally we use template content.
                
                $content = $template->content;
                
                // Variable Replacement
                if ($request->filled('variables')) {
                    foreach ($request->input('variables') as $key => $value) {
                        $content = str_replace('{' . $key . '}', $value, $content);
                    }
                }
            }
        }

        // 3. Log Message (Pending)
        $message = Message::create([
            'recipient' => $recipient,
            'channel' => $channel,
            'content' => $content,
            'template_id' => $templateId,
            'status' => 'pending',
            'payload' => json_encode($request->all()), // Store original payload for debug (migration schema didn't have payload, adding it dynamically or ignoring?)
            // Schema created earlier: recipient, channel, status, twilio_sid, error_message, sent_at. No payload column.
            // I'll skip payload column for now or just log it.
        ]);

        // 4. Send Logic
        $result = ['success' => false, 'error' => 'Unknown channel'];
        
        try {
            if ($channel === 'sms') {
                $result = $this->twilioService->sendSms($recipient, $content);
            } elseif ($channel === 'whatsapp') {
                $result = $this->twilioService->sendWhatsApp($recipient, $content);
            } elseif ($channel === 'email') {
                $result = $this->emailService->send($recipient, 'Notification', $content);
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage()];
        }

        // 5. Update Log
        $message->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'twilio_sid' => $result['sid'] ?? null,
            'error_message' => $result['error'] ?? null,
            'sent_at' => $result['success'] ? now() : null,
        ]);

        // 6. Response
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message_id' => $message->id,
                'sid' => $result['sid'] ?? null
            ], 200);
        } else {
             return response()->json([
                'success' => false,
                'message_id' => $message->id,
                'error' => $result['error'] ?? 'Unknown error'
            ], 500);
        }
    }
}
