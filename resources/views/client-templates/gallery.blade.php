@extends('layouts.app')

@section('title', 'Elegir Template')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h4><i class="fas fa-palette"></i> Elige un Template</h4>
        <p class="text-muted">Selecciona un diseño y personalízalo para tu empresa</p>
    </div>
    <div class="col-md-4 text-right">
        <a href="{{ route('client-templates.my-templates', ['client_id' => $client->id]) }}" class="btn btn-outline-primary">
            <i class="fas fa-folder"></i> Mis Templates ({{ $clientTemplates->count() }})
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="btn-group">
            <a href="{{ route('client-templates.gallery', ['client_id' => $client->id]) }}" 
               class="btn btn-sm {{ !request('channel') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
            <a href="{{ route('client-templates.gallery', ['client_id' => $client->id, 'channel' => 'sms']) }}" 
               class="btn btn-sm {{ request('channel') == 'sms' ? 'btn-primary' : 'btn-outline-primary' }}">📱 SMS</a>
            <a href="{{ route('client-templates.gallery', ['client_id' => $client->id, 'channel' => 'whatsapp']) }}" 
               class="btn btn-sm {{ request('channel') == 'whatsapp' ? 'btn-primary' : 'btn-outline-primary' }}">💬 WhatsApp</a>
            <a href="{{ route('client-templates.gallery', ['client_id' => $client->id, 'channel' => 'email']) }}" 
               class="btn btn-sm {{ request('channel') == 'email' ? 'btn-primary' : 'btn-outline-primary' }}">📧 Email</a>
        </div>
    </div>
</div>

<!-- Grid de Masters -->
<div class="row">
    @foreach($masters as $master)
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="card h-100 master-card {{ $master->is_featured ? 'featured' : '' }}">
            @if($master->is_featured)
                <div class="featured-ribbon">⭐</div>
            @endif
            
            <!-- Preview -->
            <div class="master-preview channel-{{ $master->channel }}">
                <div class="preview-badge">
                    {{ $master->channel_icon }} {{ $master->category_icon }}
                </div>
                <div class="preview-content">
                    <div class="message-bubble">
                        {!! nl2br(e(Str::limit($master->content, 70))) !!}
                    </div>
                </div>
            </div>
            
            <div class="card-body text-center">
                <h6 class="card-title">{{ $master->name }}</h6>
                <p class="text-muted small mb-3">{{ Str::limit($master->description, 50) }}</p>
                
                <a href="{{ route('client-templates.customize', ['master' => $master, 'client_id' => $client->id]) }}" 
                   class="btn btn-primary btn-block">
                    <i class="fas fa-magic"></i> Personalizar
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('styles')
<style>
.master-card {
    border: none;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    overflow: hidden;
    cursor: pointer;
}
.master-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}
.master-card.featured {
    border: 2px solid #ffc107;
}
.featured-ribbon {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 1.2rem;
    z-index: 10;
}

.master-preview {
    min-height: 150px;
    padding: 15px;
    position: relative;
}
.master-preview.channel-sms { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
}
.master-preview.channel-whatsapp { 
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); 
}
.master-preview.channel-email { 
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); 
}

.preview-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255,255,255,0.2);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}
.preview-content {
    margin-top: 30px;
}
.message-bubble {
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 12px;
    font-size: 0.8rem;
    color: #333;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
@endpush
