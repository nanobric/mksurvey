@extends('layouts.app')

@section('title', 'Detalle Request')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header {{ $apiLog->status == 'success' ? 'bg-success' : 'bg-danger' }}">
                <h3 class="card-title text-white">
                    <span class="badge badge-light">{{ $apiLog->method }}</span>
                    {{ $apiLog->endpoint }}
                </h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Fecha</dt>
                    <dd>{{ $apiLog->created_at->format('d/m/Y H:i:s') }}</dd>
                    
                    <dt>Cliente</dt>
                    <dd>{{ $apiLog->client?->name ?? 'N/A' }}</dd>
                    
                    <dt>IP</dt>
                    <dd>{{ $apiLog->ip_address }}</dd>
                    
                    <dt>User Agent</dt>
                    <dd><small>{{ $apiLog->user_agent }}</small></dd>
                    
                    <dt>Duración</dt>
                    <dd>{{ number_format($apiLog->duration_ms ?? 0, 2) }} ms</dd>
                    
                    <dt>Response Status</dt>
                    <dd>
                        <span class="badge {{ $apiLog->response_status < 400 ? 'badge-success' : 'badge-danger' }}">
                            {{ $apiLog->response_status }}
                        </span>
                    </dd>

                    @if($apiLog->error_message)
                    <dt>Error</dt>
                    <dd class="text-danger">{{ $apiLog->error_message }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Request Body -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Request Body</h3>
            </div>
            <div class="card-body">
                <pre class="bg-dark text-light p-3" style="max-height: 300px; overflow: auto;"><code>{{ json_encode($apiLog->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            </div>
        </div>

        <!-- Response Body -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Response Body</h3>
            </div>
            <div class="card-body">
                <pre class="bg-dark text-light p-3" style="max-height: 300px; overflow: auto;"><code>{{ json_encode($apiLog->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('admin.api-logs.index') }}" class="btn btn-default">
    <i class="fas fa-arrow-left"></i> Volver
</a>
@endsection
