@extends('layouts.app')

@section('title', 'Editar Template')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar Template: {{ $template->name }}</h3>
    </div>
    <form action="{{ route('templates.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}">
                @error('name') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="code">Código</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $template->code) }}">
                @error('code') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="channel">Canal</label>
                <select name="channel" class="form-control">
                    <option value="sms" {{ old('channel', $template->channel) == 'sms' ? 'selected' : '' }}>SMS</option>
                    <option value="whatsapp" {{ old('channel', $template->channel) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ old('channel', $template->channel) == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>

            <div class="form-group">
                <label for="variables">Variables (Separadas por coma)</label>
                <input type="text" name="variables" class="form-control" value="{{ old('variables', implode(', ', $template->variables ?? [])) }}">
            </div>

            <div class="form-group">
                <label for="content">Contenido</label>
                <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4">{{ old('content', $template->content) }}</textarea>
                @error('content') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('templates.index') }}" class="btn btn-default">Cancelar</a>
        </div>
    </form>
</div>
@endsection
