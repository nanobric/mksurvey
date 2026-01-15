@extends('layouts.app')

@section('title', 'Monitor API')

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_24h']) }}</h3>
                <p>Peticiones (24h)</p>
            </div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['success_24h']) }}</h3>
                <p>Exitosas (24h)</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['error_24h']) }}</h3>
                <p>Errores (24h)</p>
            </div>
            <div class="icon"><i class="fas fa-times"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($stats['avg_duration'] ?? 0, 0) }}ms</h3>
                <p>Tiempo Promedio</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
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
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Exitoso</option>
                    <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="method" class="form-control form-control-sm">
                    <option value="">-- Método --</option>
                    <option value="GET" {{ request('method') == 'GET' ? 'selected' : '' }}>GET</option>
                    <option value="POST" {{ request('method') == 'POST' ? 'selected' : '' }}>POST</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="endpoint" class="form-control form-control-sm" placeholder="Endpoint..." value="{{ request('endpoint') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('admin.api-logs.index') }}" class="btn btn-default btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Peticiones API</h3>
        <div class="card-tools">
            <form action="{{ route('admin.api-logs.cleanup') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar logs de más de 30 días?')">
                    <i class="fas fa-trash"></i> Limpiar antiguos
                </button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm text-nowrap">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Endpoint</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Duración</th>
                    <th>IP</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="{{ $log->status == 'error' ? 'table-danger' : '' }}">
                    <td>{{ $log->created_at->format('d/m H:i:s') }}</td>
                    <td>
                        <span class="badge {{ $log->method == 'POST' ? 'badge-success' : 'badge-info' }}">
                            {{ $log->method }}
                        </span>
                    </td>
                    <td><code>{{ Str::limit($log->endpoint, 40) }}</code></td>
                    <td>{{ $log->client?->name ?? '-' }}</td>
                    <td>
                        @if($log->status == 'success')
                            <span class="badge badge-success">{{ $log->response_status }}</span>
                        @else
                            <span class="badge badge-danger">{{ $log->response_status }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($log->duration_ms ?? 0, 0) }}ms</td>
                    <td><small>{{ $log->ip_address }}</small></td>
                    <td>
                        <a href="{{ route('admin.api-logs.show', $log) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No hay registros de API</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
