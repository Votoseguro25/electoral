@extends('layouts.bootstrap')

@section('titulo', 'Gestión de E14')

@section('contenido')
<div class="row">
<div class="container-fluid py-4">
    <div class="card shadow-lg border-0 rounded-4">
        {{-- me-2 → mr-2, mt-2 → mt-2 (igual en BS4) --}}
        <div class="card-header bg-gradient-primary text-white py-4 rounded-top-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-table mr-2 fs-4"></i> 
                        <span class="fw-bold">Gestión de Reportes E14</span>
                    </h3>
                    <small class="d-block mt-2 opacity-90">Visualiza, edita y administra todos los reportes electorales</small>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- Filtros --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel text-primary mr-1"></i>Departamento
                    </label>
                    <select id="filtro-departamento" class="form-control">
                        <option value="">Todos</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel text-primary mr-1"></i>Municipio
                    </label>
                    <select id="filtro-municipio" class="form-control">
                        <option value="">Todos</option>
                        @foreach($municipios as $mun)
                            <option value="{{ $mun->id }}" data-departamento="{{ $mun->departamento_id }}">{{ $mun->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search text-primary mr-1"></i>Buscar E14_ID
                    </label>
                    <input type="text" id="filtro-e14id" class="form-control" placeholder="Buscar por E14_ID...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search text-primary mr-1"></i>Buscar Mesa
                    </label>
                    <input type="text" id="filtro-mesa" class="form-control" placeholder="Buscar por mesa...">
                </div>
            </div>

            {{-- Nuevo filtro de Estado de Coincidencia --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-check2-circle text-primary mr-1"></i>Estado de Coincidencia (Votos Urna vs Suma E14)
                    </label>
                    <select id="filtro-coincidencia" class="form-control">
                        <option value="">Todos los registros</option>
                        <option value="iguales">✅ E14 correctos(sin diferencia)</option>
                        <option value="diferentes">❌ Con(con discrepancia)</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    {{-- ms-2 → ml-2 --}}
                    <div class="alert alert-info mb-0 py-2 w-100" id="info-filtro">
                        <i class="bi bi-info-circle mr-2"></i>
                        <span id="info-texto">Mostrando todos los registros</span>
                        <span class="badge bg-secondary ml-2" id="total-registros">0</span>
                    </div>
                </div>
            </div>

            {{-- Tabla de E14s --}}
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-e14">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>E14_ID</th>
                            <th>Departamento</th>
                            <th>Municipio</th>
                            <th>Puesto</th>
                            <th>Mesa</th>
                            <th>Total Votantes</th>
                            <th>Total Votos</th>
                            <th>Suma Votos</th>
                            <th>Diferencia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-e14">
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-3 text-muted">Cargando reportes...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <nav id="paginacion-container" class="mt-4"></nav>
        </div>
    </div>
</div>

{{-- Modal Ver Detalles - data-bs-dismiss → data-dismiss, btn-close → close --}}
<div class="modal fade" id="modalVerE14" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-eye mr-2"></i>Detalles del Reporte E14
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modal-body-ver">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar - data-bs-dismiss → data-dismiss, btn-close → close --}}
<div class="modal fade" id="modalEditarE14" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square mr-2"></i>Editar Reporte E14
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modal-body-editar">
                <form id="form-editar-e14">
                    <!-- El contenido se cargará dinámicamente -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-guardar-edicion" class="btn btn-success">
                    <i class="bi bi-check-circle mr-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>
  </div>
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.rounded-4 {
    border-radius: 1rem !important;
}

.rounded-top-4 {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
}

/* fw-bold y fw-semibold no existen en BS4, añadimos soporte */
.fw-bold {
    font-weight: 700 !important;
}

.fw-semibold {
    font-weight: 600 !important;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
}

.table thead th {
    border: none;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Estilos para resaltar filas con discrepancias */
.table tbody tr.fila-diferente {
    background-color: rgba(220, 53, 69, 0.1);
}

.table tbody tr.fila-diferente:hover {
    background-color: rgba(220, 53, 69, 0.2);
}

.table tbody tr.fila-igual {
    background-color: rgba(25, 135, 84, 0.05);
}

.table tbody tr.fila-igual:hover {
    background-color: rgba(25, 135, 84, 0.1);
}

.btn-group .btn {
    padding: 0.375rem 0.75rem;
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* bg-success, bg-danger, bg-secondary para badges en BS4 */
.badge.bg-success {
    background-color: #28a745 !important;
    color: #fff;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: #fff;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: #fff;
}

.badge.bg-primary {
    background-color: #007bff !important;
    color: #fff;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #0d6efd;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.info-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.1rem;
    color: #212529;
    font-weight: 500;
}

.pagination {
    justify-content: center;
}

.pagination .page-link {
    border-radius: 0.5rem;
    margin: 0 0.25rem;
    border: 2px solid #0d6efd;
    color: #0d6efd;
    font-weight: 600;
}

.pagination .page-link:hover {
    background-color: #0d6efd;
    color: white;
    transform: translateY(-2px);
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* close button styling para BS4 */
.close {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    opacity: 0.8;
}

.close:hover {
    opacity: 1;
}
</style>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let e14Data = [];
    let e14DataFiltrada = [];
    let paginaActual = 1;
    const itemsPorPagina = 10;

    // Datos de índice
    const departamentos = @json($departamentos);
    const municipios = @json($municipios);
    const puestos = @json($puestos);
    const candidatos = @json($candidatos ?? []);

    const $modalVer = window.jQuery('#modalVerE14');
    const $modalEditar = window.jQuery('#modalEditarE14');

    // Cargar datos al inicio
    cargarE14s();

    // Filtros
    document.getElementById('filtro-departamento').addEventListener('change', filtrarTabla);
    document.getElementById('filtro-municipio').addEventListener('change', filtrarTabla);
    document.getElementById('filtro-e14id').addEventListener('input', filtrarTabla);
    document.getElementById('filtro-mesa').addEventListener('input', filtrarTabla);
    document.getElementById('filtro-coincidencia').addEventListener('change', filtrarTabla);

    // Función para cargar todos los E14s
    async function cargarE14s() {
        try {
            const res = await axios.get('{{ route('testigos.e14.listar') }}', {
                headers: { 'Accept': 'application/json' }
            });
            
            e14Data = res.data.data || res.data;
            e14DataFiltrada = [...e14Data];
            renderizarTabla();
            renderizarPaginacion();
            actualizarInfoFiltro();
        } catch (err) {
            console.error('Error cargando E14s:', err);
            document.getElementById('tbody-e14').innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-3">Error al cargar los reportes</p>
                    </td>
                </tr>
            `;
        }
    }

    function actualizarInfoFiltro() {
        const filtroCoincidencia = document.getElementById('filtro-coincidencia').value;
        const infoTexto = document.getElementById('info-texto');
        const totalRegistros = document.getElementById('total-registros');
        const alertInfo = document.getElementById('info-filtro');
        
        totalRegistros.textContent = e14DataFiltrada.length + ' registros';
        
        if (filtroCoincidencia === 'diferentes') {
            infoTexto.textContent = 'Mostrando registros con DISCREPANCIAS (Votos Urna ≠ Suma E14)';
            alertInfo.className = 'alert alert-danger mb-0 py-2 w-100';
            totalRegistros.className = 'badge bg-danger ml-2';
        } else if (filtroCoincidencia === 'iguales') {
            infoTexto.textContent = 'Mostrando registros CORRECTOS (Votos Urna = Suma E14)';
            alertInfo.className = 'alert alert-success mb-0 py-2 w-100';
            totalRegistros.className = 'badge bg-success ml-2';
        } else {
            infoTexto.textContent = 'Mostrando todos los registros';
            alertInfo.className = 'alert alert-info mb-0 py-2 w-100';
            totalRegistros.className = 'badge bg-secondary ml-2';
        }
    }

    // Función para filtrar tabla
    function filtrarTabla() {
        const depId = document.getElementById('filtro-departamento').value;
        const munId = document.getElementById('filtro-municipio').value;
        const e14id = document.getElementById('filtro-e14id').value.toLowerCase();
        const mesa = document.getElementById('filtro-mesa').value.toLowerCase();
        const coincidencia = document.getElementById('filtro-coincidencia').value;

        e14DataFiltrada = e14Data.filter(item => {
            const matchDep = !depId || item.DEPARTAMENTO == depId;
            const matchMun = !munId || item.MUNICIPIO == munId;
            const matchE14 = !e14id || (item.E14_ID && item.E14_ID.toLowerCase().includes(e14id));
            const matchMesa = !mesa || (item.MESA && item.MESA.toLowerCase().includes(mesa));
            
            const totalVotosUrna = parseInt(item['TOTAL_VOTOS-URNA']) || 0;
            const sumaVotosE14 = parseInt(item['SUMA_VOTOS-E14']) || 0;
            const diferencia = totalVotosUrna - sumaVotosE14;
            
            let matchCoincidencia = true;
            if (coincidencia === 'iguales') {
                matchCoincidencia = diferencia === 0;
            } else if (coincidencia === 'diferentes') {
                matchCoincidencia = diferencia !== 0;
            }
            
            return matchDep && matchMun && matchE14 && matchMesa && matchCoincidencia;
        });

        paginaActual = 1;
        renderizarTabla();
        renderizarPaginacion();
        actualizarInfoFiltro();
    }

    // Función para renderizar tabla
    function renderizarTabla() {
        const inicio = (paginaActual - 1) * itemsPorPagina;
        const fin = inicio + itemsPorPagina;
        const items = e14DataFiltrada.slice(inicio, fin);

        if (items.length === 0) {
            document.getElementById('tbody-e14').innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="mt-3 text-muted">No se encontraron reportes</p>
                    </td>
                </tr>
            `;
            return;
        }

        const html = items.map(item => {
            const dep = departamentos.find(d => d.id == item.DEPARTAMENTO);
            const mun = municipios.find(m => m.id == item.MUNICIPIO);
            const puesto = puestos.find(p => p.id == item.PUESTO);
            
            const diferencia = (item['TOTAL_VOTOS-URNA'] || 0) - (item['SUMA_VOTOS-E14'] || 0);
            const badgeClass = diferencia === 0 ? 'bg-success' : 'bg-danger';
            const filaClass = diferencia === 0 ? 'fila-igual' : 'fila-diferente';

            return `
                <tr class="${filaClass}">
                    <td class="fw-bold">${item.E14_ID || '-'}</td>
                    <td>${dep ? dep.nombre : '-'}</td>
                    <td>${mun ? mun.nombre : '-'}</td>
                    <td>${puesto ? puesto.nombre : '-'}</td>
                    <td><span class="badge bg-primary">${item.MESA || '-'}</span></td>
                    <td>${item['TOTAL_VOTANTES-E11'] || 0}</td>
                    <td>${item['TOTAL_VOTOS-URNA'] || 0}</td>
                    <td>${item['SUMA_VOTOS-E14'] || 0}</td>
                    <td><span class="badge ${badgeClass}">${diferencia >= 0 ? '+' : ''}${diferencia}</span></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info text-white" onclick="verE14(${item.ID})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning text-white" onclick="editarE14(${item.ID})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarE14(${item.ID})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('tbody-e14').innerHTML = html;
    }

    // Función para renderizar paginación
    function renderizarPaginacion() {
        const totalPaginas = Math.ceil(e14DataFiltrada.length / itemsPorPagina);
        
        if (totalPaginas <= 1) {
            document.getElementById('paginacion-container').innerHTML = '';
            return;
        }

        let html = '<ul class="pagination">';
        
        // Anterior
        html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1}); return false;">Anterior</a>
        </li>`;

        // Páginas
        for (let i = 1; i <= totalPaginas; i++) {
            html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
            </li>`;
        }

        // Siguiente
        html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1}); return false;">Siguiente</a>
        </li>`;

        html += '</ul>';
        document.getElementById('paginacion-container').innerHTML = html;
    }

    // Función para cambiar página
    window.cambiarPagina = function(pagina) {
    const totalPaginas = Math.ceil(e14DataFiltrada.length / itemsPorPagina);

    if (pagina < 1 || pagina > totalPaginas) return;

    paginaActual = pagina;
    renderizarTabla();
    renderizarPaginacion();
};

    window.verE14 = async function(id) {
    $modalVer.modal('show');
    document.getElementById('modal-body-ver').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    `;

    try {
        const res = await axios.get(`{{ url('testigos/e14') }}/${id}`, {
            headers: { 'Accept': 'application/json' }
        });

        const data = res.data.data || res.data;

        const dep = departamentos.find(d => d.id == data.DEPARTAMENTO);
        const mun = municipios.find(m => m.id == data.MUNICIPIO);
        const puesto = puestos.find(p => p.id == data.PUESTO);

        const archivo = data.ARCHIVO;
        let previewHtml = `
            <div class="alert alert-warning">
                <i class="bi bi-file-earmark-excel mr-2"></i>
                Este registro no tiene archivo adjunto
            </div>
        `;

        if (archivo) {

    const urlArchivo = `{{ url('/public/') }}/${archivo}`;
    const extension = archivo.split('.').pop().toLowerCase();

    if (extension === 'pdf') {
        previewHtml = `
            <iframe 
                src="${urlArchivo}" 
                width="100%" 
                height="500"
                style="border:1px solid #ddd; border-radius:8px;">
            </iframe>

            <a href="${urlArchivo}" 
               class="btn btn-primary mt-3" 
               target="_blank" 
               download>
                <i class="bi bi-download mr-1"></i> Descargar PDF
            </a>
        `;
    } 
    else if (['jpg', 'jpeg', 'png', 'webp'].includes(extension)) {
        previewHtml = `
            <img 
                src="${urlArchivo}" 
                class="img-fluid rounded shadow mb-3"
                alt="Archivo E14">

            <a href="${urlArchivo}" 
               class="btn btn-primary" 
               target="_blank" 
               download>
                <i class="bi bi-download mr-1"></i> Descargar imagen
            </a>
        `;
    }
    else {
        previewHtml = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle mr-2"></i>
                Tipo de archivo no soportado
            </div>
        `;
    }

} else {
    previewHtml = `
        <div class="alert alert-warning">
            <i class="bi bi-file-earmark-excel mr-2"></i>
            Este registro no tiene archivo adjunto
        </div>
    `;
}


        const html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-label">E14_ID</div>
                        <div class="info-value">${data.E14_ID || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-label">Mesa</div>
                        <div class="info-value">${data.MESA || '-'}</div>
                    </div>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">Resultados de Votación</h5>

            <div class="row">
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-label">Total Votantes (E11)</div>
                        <div class="info-value">${data['TOTAL_VOTANTES-E11'] || 0}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-label">Total Votos Urna</div>
                        <div class="info-value">${data['TOTAL_VOTOS-URNA'] || 0}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-label">Suma Votos E14</div>
                        <div class="info-value">${data['SUMA_VOTOS-E14'] || 0}</div>
                    </div>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">Archivo E14</h5>
            ${previewHtml}
        `;

        document.getElementById('modal-body-ver').innerHTML = html;

    } catch (error) {
        console.error(error);
        document.getElementById('modal-body-ver').innerHTML = `
            <div class="alert alert-danger">
                Error al cargar el reporte
            </div>
        `;
    }
};


    window.editarE14 = async function(id) {
        $modalEditar.modal('show');
        document.getElementById('modal-body-editar').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `;

        try {
            const res = await axios.get(`{{ url('testigos/e14') }}/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            
            const data = res.data.data || res.data;

            let html = `
                <form id="form-editar-e14">
                    <input type="hidden" name="id" value="${data.ID}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-semibold">E14_ID</label>
                                <input type="text" class="form-control" name="E14_ID" value="${data.E14_ID || ''}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Mesa</label>
                                <input type="text" class="form-control" name="MESA" value="${data.MESA || ''}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Total Votantes (E11)</label>
                                <input type="number" class="form-control" name="TOTAL_VOTANTES-E11" value="${data['TOTAL_VOTANTES-E11'] || 0}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Total Votos Urna</label>
                                <input type="number" class="form-control" name="TOTAL_VOTOS-URNA" value="${data['TOTAL_VOTOS-URNA'] || 0}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Suma Votos E14</label>
                                <input type="number" class="form-control" name="SUMA_VOTOS-E14" value="${data['SUMA_VOTOS-E14'] || 0}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Observación</label>
                                <textarea class="form-control" name="OBSERVACION" rows="3" placeholder="Ingrese una observación...">${data.OBSERVACION || ''}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
            `;

            document.getElementById('modal-body-editar').innerHTML = html;
        } catch (err) {
            console.error('Error cargando datos para editar:', err);
            document.getElementById('modal-body-editar').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle mr-2"></i>
                    Error al cargar los datos para editar
                </div>
            `;
        }
    };

    // Guardar edición
    document.getElementById('btn-guardar-edicion').addEventListener('click', async function() {
        const form = document.getElementById('form-editar-e14');
        const formData = new FormData(form);
        const id = formData.get('id');

        try {
            await axios.put(`{{ url('testigos/e14') }}/${id}`, Object.fromEntries(formData), {
                headers: { 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $modalEditar.modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'El reporte se actualizó correctamente',
                timer: 2000,
                showConfirmButton: false
            });

            cargarE14s();
        } catch (err) {
            console.error('Error guardando:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo guardar el reporte'
            });
        }
    });

    // Eliminar E14
    window.eliminarE14 = async function(id) {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                await axios.delete(`{{ url('testigos/e14') }}/${id}`, {
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: 'El reporte fue eliminado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                });

                cargarE14s();
            } catch (err) {
                console.error('Error eliminando:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo eliminar el reporte'
                });
            }
        }
    };
});
</script>
@endsection
