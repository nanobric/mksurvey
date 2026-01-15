@extends('layouts.app')

@section('title', isset($template) ? 'Editar Template' : 'Crear Template')

@push('styles')
<style>
/* Builder Layout */
.builder-container {
    display: grid;
    grid-template-columns: 250px 1fr 350px;
    gap: 20px;
    min-height: 70vh;
}

/* Components Panel */
.components-panel {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}
.component-item {
    background: white;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    cursor: grab;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 10px;
}
.component-item:hover {
    border-color: #007bff;
    background: #e7f1ff;
}
.component-item i {
    font-size: 1.5rem;
    width: 30px;
    text-align: center;
}

/* Canvas */
.canvas-area {
    background: #fff;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    min-height: 400px;
    padding: 20px;
}
.canvas-drop-zone {
    min-height: 300px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    background: #fafafa;
}
.canvas-drop-zone.drag-over {
    border-color: #007bff;
    background: #e7f1ff;
}
.canvas-drop-zone:empty::before {
    content: '🎨 Arrastra componentes aquí';
    display: block;
    text-align: center;
    color: #adb5bd;
    padding: 50px;
    font-size: 1.1rem;
}

/* Canvas Items */
.canvas-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    position: relative;
    cursor: move;
}
.canvas-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.canvas-item .item-actions {
    position: absolute;
    top: 5px;
    right: 5px;
    display: none;
}
.canvas-item:hover .item-actions {
    display: block;
}
.canvas-item .item-actions button {
    padding: 2px 6px;
    font-size: 0.75rem;
}

/* Preview Panel */
.preview-panel {
    background: #1a1a2e;
    border-radius: 20px;
    padding: 15px;
}
.phone-frame {
    background: white;
    border-radius: 15px;
    overflow: hidden;
}
.phone-header {
    background: #075e54;
    color: white;
    padding: 10px 15px;
    font-size: 0.9rem;
}
.phone-body {
    min-height: 400px;
    padding: 15px;
    background: #e5ddd5;
}
.message-bubble {
    background: #dcf8c6;
    border-radius: 10px;
    padding: 10px 15px;
    margin-bottom: 10px;
    max-width: 85%;
    margin-left: auto;
    word-wrap: break-word;
}
.message-bubble.incoming {
    background: white;
    margin-left: 0;
    margin-right: auto;
}
.message-bubble img {
    max-width: 100%;
    border-radius: 8px;
    margin-bottom: 8px;
}

/* Variables Panel */
.variables-panel {
    background: #fff3cd;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
}
.variable-input {
    margin-bottom: 10px;
}
.variable-input label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #856404;
}
</style>
@endpush

