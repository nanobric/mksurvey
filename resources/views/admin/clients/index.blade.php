@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Clientes</h3>
        <div class="card-tools">
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empresa</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td>{{ $client->id }}</td>
                    <td>
                        <a href="{{ route('admin.clients.show', $client) }}">
                            <strong>{{ $client->name }}</strong>
                        </a>
                    </td>
                    <td>{{ $client->email }}</td>
                    <td>
                        @if($client->activeSubscription)
                            <span class="badge badge-info">{{ $client->activeSubscription->plan->name }}</span>
                        @else
                            <span class="badge badge-secondary">Sin plan</span>
                        @endif
                    </td>
                    <td>
                        @switch($client->status)
                            @case('active')
                                <span class="badge badge-success">Activo</span>
                                @break
                            @case('trial')
                                <span class="badge badge-warning">Trial</span>
                                @break
                            @case('suspended')
                                <span class="badge badge-danger">Suspendido</span>
                                @break
                            @default
                                <span class="badge badge-secondary">{{ $client->status }}</span>
                        @endswitch
                    </td>
                    <td>{{ $client->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No hay clientes registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $clients->links() }}
    </div>
</div>
@endsection
