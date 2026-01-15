@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar: {{ $client->name }}</h3>
    </div>
    <form action="{{ route('admin.clients.update', $client) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de la Empresa *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $client->name) }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Razón Social</label>
                        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name', $client->legal_name) }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>RFC</label>
                        <input type="text" name="rfc" class="form-control" value="{{ old('rfc', $client->rfc) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $client->email) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $client->address) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Ciudad</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $client->city) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $client->state) }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="status" class="form-control" required>
                            <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="trial" {{ old('status', $client->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            <option value="suspended" {{ old('status', $client->status) == 'suspended' ? 'selected' : '' }}>Suspendido</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Volumen Mensual Est.</label>
                        <select name="volume_tier" class="form-control" id="volumeTier">
                            <option value="">-- Seleccionar --</option>
                            <option value="small" {{ old('volume_tier', $client->volume_tier) == 'small' ? 'selected' : '' }}>Pequeño (< 1,000)</option>
                            <option value="medium" {{ old('volume_tier', $client->volume_tier) == 'medium' ? 'selected' : '' }}>Mediano (1,000 - 10,000)</option>
                            <option value="large" {{ old('volume_tier', $client->volume_tier) == 'large' ? 'selected' : '' }}>Grande (10,000 - 100,000)</option>
                            <option value="enterprise" {{ old('volume_tier', $client->volume_tier) == 'enterprise' ? 'selected' : '' }}>Enterprise (100,000+)</option>
                            <option value="custom" {{ old('volume_tier', $client->volume_tier) == 'custom' ? 'selected' : '' }}>Personalizado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3" id="customVolumeWrapper" style="{{ old('volume_tier', $client->volume_tier) == 'custom' ? '' : 'display: none;' }}">
                    <div class="form-group">
                        <label>Mensajes/Mes *</label>
                        <input type="number" name="expected_monthly_volume" id="expectedVolume" class="form-control" value="{{ old('expected_monthly_volume', $client->expected_monthly_volume) }}" min="1" placeholder="Ej: 50000">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $client->notes) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-default">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('volumeTier').addEventListener('change', function() {
    const wrapper = document.getElementById('customVolumeWrapper');
    const input = document.getElementById('expectedVolume');
    if (this.value === 'custom') {
        wrapper.style.display = 'block';
        input.required = true;
    } else {
        wrapper.style.display = 'none';
        input.required = false;
        input.value = '';
    }
});
</script>
@endpush

