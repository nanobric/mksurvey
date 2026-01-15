@extends('layouts.app')

@section('title', isset($templateMaster) ? 'Editar Template Master' : 'Nuevo Template Master')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-palette"></i> 
                    {{ isset($templateMaster) ? 'Editar' : 'Crear' }} Template Master
                </h5>
            </div>
            <form action="{{ isset($templateMaster) ? route('admin.template-masters.update', $templateMaster) : route('admin.template-masters.store') }}" method="POST">
                @csrf
                @if(isset($templateMaster))
                    @method('PUT')
                @endif
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" 
                                       value="{{ old('name', $templateMaster->name ?? '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Canal <span class="text-danger">*</span></label>
                                <select name="channel" class="form-control" required>
                                    <option value="sms" {{ old('channel', $templateMaster->channel ?? '') == 'sms' ? 'selected' : '' }}>📱 SMS</option>
                                    <option value="whatsapp" {{ old('channel', $templateMaster->channel ?? '') == 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                                    <option value="email" {{ old('channel', $templateMaster->channel ?? '') == 'email' ? 'selected' : '' }}>📧 Email</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Categoría <span class="text-danger">*</span></label>
                                <select name="category" class="form-control" required>
                                    <option value="welcome" {{ old('category', $templateMaster->category ?? '') == 'welcome' ? 'selected' : '' }}>👋 Bienvenida</option>
                                    <option value="promo" {{ old('category', $templateMaster->category ?? '') == 'promo' ? 'selected' : '' }}>🎉 Promoción</option>
                                    <option value="reminder" {{ old('category', $templateMaster->category ?? '') == 'reminder' ? 'selected' : '' }}>📅 Recordatorio</option>
                                    <option value="survey" {{ old('category', $templateMaster->category ?? '') == 'survey' ? 'selected' : '' }}>⭐ Encuesta</option>
                                    <option value="otp" {{ old('category', $templateMaster->category ?? '') == 'otp' ? 'selected' : '' }}>🔐 OTP</option>
                                    <option value="transactional" {{ old('category', $templateMaster->category ?? '') == 'transactional' ? 'selected' : '' }}>📦 Transaccional</option>
                                    <option value="newsletter" {{ old('category', $templateMaster->category ?? '') == 'newsletter' ? 'selected' : '' }}>📰 Newsletter</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Opciones</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1"
                                           {{ old('is_featured', $templateMaster->is_featured ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_featured">⭐ Destacado</label>
                                </div>
                                @if(isset($templateMaster))
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $templateMaster->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Activo</label>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $templateMaster->description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Contenido del Mensaje <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-1">Usa @{{ variable }} para campos dinámicos</p>
                        <textarea name="content" id="contentField" class="form-control" rows="6" required 
                                  oninput="detectVariables()">{{ old('content', $templateMaster->content ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Campos Editables por Cliente <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-1">Separados por coma: empresa, mensaje, logo</p>
                        <input type="text" id="editableFields" class="form-control" 
                               value="{{ old('editable_fields', isset($templateMaster) ? implode(', ', $templateMaster->editable_fields ?? []) : '') }}">
                        <input type="hidden" name="editable_fields[]" id="editableFieldsHidden">
                    </div>

                    <div class="alert alert-info" id="variablesDetected">
                        <strong><i class="fas fa-code"></i> Variables detectadas:</strong>
                        <span id="variablesList">Ninguna aún</span>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="{{ route('admin.template-masters.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="fas fa-mobile-alt"></i> Preview</h6>
            </div>
            <div class="card-body p-0">
                <div class="phone-preview">
                    <div class="phone-header">💬 Preview</div>
                    <div class="phone-body" id="previewBody">
                        <div class="message-bubble" id="previewMessage">
                            Escribe contenido para ver preview...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.phone-preview {
    background: #1a1a2e;
    padding: 15px;
}
.phone-header {
    background: #075e54;
    color: white;
    padding: 10px;
    border-radius: 10px 10px 0 0;
}
.phone-body {
    background: #e5ddd5;
    min-height: 250px;
    padding: 15px;
    border-radius: 0 0 10px 10px;
}
.message-bubble {
    background: #dcf8c6;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.85rem;
    white-space: pre-wrap;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    detectVariables();
    
    document.getElementById('contentField').addEventListener('input', function() {
        detectVariables();
        updatePreview();
    });
});

function detectVariables() {
    const content = document.getElementById('contentField').value;
    const regex = /\{\{(\w+)\}\}/g;
    let matches = [];
    let match;
    
    while ((match = regex.exec(content)) !== null) {
        matches.push(match[1]);
    }
    
    const unique = [...new Set(matches)];
    document.getElementById('variablesList').innerHTML = unique.length > 0 
        ? unique.map(v => `<span class="badge badge-warning mx-1">@{{ ${v} }}</span>`).join('') 
        : 'Ninguna aún';
    
    updatePreview();
}

function updatePreview() {
    let content = document.getElementById('contentField').value;
    content = content.replace(/\{\{(\w+)\}\}/g, '<span class="badge badge-warning">{{$1}}</span>');
    document.getElementById('previewMessage').innerHTML = content.replace(/\n/g, '<br>') || 'Escribe contenido...';
}

document.querySelector('form').addEventListener('submit', function() {
    const fields = document.getElementById('editableFields').value
        .split(',')
        .map(f => f.trim())
        .filter(f => f);
    
    document.getElementById('editableFieldsHidden').name = 'editable_fields';
    document.getElementById('editableFieldsHidden').remove();
    
    fields.forEach(field => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'editable_fields[]';
        input.value = field;
        this.appendChild(input);
    });
});
</script>
@endpush
