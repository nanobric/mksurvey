<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCampaignBatch;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckCampaignPauses extends Command
{
    protected $signature = 'campaign:check-pauses';
    protected $description = 'Reactiva campañas pausadas por horario que deben continuar';

    public function handle(): int
    {
        $now = now();

        // Buscar campañas programadas que deben iniciar
        $scheduledCampaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        foreach ($scheduledCampaigns as $campaign) {
            $this->info("Iniciando campaña programada: {$campaign->id} - {$campaign->name}");
            
            $campaign->update(['status' => 'received']);
            ProcessCampaignBatch::dispatch($campaign);
            
            Log::info("Campaign {$campaign->id} started by scheduler");
        }

        // Buscar campañas pausadas por horario que deben reactivarse
        $pausedCampaigns = Campaign::where('status', 'paused_by_schedule')
            ->where(function ($query) use ($now) {
                $query->whereNull('deadline_at')
                      ->orWhere('deadline_at', '>', $now);
            })
            ->get();

        foreach ($pausedCampaigns as $campaign) {
            $this->info("Reactivando campaña pausada: {$campaign->id} - {$campaign->name}");
            
            $campaign->update(['status' => 'processing']);
            ProcessCampaignBatch::dispatch($campaign);
            
            Log::info("Campaign {$campaign->id} resumed by scheduler");
        }

        // Cancelar campañas que pasaron su deadline
        $expiredCampaigns = Campaign::where('status', 'paused_by_schedule')
            ->where('deadline_at', '<=', $now)
            ->where('on_timeout_policy', 'cancel')
            ->get();

        foreach ($expiredCampaigns as $campaign) {
            $this->warn("Cancelando campaña expirada: {$campaign->id} - {$campaign->name}");
            
            $campaign->update([
                'status' => 'cancelled',
                'completed_at' => $now,
            ]);
            
            Log::info("Campaign {$campaign->id} cancelled due to deadline");
        }

        $total = $scheduledCampaigns->count() + $pausedCampaigns->count() + $expiredCampaigns->count();
        $this->info("Procesadas {$total} campañas");

        return Command::SUCCESS;
    }
}
