@extends('layouts.bootstrap')

@section('titulo', 'Reporte E14 con Madara AI')

@section('contenido')
<div class="col-md-12">
    <div class="card shadow-lg border-0 rounded-4">
        {{-- Header con diseño azul sólido --}}
        <div class="card-header text-white py-4 rounded-top-4" style="background: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%);">
            <h3 class="card-title mb-0 d-flex align-items-center">
                <i class="bi bi-graph-up me-2 fs-4"></i> 
                <span class="fw-bold">Reportar E14</span>
            </h3>
            <small class="d-block mt-2 opacity-90">Sistema inteligente de captura de resultados electorales</small>
        </div>

        <div class="card-body p-4">
            <form id="formE14" action="{{ route('testigos.reportare14.guardarCamara') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- SECCIÓN DE UPLOAD MEJORADA --}}
                <div style="background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%); border-radius: 1.25rem; padding: 1.75rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); margin-bottom: 1.5rem;">
                    
                    {{-- Header del upload --}}
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; box-shadow: 0 4px 14px rgba(26, 115, 232, 0.35);">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <span style="font-weight: 700; font-size: 1.15rem; color: #1e293b;">Análisis Inteligente de Documento</span>
                            <span style="font-size: 0.875rem; color: #64748b;">Carga el formulario E14 para análisis automático</span>
                        </div>
                    </div>
                    
                    {{-- Contenedor del dropzone y botón --}}
                    <div style="display: flex; gap: 1.25rem; align-items: stretch;">
                        
                        {{-- Dropzone --}}
                        <div style="flex: 1; position: relative;">
                            <input type="file" name="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" required
                                style="position: absolute; width: 0.1px; height: 0.1px; opacity: 0; overflow: hidden; z-index: -1;">
                            
                            <label for="fileInput" id="dropzoneLabel" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; min-height: 180px; padding: 2rem; background: linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%); border: 2px dashed #1a73e8; border-radius: 1rem; cursor: pointer; transition: all 0.3s ease;">
                                
                                {{-- Contenido normal del dropzone --}}
                                <div id="dropzoneContent" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #e3f0ff 0%, #d4e8ff 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #1a73e8; margin-bottom: 1rem;">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                        <span style="font-weight: 600; font-size: 1.05rem; color: #1e293b;">Haz clic para seleccionar</span>
                                        <span style="font-size: 0.9rem; color: #64748b;">o arrastra el archivo aquí</span>
                                    </div>
                                    <div style="margin-top: 1rem; padding: 0.6rem 1.25rem; background: rgba(26, 115, 232, 0.08); border-radius: 2rem; font-size: 0.8rem; color: #475569; display: flex; align-items: center; gap: 0.35rem; border: 1px solid rgba(26, 115, 232, 0.15);">
                                        <i class="bi bi-info-circle" style="color: #1a73e8;"></i>
                                        <span>PDF, JPG, JPEG o PNG (máx. 10MB)</span>
                                    </div>
                                </div>
                                
                                {{-- Contenido cuando hay archivo seleccionado --}}
                                <div id="dropzoneSelected" style="display: none; flex-direction: column; align-items: center; text-align: center;">
                                    <div style="position: relative; margin-bottom: 0.75rem;">
                                        <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: white; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                                            <i class="bi bi-file-earmark-check-fill"></i>
                                        </div>
                                        <div style="position: absolute; bottom: -4px; right: -4px; width: 24px; height: 24px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <span id="selectedFilename" style="font-weight: 700; color: #059669; font-size: 1rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                                        <span style="font-size: 0.8rem; color: #64748b;">Haz clic para cambiar el archivo</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        {{-- Botón Analizar --}}
                        <button type="button" id="analyzeAI" style="display: flex; align-items: center; justify-content: center; padding: 1.25rem 2rem; background: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%); color: white; border: none; border-radius: 1rem; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; white-space: nowrap; min-width: 200px; min-height: 180px; box-shadow: 0 4px 14px rgba(26, 115, 232, 0.35);">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                    <i class="bi bi-cpu"></i>
                                </div>
                                <span>Analizar Archivo</span>
                            </div>
                        </button>
                    </div>
                </div>

                <hr class="my-4">

                {{-- === UBICACION === --}}
                <div class="mb-4">
                    <h5 class="fw-bold mb-3 text-primary">
                        <i class="bi bi-geo-alt me-2"></i>Ubicación Electoral
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="departamento_id" class="form-label fw-semibold">
                                <i class="bi bi-map text-primary me-1"></i>Departamento
                            </label>
                            <select name="departamento_id" id="departamento_id" class="form-select form-select-lg border-2 custom-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($departamentos as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="municipio_id" class="form-label fw-semibold">
                                <i class="bi bi-building text-primary me-1"></i>Municipio
                            </label>
                            <select name="municipio_id" id="municipio_id" class="form-select form-select-lg border-2 custom-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($municipios as $mun)
                                    <option value="{{ $mun->id }}" data-departamento="{{ $mun->departamento_id }}">{{ $mun->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="puesto_id" class="form-label fw-semibold">
                                <i class="bi bi-pin-map text-primary me-1"></i>Puesto de votación
                            </label>
                            <select name="puesto_id" id="puesto_id" class="form-select form-select-lg border-2 custom-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($puestos as $p)
                                    <option value="{{ $p->id }}" data-municipio="{{ $p->municipio_id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="mesa" class="form-label fw-semibold">
                                <i class="bi bi-table text-primary me-1"></i>Mesa de votación
                            </label>
                            <input type="text" id="mesa" name="mesa" class="form-control form-control-lg border-2 custom-input" placeholder="Auto-completado por IA">
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- PARTIDOS CON CANDIDATOS DESPLEGABLES --}}
                <div class="mb-4">
                    <h5 class="fw-bold mb-3 text-primary">
                        <i class="bi bi-people me-2"></i>Resultados por Partidos y Candidatos
                    </h5>
                    <div class="row g-3">
                        @foreach ($Partido as $Partidos)
                            @php
                                $candidatosPartido = $candidatos->where('partido_id', $Partidos->id);
                            @endphp
                            <div class="col-12">
                                <div class="card border-2 shadow-sm hover-shadow transition-all partido-card" data-partido-id="{{ $Partidos->id }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle" style="width: 12px; height: 12px; background-color: {{ $Partidos->color ?? '#000' }}; box-shadow: 0 0 10px {{ $Partidos->color ?? '#000' }}50;"></div>
                                                <strong class="fs-5">{{ $Partidos->nombre }}</strong>
                                                @if($candidatosPartido->count() > 0)
                                                    <button type="button" class="btn btn-outline-primary btn-sm toggle-candidatos" data-partido="{{ $Partidos->id }}">
                                                        <i class="bi bi-chevron-down"></i> Ver Candidatos ({{ $candidatosPartido->count() }})
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                {{-- Badge Total --}}
                                                <div class="total-votos-partido-badge" data-partido-id="{{ $Partidos->id }}" style="margin-right: 1.5rem;">
                                                    <i class="bi bi-calculator me-1"></i>
                                                    <span class="total-label">Totalsuma votos Partido + Candidatos:</span>
                                                    <span class="total-value" id="total-partido-{{ $Partidos->id }}">0</span>
                                                </div>
                                                
                                                {{-- Votos solo partido --}}
                                                <div class="d-flex align-items-center gap-2 border-end pe-4" style="margin-right: 1.5rem;">
                                                    <span class="text-muted small fw-medium">Votos partido:</span>
                                                    <input type="number" min="0" value="0" 
                                                        class="form-control form-control-sm text-center fw-bold votos-solo-agrupacion bg-light border-secondary"
                                                        data-partido-id="{{ $Partidos->id }}"
                                                        style="width: 65px;">
                                                </div> 
                                                
                                                {{-- Votos totales con botones --}}
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted small fw-medium me-1">Votos escritos e14:</span>
                                                    <button type="button" class="btn btn-outline-danger btn-lg restar shadow-sm" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-dash-lg"></i>
                                                    </button>
                                                    <input type="number" min="0" value="0"
                                                        class="form-control form-control-lg text-center fw-bold fs-4 votos votos-partido border-2"
                                                        name="Partido[{{ $Partidos->id }}]"
                                                        data-partido-nombre="{{ $Partidos->nombre }}"
                                                        data-partido-id="{{ $Partidos->id }}"
                                                        style="width: 100px;">
                                                    <button type="button" class="btn btn-outline-success btn-lg sumar shadow-sm" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        @if($candidatosPartido->count() > 0)
                                            <div class="candidatos-container mt-3 d-none" id="candidatos-partido-{{ $Partidos->id }}">
                                                <div class="border-top pt-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="fw-semibold text-secondary">
                                                            <i class="bi bi-person-badge me-1"></i>Votos por Candidato
                                                        </span>
                                                        <span class="badge bg-info total-candidatos-badge" data-partido="{{ $Partidos->id }}">
                                                           Total: <span class="total-votos-candidatos">0</span>
                                                        </span>
                                                    </div>
                                                    <div class="row g-2">
                                                        @foreach ($candidatosPartido as $candidato)
                                                            <div class="col-md-4 col-lg-3">
                                                                <div class="card border h-100 candidato-card">
                                                                    <div class="card-body p-2">
                                                                        <div class="d-flex flex-column">
                                                                            <small class="text-muted mb-1">
                                                                                #{{ $candidato->Tarjeton ?? $candidato->id }}
                                                                            </small>
                                                                            <span class="fw-semibold small mb-2" title="{{ $candidato->nombre }}">
                                                                                {{ Str::limit($candidato->nombre . ' ' . $candidato->Tarjeton, 25) }}
                                                                            </span>
                                                                            <div class="input-group input-group-sm">
                                                                                <button type="button" class="btn btn-outline-danger restar-candidato">
                                                                                    <i class="bi bi-dash"></i>
                                                                                </button>
                                                                                <input type="number" min="0" value="0"
                                                                                    class="form-control text-center fw-bold votos-candidato"
                                                                                    name="Candidatos[{{ $candidato->id }}]"
                                                                                    data-candidato-id="{{ $candidato->id }}"
                                                                                    data-candidato-numero="{{ $candidato->Tarjeton ?? $candidato->id }}"
                                                                                    data-partido-id="{{ $Partidos->id }}"
                                                                                    style="max-width: 70px;">
                                                                                <button type="button" class="btn btn-outline-success sumar-candidato">
                                                                                    <i class="bi bi-plus"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-4">

                {{-- === OTROS VOTOS === --}}
                <div class="mb-4">
                    <h5 class="fw-bold mb-3 text-primary">
                        <i class="bi bi-clipboard-data me-2"></i>Otros Resultados
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border-2 shadow-sm h-100">
                                <div class="card-body">
                                    <label for="votos_blanco" class="form-label fw-semibold">
                                        <i class="bi bi-square text-secondary me-2"></i>Votos en blanco
                                    </label>
                                    <input type="number" min="0" value="0" id="votos_blanco" name="votos_blanco" class="form-control form-control-lg border-2 fw-bold custom-input" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-2 shadow-sm h-100">
                                <div class="card-body">
                                    <label for="votos_nulos" class="form-label fw-semibold">
                                        <i class="bi bi-x-circle text-danger me-2"></i>Votos nulos
                                    </label>
                                    <input type="number" min="0" value="0" id="votos_nulos" name="votos_nulos" class="form-control form-control-lg border-2 fw-bold custom-input" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-2 shadow-sm h-100">
                                <div class="card-body">
                                    <label for="votos_no_marcados" class="form-label fw-semibold">
                                        <i class="bi bi-dash-circle text-warning me-2"></i>Votos no marcados
                                    </label>
                                    <input type="number" min="0" value="0" id="votos_no_marcados" name="votos_no_marcados" class="form-control form-control-lg border-2 fw-bold custom-input" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Totales IA --}}
                <div id="totalesIA" class="d-none mb-4">
                    <div class="alert alert-info border-2">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calculator me-2"></i>Resumen de Totales</h6>
                        <div class="row">
                            <div class="col-md-4" id="totalIAText">
                                <span>Total suma partidos: </span><strong>0</strong>
                            </div>
                            <div class="col-md-4" id="totalUrnaText">
                                <span>Total urna E14: </span><strong>0</strong>
                            </div>
                            <div class="col-md-4" id="totalE11Text">
                                <span>Total votantes E11: </span><strong>0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="mb-4">
                    <label for="observaciones" class="form-label fw-semibold">
                        <i class="bi bi-journal-text text-primary me-2"></i>Observaciones
                    </label>
                    <textarea id="observaciones" name="observaciones" rows="4" class="form-control border-2 custom-input" placeholder="Observaciones detectadas por la IA o ingresadas manualmente..."></textarea>
                </div>

                {{-- Campo oculto JSON IA --}}
                <input type="hidden" id="json_ia" name="json_ia" value="">

                {{-- Botón guardar --}}
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                        <i class="bi bi-check-circle me-2"></i>Guardar Reporte E14
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Custom inputs and selects */
.custom-input:focus,
.custom-select:focus {
    border-color: #1a73e8 !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25) !important;
}