@section('content')
<form id="templateForm" action="{{ isset($template) ? route('templates.update', $template) : route('templates.store') }}" method="POST">
    @csrf
    @if(isset($template))
        @method('PUT')
    @endif

    <!-- Header -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control form-control-lg" 
                           placeholder="Nombre del template" 
                           value="{{ old('name', $template->name ?? '') }}" required>
                </div>
                <div class="col-md-3">
                    <select name="channel" id="channelSelect" class="form-control" required>
                        <option value="sms" {{ (old('channel', $template->channel ?? '') == 'sms') ? 'selected' : '' }}>📱 SMS</option>
                        <option value="whatsapp" {{ (old('channel', $template->channel ?? '') == 'whatsapp') ? 'selected' : '' }}>💬 WhatsApp</option>
                        <option value="email" {{ (old('channel', $template->channel ?? '') == 'email') ? 'selected' : '' }}>📧 Email</option>
                    </select>
                </div>
                <div class="col-md-5 text-right">
                    <a href="{{ route('templates.index') }}" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Template
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Builder Grid -->
    <div class="builder-container">
        <!-- Components Panel -->
        <div class="components-panel">
            <h6 class="text-muted mb-3">COMPONENTES</h6>
            
            <div id="componentsList">
                <div class="component-item" data-type="text">
                    <i class="fas fa-paragraph text-primary"></i>
                    <span>Texto</span>
                </div>
                <div class="component-item" data-type="image">
                    <i class="fas fa-image text-success"></i>
                    <span>Imagen</span>
                </div>
                <div class="component-item" data-type="button" data-channel="whatsapp">
                    <i class="fas fa-mouse-pointer text-info"></i>
                    <span>Botón</span>
                </div>
                <div class="component-item" data-type="variable">
                    <i class="fas fa-code text-warning"></i>
                    <span>Variable</span>
                </div>
                <div class="component-item" data-type="separator">
                    <i class="fas fa-minus text-secondary"></i>
                    <span>Separador</span>
                </div>
                <div class="component-item" data-type="emoji">
                    <i class="fas fa-smile text-danger"></i>
                    <span>Emoji</span>
                </div>
            </div>

            <!-- Variables detectadas -->
            <div class="variables-panel" id="variablesPanel" style="display: none;">
                <h6><i class="fas fa-code"></i> Variables Detectadas</h6>
                <div id="variablesList"></div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="canvas-area">
            <div class="mb-3">
                <textarea name="description" class="form-control" rows="2" 
                          placeholder="Descripción del template (opcional)">{{ old('description', $template->description ?? '') }}</textarea>
            </div>

            <h6 class="text-muted mb-2">CONTENIDO DEL MENSAJE</h6>
            <div class="canvas-drop-zone" id="canvasDropZone">
                <!-- Los componentes arrastrados aparecen aquí -->
            </div>

            <!-- Content hidden field -->
            <input type="hidden" name="content" id="contentField">
            <input type="hidden" name="components" id="componentsField">
        </div>

        <!-- Preview Panel -->
        <div>
            <div class="preview-panel">
                <div class="phone-frame">
                    <div class="phone-header">
                        <i class="fab fa-whatsapp"></i> Preview
                    </div>
                    <div class="phone-body" id="previewBody">
                        <div class="message-bubble" id="previewMessage">
                            <span class="text-muted">El preview aparecerá aquí...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Variables -->
            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-flask"></i> Probar Variables</h6>
                </div>
                <div class="card-body" id="testVariablesPanel">
                    <p class="text-muted small">Las variables aparecerán aquí cuando las agregues al mensaje.</p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('canvasDropZone');
    const preview = document.getElementById('previewMessage');
    const contentField = document.getElementById('contentField');
    const componentsField = document.getElementById('componentsField');
    const testPanel = document.getElementById('testVariablesPanel');
    let componentId = 0;

    // Hacer componentes arrastrables
    new Sortable(document.getElementById('componentsList'), {
        group: { name: 'shared', pull: 'clone', put: false },
        sort: false,
        animation: 150
    });

    // Canvas donde se sueltan
    new Sortable(canvas, {
        group: { name: 'shared', pull: false, put: true },
        animation: 150,
        onAdd: function(evt) {
            const type = evt.item.dataset.type;
            evt.item.remove();
            addComponent(type);
        },
        onSort: function() {
            updatePreview();
        }
    });

    function addComponent(type) {
        componentId++;
        const id = 'comp-' + componentId;
        let html = '';

        switch(type) {
            case 'text':
                html = `
                    <div class="canvas-item" data-id="${id}" data-type="text">
                        <div class="item-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="removeComponent('${id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea class="form-control component-content" rows="2" 
                                  placeholder="Escribe tu texto aquí... Usa {{variable}} para campos dinámicos"
                                  oninput="updatePreview()"></textarea>
                    </div>`;
                break;
            case 'image':
                html = `
                    <div class="canvas-item" data-id="${id}" data-type="image">
                        <div class="item-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="removeComponent('${id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-image"></i></span>
                            </div>
                            <input type="url" class="form-control component-content" 
                                   placeholder="URL de la imagen" oninput="updatePreview()">
                        </div>
                    </div>`;
                break;
            case 'button':
                html = `
                    <div class="canvas-item" data-id="${id}" data-type="button">
                        <div class="item-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="removeComponent('${id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <input type="text" class="form-control component-text" 
                                       placeholder="Texto del botón" oninput="updatePreview()">
                            </div>
                            <div class="col-6">
                                <input type="url" class="form-control component-url" 
                                       placeholder="URL" oninput="updatePreview()">
                            </div>
                        </div>
                    </div>`;
                break;
            case 'variable':
                html = `
                    <div class="canvas-item" data-id="${id}" data-type="variable">
                        <div class="item-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="removeComponent('${id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{</span>
                            </div>
                            <input type="text" class="form-control component-content" 
                                   placeholder="nombre_variable" oninput="updatePreview()">
                            <div class="input-group-append">
                                <span class="input-group-text">}}</span>
                            </div>
                        </div>
                    </div>`;
                break;
            case 'separator':
                html = `
                    <div class="canvas-item" data-id="${id}" data-type="separator">
                        <div class="item-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="removeComponent('${id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <hr class="my-0">
                    </div>`;
                break;
            case 'emoji':
                html = `
                    <div class="canvas-item" data-id="${id}" data-type="emoji">
                        <div class="item-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="removeComponent('${id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <input type="text" class="form-control component-content" 
                               placeholder="🎉 Escribe emojis" oninput="updatePreview()">
                    </div>`;
                break;
        }

        canvas.insertAdjacentHTML('beforeend', html);
        updatePreview();
    }

    window.removeComponent = function(id) {
        document.querySelector(`[data-id="${id}"]`).remove();
        updatePreview();
    };

    window.updatePreview = function() {
        const items = canvas.querySelectorAll('.canvas-item');
        let content = '';
        let components = [];
        let variables = new Set();

        items.forEach(item => {
            const type = item.dataset.type;
            let value = '';

            if (type === 'text' || type === 'emoji') {
                value = item.querySelector('.component-content')?.value || '';
                content += value + '\n';
                // Detectar variables
                const matches = value.match(/\{\{(\w+)\}\}/g);
                if (matches) matches.forEach(m => variables.add(m.replace(/[{}]/g, '')));
            } else if (type === 'variable') {
                const varName = item.querySelector('.component-content')?.value || '';
                if (varName) {
                    content += `{{${varName}}} `;
                    variables.add(varName);
                }
            } else if (type === 'image') {
                value = item.querySelector('.component-content')?.value || '';
                components.push({ type: 'image', url: value });
            } else if (type === 'button') {
                const text = item.querySelector('.component-text')?.value || '';
                const url = item.querySelector('.component-url')?.value || '';
                components.push({ type: 'button', text, url });
            } else if (type === 'separator') {
                content += '---\n';
            }

            if (type !== 'image' && type !== 'button') {
                components.push({ type, content: value });
            }
        });

        // Actualizar campos ocultos
        contentField.value = content.trim();
        componentsField.value = JSON.stringify(components);

        // Actualizar preview
        let previewHtml = content.trim().replace(/\n/g, '<br>');
        
        // Resaltar variables en preview
        previewHtml = previewHtml.replace(/\{\{(\w+)\}\}/g, '<span class="badge badge-warning">{{$1}}</span>');
        
        preview.innerHTML = previewHtml || '<span class="text-muted">El preview aparecerá aquí...</span>';

        // Actualizar panel de variables de prueba
        updateTestPanel(Array.from(variables));
    };

    function updateTestPanel(variables) {
        if (variables.length === 0) {
            testPanel.innerHTML = '<p class="text-muted small">Las variables aparecerán aquí cuando las agregues al mensaje.</p>';
            return;
        }

        let html = '';
        variables.forEach(v => {
            html += `
                <div class="variable-input">
                    <label>{{${v}}}</label>
                    <input type="text" class="form-control form-control-sm test-variable" 
                           data-var="${v}" placeholder="Valor de prueba" oninput="updateTestPreview()">
                </div>`;
        });
        testPanel.innerHTML = html;
    }

    window.updateTestPreview = function() {
        let content = contentField.value;
        document.querySelectorAll('.test-variable').forEach(input => {
            const varName = input.dataset.var;
            const value = input.value || `{{${varName}}}`;
            content = content.replace(new RegExp(`\\{\\{${varName}\\}\\}`, 'g'), value);
        });
        preview.innerHTML = content.replace(/\n/g, '<br>');
    };

    // Cargar datos existentes si estamos editando
    @if(isset($template) && $template->components)
        const existingComponents = @json($template->components);
        existingComponents.forEach(comp => {
            addComponent(comp.type);
            const lastItem = canvas.lastElementChild;
            if (comp.content && lastItem.querySelector('.component-content')) {
                lastItem.querySelector('.component-content').value = comp.content;
            }
        });
        updatePreview();
    @endif
});
</script>
@endpush
