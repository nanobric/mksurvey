<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::latest()->paginate(12);
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.builder');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required|string',
            'components' => 'nullable|array',
            'media_url' => 'nullable|url',
            'media_type' => 'nullable|in:image,video,document',
        ]);

        // Generar código único
        $validated['code'] = Str::slug($validated['name']) . '-' . Str::random(6);
        
        // Detectar variables
        preg_match_all('/\{\{(\w+)\}\}/', $validated['content'], $matches);
        $validated['variables'] = array_unique($matches[1] ?? []);
        
        $validated['status'] = 'active';

        $template = Template::create($validated);

        return redirect()->route('templates.show', $template)
            ->with('success', 'Template creado correctamente');
    }

    public function show(Template $template)
    {
        return view('templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        return view('templates.builder', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required|string',
            'components' => 'nullable|array',
            'media_url' => 'nullable|url',
            'media_type' => 'nullable|in:image,video,document',
        ]);

        // Detectar variables
        preg_match_all('/\{\{(\w+)\}\}/', $validated['content'], $matches);
        $validated['variables'] = array_unique($matches[1] ?? []);

        $template->update($validated);

        return redirect()->route('templates.show', $template)
            ->with('success', 'Template actualizado correctamente');
    }

    public function destroy(Template $template)
    {
        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template eliminado');
    }

    /**
     * Preview del template con variables.
     */
    public function preview(Request $request, Template $template)
    {
        $data = $request->input('variables', []);
        $rendered = $template->render($data);

        return response()->json([
            'content' => $rendered,
            'variables' => $template->variables,
        ]);
    }
}
