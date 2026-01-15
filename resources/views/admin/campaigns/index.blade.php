@extends('layouts.app')

@section('title', 'Monitor de Campañas')

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Campañas</p>
            </div>
            <div class="icon"><i class="fas fa-paper-plane"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['processing'] }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon"><i class="fas fa-spinner"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['completed'] }}</h3>
                <p>Completadas</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['failed'] }}</h3>
                <p>Fallidas</p>
            </div>
            <div class="icon"><i class="fas fa-times"></i></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Filtros</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- Estado --</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Recibida</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Procesando</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallida</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="channel" class="form-control form-control-sm">
                    <option value="">-- Canal --</option>
                    <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                    <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por nombre o request_id" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('admin.campaigns.index') }}" class="btn btn-default btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Campañas</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Nombre</th>
                    <th>Canal</th>
                    <th>Estado</th>
                    <th>Progreso</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->id }}</td>
                    <td>{{ $campaign->client?->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.campaigns.show', $campaign) }}">
                            {{ Str::limit($campaign->name, 25) }}
                        </a>
                    </td>
                    <td>
                        @if($campaign->channel == 'sms')
                            <span class="badge badge-primary"><i class="fas fa-sms"></i> SMS</span>
                        @elseif($campaign->channel == 'whatsapp')
                            <span class="badge badge-success"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                        @else
                            <span class="badge badge-info"><i class="fas fa-envelope"></i> Email</span>
                        @endif
                    </td>
                    <td>
                        @switch($campaign->status)
                            @case('received')<span class="badge badge-secondary">Recibida</span>@break
                            @case('scheduled')<span class="badge badge-info">Programada</span>@break
                            @case('processing')<span class="badge badge-warning">Procesando</span>@break
                            @case('completed')<span class="badge badge-success">Completada</span>@break
                            @case('failed')<span class="badge badge-danger">Fallida</span>@break
                            @case('paused_by_user')<span class="badge badge-secondary">Pausada</span>@break
                            @case('cancelled')<span class="badge badge-dark">Cancelada</span>@break
                            @default<span class="badge badge-secondary">{{ $campaign->status }}</span>
                        @endswitch
                    </td>
                    <td>
                        @php $pct = $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100) : 0; @endphp
                        <div class="progress progress-xs">
                            <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                        </div>
                        <small>{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</small>
                    </td>
                    <td>{{ $campaign->created_at->format('d/m H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.campaigns.show', $campaign) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(in_array($campaign->status, ['processing', 'scheduled']))
                        <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-warning btn-xs"><i class="fas fa-pause"></i></button>
                        </form>
                        @endif
                        @if(in_array($campaign->status, ['paused_by_user', 'paused_by_schedule']))
                        <form action="{{ route('admin.campaigns.resume', $campaign) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-xs"><i class="fas fa-play"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No hay campañas registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $campaigns->withQueryString()->links() }}
    </div>
</div>
@endsection
