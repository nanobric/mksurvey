@extends('layouts.app')

@section('title', 'Campaña: ' . $campaign->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Campaign Info -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Información</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>ID</dt>
                    <dd>{{ $campaign->id }}</dd>
                    
                    <dt>Request ID</dt>
                    <dd><code>{{ $campaign->request_id }}</code></dd>
                    
                    <dt>Cliente</dt>
                    <dd>{{ $campaign->client?->name ?? 'Sin cliente' }}</dd>
                    
                    <dt>Canal</dt>
                    <dd>
                        @if($campaign->channel == 'sms')
                            <span class="badge badge-primary">SMS</span>
                        @elseif($campaign->channel == 'whatsapp')
                            <span class="badge badge-success">WhatsApp</span>
                        @else
                            <span class="badge badge-info">Email</span>
                        @endif
                        <span class="badge badge-secondary">{{ $campaign->route_tier }}</span>
                    </dd>
                    
                    <dt>Estado</dt>
                    <dd>
                        @switch($campaign->status)
                            @case('completed')<span class="badge badge-success">Completada</span>@break
                            @case('processing')<span class="badge badge-warning">Procesando</span>@break
                            @case('failed')<span class="badge badge-danger">Fallida</span>@break
                            @default<span class="badge badge-secondary">{{ $campaign->status }}</span>
                        @endswitch
                    </dd>
                    
                    <dt>Creada</dt>
                    <dd>{{ $campaign->created_at->format('d/m/Y H:i:s') }}</dd>
                    
                    @if($campaign->started_at)
                    <dt>Iniciada</dt>
                    <dd>{{ $campaign->started_at->format('d/m/Y H:i:s') }}</dd>
                    @endif
                    
                    @if($campaign->completed_at)
                    <dt>Completada</dt>
                    <dd>{{ $campaign->completed_at->format('d/m/Y H:i:s') }}</dd>
                    @endif
                </dl>

                <hr>

                <!-- Actions -->
                @if(in_array($campaign->status, ['processing', 'scheduled']))
                <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-warning btn-sm"><i class="fas fa-pause"></i> Pausar</button>
                </form>
                @endif
                
                @if(in_array($campaign->status, ['paused_by_user', 'paused_by_schedule']))
                <form action="{{ route('admin.campaigns.resume', $campaign) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="fas fa-play"></i> Reanudar</button>
                </form>
                @endif
                
                @if(!in_array($campaign->status, ['completed', 'cancelled']))
                <form action="{{ route('admin.campaigns.cancel', $campaign) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Cancelar esta campaña?')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Progress -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progreso</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="text-lg">{{ $campaign->total_recipients }}</div>
                        <div class="text-muted">Total</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="text-lg text-success">{{ $recipientStats['sent'] + $recipientStats['delivered'] }}</div>
                        <div class="text-muted">Enviados</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="text-lg text-info">{{ $recipientStats['delivered'] }}</div>
                        <div class="text-muted">Entregados</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="text-lg text-danger">{{ $recipientStats['failed'] }}</div>
                        <div class="text-muted">Fallidos</div>
                    </div>
                </div>

                @php $pct = $campaign->total_recipients > 0 ? round((($campaign->sent_count + $campaign->failed_count) / $campaign->total_recipients) * 100) : 0; @endphp
                <div class="progress mt-4" style="height: 30px;">
                    <div class="progress-bar bg-success" style="width: {{ $pct }}%">{{ $pct }}%</div>
                </div>
            </div>
        </div>

        <!-- Recipients -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recipients (últimos 50)</h3>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 400px;">
                <table class="table table-sm table-head-fixed">
                    <thead>
                        <tr>
                            <th>Destino</th>
                            <th>Estado</th>
                            <th>SID</th>
                            <th>Enviado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaign->recipients->take(50) as $recipient)
                        <tr>
                            <td>{{ $recipient->to }}</td>
                            <td>
                                @switch($recipient->status)
                                    @case('sent')<span class="badge badge-warning">Enviado</span>@break
                                    @case('delivered')<span class="badge badge-success">Entregado</span>@break
                                    @case('failed')<span class="badge badge-danger" title="{{ $recipient->error_message }}">Fallido</span>@break
                                    @case('pending')<span class="badge badge-secondary">Pendiente</span>@break
                                    @default<span class="badge badge-secondary">{{ $recipient->status }}</span>
                                @endswitch
                            </td>
                            <td><small>{{ Str::limit($recipient->provider_sid, 20) }}</small></td>
                            <td>{{ $recipient->sent_at?->format('H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
