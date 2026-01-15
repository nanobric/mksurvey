@extends('layouts.app')

@section('title', 'Crear Template')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Template</h3>
    </div>
    <form action="{{ route('templates.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Bienvenida Cliente">
                @error('name') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="code">Código (Único para API)</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Ej: WELCOME_01">
                @error('code') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="channel">Canal</label>
                <select name="channel" class="form-control">
                    <option value="sms" {{ old('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                    <option value="whatsapp" {{ old('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ old('channel') == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>

            <div class="form-group">
                <label for="variables">Variables (Separadas por coma)</label>
                <input type="text" name="variables" class="form-control" value="{{ old('variables') }}" placeholder="Ej: name, url, date">
            </div>

            <div class="form-group">
                <label for="content">Contenido</label>
                <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4" placeholder="Hola {name}, bienvenido...">{{ old('content') }}</textarea>
                @error('content') <span class="error invalid-feedback">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('templates.index') }}" class="btn btn-default">Cancelar</a>
        </div>
    </form>
</div>
@endsection
