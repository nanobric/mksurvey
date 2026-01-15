@extends('layouts.app')

@section('title', 'Nuevo Cliente')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Registrar Nuevo Cliente</h3>
    </div>
    <form action="{{ route('admin.clients.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de la Empresa *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Razón Social</label>
                        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name') }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>RFC</label>
                        <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror" value="{{ old('rfc') }}">
                        @error('rfc') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Ciudad</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Código Postal</label>
                        <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Industria</label>
                        <input type="text" name="industry" class="form-control" value="{{ old('industry') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sitio Web</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website') }}" placeholder="https://">
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="status" class="form-control" required>
                            <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial (14 días)</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Plan</label>
                        <select name="plan_id" class="form-control">
                            <option value="">Sin plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - ${{ number_format($plan->price_monthly, 2) }}/mes
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Volumen Mensual Est.</label>
                        <select name="volume_tier" class="form-control" id="volumeTier">
                            <option value="">-- Seleccionar --</option>
                            <option value="small" {{ old('volume_tier') == 'small' ? 'selected' : '' }}>Pequeño (< 1,000)</option>
                            <option value="medium" {{ old('volume_tier') == 'medium' ? 'selected' : '' }}>Mediano (1,000 - 10,000)</option>
                            <option value="large" {{ old('volume_tier') == 'large' ? 'selected' : '' }}>Grande (10,000 - 100,000)</option>
                            <option value="enterprise" {{ old('volume_tier') == 'enterprise' ? 'selected' : '' }}>Enterprise (100,000+)</option>
                            <option value="custom" {{ old('volume_tier') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3" id="customVolumeWrapper" style="{{ old('volume_tier') == 'custom' ? '' : 'display: none;' }}">
                    <div class="form-group">
                        <label>Mensajes/Mes *</label>
                        <input type="number" name="expected_monthly_volume" id="expectedVolume" class="form-control" value="{{ old('expected_monthly_volume') }}" min="1" placeholder="Ej: 50000">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear Cliente</button>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-default">Cancelar</a>
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

