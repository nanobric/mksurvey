@extends('layouts.app')

@section('title', 'Dashboard ')

@section('content')
<!-- Row 1: KPI Cards principales -->
<div class="row">
    <!-- Clientes -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ $clientsKpis['active'] }}</h3>
                <p>Clientes Activos</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="{{ route('admin.clients.index') }}" class="small-box-footer">
                +{{ $clientsKpis['new_this_month'] }} este mes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Campañas Activas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $campaignsKpis['active'] }}</h3>
                <p>Campañas Activas</p>
            </div>
            <div class="icon"><i class="fas fa-paper-plane"></i></div>
            <a href="{{ route('admin.campaigns.index') }}" class="small-box-footer">
                {{ $campaignsKpis['completed_today'] }} completadas hoy <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Tasa de Entrega -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ $campaignsKpis['delivery_rate'] }}<sup style="font-size: 20px">%</sup></h3>
                <p>Tasa de Entrega</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">
                {{ $campaignsKpis['error_rate'] }}% errores <i class="fas fa-exclamation-triangle"></i>
            </a>
        </div>
    </div>

    <!-- Templates -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $templatesKpis['masters_active'] }}</h3>
                <p>Template Masters</p>
            </div>
            <div class="icon"><i class="fas fa-palette"></i></div>
            <a href="{{ route('admin.template-masters.index') }}" class="small-box-footer">
                {{ $templatesKpis['client_templates'] }} personalizados <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Row 2: Gráfica y Top Clientes -->
<div class="row">
    <!-- Gráfica de últimos 7 días -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Campañas últimos 7 días</h3>
                </div>
            </div>
            <div class="card-body">
                <canvas id="campaignsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Clientes -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-trophy text-warning"></i> Top 5 Clientes</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($clientsKpis['top_by_volume'] as $index => $client)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <span class="badge badge-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'light') }} mr-2">{{ $index + 1 }}</span>
                            {{ $client->name }}
                        </span>
                        <span class="badge badge-primary badge-pill">{{ $client->volume_tier ?? 'N/A' }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Sin datos aún</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Distribución y Alertas -->
<div class="row">
    <!-- Distribución por Canal -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-broadcast-tower"></i> Templates por Canal</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <h4 class="text-primary">{{ $templatesKpis['by_channel']['sms'] ?? 0 }}</h4>
                        <small>📱 SMS</small>
                    </div>
                    <div>
                        <h4 class="text-success">{{ $templatesKpis['by_channel']['whatsapp'] ?? 0 }}</h4>
                        <small>💬 WhatsApp</small>
                    </div>
                    <div>
                        <h4 class="text-info">{{ $templatesKpis['by_channel']['email'] ?? 0 }}</h4>
                        <small>📧 Email</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planes -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags"></i> Distribución de Planes</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($plansKpis['subscribers_by_plan'] as $plan)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $plan->name }}</span>
                        <span class="badge badge-info">{{ $plan->count }} clientes</span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Sin suscripciones</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Panel de Alertas -->
    <div class="col-lg-4">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle text-danger"></i> Alertas</h3>
            </div>
            <div class="card-body p-0">
                @forelse($alerts as $alert)
                <div class="alert alert-{{ $alert['type'] }} m-2 mb-0">
                    <strong>{{ $alert['icon'] }} {{ $alert['message'] }}</strong>
                    <br><small class="text-muted">{{ $alert['action'] }}</small>
                </div>
                @empty
                <div class="p-3 text-center text-success">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p class="mb-0">¡Todo en orden!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Resumen Rápido -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Resumen</h3>
            </div>
            <div class="card-body">
                <div class="info-box bg-light mb-2">
                    <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Clientes</span>
                        <span class="info-box-number">{{ $clientsKpis['total'] }}</span>
                    </div>
                </div>
                <div class="info-box bg-light mb-2">
                    <span class="info-box-icon bg-success"><i class="fas fa-paper-plane"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Campañas</span>
                        <span class="info-box-number">{{ $campaignsKpis['total'] }}</span>
                    </div>
                </div>
                <div class="info-box bg-light mb-2">
                    <span class="info-box-icon bg-warning"><i class="fas fa-pause"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Campañas Pausadas</span>
                        <span class="info-box-number">{{ $campaignsKpis['paused'] }}</span>
                    </div>
                </div>
                <div class="info-box bg-light mb-0">
                    <span class="info-box-icon bg-info"><i class="fas fa-{{ $campaignsKpis['top_channel'] == 'sms' ? 'mobile-alt' : ($campaignsKpis['top_channel'] == 'whatsapp' ? 'comment' : 'envelope') }}"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Canal Principal</span>
                        <span class="info-box-number">{{ strtoupper($campaignsKpis['top_channel']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($campaignsLast7Days);
    const labels = Object.keys(chartData);
    const data = Object.values(chartData);

    // Llenar días faltantes
    const last7Days = [];
    const last7Values = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        last7Days.push(dateStr);
        last7Values.push(chartData[dateStr] || 0);
    }

    new Chart(document.getElementById('campaignsChart'), {
        type: 'line',
        data: {
            labels: last7Days.map(d => {
                const date = new Date(d);
                return date.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Campañas',
                data: last7Values,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
