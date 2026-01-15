<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Client;
use App\Models\ClientTemplate;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\TemplateMaster;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ======================
        // 👥 CLIENTES KPIs
        // ======================
        $clientsKpis = [
            'total' => Client::count(),
            'active' => Client::where('status', 'active')->count(),
            'inactive' => Client::where('status', 'inactive')->count(),
            'new_this_month' => Client::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'top_by_volume' => Client::select('clients.*')
                ->leftJoin('campaigns', 'clients.id', '=', 'campaigns.client_id')
                ->leftJoin('campaign_recipients', 'campaigns.id', '=', 'campaign_recipients.campaign_id')
                ->groupBy('clients.id')
                ->orderByRaw('COUNT(campaign_recipients.id) DESC')
                ->limit(5)
                ->get(),
        ];

        // ======================
        // 📨 CAMPAÑAS KPIs
        // ======================
        $campaignsKpis = [
            'total' => Campaign::count(),
            'active' => Campaign::whereIn('status', ['processing', 'pending'])->count(),
            'completed_today' => Campaign::where('status', 'completed')
                ->whereDate('updated_at', today())->count(),
            'paused' => Campaign::where('status', 'paused')->count(),
            'failed' => Campaign::where('status', 'failed')->count(),
        ];

        // Tasas de entrega
        $recipientStats = CampaignRecipient::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
        ")->first();

        $campaignsKpis['delivery_rate'] = $recipientStats->total > 0 
            ? round(($recipientStats->delivered / $recipientStats->total) * 100, 1) 
            : 0;
        $campaignsKpis['error_rate'] = $recipientStats->total > 0 
            ? round(($recipientStats->failed / $recipientStats->total) * 100, 1) 
            : 0;

        // Canal más usado
        $channelUsage = Campaign::select('channel', DB::raw('COUNT(*) as count'))
            ->groupBy('channel')
            ->orderByDesc('count')
            ->first();
        $campaignsKpis['top_channel'] = $channelUsage->channel ?? 'sms';

        // Campañas últimos 7 días para gráfica
        $campaignsLast7Days = Campaign::selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // ======================
        // 💳 PLANES KPIs
        // ======================
        $plansKpis = [
            'total' => Plan::count(),
            'active' => Plan::where('is_active', true)->count(),
            'subscribers_by_plan' => Subscription::select('plans.name', DB::raw('COUNT(*) as count'))
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->groupBy('plans.id', 'plans.name')
                ->orderByDesc('count')
                ->get(),
            'most_popular' => Subscription::select('plans.name', DB::raw('COUNT(*) as count'))
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->groupBy('plans.id', 'plans.name')
                ->orderByDesc('count')
                ->first(),
        ];

        // ======================
        // 📝 TEMPLATES KPIs
        // ======================
        $templatesKpis = [
            'masters_total' => TemplateMaster::count(),
            'masters_active' => TemplateMaster::where('is_active', true)->count(),
            'client_templates' => ClientTemplate::count(),
            'by_channel' => TemplateMaster::select('channel', DB::raw('COUNT(*) as count'))
                ->groupBy('channel')
                ->get()
                ->pluck('count', 'channel')
                ->toArray(),
            'most_used' => TemplateMaster::withCount('clientTemplates')
                ->orderByDesc('client_templates_count')
                ->first(),
        ];

        // ======================
        // ⚠️ ALERTAS
        // ======================
        $alerts = [];
        
        // Clientes inactivos (>30 días sin campaña)
        $inactiveClients = Client::where('status', 'active')
            ->whereDoesntHave('campaigns', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->count();
        if ($inactiveClients > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => '👥',
                'message' => "{$inactiveClients} clientes sin actividad en 30+ días",
                'action' => 'Contactar para retención',
            ];
        }

        // Campañas con alto error rate
        $highErrorCampaigns = Campaign::where('status', 'completed')
            ->whereRaw('failed_count > total_recipients * 0.05')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        if ($highErrorCampaigns > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => '❌',
                'message' => "{$highErrorCampaigns} campañas con >5% de errores",
                'action' => 'Revisar calidad de datos',
            ];
        }

        // Campañas pausadas
        if ($campaignsKpis['paused'] > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => '⏸️',
                'message' => "{$campaignsKpis['paused']} campañas en pausa",
                'action' => 'Revisar y reanudar',
            ];
        }

        return view('dashboard.index', compact(
            'clientsKpis',
            'campaignsKpis',
            'plansKpis',
            'templatesKpis',
            'campaignsLast7Days',
            'alerts'
        ));
    }
}
