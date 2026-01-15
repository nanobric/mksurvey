<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = TemplateMaster::query();

        if ($request->channel) {
            $query->where('channel', $request->channel);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        $masters = $query->orderBy('sort_order')->orderBy('name')->paginate(12);

        return view('admin.template-masters.index', compact('masters'));
    }

    public function create()
    {
        return view('admin.template-masters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:welcome,promo,reminder,survey,otp,transactional,newsletter',
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required|string',
            'editable_fields' => 'required|array',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        
        // Detectar variables
        preg_match_all('/\{\{(\w+)\}\}/', $validated['content'], $matches);
        $validated['variables'] = array_unique($matches[1] ?? []);
        $validated['is_active'] = true;

        TemplateMaster::create($validated);

        return redirect()->route('admin.template-masters.index')
            ->with('success', 'Template Master creado');
    }

    public function edit(TemplateMaster $templateMaster)
    {
        return view('admin.template-masters.edit', compact('templateMaster'));
    }

    public function update(Request $request, TemplateMaster $templateMaster)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:welcome,promo,reminder,survey,otp,transactional,newsletter',
            'channel' => 'required|in:sms,whatsapp,email',
            'content' => 'required|string',
            'editable_fields' => 'required|array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        preg_match_all('/\{\{(\w+)\}\}/', $validated['content'], $matches);
        $validated['variables'] = array_unique($matches[1] ?? []);

        $templateMaster->update($validated);

        return redirect()->route('admin.template-masters.index')
            ->with('success', 'Template Master actualizado');
    }

    public function destroy(TemplateMaster $templateMaster)
    {
        $templateMaster->delete();

        return redirect()->route('admin.template-masters.index')
            ->with('success', 'Template Master eliminado');
    }
}
