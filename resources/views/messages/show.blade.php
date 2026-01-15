@extends('layouts.app')

@section('title', 'Detalle Mensaje')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mensaje #{{ $message->id }}</h3>
        <div class="card-tools">
            <a href="{{ route('messages.index') }}" class="btn btn-default btn-sm">Regresar</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Fecha de Envío</dt>
            <dd class="col-sm-9">{{ $message->sent_at ? $message->sent_at->format('d/m/Y H:i:s') : 'Pendiente/Fallido' }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                {{ ucfirst($message->status) }} 
                @if($message->error_message)
                    <br><span class="text-danger small">{{ $message->error_message }}</span>
                @endif
            </dd>

            <dt class="col-sm-3">Canal</dt>
            <dd class="col-sm-9">{{ ucfirst($message->channel) }}</dd>

            <dt class="col-sm-3">Destinatario</dt>
            <dd class="col-sm-9">{{ $message->recipient }}</dd>
            
            @if($message->twilio_sid)
            <dt class="col-sm-3">SID Externo</dt>
            <dd class="col-sm-9"><code>{{ $message->twilio_sid }}</code></dd>
            @endif

            <dt class="col-sm-3">Contenido</dt>
            <dd class="col-sm-9">
                <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">{{ $message->content }}</div>
            </dd>
        </dl>
    </div>
</div>
@endsection
