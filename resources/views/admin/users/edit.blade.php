@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Avatar Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                <img id="avatarPreview" 
                     class="profile-user-img img-fluid img-circle" 
                     src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('adminlte/dist/img/user2-160x160.jpg') }}" 
                     alt="Avatar"
                     style="width: 150px; height: 150px; object-fit: cover;">
                
                <h3 class="profile-username mt-3">{{ $user->name }}</h3>
                <p class="text-muted">{{ $user->email }}</p>

                <!-- Botones de Avatar -->
                <div class="btn-group mt-3">
                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-upload"></i> Subir
                    </button>
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#cameraModal">
                        <i class="fas fa-camera"></i> Cámara
                    </button>
                </div>

                <!-- Form oculto para subir archivo -->
                <form id="avatarForm" action="{{ route('admin.users.avatar', $user) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="this.form.submit()">
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Datos Usuario -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Información del Usuario</h3>
            </div>
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Cambiar Contraseña</h3>
            </div>
            <form action="{{ route('admin.users.password', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cámara -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tomar Foto</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <video id="cameraVideo" width="400" height="300" autoplay style="display: none; border-radius: 8px;"></video>
                <canvas id="cameraCanvas" width="400" height="300" style="display: none; border-radius: 8px;"></canvas>
                <div id="cameraPlaceholder" class="p-5 bg-secondary text-white" style="border-radius: 8px;">
                    <i class="fas fa-camera fa-3x mb-3"></i>
                    <p>Haz clic en "Iniciar Cámara" para comenzar</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="startCameraBtn">
                    <i class="fas fa-video"></i> Iniciar Cámara
                </button>
                <button type="button" class="btn btn-success" id="captureBtn" style="display: none;">
                    <i class="fas fa-camera"></i> Capturar
                </button>
                <button type="button" class="btn btn-warning" id="retakeBtn" style="display: none;">
                    <i class="fas fa-redo"></i> Volver a Tomar
                </button>
                <button type="button" class="btn btn-info" id="savePhotoBtn" style="display: none;">
                    <i class="fas fa-save"></i> Guardar Foto
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const placeholder = document.getElementById('cameraPlaceholder');
    const startBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    let stream = null;

    // Iniciar cámara
    startBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
            startBtn.style.display = 'none';
            captureBtn.style.display = 'inline-block';
        } catch (err) {
            alert('No se pudo acceder a la cámara: ' + err.message);
        }
    });

    // Capturar foto
    captureBtn.addEventListener('click', function() {
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        video.style.display = 'none';
        canvas.style.display = 'block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        saveBtn.style.display = 'inline-block';
        
        // Detener stream
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });

    // Volver a tomar
    retakeBtn.addEventListener('click', async function() {
        canvas.style.display = 'none';
        retakeBtn.style.display = 'none';
        saveBtn.style.display = 'none';
        
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = 'block';
        captureBtn.style.display = 'inline-block';
    });

    // Guardar foto
    saveBtn.addEventListener('click', function() {
        const imageData = canvas.toDataURL('image/png');
        
        fetch('{{ route('admin.users.capture-avatar', $user) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ avatar_data: imageData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('avatarPreview').src = data.avatar_url;
                $('#cameraModal').modal('hide');
            }
        })
        .catch(err => alert('Error al guardar: ' + err));
    });

    // Limpiar al cerrar modal
    $('#cameraModal').on('hidden.bs.modal', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        video.style.display = 'none';
        canvas.style.display = 'none';
        placeholder.style.display = 'block';
        startBtn.style.display = 'inline-block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'none';
        saveBtn.style.display = 'none';
    });
});
</script>
@endpush
