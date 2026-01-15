@extends('layouts.app')

@section('title', isset($plan) ? 'Editar Plan' : 'Nuevo Plan')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ isset($plan) ? 'Editar Plan: ' . $plan->name : 'Crear Nuevo Plan' }}</h3>
    </div>
    <form action="{{ isset($plan) ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" method="POST">
        @csrf
        @if(isset($plan)) @method('PUT') @endif
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre del Plan *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $plan->name ?? '') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $plan->description ?? '') }}">
                    </div>
                </div>
            </div>

            <h5 class="mt-4">Límites Mensuales</h5>
            <small class="text-muted">Usa 0 para ilimitado</small>
            <hr>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-sms text-primary"></i> SMS/mes *</label>
                        <input type="number" name="monthly_sms_limit" class="form-control" value="{{ old('monthly_sms_limit', $plan->monthly_sms_limit ?? 1000) }}" min="0" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp text-success"></i> WhatsApp/mes *</label>
                        <input type="number" name="monthly_whatsapp_limit" class="form-control" value="{{ old('monthly_whatsapp_limit', $plan->monthly_whatsapp_limit ?? 1000) }}" min="0" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-envelope text-info"></i> Email/mes *</label>
                        <input type="number" name="monthly_email_limit" class="form-control" value="{{ old('monthly_email_limit', $plan->monthly_email_limit ?? 5000) }}" min="0" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-bullhorn text-warning"></i> Campañas/mes *</label>
                        <input type="number" name="max_campaigns_per_month" class="form-control" value="{{ old('max_campaigns_per_month', $plan->max_campaigns_per_month ?? 10) }}" min="0" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-users text-secondary"></i> Recipients por campaña *</label>
                        <input type="number" name="max_recipients_per_campaign" class="form-control" value="{{ old('max_recipients_per_campaign', $plan->max_recipients_per_campaign ?? 10000) }}" min="0" required>
                    </div>
                </div>
            </div>

            <h5 class="mt-4">Precios</h5>
            <hr>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Precio Mensual *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="0.01" name="price_monthly" class="form-control" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Precio Anual</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="0.01" name="price_yearly" class="form-control" value="{{ old('price_yearly', $plan->price_yearly ?? 0) }}" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Moneda *</label>
                        <select name="currency" class="form-control" required>
                            <option value="MXN" {{ old('currency', $plan->currency ?? 'MXN') == 'MXN' ? 'selected' : '' }}>MXN</option>
                            <option value="USD" {{ old('currency', $plan->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">Plan Activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">{{ isset($plan) ? 'Guardar Cambios' : 'Crear Plan' }}</button>
            <a href="{{ route('admin.plans.index') }}" class="btn btn-default">Cancelar</a>
        </div>
    </form>
</div>
@endsection
