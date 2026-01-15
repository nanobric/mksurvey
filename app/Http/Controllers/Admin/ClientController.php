<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('activeSubscription.plan')->latest()->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.clients.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:20|unique:clients,rfc',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url',
            'notes' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'status' => 'required|in:active,inactive,trial',
            'expected_monthly_volume' => 'nullable|integer|min:0',
            'volume_tier' => 'nullable|in:small,medium,large,enterprise,custom',
        ]);

        // Crear cliente
        $client = Client::create([
            ...$validated,
            'trial_ends_at' => $validated['status'] === 'trial' ? now()->addDays(14) : null,
        ]);

        // Crear suscripción si se seleccionó plan
        if ($request->filled('plan_id')) {
            Subscription::create([
                'client_id' => $client->id,
                'plan_id' => $validated['plan_id'],
                'billing_cycle' => 'monthly',
                'starts_at' => now(),
                'status' => 'active',
                'usage_resets_at' => now()->addMonth(),
            ]);
        }

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente creado correctamente');
    }

    public function show(Client $client)
    {
        $client->load(['subscriptions.plan', 'users', 'campaigns' => function($q) {
            $q->latest()->take(10);
        }]);
        
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.clients.edit', compact('client', 'plans'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:20|unique:clients,rfc,' . $client->id,
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended,trial',
            'expected_monthly_volume' => 'nullable|integer|min:0',
            'volume_tier' => 'nullable|in:small,medium,large,enterprise,custom',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Generar nuevo API Token para el cliente.
     */
    public function generateToken(Client $client)
    {
        $plainToken = $client->generateApiToken();

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Token generado correctamente')
            ->with('new_token', $plainToken);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}
