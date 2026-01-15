@extends('layouts.app')

@section('title', $client->name)

@section('content')
@if(session('new_token'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <h5><i class="icon fas fa-key"></i> ¡Token Generado!</h5>
    <p><strong>Guarda este token, no se mostrará de nuevo:</strong></p>
    <code class="bg-dark text-light p-2 d-block" style="word-break: break-all;">{{ session('new_token') }}</code>
</div>
@endif

<div class="row">
    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $client->name }}</h3>
                <p class="text-muted text-center">{{ $client->industry ?? 'Sin industria' }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Estado</b>
                        <span class="float-right">
                            @switch($client->status)
                                @case('active')<span class="badge badge-success">Activo</span>@break
                                @case('trial')<span class="badge badge-warning">Trial</span>@break
                                @case('suspended')<span class="badge badge-danger">Suspendido</span>@break
                                @default<span class="badge badge-secondary">{{ $client->status }}</span>
                            @endswitch
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Email</b> <span class="float-right">{{ $client->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Teléfono</b> <span class="float-right">{{ $client->phone ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>RFC</b> <span class="float-right">{{ $client->rfc ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Plan</b>
                        <span class="float-right">
                            @if($client->activeSubscription)
                                <span class="badge badge-info">{{ $client->activeSubscription->plan->name }}</span>
                            @else
                                <span class="badge badge-secondary">Sin plan</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>API Token</b>
                        <span class="float-right">
                            @if($client->api_token)
                                <span class="badge badge-success">Configurado</span>
                            @else
                                <span class="badge badge-warning">No generado</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Volumen Mensual</b>
                        <span class="float-right">
                            @if($client->expected_monthly_volume)
                                <strong>{{ number_format($client->expected_monthly_volume) }}</strong> msg/mes
                            @elseif($client->volume_tier)
                                @switch($client->volume_tier)
                                    @case('small')<span class="badge badge-secondary">< 1,000</span>@break
                                    @case('medium')<span class="badge badge-info">1K - 10K</span>@break
                                    @case('large')<span class="badge badge-primary">10K - 100K</span>@break
                                    @case('enterprise')<span class="badge badge-success">100K+</span>@break
                                    @default<span class="badge badge-secondary">{{ $client->volume_tier }}</span>
                                @endswitch
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </li>
                </ul>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.clients.generate-token', $client) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Generar nuevo token? El anterior quedará inválido.')">
                            <i class="fas fa-key"></i> Generar Token
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subscription Card -->
        @if($client->activeSubscription)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Uso del Plan</h3>
            </div>
            <div class="card-body">
                @php $sub = $client->activeSubscription; @endphp
                
                <div class="mb-3">
                    <label>SMS ({{ $sub->sms_used }}/{{ $sub->plan->monthly_sms_limit ?: '∞' }})</label>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: {{ $sub->usagePercentage('sms') }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>WhatsApp ({{ $sub->whatsapp_used }}/{{ $sub->plan->monthly_whatsapp_limit ?: '∞' }})</label>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $sub->usagePercentage('whatsapp') }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Email ({{ $sub->email_used }}/{{ $sub->plan->monthly_email_limit ?: '∞' }})</label>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: {{ $sub->usagePercentage('email') }}%"></div>
                    </div>
                </div>

                <small class="text-muted">Reset: {{ $sub->usage_resets_at?->format('d/m/Y') }}</small>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <!-- Campaigns Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimas Campañas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Canal</th>
                            <th>Estado</th>
                            <th>Recipients</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->id }}</td>
                            <td>{{ Str::limit($campaign->name, 30) }}</td>
                            <td><span class="badge badge-primary">{{ $campaign->channel }}</span></td>
                            <td>
                                @switch($campaign->status)
                                    @case('completed')<span class="badge badge-success">Completada</span>@break
                                    @case('processing')<span class="badge badge-warning">Procesando</span>@break
                                    @case('failed')<span class="badge badge-danger">Fallida</span>@break
                                    @default<span class="badge badge-secondary">{{ $campaign->status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</td>
                            <td>{{ $campaign->created_at->format('d/m H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Sin campañas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
