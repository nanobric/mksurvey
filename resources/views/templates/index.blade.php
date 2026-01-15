@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de Templates</h3>
        <div class="card-tools">
            <a href="{{ route('templates.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Template
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Canal</th>
                    <th>Variables</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                <tr>
                    <td>{{ $template->id }}</td>
                    <td>{{ $template->name }}</td>
                    <td><code>{{ $template->code }}</code></td>
                    <td>
                        <span class="badge {{ $template->channel == 'whatsapp' ? 'badge-success' : ($template->channel == 'sms' ? 'badge-info' : 'badge-warning') }}">
                            {{ ucfirst($template->channel) }}
                        </span>
                    </td>
                    <td>{{ implode(', ', $template->variables ?? []) }}</td>
                    <td>
                        <a href="{{ route('templates.edit', $template) }}" class="btn btn-info btn-xs"><i class="fas fa-pencil-alt"></i></a>
                        <form action="{{ route('templates.destroy', $template) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('¿Seguro?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay templates registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $templates->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
