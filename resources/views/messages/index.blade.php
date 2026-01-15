@extends('layouts.app')

@section('title', 'Mensajes')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Historial de Mensajes</h3>
        <div class="card-tools">
            <a href="{{ route('messages.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-paper-plane"></i> Enviar Nuevo
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Destinatario</th>
                    <th>Canal</th>
                    <th>Estado</th>
                    <th>Fecha Envío</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                <tr>
                    <td>{{ $message->id }}</td>
                    <td>{{ $message->recipient }}</td>
                    <td>
                         <span class="badge {{ $message->channel == 'whatsapp' ? 'badge-success' : ($message->channel == 'sms' ? 'badge-info' : 'badge-warning') }}">
                            {{ ucfirst($message->channel) }}
                        </span>
                    </td>
                    <td>
                        @if($message->status == 'sent')
                            <span class="badge badge-success">Enviado</span>
                        @elseif($message->status == 'failed')
                            <span class="badge badge-danger">Falló</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($message->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $message->sent_at ? $message->sent_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <a href="{{ route('messages.show', $message) }}" class="btn btn-default btn-xs"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay mensajes registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $messages->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
