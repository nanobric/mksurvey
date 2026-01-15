@extends('layouts.app')

@section('title', 'Planes')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Planes de Suscripción</h3>
        <div class="card-tools">
            <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Plan
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($plans as $plan)
            <div class="col-md-4">
                <div class="card card-outline {{ $plan->is_active ? 'card-primary' : 'card-secondary' }}">
                    <div class="card-header">
                        <h3 class="card-title">{{ $plan->name }}</h3>
                        @if(!$plan->is_active)
                            <span class="badge badge-secondary float-right">Inactivo</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <h2 class="text-center mb-3">
                            ${{ number_format($plan->price_monthly, 2) }}
                            <small class="text-muted">/mes</small>
                        </h2>
                        
                        <ul class="list-unstyled">
                            <li><i class="fas fa-sms text-primary"></i> {{ $plan->monthly_sms_limit ?: '∞' }} SMS/mes</li>
                            <li><i class="fab fa-whatsapp text-success"></i> {{ $plan->monthly_whatsapp_limit ?: '∞' }} WhatsApp/mes</li>
                            <li><i class="fas fa-envelope text-info"></i> {{ $plan->monthly_email_limit ?: '∞' }} Emails/mes</li>
                            <li><i class="fas fa-bullhorn text-warning"></i> {{ $plan->max_campaigns_per_month ?: '∞' }} Campañas/mes</li>
                            <li><i class="fas fa-users text-secondary"></i> {{ $plan->max_recipients_per_campaign ?: '∞' }} Recipients/campaña</li>
                        </ul>

                        <p class="text-muted small">{{ $plan->description }}</p>
                        
                        <div class="text-center">
                            <span class="badge badge-info">{{ $plan->active_subscriptions_count }} suscripciones activas</span>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        @if($plan->active_subscriptions_count == 0)
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este plan?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p class="text-muted">No hay planes configurados.</p>
                <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">Crear primer plan</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
