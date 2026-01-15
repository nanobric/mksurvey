<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientTemplate;
use App\Models\TemplateMaster;
use Illuminate\Http\Request;

/**
 * Controller para que clientes personalicen templates.
 */
class ClientTemplateController extends Controller
{
    /**
     * Galería de Template Masters para elegir.
     */
    public function gallery(Request $request)
    {
        // TODO: En producción, obtener client_id del usuario autenticado
        $clientId = $request->get('client_id', 1);
        $client = Client::findOrFail($clientId);

        $query = TemplateMaster::active();

        if ($request->channel) {
            $query->forChannel($request->channel);
        }

        if ($request->category) {
            $query->forCategory($request->category);
        }

        $masters = $query->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();

        // Templates ya creados por el cliente
        $clientTemplates = ClientTemplate::where('client_id', $clientId)->get();

        return view('client-templates.gallery', compact('masters', 'client', 'clientTemplates'));
    }

    /**
     * Formulario de personalización.
     */
    public function customize(TemplateMaster $master, Request $request)
    {
        $clientId = $request->get('client_id', 1);
        $client = Client::findOrFail($clientId);

        return view('client-templates.customize', compact('master', 'client'));
    }

    /**
     * Guardar template personalizado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'master_id' => 'required|exists:template_masters,id',
            'name' => 'required|string|max:255',
            'customizations' => 'required|array',
            'media_url' => 'nullable|url',
        ]);

        $validated['status'] = 'active';

        $template = ClientTemplate::create($validated);

        return redirect()->route('client-templates.show', $template)
            ->with('success', 'Template personalizado guardado');
    }

    /**
     * Ver template personalizado.
     */
    public function show(ClientTemplate $clientTemplate)
    {
        return view('client-templates.show', compact('clientTemplate'));
    }

    /**
     * Editar template personalizado.
     */
    public function edit(ClientTemplate $clientTemplate)
    {
        return view('client-templates.customize', [
            'master' => $clientTemplate->master,
            'client' => $clientTemplate->client,
            'clientTemplate' => $clientTemplate,
        ]);
    }

    /**
     * Actualizar template personalizado.
     */
    public function update(Request $request, ClientTemplate $clientTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customizations' => 'required|array',
            'media_url' => 'nullable|url',
        ]);

        $clientTemplate->update($validated);

        return redirect()->route('client-templates.show', $clientTemplate)
            ->with('success', 'Template actualizado');
    }

    /**
     * Preview AJAX con variables.
     */
    public function preview(Request $request, TemplateMaster $master)
    {
        $customizations = $request->input('customizations', []);
        $rendered = $master->render($customizations);

        return response()->json([
            'content' => $rendered,
            'html' => nl2br(e($rendered)),
        ]);
    }

    /**
     * Mis templates (del cliente).
     */
    public function myTemplates(Request $request)
    {
        $clientId = $request->get('client_id', 1);
        $client = Client::findOrFail($clientId);

        $templates = ClientTemplate::where('client_id', $clientId)
            ->with('master')
            ->latest()
            ->paginate(12);

        return view('client-templates.my-templates', compact('templates', 'client'));
    }
}
