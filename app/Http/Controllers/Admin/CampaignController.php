<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('client')->latest();

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('request_id', 'like', "%{$request->search}%");
            });
        }

        $campaigns = $query->paginate(20);
        
        // Stats
        $stats = [
            'total' => Campaign::count(),
            'processing' => Campaign::where('status', 'processing')->count(),
            'completed' => Campaign::where('status', 'completed')->count(),
            'failed' => Campaign::where('status', 'failed')->count(),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'stats'));
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['client', 'recipients' => function($q) {
            $q->latest()->paginate(50);
        }]);

        // Stats de recipients
        $recipientStats = [
            'pending' => $campaign->recipients()->where('status', 'pending')->count(),
            'sent' => $campaign->recipients()->where('status', 'sent')->count(),
            'delivered' => $campaign->recipients()->where('status', 'delivered')->count(),
            'failed' => $campaign->recipients()->where('status', 'failed')->count(),
        ];

        return view('admin.campaigns.show', compact('campaign', 'recipientStats'));
    }

    /**
     * Pausar campaña manualmente.
     */
    public function pause(Campaign $campaign)
    {
        if (!in_array($campaign->status, ['processing', 'scheduled'])) {
            return back()->with('error', 'Solo se pueden pausar campañas en procesamiento o programadas');
        }

        $campaign->update(['status' => 'paused_by_user']);

        return back()->with('success', 'Campaña pausada correctamente');
    }

    /**
     * Reanudar campaña pausada.
     */
    public function resume(Campaign $campaign)
    {
        if (!in_array($campaign->status, ['paused_by_user', 'paused_by_schedule'])) {
            return back()->with('error', 'Solo se pueden reanudar campañas pausadas');
        }

        $campaign->update(['status' => 'processing']);
        
        // Re-dispatch job
        \App\Jobs\ProcessCampaignBatch::dispatch($campaign);

        return back()->with('success', 'Campaña reanudada correctamente');
    }

    /**
     * Cancelar campaña.
     */
    public function cancel(Campaign $campaign)
    {
        if (in_array($campaign->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Esta campaña ya finalizó');
        }

        $campaign->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Campaña cancelada correctamente');
    }
}
