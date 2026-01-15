<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected \App\Services\TwilioService $twilioService,
        protected \App\Services\EmailService $emailService
    ) {}

    public function index()
    {
        $messages = \App\Models\Message::latest()->paginate(20);
        return view('messages.index', compact('messages'));
    }

    public function create()
    {
        $templates = \App\Models\Template::all();
        return view('messages.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|string',
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required_without:template_id|string|nullable',
            'template_id' => 'nullable|exists:templates,id',
            // If template is selected, we might want to validate vars, but for simple manual send, assume content provided or template
        ]);

        // If template_id, retrieve content (simplification: if content not posted, use template)
        // In a real app, JS would populate content. Here let's assume content is passed OR we fetch it.
        // Let's enforce content to be sent from form even if template selected (JS fills it)
        $content = $request->input('content');
        if (empty($content) && $request->filled('template_id')) {
             $template = \App\Models\Template::find($request->input('template_id'));
             $content = $template->content; 
             // Variable replacement logic would go here if we had variables input
        }
        
        if (empty($content)) {
            return back()->withErrors(['content' => 'El contenido es requerido']);
        }

        // Create Message Log as pending
        $message = \App\Models\Message::create([
            'recipient' => $validated['recipient'],
            'channel' => $validated['channel'],
            'content' => $content, // Store exact sent content
            'template_id' => $request->input('template_id'),
            'status' => 'pending'
        ]);

        // Send logic
        $result = ['success' => false, 'error' => 'Canal no soportado'];
        
        if ($validated['channel'] === 'sms') {
            $result = $this->twilioService->sendSms($validated['recipient'], $content);
        } elseif ($validated['channel'] === 'whatsapp') {
            $result = $this->twilioService->sendWhatsApp($validated['recipient'], $content);
        } elseif ($validated['channel'] === 'email') {
            $result = $this->emailService->send($validated['recipient'], 'Notificación', $content);
        }

        // Update Log
        $message->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'twilio_sid' => $result['sid'] ?? null,  // Email log might not have SID, update Schema if needed or store null
            'error_message' => $result['error'] ?? null,
            'sent_at' => $result['success'] ? now() : null,
        ]);

        return redirect()->route('messages.index')
            ->with($result['success'] ? 'success' : 'error', 
                   $result['success'] ? 'Mensaje enviado correctamente' : 'Error al enviar: ' . ($result['error'] ?? ''));
    }

    public function show(\App\Models\Message $message)
    {
        return view('messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
