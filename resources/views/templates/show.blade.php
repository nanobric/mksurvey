@extends('layouts.app')

@section('title', $template->name)

@section('content')
<div class="row">
    <!-- Info y Acciones -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $template->name }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Canal:</strong>
                        @switch($template->channel)
                            @case('sms')<span class="badge badge-info">📱 SMS</span>@break
                            @case('whatsapp')<span class="badge badge-success">💬 WhatsApp</span>@break
                            @case('email')<span class="badge badge-primary">📧 Email</span>@break
                        @endswitch
                    </li>
                    <li class="mb-2">
                        <strong>Código:</strong> <code>{{ $template->code }}</code>
                    </li>
                    <li class="mb-2">
                        <strong>Estado:</strong>
                        @if($template->status == 'active')
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">{{ $template->status }}</span>
                        @endif
                    </li>
                </ul>

                @if($template->description)
                <p class="text-muted small">{{ $template->description }}</p>
                @endif

                <div class="btn-group w-100">
                    <a href="{{ route('templates.edit', $template) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('templates.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Variables -->
        @if($template->variables && count($template->variables) > 0)
        <div class="card mt-3">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="fas fa-code"></i> Variables</h6>
            </div>
            <div class="card-body">
                @foreach($template->variables as $var)
                    <span class="badge badge-light border mb-1">{{ "{{" . $var . "}}" }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Preview -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-mobile-alt"></i> Preview del Mensaje</h6>
            </div>
            <div class="card-body p-0">
                <!-- Phone Frame -->
                <div class="phone-container">
                    <div class="phone-frame">
                        <div class="phone-header">
                            <i class="fab fa-whatsapp"></i> Mi Empresa
                        </div>
                        <div class="phone-body">
                            <div class="message-bubble outgoing">
                                @if($template->media_url)
                                    <img src="{{ $template->media_url }}" class="msg-image" alt="Media">
                                @endif
                                <div class="msg-text">{!! nl2br(e($template->content)) !!}</div>
                                <div class="msg-time">{{ now()->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Raw -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-code"></i> Contenido</h6>
            </div>
            <div class="card-body">
                <pre class="bg-dark text-light p-3 rounded"><code>{{ $template->content }}</code></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.phone-container {
    display: flex;
    justify-content: center;
    padding: 20px;
    background: #1a1a2e;
}
.phone-frame {
    width: 320px;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}
.phone-header {
    background: #075e54;
    color: white;
    padding: 12px 15px;
    font-weight: 500;
}
.phone-body {
    background: #e5ddd5;
    min-height: 350px;
    padding: 15px;
}
.message-bubble.outgoing {
    background: #dcf8c6;
    border-radius: 10px;
    padding: 8px 12px;
    max-width: 85%;
    margin-left: auto;
}
.msg-image {
    max-width: 100%;
    border-radius: 8px;
    margin-bottom: 8px;
}
.msg-text {
    font-size: 0.9rem;
    line-height: 1.4;
}
.msg-time {
    text-align: right;
    font-size: 0.7rem;
    color: #667781;
    margin-top: 4px;
}
</style>
@endpush
