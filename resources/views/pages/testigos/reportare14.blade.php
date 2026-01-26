@extends('layouts.bootstrap')

@section('titulo', 'Reporte E14 con Madara AI')

@section('contenido')
<div class="col-md-12">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-gradient-primary text-white py-4 rounded-top-4">
            <h3 class="card-title mb-0 d-flex align-items-center">
                <i class="bi bi-graph-up me-2 fs-4"></i> 
                <span class="fw-bold">Reportar E14</span>
            </h3>
            <small class="d-block mt-2 opacity-90">Sistema inteligente de captura de resultados electorales</small>
        </div>

        <div class="card-body p-4">
            <form id="formE14" action="{{ route('testigos.reportare14.guardar') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- === CARGA DOCUMENTAL CON IA === --}}
                <div class="mb-4 p-4 bg-light rounded-4 border-2 border-primary border-dashed">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-cpu fs-4"></i>
                        </div>
                        <div>
                            <label class="form-label fw-bold mb-0 fs-5">Análisis Inteligente de Documento</label>
                            <small class="text-muted d-block">Carga el formulario E14 para análisis automático</small>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <div class="flex-grow-1">
                            <div class="file-upload-wrapper">
                                <input type="file" name="file" id="fileInput" class="file-input-hidden" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label for="fileInput" class="file-upload-label">
                                    <div class="file-upload-content">
                                        <i class="bi bi-cloud-upload fs-1 text-primary mb-2"></i>
                                        <div class="file-upload-text">
                                            <span class="fw-bold">Haz clic para seleccionar</span>
                                            <span class="text-muted d-block">o arrastra el archivo aquí</span>
                                        </div>
                                        <div class="file-upload-info mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <small>PDF, JPG, JPEG o PNG (máx. 10MB)</small>
                                        </div>
                                    </div>
                                    <div class="file-selected d-none">
                                        <i class="bi bi-file-earmark-check fs-1 text-success mb-2"></i>
                                        <div class="selected-file-name fw-bold text-success"></div>
                                        <small class="text-muted d-block mt-1">Haz clic para cambiar el archivo</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <button type="button" id="analyzeAI" class="btn btn-primary btn-lg px-4 shadow-sm">
                            <i class="bi bi-cpu me-2"></i> Analizar Archivo
                        </button>
                    </div>
                </div>

                <hr class="my-4">

                {{-- === UBICACIÓN === --}}
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

                {{-- === CANDIDATOS Y VOTOS === --}}
                <div class="mb-4">
                    <h5 class="fw-bold mb-3 text-primary">
                        <i class="bi bi-people me-2"></i>Resultados por Candidato
                    </h5>
                    <div class="row g-3">
                        @foreach ($candidatos as $candidato)
                            <div class="col-12">
                                <div class="card border-2 shadow-sm hover-shadow transition-all">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle" style="width: 12px; height: 12px; background-color: {{ $candidato->color ?? '#000' }}; box-shadow: 0 0 10px {{ $candidato->color ?? '#000' }}50;"></div>
                                                <strong class="fs-5">{{ $candidato->nombre }} {{ $candidato->apellido ?? '' }}</strong>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-outline-danger btn-lg restar shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <input type="number" min="0" value="0"
                                                    class="form-control form-control-lg text-center fw-bold fs-4 votos border-2"
                                                    name="candidatos[{{ $candidato->id }}]"
                                                    style="width: 100px;">
                                                <button type="button" class="btn btn-outline-success btn-lg sumar shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
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
                <input type="hidden" name="json_ia" id="json_ia">
                
                            <div id="totalesIA" class="mb-4 d-none">
                <h5 class="fw-bold text-primary">
                    <i class="bi bi-clipboard-check me-2"></i>Totales Detectados por IA
                </h5>
                <div class="alert alert-info border-2 shadow-sm p-3">
                    <p id="totalIAText" class="mb-1">Total IA: <strong>0</strong></p>
                    <p id="totalUrnaText" class="mb-0">Total Urna: <strong>0</strong></p>
                    <p id="totalE11Text" class="mb-0">Total votantes E11: <strong>0</strong></p>
                </div>
            </div>

                <hr class="my-4">

                {{-- === OBSERVACIONES === --}}
                <div class="mb-4">
                    <label for="observaciones" class="form-label fw-bold fs-5">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Observaciones
                    </label>
                    <textarea name="observaciones" id="observaciones" class="form-control form-control-lg border-2 custom-input" rows="4" placeholder="Agrega notas, observaciones o incidencias sobre este reporte electoral..."></textarea>
                </div>

                {{-- === BOTÓN GUARDAR === --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg w-100 shadow-lg py-3">
                        <i class="bi bi-check-circle me-2 fs-5"></i> 
                        <span class="fw-bold fs-5">Guardar Reporte</span>
                    </button>
                </div>
            </form>

            {{-- === RESULTADO IA === --}}
            <div id="resultadoIA" class="mt-4 d-none">
                <div class="alert alert-info border-2 shadow-sm">
                    <h5 class="fw-bold text-info mb-3">
                        <i class="bi bi-stars me-2"></i>Resultados del Análisis IA
                    </h5>
                    <pre class="bg-white border-2 p-3 rounded-3 mb-0" style="white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}

.transition-all {
    transition: all 0.3s ease;
}

.rounded-4 {
    border-radius: 1rem !important;
}

.rounded-top-4 {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
}

.custom-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%230d6efd' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
    cursor: pointer;
    font-weight: 500;
}

.custom-select:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.custom-select:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    background-color: #fff;
}