/* Partido cards */
.partido-card {
    transition: all 0.2s ease;
}

.partido-card:hover {
    border-color: #1a73e8 !important;
}

/* Candidato cards */
.candidato-card {
    transition: all 0.2s ease;
}

.candidato-card:hover {
    border-color: #1a73e8 !important;
    background-color: #f8fafc;
}

.candidato-card .votos-candidato {
    font-weight: 600;
}

.candidato-card .votos-candidato:focus {
    box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25);
}

.toggle-candidatos {
    transition: all 0.2s ease;
}

.toggle-candidatos.active i {
    transform: rotate(180deg);
}

.total-candidatos-badge {
    font-size: 0.85rem;
}

/* Estilos para el badge de Total Votos por Partido */
.total-votos-partido-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.total-votos-partido-badge .total-label {
    opacity: 0.9;
}

.total-votos-partido-badge .total-value {
    font-size: 1.1rem;
    font-weight: 700;
    min-width: 2rem;
    text-align: center;
}

/* Estilos para campo Solo Lista */
.votos-solo-agrupacion {
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .total-votos-partido-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const formE14 = document.getElementById('formE14');
    const btnAI = document.getElementById('analyzeAI');
    const fileInput = document.getElementById('fileInput');
    const observacionesInput = document.getElementById('observaciones');
    const inputJsonIA = document.getElementById('json_ia');
    const dropzoneLabel = document.getElementById('dropzoneLabel');
    const dropzoneContent = document.getElementById('dropzoneContent');
    const dropzoneSelected = document.getElementById('dropzoneSelected');
    const selectedFilename = document.getElementById('selectedFilename');

    const departamentoSelect = document.getElementById('departamento_id');
    const municipioSelect = document.getElementById('municipio_id');
    const puestoSelect = document.getElementById('puesto_id');

    // Guardar todas las opciones originales de municipios y puestos
    const todasLasOpcionesMunicipios = Array.from(municipioSelect.options).slice(1);
    const todasLasOpcionesPuestos = Array.from(puestoSelect.options).slice(1);

    // Filtrar municipios cuando cambia el departamento
    departamentoSelect.addEventListener('change', function() {
        const departamentoId = this.value;
        
        // Limpiar selects dependientes
        municipioSelect.innerHTML = '<option value="">Seleccione...</option>';
        puestoSelect.innerHTML = '<option value="">Seleccione...</option>';

        if (departamentoId) {
            // Filtrar municipios que pertenecen al departamento seleccionado
            todasLasOpcionesMunicipios.forEach(function(opcion) {
                if (opcion.dataset.departamento == departamentoId) {
                    municipioSelect.appendChild(opcion.cloneNode(true));
                }
            });
        }
    });

    // Filtrar puestos cuando cambia el municipio
    municipioSelect.addEventListener('change', function() {
        const municipioId = this.value;
        
        // Limpiar select de puestos
        puestoSelect.innerHTML = '<option value="">Seleccione...</option>';

        if (municipioId) {
            // Filtrar puestos que pertenecen al municipio seleccionado
            todasLasOpcionesPuestos.forEach(function(opcion) {
                if (opcion.dataset.municipio == municipioId) {
                    puestoSelect.appendChild(opcion.cloneNode(true));
                }
            });
        }
    });

    // === Drag and Drop ===
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzoneLabel.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneLabel.addEventListener(eventName, () => {
            dropzoneLabel.style.background = 'linear-gradient(145deg, #e3f0ff 0%, #d4e8ff 100%)';
            dropzoneLabel.style.borderStyle = 'solid';
            dropzoneLabel.style.transform = 'scale(1.01)';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneLabel.addEventListener(eventName, () => {
            if (!dropzoneLabel.classList.contains('has-file')) {
                dropzoneLabel.style.background = 'linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%)';
                dropzoneLabel.style.borderStyle = 'dashed';
            }
            dropzoneLabel.style.transform = 'scale(1)';
        }, false);
    });

    dropzoneLabel.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            actualizarUIArchivo(files[0]);
        }
    }, false);

    // Hover effects para el botón
    btnAI.addEventListener('mouseenter', function() {
        this.style.background = 'linear-gradient(135deg, #1557b0 0%, #0f4a8a 100%)';
        this.style.transform = 'translateY(-3px)';
        this.style.boxShadow = '0 8px 25px rgba(26, 115, 232, 0.4)';
    });
    
    btnAI.addEventListener('mouseleave', function() {
        this.style.background = 'linear-gradient(135deg, #1a73e8 0%, #1557b0 100%)';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 4px 14px rgba(26, 115, 232, 0.35)';
    });

    // Hover effects para el dropzone
    dropzoneLabel.addEventListener('mouseenter', function() {
        if (!dropzoneLabel.classList.contains('has-file')) {
            this.style.background = 'linear-gradient(145deg, #f0f7ff 0%, #e3f0ff 100%)';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 25px rgba(26, 115, 232, 0.15)';
        }
    });
    
    dropzoneLabel.addEventListener('mouseleave', function() {
        if (!dropzoneLabel.classList.contains('has-file')) {
            this.style.background = 'linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%)';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        }
    });

    // === Manejo de selección de archivo ===
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            actualizarUIArchivo(this.files[0]);
        }
    });

    function actualizarUIArchivo(file) {
        if (file) {
            dropzoneContent.style.display = 'none';
            dropzoneSelected.style.display = 'flex';
            selectedFilename.textContent = file.name;
            dropzoneLabel.classList.add('has-file');
            dropzoneLabel.style.background = 'linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%)';
            dropzoneLabel.style.borderColor = '#10b981';
            dropzoneLabel.style.borderStyle = 'solid';
        } else {
            dropzoneContent.style.display = 'flex';
            dropzoneSelected.style.display = 'none';
            selectedFilename.textContent = '';
            dropzoneLabel.classList.remove('has-file');
            dropzoneLabel.style.background = 'linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%)';
            dropzoneLabel.style.borderColor = '#1a73e8';
            dropzoneLabel.style.borderStyle = 'dashed';
        }
    }

    // === Función para calcular y actualizar el total de votos por partido ===
    function actualizarTotalVotosPartido(partidoId) {
        const inputSoloPartido = document.querySelector(`.votos-solo-agrupacion[data-partido-id="${partidoId}"]`);
        const votosSoloPartido = parseInt(inputSoloPartido?.value || 0);
        
        const inputsCandidatos = document.querySelectorAll(`.votos-candidato[data-partido-id="${partidoId}"]`);
        let votosCandidatos = 0;
        inputsCandidatos.forEach(input => {
            votosCandidatos += parseInt(input.value || 0);
        });
        
        const total = votosSoloPartido + votosCandidatos;
        
        const totalElement = document.getElementById(`total-partido-${partidoId}`);
        if (totalElement) {
            totalElement.textContent = total;
        }
    }

    function inicializarTotalesPartidos() {
        document.querySelectorAll('.partido-card').forEach(card => {
            const partidoId = card.dataset.partidoId;
            if (partidoId) {
                actualizarTotalVotosPartido(partidoId);
            }
        });
    }

    inicializarTotalesPartidos();

    document.querySelectorAll('.votos-solo-agrupacion').forEach(input => {
        input.addEventListener('input', function() {
            actualizarTotalVotosPartido(this.dataset.partidoId);
        });
    });

    // === Variables de índice (Laravel Blade) ===
    const departamentosIndex = @json($departamentos);
    const municipiosIndex = @json($municipios);
    const puestosIndex = @json($puestos);
    const PartidoIndex = @json($Partido);
    const candidatosIndex = @json($candidatos);

    document.querySelectorAll('.toggle-candidatos').forEach(btn => {
        btn.addEventListener('click', function() {
            const partidoId = this.dataset.partido;
            const container = document.getElementById(`candidatos-partido-${partidoId}`);
            
            if (container) {
                container.classList.toggle('d-none');
                this.classList.toggle('active');
                
                if (container.classList.contains('d-none')) {
                    this.innerHTML = `<i class="bi bi-chevron-down"></i> Ver Candidatos`;
                } else {
                    this.innerHTML = `<i class="bi bi-chevron-up"></i> Ocultar Candidatos`;
                }
            }
        });
    });

    document.querySelectorAll('.sumar-candidato').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.votos-candidato');
            input.value = parseInt(input.value || 0) + 1;
            actualizarTotalCandidatos(input.dataset.partidoId);
            actualizarTotalVotosPartido(input.dataset.partidoId);
        });
    });

    document.querySelectorAll('.restar-candidato').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.votos-candidato');
            const val = parseInt(input.value || 0);
            input.value = Math.max(0, val - 1);
            actualizarTotalCandidatos(input.dataset.partidoId);
            actualizarTotalVotosPartido(input.dataset.partidoId);
        });
    });

    function actualizarTotalCandidatos(partidoId) {
        const inputs = document.querySelectorAll(`.votos-candidato[data-partido-id="${partidoId}"]`);
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value || 0);
        });
        
        const badge = document.querySelector(`.total-candidatos-badge[data-partido="${partidoId}"] .total-votos-candidatos`);
        if (badge) {
            badge.textContent = total;
        }
    }

    document.querySelectorAll('.votos-candidato').forEach(input => {
        input.addEventListener('change', function() {
            actualizarTotalCandidatos(this.dataset.partidoId);
            actualizarTotalVotosPartido(this.dataset.partidoId);
        });
        input.addEventListener('input', function() {
            actualizarTotalCandidatos(this.dataset.partidoId);
            actualizarTotalVotosPartido(this.dataset.partidoId);
        });
    });

    const stripNumericPrefix = str => {
        if (!str) return '';
        return str.replace(/^\d+\s*[-–]?\s*/, '');
    };

    const normalize = str =>
        String(str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^\w\s]/g, '').replace(/\s+/g, ' ').toUpperCase().trim();

    const normalizeUbicacion = str => {
        return normalize(stripNumericPrefix(str));
    };

    const ubicacionMatch = (indexNombre, jsonNombre) => {
        const a = normalizeUbicacion(indexNombre);
        const b = normalizeUbicacion(jsonNombre);
        
        if (a === b) return true;
        
        const aT = a.split(' ').filter(t => t.length > 2);
        const bT = b.split(' ').filter(t => t.length > 2);
        
        const comunes = bT.filter(bt => aT.includes(bt)).length;
        
        return comunes >= Math.min(2, bT.length);
    };

    const calcularScoreSimilitud = (indexNombre, jsonNombre) => {
        const normalizar = (s) => s.toUpperCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/["""'']/g, '')
            .replace(/PARTIDO\s*(POLITICO)?\s*/g, '')
            .replace(/\s+/g, ' ')
            .trim();
        
        const indexNorm = normalizar(indexNombre);
        const jsonNorm = normalizar(jsonNombre);
        
        if (indexNorm === jsonNorm) {
            return { score: 1000, coincidencias: [indexNorm], sobrantes: [], faltantes: [] };
        }
        
        const palabrasIgnorar = ['DE', 'LA', 'EL', 'LOS', 'LAS', 'DEL', 'Y', 'POR', 'PARA', 'EN', 'CON'];
        
        const indexTokens = indexNorm.split(' ').filter(t => t.length > 1 && !palabrasIgnorar.includes(t));
        const jsonTokens = jsonNorm.split(' ').filter(t => t.length > 1 && !palabrasIgnorar.includes(t));
        
        const coincidencias = indexTokens.filter(t => jsonTokens.includes(t));
        const sobrantes = indexTokens.filter(t => !jsonTokens.includes(t));
        const faltantes = jsonTokens.filter(t => !indexTokens.includes(t));
        
        const score = (coincidencias.length * 10) - (sobrantes.length * 3) - (faltantes.length * 6);
        
        return { score, coincidencias, sobrantes, faltantes };
    };

    const findBestMatch = (jsonNombre, partidosIndex) => {
        let mejorMatch = null;
        let mejorScore = -Infinity;
        let mejorInfo = null;
        
        partidosIndex.forEach(partido => {
            const resultado = calcularScoreSimilitud(partido.nombre, jsonNombre);
            
            if (resultado.score > mejorScore) {
                mejorMatch = partido;
                mejorScore = resultado.score;
                mejorInfo = resultado;
            }
        });
        
        if (mejorMatch && mejorScore > 0 && mejorInfo.coincidencias.length >= 1) {
            return mejorMatch;
        }
        
        return null;
    };

    function setSelectsCascada(depId, munId, puestoId) {
        // Primero establecer departamento
        if (depId) {
            departamentoSelect.value = depId;
            // Disparar evento change para filtrar municipios
            departamentoSelect.dispatchEvent(new Event('change'));
            
            // Esperar un momento para que se actualicen los municipios
            setTimeout(() => {
                if (munId) {
                    municipioSelect.value = munId;
                    // Disparar evento change para filtrar puestos
                    municipioSelect.dispatchEvent(new Event('change'));
                    
                    setTimeout(() => {
                        if (puestoId) {
                            puestoSelect.value = puestoId;
                        }
                    }, 50);
                }
            }, 50);
        }
    }

    // === Botón IA ===
    btnAI.addEventListener('click', async () => {
        if (!fileInput.files.length) return Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debes seleccionar un archivo primero.',
            confirmButtonColor: '#1a73e8'
        });

        const form = new FormData();
        form.append('file', fileInput.files[0]);

        Swal.fire({
            title: 'Analizando con IA...',
            html: '<div class="spinner-border text-primary mb-3" role="status"></div><br>Procesando documento...',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'rounded-4' }
        });

        try {
            const res = await axios.post('{{ route('testigos.reportare14.iaCamara') }}', form, {
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            Swal.close();

            let json = res.data.json || null;
            if(!json && res.data.text){
                const match = res.data.text.match(/\{[\s\S]*\}/);
                if(match) {
                    try { 
                        json = JSON.parse(match[0]); 
                    } catch(parseErr) {
                        console.error('Error parseando JSON:', parseErr);
                    }
                }
            }
            if(!json) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Error de Análisis',
                    text: 'No se pudo interpretar información válida del documento.',
                    confirmButtonColor: '#1a73e8'
                });
            }

            inputJsonIA.value = JSON.stringify(json);

            const ubicacion = json.ubicacion || {};
            let depIdEncontrado = null;
            let munIdEncontrado = null;
            let puestoIdEncontrado = null;

            if (ubicacion.departamento) {
                const dep = departamentosIndex.find(d => ubicacionMatch(d.nombre, ubicacion.departamento));
                if (dep) depIdEncontrado = dep.id;
            }
            
            if (ubicacion.municipio) {
                const mun = municipiosIndex.find(m => ubicacionMatch(m.nombre, ubicacion.municipio));
                if (mun) munIdEncontrado = mun.id;
            }
            
            if (ubicacion.lugar_votacion) {
                const puesto = puestosIndex.find(p => ubicacionMatch(p.nombre, ubicacion.lugar_votacion));
                if (puesto) puestoIdEncontrado = puesto.id;
            }

            // Usar la función de cascada para establecer los valores
            setSelectsCascada(depIdEncontrado, munIdEncontrado, puestoIdEncontrado);
            
            if (ubicacion.mesa) {
                document.getElementById('mesa').value = ubicacion.mesa;
            }

            // --- Resultados por Partido ---
            const partidosIA = json.resultados_votacion?.Partido || [];
            let sumaPartido = 0;
            let diferenciasPartidos = [];
            let partidosNoEncontrados = [];
            
            partidosIA.forEach(p => {
                const nombreIA = p.nombre;
                const votosReportados = Number(p.votos_reportados || 0);
                
                const detallePreferente = p.detalle_preferente || {};
                const votosSoloPartido = Number(detallePreferente.votos_solo_agrupacion || 0);
                const candidatosIA = detallePreferente.candidatos || [];
                const votosCandidatos = candidatosIA.reduce((sum, c) => sum + Number(c.votos || 0), 0);
                const totalCalculadoLocal = votosSoloPartido + votosCandidatos;
                const diferenciaLocal = votosReportados - totalCalculadoLocal;
            
                const match = findBestMatch(nombreIA, PartidoIndex);

                if (match) {
                    const input = document.querySelector(`[name="Partido[${match.id}]"]`);
                    if (input) {
                        input.value = votosReportados;
                        sumaPartido += votosReportados;

                        const inputSoloAgrupacion = document.querySelector(`.votos-solo-agrupacion[data-partido-id="${match.id}"]`);
                        if (inputSoloAgrupacion) {
                            inputSoloAgrupacion.value = votosSoloPartido;
                        }

                        if (candidatosIA.length > 0) {
                            const container = document.getElementById(`candidatos-partido-${match.id}`);
                            const toggleBtn = document.querySelector(`.toggle-candidatos[data-partido="${match.id}"]`);
                            
                            if (container && toggleBtn) {
                                container.classList.remove('d-none');
                                toggleBtn.classList.add('active');
                                toggleBtn.innerHTML = `<i class="bi bi-chevron-up"></i> Ocultar Candidatos`;
                            }

                            candidatosIA.forEach(candIA => {
                                const numeroCandidato = String(candIA.numero).trim();
                                const votosCand = Number(candIA.votos || 0);

                                const inputCandidato = document.querySelector(
                                    `.votos-candidato[data-partido-id="${match.id}"][data-candidato-numero="${numeroCandidato}"]`
                                );

                                if (inputCandidato) {
                                    inputCandidato.value = votosCand;
                                    inputCandidato.classList.add('border-primary', 'bg-light');
                                } else {
                                    const inputsCandidatosPartido = document.querySelectorAll(
                                        `.votos-candidato[data-partido-id="${match.id}"]`
                                    );
                                    
                                    inputsCandidatosPartido.forEach(inp => {
                                        const numTarjeton = String(inp.dataset.candidatoNumero);
                                        if (numTarjeton.endsWith(numeroCandidato) || numeroCandidato.endsWith(numTarjeton)) {
                                            inp.value = votosCand;
                                            inp.classList.add('border-primary', 'bg-light');
                                        }
                                    });
                                }
                            });

                            actualizarTotalCandidatos(match.id);
                        }
                        
                        actualizarTotalVotosPartido(match.id);
            
                        if (diferenciaLocal !== 0) {
                            input.classList.add('border-danger');
                            
                            const candidatosStr = candidatosIA.map(c => `C${c.numero}=${c.votos}`).join(', ');
                            diferenciasPartidos.push(
                                `PARTIDO ${p.nombre}\n` +
                                `   Total escrito en E14: ${votosReportados}\n` +
                                `   Votos solo partido: ${votosSoloPartido}\n` +
                                `   Votos candidatos: ${candidatosStr || 'ninguno'}\n` +
                                `   Total calculado: ${totalCalculadoLocal}\n` +
                                `   Diferencia: ${diferenciaLocal}`
                            );
                        } else {
                            input.classList.add('border-success');
                        }
                    }
                } else {
                    partidosNoEncontrados.push(`${nombreIA} (${votosReportados} votos)`);
                }
            });
            
            const advertenciasIA = json.resultados_votacion?.advertencias_validacion || [];

            let observacionesTexto = '';
            
            if (diferenciasPartidos.length > 0) {
                observacionesTexto = diferenciasPartidos.join('\n\n');
            }
            
            if (partidosNoEncontrados.length > 0) {
                const textoNoEncontrados = 'PARTIDOS NO MAPEADOS:\n' + partidosNoEncontrados.join('\n');
                observacionesTexto = observacionesTexto 
                    ? observacionesTexto + '\n\n' + textoNoEncontrados
                    : textoNoEncontrados;
            }
            
            if (advertenciasIA.length) {
                const advertenciasTextoIA = advertenciasIA.map(a => {
                    const tipo = a.tipo.toUpperCase();
        
                    if (tipo.includes('DIFERENCIA_TOTAL_PARTIDO')) {
                        return `Partido ${a.partido}: diferencia ${a.diferencia} votos`;
                    }
        
                    if (tipo.includes('CONCENTRACION_VOTOS_CANDIDATO')) {
                        return `Partido ${a.partido}, candidato ${a.candidato}: concentración anómala`;
                    }
        
                    if (tipo.includes('DIFERENCIA_TOTAL_URNA') || tipo.includes('TOTALES')) {
                        return `Diferencia total urna: ${a.diferencia} votos`;
                    }
        
                    return '';
                }).filter(Boolean).join('\n');
                
                if (advertenciasTextoIA) {
                    observacionesTexto = observacionesTexto 
                        ? observacionesTexto + '\n\n--- ADVERTENCIAS IA ---\n' + advertenciasTextoIA
                        : advertenciasTextoIA;
                }
            }

            // --- Otros votos ---
            const otros = json.resultados_votacion || {};
            if(document.getElementById('votos_blanco')) document.getElementById('votos_blanco').value = Number(otros.votos_en_blanco || 0);
            if(document.getElementById('votos_nulos')) document.getElementById('votos_nulos').value = Number(otros.votos_nulos || 0);
            if(document.getElementById('votos_no_marcados')) document.getElementById('votos_no_marcados').value = Number(otros.votos_no_marcados || 0);

            // --- Totales ---
            const validacion = json.resultados_votacion?.validacion_global || {};
            const totalIA = Number(validacion.suma_votos_partidos || 0);
            const totalUrna = Number(validacion.total_urna_E14 || 0);
            const totalVotantesE11 = Number(validacion.total_votantes_E11 || 0);

            const totalesDiv = document.getElementById('totalesIA');
            const totalIAText = document.getElementById('totalIAText');
            const totalUrnaText = document.getElementById('totalUrnaText');
            const totalE11Text = document.getElementById('totalE11Text');
            
            totalesDiv.classList.remove('d-none');
            totalIAText.querySelector('strong').textContent = totalIA;
            totalUrnaText.querySelector('strong').textContent = totalUrna;
            totalE11Text.querySelector('strong').textContent = totalVotantesE11;
            
            if(totalIA !== totalUrna){
                totalIAText.querySelector('strong').classList.add('text-danger', 'fw-bold');
                totalUrnaText.querySelector('strong').classList.add('text-danger', 'fw-bold');
            } else {
                totalIAText.querySelector('strong').classList.add('text-success', 'fw-bold');
                totalUrnaText.querySelector('strong').classList.add('text-success', 'fw-bold');
            }
            totalE11Text.querySelector('strong').classList.add('text-primary', 'fw-bold');

            if(observacionesInput){
                if(totalIA !== totalUrna){
                    const diferencia = totalIA - totalUrna;
                    const mensajeTotales = `Diferencia detectada: ${diferencia} votos (IA: ${totalIA}, Urna: ${totalUrna}). Total votantes E11: ${totalVotantesE11}`;
                    
                    observacionesInput.value = observacionesTexto 
                        ? observacionesTexto + '\n\n--- TOTALES ---\n' + mensajeTotales
                        : mensajeTotales;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Posible Alteración Detectada',
                        html: mensajeTotales,
                        confirmButtonColor: '#dc3545'
                    });
                } else if (diferenciasPartidos.length > 0) {
                    observacionesInput.value = observacionesTexto + `\n\nTotal votos: ${totalIA}. Total votantes E11: ${totalVotantesE11}`;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Diferencias en Partidos',
                        html: `Se detectaron diferencias en ${diferenciasPartidos.length} partido(s).`,
                        confirmButtonColor: '#ffc107'
                    });
                } else {
                    observacionesInput.value = `Análisis completado. Total votos: ${totalIA}. Total votantes E11: ${totalVotantesE11}`;
                    if (partidosNoEncontrados.length > 0) {
                        observacionesInput.value += '\n\n' + observacionesTexto;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Análisis Completado',
                        html: `Total votos: ${totalIA}.`,
                        confirmButtonColor: '#198754',
                        timer: 3000
                    });
                }
            }
        } catch(err){
            Swal.close();
            console.error('Error:', err);
            
            let mensajeError = 'No se pudo analizar el archivo.';
            
            if (err.response) {
                if (err.response.status === 422) mensajeError = 'Archivo inválido.';
                else if (err.response.status === 413) mensajeError = 'Archivo muy grande.';
                else if (err.response.status === 500) mensajeError = 'Error del servidor.';
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensajeError,
                confirmButtonColor: '#dc3545'
            });
        }
    });

    // === Botones sumar/restar ===
    document.querySelectorAll('.sumar').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.votos');
            input.value = parseInt(input.value || 0) + 1;
        });
    });

    document.querySelectorAll('.restar').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.votos');
            const val = parseInt(input.value || 0);
            input.value = Math.max(0, val - 1);
        });
    });

    // === Envío del formulario ===
    formE14.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Guardando reporte...',
            html: '<div class="spinner-border text-success mb-3" role="status"></div><br>Por favor espera...',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        try {
            const formData = new FormData(this);
            
            const res = await axios.post(this.action, formData, {
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            Swal.fire({
                icon: 'success',
                title: 'Reporte Guardado',
                text: res.data.message || 'El reporte E14 se guardó correctamente.',
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location.reload();
            });

        } catch(err) {
            Swal.close();
            
            let mensajeError = 'No se pudo guardar el reporte.';
            
            if (err.response?.data?.message) {
                mensajeError = err.response.data.message;
            }
            if (err.response?.data?.errors) {
                const errores = Object.values(err.response.data.errors).flat().join('\n');
                mensajeError += '\n\n' + errores;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error al Guardar',
                text: mensajeError,
                confirmButtonColor: '#dc3545'
            });
        }
    });
});
</script>
@endsection
