@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h4><i class="fas fa-file-alt"></i> Templates de Mensajes</h4>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{ route('templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Template
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="btn-group">
            <a href="{{ route('templates.index') }}" class="btn btn-sm {{ !request('channel') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
            <a href="{{ route('templates.index', ['channel' => 'sms']) }}" class="btn btn-sm {{ request('channel') == 'sms' ? 'btn-primary' : 'btn-outline-primary' }}">📱 SMS</a>
            <a href="{{ route('templates.index', ['channel' => 'whatsapp']) }}" class="btn btn-sm {{ request('channel') == 'whatsapp' ? 'btn-primary' : 'btn-outline-primary' }}">💬 WhatsApp</a>
            <a href="{{ route('templates.index', ['channel' => 'email']) }}" class="btn btn-sm {{ request('channel') == 'email' ? 'btn-primary' : 'btn-outline-primary' }}">📧 Email</a>
        </div>
    </div>
</div>

<!-- Grid de Templates -->
<div class="row">
    @forelse($templates as $template)
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="card h-100 template-card">
            <!-- Preview del mensaje estilo WhatsApp -->
            <div class="card-img-top template-preview">
                <div class="message-bubble">
                    @if($template->media_url)
                        <img src="{{ $template->media_url }}" class="img-fluid rounded mb-2" alt="Media">
                    @endif
                    <div class="message-text">
                        {!! nl2br(e(Str::limit($template->content, 100))) !!}
                    </div>
                    <div class="message-time">12:10</div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">{{ $template->name }}</h6>
                    @switch($template->channel)
                        @case('sms')
                            <span class="badge badge-info">📱 SMS</span>
                            @break
                        @case('whatsapp')
                            <span class="badge badge-success">💬 WA</span>
                            @break
                        @case('email')
                            <span class="badge badge-primary">📧 Email</span>
                            @break
                    @endswitch
                </div>
                
                @if($template->variables && count($template->variables) > 0)
                <div class="mb-2">
                    @foreach(array_slice($template->variables, 0, 3) as $var)
                        <span class="badge badge-warning badge-sm">@{{ $var }}</span>
                    @endforeach
                    @if(count($template->variables) > 3)
                        <span class="text-muted small">+{{ count($template->variables) - 3 }}</span>
                    @endif
                </div>
                @endif
            </div>
            
            <div class="card-footer bg-white border-top-0">
                <div class="btn-group btn-group-sm w-100">
                    <a href="{{ route('templates.edit', $template) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('templates.show', $template) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i>
                    </a>
                    <form action="{{ route('templates.destroy', $template) }}" method="POST" class="d-inline">
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
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5>No hay templates</h5>
                <p class="text-muted">Crea tu primer template para enviar mensajes</p>
                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Template
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

{{ $templates->links() }}
@endsection

@push('styles')
<style>
.template-card {
    transition: all 0.2s;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.template-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.template-preview {
    background: #e5ddd5;
    min-height: 160px;
    padding: 15px;
    border-radius: 8px 8px 0 0;
}
.message-bubble {
    background: #dcf8c6;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 0.8rem;
    position: relative;
}
.message-bubble img {
    max-width: 100%;
    border-radius: 6px;
}
.message-time {
    text-align: right;
    font-size: 0.65rem;
    color: #667781;
    margin-top: 4px;
}
.badge-sm {
    font-size: 0.65rem;
    font-weight: normal;
}
</style>
@endpush
