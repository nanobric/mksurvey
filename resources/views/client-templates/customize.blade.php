@extends('layouts.app')

@section('title', 'Personalizar Template')

@section('content')
<form id="customizeForm" action="{{ isset($clientTemplate) ? route('client-templates.update', $clientTemplate) : route('client-templates.store') }}" method="POST">
    @csrf
    @if(isset($clientTemplate))
        @method('PUT')
    @endif
    <input type="hidden" name="client_id" value="{{ $client->id }}">
    <input type="hidden" name="master_id" value="{{ $master->id }}">

    <div class="row mb-3">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('client-templates.gallery', ['client_id' => $client->id]) }}">Templates</a></li>
                    <li class="breadcrumb-item active">{{ $master->name }}</li>
                </ol>
            </nav>
            <h4>
                {{ $master->channel_icon }} Personaliza: {{ $master->name }}
            </h4>
        </div>
        <div class="col-md-4 text-right">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar Mi Template
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Formulario de personalización -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-edit"></i> Personaliza tu mensaje</h6>
                </div>
                <div class="card-body">
                    <!-- Nombre del template -->
                    <div class="form-group">
                        <label><strong>Nombre de tu template</strong></label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $clientTemplate->name ?? $master->name . ' - ' . $client->name) }}" required>
                    </div>

                    <hr>
                    <h6 class="text-muted"><i class="fas fa-sliders-h"></i> Campos Personalizables</h6>

                    @foreach($master->editable_fields ?? [] as $field)
                    <div class="form-group">
                        <label><strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong></label>
                        @if(in_array($field, ['mensaje', 'descripcion', 'contenido']))
                            <textarea name="customizations[{{ $field }}]" class="form-control customization-field" 
                                      rows="3" data-field="{{ $field }}"
                                      placeholder="Escribe tu {{ $field }}...">{{ old("customizations.$field", $clientTemplate->customizations[$field] ?? '') }}</textarea>
                        @elseif(in_array($field, ['imagen', 'logo']))
                            <input type="url" name="customizations[{{ $field }}]" class="form-control customization-field" 
                                   data-field="{{ $field }}"
                                   placeholder="https://ejemplo.com/imagen.jpg"
                                   value="{{ old("customizations.$field", $clientTemplate->customizations[$field] ?? '') }}">
                            <small class="text-muted">URL de la imagen</small>
                        @else
                            <input type="text" name="customizations[{{ $field }}]" class="form-control customization-field" 
                                   data-field="{{ $field }}"
                                   placeholder="Tu {{ $field }}"
                                   value="{{ old("customizations.$field", $clientTemplate->customizations[$field] ?? ($field == 'empresa' ? $client->name : '')) }}">
                        @endif
                    </div>
                    @endforeach

                    <!-- Variables automáticas -->
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle"></i> Variables automáticas</h6>
                        <p class="small mb-0">Estas variables se llenarán automáticamente al enviar:</p>
                        <div class="mt-2">
                            @foreach($master->variables ?? [] as $var)
                                @if(!in_array($var, $master->editable_fields ?? []))
                                    <span class="badge badge-secondary">@{{ $var }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview -->
        <div class="col-md-5">
            <div class="preview-container">
                <div class="phone-frame">
                    <div class="phone-notch"></div>
                    <div class="phone-header">
                        {{ $master->channel_icon }} {{ $client->name }}
                    </div>
                    <div class="phone-body channel-{{ $master->channel }}">
                        <div class="message-bubble outgoing" id="previewMessage">
                            {!! nl2br(e($master->content)) !!}
                        </div>
                        <div class="message-time">{{ now()->format('H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body text-center">
                    <small class="text-muted">El preview se actualiza mientras escribes</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
.preview-container {
    perspective: 1000px;
}
.phone-frame {
    background: #1a1a2e;
    border-radius: 30px;
    padding: 10px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}
.phone-notch {
    width: 120px;
    height: 25px;
    background: #000;
    border-radius: 0 0 15px 15px;
    margin: 0 auto 10px;
}
.phone-header {
    background: linear-gradient(135deg, #075e54, #128c7e);
    color: white;
    padding: 12px 15px;
    border-radius: 15px 15px 0 0;
    font-weight: 500;
}
.phone-body {
    background: #e5ddd5;
    min-height: 350px;
    padding: 15px;
    border-radius: 0 0 15px 15px;
}
.phone-body.channel-sms { background: #f0f0f0; }
.phone-body.channel-email { background: #fafafa; }

.message-bubble.outgoing {
    background: #dcf8c6;
    border-radius: 12px;
    padding: 12px 15px;
    max-width: 90%;
    margin-left: auto;
    font-size: 0.9rem;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.message-time {
    text-align: right;
    font-size: 0.7rem;
    color: #667781;
    margin-top: 5px;
}
.customization-field {
    border-left: 3px solid #007bff;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const originalContent = @json($master->content);
    const previewEl = document.getElementById('previewMessage');
    
    // Actualizar preview cuando cambie cualquier campo
    document.querySelectorAll('.customization-field').forEach(input => {
        input.addEventListener('input', updatePreview);
    });
    
    function updatePreview() {
        let content = originalContent;
        
        document.querySelectorAll('.customization-field').forEach(input => {
            const field = input.dataset.field;
            const value = input.value || `{{${field}}}`;
            const regex = new RegExp(`\\{\\{${field}\\}\\}`, 'g');
            content = content.replace(regex, value);
        });
        
        // Variables no editables se muestran como badges
        content = content.replace(/\{\{(\w+)\}\}/g, '<span class="badge badge-warning">{{$1}}</span>');
        
        previewEl.innerHTML = content.replace(/\n/g, '<br>');
    }
    
    // Ejecutar al cargar
    updatePreview();
});
</script>
@endpush
