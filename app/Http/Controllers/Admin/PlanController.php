<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('activeSubscriptions')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_sms_limit' => 'required|integer|min:0',
            'monthly_whatsapp_limit' => 'required|integer|min:0',
            'monthly_email_limit' => 'required|integer|min:0',
            'max_campaigns_per_month' => 'required|integer|min:0',
            'max_recipients_per_campaign' => 'required|integer|min:0',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan creado correctamente');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_sms_limit' => 'required|integer|min:0',
            'monthly_whatsapp_limit' => 'required|integer|min:0',
            'monthly_email_limit' => 'required|integer|min:0',
            'max_campaigns_per_month' => 'required|integer|min:0',
            'max_recipients_per_campaign' => 'required|integer|min:0',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan actualizado correctamente');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->activeSubscriptions()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un plan con suscripciones activas');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan eliminado correctamente');
    }
}
