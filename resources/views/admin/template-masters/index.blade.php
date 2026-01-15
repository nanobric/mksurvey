@extends('layouts.app')

@section('title', 'Template Masters')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h4><i class="fas fa-palette"></i> Template Masters</h4>
        <p class="text-muted mb-0">Templates profesionales probados con Twilio</p>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{ route('admin.template-masters.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Master
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="btn-group">
                    <a href="{{ route('admin.template-masters.index') }}" class="btn btn-sm {{ !request('channel') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
                    <a href="{{ route('admin.template-masters.index', ['channel' => 'sms']) }}" class="btn btn-sm {{ request('channel') == 'sms' ? 'btn-primary' : 'btn-outline-primary' }}">📱 SMS</a>
                    <a href="{{ route('admin.template-masters.index', ['channel' => 'whatsapp']) }}" class="btn btn-sm {{ request('channel') == 'whatsapp' ? 'btn-primary' : 'btn-outline-primary' }}">💬 WhatsApp</a>
                    <a href="{{ route('admin.template-masters.index', ['channel' => 'email']) }}" class="btn btn-sm {{ request('channel') == 'email' ? 'btn-primary' : 'btn-outline-primary' }}">📧 Email</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="btn-group float-right">
                    <a href="{{ route('admin.template-masters.index', array_merge(request()->all(), ['category' => 'welcome'])) }}" class="btn btn-sm btn-outline-secondary">👋 Bienvenida</a>
                    <a href="{{ route('admin.template-masters.index', array_merge(request()->all(), ['category' => 'promo'])) }}" class="btn btn-sm btn-outline-secondary">🎉 Promo</a>
                    <a href="{{ route('admin.template-masters.index', array_merge(request()->all(), ['category' => 'reminder'])) }}" class="btn btn-sm btn-outline-secondary">📅 Recordatorio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid de Masters -->
<div class="row">
    @forelse($masters as $master)
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="card h-100 master-card {{ $master->is_featured ? 'featured' : '' }}">
            @if($master->is_featured)
                <div class="featured-badge">⭐ Destacado</div>
            @endif
            
            <!-- Preview -->
            <div class="master-preview channel-{{ $master->channel }}">
                <div class="preview-header">
                    <span>{{ $master->channel_icon }} {{ ucfirst($master->channel) }}</span>
                    <span class="badge badge-light">{{ $master->category_icon }} {{ ucfirst($master->category) }}</span>
                </div>
                <div class="preview-body">
                    <div class="message-bubble">
                        {!! nl2br(e(Str::limit($master->content, 80))) !!}
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <h6 class="card-title mb-1">{{ $master->name }}</h6>
                <p class="text-muted small mb-2">{{ Str::limit($master->description, 60) }}</p>
                
                <!-- Variables -->
                <div class="mb-2">
                    @php
                        $vars = is_array($master->variables) ? $master->variables : json_decode($master->variables ?? '[]', true) ?? [];
                    @endphp
                    @foreach(array_slice($vars, 0, 4) as $var)
                        <span class="badge badge-warning badge-sm">@{{ $var }}</span>
                    @endforeach
                </div>

                <!-- Status -->
                <div class="d-flex justify-content-between align-items-center">
                    @if($master->is_active)
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-secondary">Inactivo</span>
                    @endif
                    <small class="text-muted">{{ $master->clientTemplates->count() }} usos</small>
                </div>
            </div>
            
            <div class="card-footer bg-white">
                <div class="btn-group btn-group-sm w-100">
                    <a href="{{ route('admin.template-masters.edit', $master) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.template-masters.destroy', $master) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-palette fa-4x text-muted mb-3"></i>
                <h5>No hay Template Masters</h5>
                <p class="text-muted">Crea templates profesionales para tus clientes</p>
                <a href="{{ route('admin.template-masters.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Master
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

{{ $masters->links() }}
@endsection

@push('styles')
<style>
.master-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}
.master-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.master-card.featured {
    border: 2px solid #ffc107;
}
.featured-badge {
    position: absolute;
    top: 10px;
    right: -30px;
    background: #ffc107;
    color: #000;
    padding: 2px 40px;
    font-size: 0.7rem;
    font-weight: bold;
    transform: rotate(45deg);
    z-index: 10;
}

.master-preview {
    min-height: 140px;
    padding: 10px;
    border-radius: 8px 8px 0 0;
}
.master-preview.channel-sms { background: linear-gradient(135deg, #667eea, #764ba2); }
.master-preview.channel-whatsapp { background: #075e54; }
.master-preview.channel-email { background: linear-gradient(135deg, #2196F3, #21CBF3); }

.preview-header {
    display: flex;
    justify-content: space-between;
    color: white;
    font-size: 0.75rem;
    margin-bottom: 10px;
}
.preview-body {
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 8px;
}
.message-bubble {
    background: #dcf8c6;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 0.75rem;
    color: #333;
}
.badge-sm {
    font-size: 0.65rem;
    font-weight: normal;
}
</style>
@endpush
