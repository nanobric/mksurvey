<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::latest()->paginate(10);
        return view('templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:templates',
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required|string',
            'variables' => 'nullable|string', // Comma separated in UI
        ]);

        // Process variables from string "name, url" to array ["name", "url"]
        if (!empty($validated['variables'])) {
            $validated['variables'] = array_map('trim', explode(',', $validated['variables']));
        } else {
            $validated['variables'] = [];
        }

        Template::create($validated);

        return redirect()->route('templates.index')
            ->with('success', 'Template creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template)
    {
        return view('templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Template $template)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:templates,code,' . $template->id,
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required|string',
            'variables' => 'nullable|string',
        ]);

        if (!empty($validated['variables'])) {
            $validated['variables'] = array_map('trim', explode(',', $validated['variables']));
        } else {
            $validated['variables'] = [];
        }

        $template->update($validated);

        return redirect()->route('templates.index')
            ->with('success', 'Template actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        $template->delete();
        return redirect()->route('templates.index')
            ->with('success', 'Template eliminado correctamente');
    }
}
