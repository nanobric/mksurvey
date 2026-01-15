@extends('layouts.app')

@section('title', 'Enviar Mensaje')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Mensaje</h3>
    </div>
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="recipient">Destinatario</label>
                <input type="text" name="recipient" class="form-control @error('recipient') is-invalid @enderror" value="{{ old('recipient') }}" placeholder="+52... o email@ejemplo.com">
                <small class="form-text text-muted">Incluir código de país para WhatsApp/SMS (ej: +521...)</small>
                @error('recipient') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="channel">Canal</label>
                <select name="channel" id="channelSelect" class="form-control">
                    <option value="sms">SMS</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>

            <div class="form-group">
                <label for="template_id">Cargar Template (Opcional)</label>
                <select name="template_id" id="templateSelect" class="form-control">
                    <option value="">-- Seleccionar --</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" data-content="{{ $template->content }}" data-channel="{{ $template->channel }}">
                            {{ $template->name }} ({{ $template->channel }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="content">Contenido del Mensaje</label>
                <textarea name="content" id="messageContent" class="form-control @error('content') is-invalid @enderror" rows="5">{{ old('content') }}</textarea>
                @error('content') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enviar</button>
            <a href="{{ route('messages.index') }}" class="btn btn-default">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('templateSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var content = selectedOption.getAttribute('data-content');
        var channel = selectedOption.getAttribute('data-channel');
        
        if (content) {
            document.getElementById('messageContent').value = content;
        }
        
        if (channel) {
            document.getElementById('channelSelect').value = channel;
        }
    });
</script>
@endpush
@endsection