.custom-input {
    transition: all 0.3s ease;
    font-weight: 500;
}

.custom-input:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.custom-input:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    background-color: #fff;
    transform: scale(1.01);
}

.custom-input::placeholder {
    color: #adb5bd;
    font-weight: 400;
}

.custom-input[readonly] {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.8;
}

.custom-input[readonly]:hover {
    background-color: #e9ecef;
}

input[type="number"].votos {
    font-size: 1.5rem;
    letter-spacing: 0.05em;
    transition: all 0.2s ease;
}

input[type="number"].votos:focus {
    transform: scale(1.05);
    box-shadow: 0 0 0 0.3rem rgba(13, 110, 253, 0.2) !important;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

.file-upload-wrapper {
    position: relative;
    width: 100%;
}

.file-input-hidden {
    position: absolute;
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    z-index: -1;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 180px;
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 3px dashed #0d6efd;
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.file-upload-label::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.file-upload-label:hover {
    border-color: #0a58ca;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.15);
}

.file-upload-label:hover::before {
    opacity: 1;
}

.file-upload-label:active {
    transform: translateY(0);
}

.file-upload-content,
.file-selected {
    position: relative;
    z-index: 1;
    text-align: center;
    transition: all 0.3s ease;
}

.file-upload-text {
    font-size: 1.1rem;
    line-height: 1.4;
}

.file-upload-info {
    color: #6c757d;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 0.5rem;
}

.file-upload-label:hover .file-upload-content i {
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.selected-file-name {
    font-size: 1.1rem;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-selected i {
    animation: checkmark 0.5s ease;
}

@keyframes checkmark {
    0% { transform: scale(0) rotate(-45deg); }
    50% { transform: scale(1.2) rotate(10deg); }
    100% { transform: scale(1) rotate(0); }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const formE14 = document.getElementById('formE14');
    const btnAI = document.getElementById('analyzeAI');
    const fileInput = document.getElementById('fileInput');
    const observacionesInput = document.getElementById('observaciones');
    const inputJsonIA = document.getElementById('json_ia');

    // === Manejo de selección de archivo ===
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) actualizarUIArchivo(this.files[0]);
    });

    function actualizarUIArchivo(file) {
        const label = document.querySelector('.file-upload-label');
        const uploadContent = label.querySelector('.file-upload-content');
        const selectedContent = label.querySelector('.file-selected');
        const fileName = label.querySelector('.selected-file-name');

        if (file) {
            uploadContent.classList.add('d-none');
            selectedContent.classList.remove('d-none');
            fileName.textContent = file.name;
        } else {
            uploadContent.classList.remove('d-none');
            selectedContent.classList.add('d-none');
            fileName.textContent = '';
        }
    }

    // === Variables de índice (Laravel Blade) ===
    const departamentosIndex = @json($departamentos);
    const municipiosIndex = @json($municipios);
    const puestosIndex = @json($puestos);
    const candidatosIndex = @json($candidatos);

 
    
    
    const stripNumericPrefix = str => {
    if (!str) return '';
    return str.replace(/^\d+\s*[-–]?\s*/, ''); // Quita "17 - " o "001 - "
};

const normalize = str =>
    String(str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\w\s]/g, '').replace(/\s+/g, ' ').toUpperCase().trim();

const tokensMatch = (a, b) => {
    a = normalize(stripNumericPrefix(a));
    b = normalize(stripNumericPrefix(b));

    if (a === b) return true;
    const aT = a.split(' '), bT = b.split(' ');
    return bT.filter(bt => aT.some(at => at.includes(bt))).length >= Math.ceil(bT.length * 0.8);
};

    // === Botón IA ===
    btnAI.addEventListener('click', async () => {
        if (!fileInput.files.length) return Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debes seleccionar un archivo primero.',
            confirmButtonColor: '#0d6efd'
        });

        const form = new FormData();
        form.append('file', fileInput.files[0]);

        Swal.fire({
            title: '🤖 Analizando con IA...',
            html: '<div class="spinner-border text-primary mb-3" role="status"></div><br>Procesando documento...',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'rounded-4' }
        });

        try {
            const res = await axios.post('{{ route('testigos.reportare14.ia') }}', form, {
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            Swal.close();

            let json = res.data.json || null;
            if(!json && res.data.text){
                const match = res.data.text.match(/\{[\s\S]*\}/);
                if(match) try{ json = JSON.parse(match[0]); } catch{}
            }
            if(!json) return Swal.fire({
                icon: 'error',
                title: 'Error de Análisis',
                text: 'No se pudo interpretar información válida del documento.',
                confirmButtonColor: '#0d6efd'
            });

            inputJsonIA.value = JSON.stringify(json);

            // --- Ubicación ---
            // === Ubicación desde JSON ===
            const ubicacion = json.ubicacion || {};
            if (ubicacion.departamento) {
                const dep = departamentosIndex.find(d => tokensMatch(d.nombre, ubicacion.departamento));
                if (dep) document.getElementById('departamento_id').value = dep.id;
            }
            
            if (ubicacion.municipio) {
                const mun = municipiosIndex.find(m => tokensMatch(m.nombre, ubicacion.municipio));
                if (mun) document.getElementById('municipio_id').value = mun.id;
            }
            
            if (ubicacion.lugar_votacion) {
                const puesto = puestosIndex.find(p => tokensMatch(p.nombre, ubicacion.lugar_votacion));
                if (puesto) document.getElementById('puesto_id').value = puesto.id;
            }
            
            if (ubicacion.mesa) {
                document.getElementById('mesa').value = ubicacion.mesa;
            }

            // --- Resultados de candidatos ---
            const candidatosAI = json.resultados_votacion?.candidatos || [];
            let sumaCandidatos = 0;
            candidatosAI.forEach(cand => {
                const nombre = normalize(cand.nombre);
                const votos = Number(cand.votos || 0);
                const match = candidatosIndex.find(c => tokensMatch(normalize(c.nombre+' '+(c.apellido||'')), nombre));
                if(match){
                    const input = document.querySelector(`[name="candidatos[${match.id}]"]`);
                    if(input) {
                        input.value = votos;
                        sumaCandidatos += votos;
                    }
                }
            });

            // --- Otros votos ---
            const otros = json.resultados_votacion || {};
            if(document.getElementById('votos_blanco')) document.getElementById('votos_blanco').value = Number(otros.votos_en_blanco || 0);
            if(document.getElementById('votos_nulos')) document.getElementById('votos_nulos').value = Number(otros.votos_nulos || 0);
            if(document.getElementById('votos_no_marcados')) document.getElementById('votos_no_marcados').value = Number(otros.votos_no_marcados || 0);

            // --- Totales y observaciones ---
            const totalIA = Number(otros.suma_total_votos || sumaCandidatos + Number(otros.votos_en_blanco||0) + Number(otros.votos_nulos||0) + Number(otros.votos_no_marcados||0));
            const totalUrna = Number(json.resumen_votacion?.total_votos_alcalde_en_la_urna || 0);
            const totalVotantesE11 = Number(json.resumen_votacion?.total_votantes_formulario_E11 || 0);
            
            const totalesDiv = document.getElementById('totalesIA');
            const totalIAText = document.getElementById('totalIAText');
            const totalUrnaText = document.getElementById('totalUrnaText');
            const totalE11Text = document.getElementById('totalE11Text');
            
            // Mostrar totales
            totalesDiv.classList.remove('d-none');
            totalIAText.querySelector('strong').textContent = totalIA;
            totalUrnaText.querySelector('strong').textContent = totalUrna;
            totalE11Text.querySelector('strong').textContent = totalVotantesE11;
            
            // Resaltar diferencias si existen
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
                    observacionesInput.value = `⚠️ Diferencia detectada: ${diferencia} votos (IA: ${totalIA}, Urna: ${totalUrna}). Total votantes E11: ${totalVotantesE11}`;
                    Swal.fire({
                        icon: 'error',
                        title: '⚠️ Posible Alteración Detectada',
                        html: observacionesInput.value,
                        confirmButtonColor: '#dc3545'
                    });
                } else {
                    observacionesInput.value = `✅ Análisis completado. Total votos: ${totalIA}. Total votantes E11: ${totalVotantesE11}`;
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Análisis Completado',
                        html: observacionesInput.value,
                        confirmButtonColor: '#198754',
                        timer: 3000
                    });
                }
            }
        } catch(err){
            Swal.close();
            console.error('Error en análisis IA:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'No se pudo analizar el archivo. Por favor, intenta nuevamente.',
                confirmButtonColor: '#dc3545'
            });
        }
    });

    // === Incrementar/Decrementar votos ===
    document.querySelectorAll('.sumar').forEach(btn => {
        btn.addEventListener('click', e => {
            const input = e.target.closest('div').querySelector('input');
            input.value = Number(input.value) + 1;
            input.classList.add('border-success');
            setTimeout(() => input.classList.remove('border-success'), 300);
        });
    });
    document.querySelectorAll('.restar').forEach(btn => {
        btn.addEventListener('click', e => {
            const input = e.target.closest('div').querySelector('input');
            input.value = Math.max(0, Number(input.value) - 1);
            input.classList.add('border-danger');
            setTimeout(() => input.classList.remove('border-danger'), 300);
        });
    });

    // === Guardar reporte vía AJAX ===
    formE14.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formE14);

        Swal.fire({
            title: '💾 Guardando reporte...',
            html: '<div class="spinner-border text-success mb-3" role="status"></div><br>Por favor espera...',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'rounded-4' }
        });

        try {
            const res = await axios.post(formE14.action, formData, {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            Swal.close();
            Swal.fire({
                icon: 'success',
                title: '¡Reporte guardado!',
                text: res.data.message || 'El reporte se guardó correctamente.',
                confirmButtonColor: '#198754'
            });

            formE14.reset();
            actualizarUIArchivo(null);

        } catch (err) {
            Swal.close();
            console.error('Error guardando reporte:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo guardar el reporte.',
                confirmButtonColor: '#dc3545'
            });
        }
    });

});
</script>
@endsection

