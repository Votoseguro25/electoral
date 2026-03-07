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
                        <i class="bi bi-funnel text-primary mr-1"></i>Puesto
                    </label>
                    <select id="filtro-puesto" class="form-control">
                        <option value="">Todos</option>
                        {{-- Los puestos se cargarán dinámicamente según el municipio --}}
                    </select>
                </div>
            </div>

            {{-- Segunda fila de filtros --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search text-primary mr-1"></i>Buscar E14_ID
                    </label>
                    <input type="text" id="filtro-e14id" class="form-control" placeholder="Buscar por E14_ID...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search text-primary mr-1"></i>Buscar Mesa
                    </label>
                    <input type="text" id="filtro-mesa" class="form-control" placeholder="Buscar por mesa...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-check2-circle text-primary mr-1"></i>Coincidencia
                    </label>
                    <select id="filtro-coincidencia" class="form-control">
                        <option value="">Todos los registros</option>
                        <option value="iguales">E14 correctos (sin diferencia)</option>
                        <option value="diferentes">Con discrepancia</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-flag text-primary mr-1"></i>Estado Revisión
                    </label>
                    <select id="filtro-estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="0">Sin revisar</option>
                        <option value="1">Revisado sin novedad</option>
                        <option value="2">Revisado con novedad</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
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
                            <!--<th>E14_ID</th>-->
                            <th>Departamento</th>
                            <th>Municipio</th>
                            <th>Puesto</th>
                            <th>Mesa</th>
                            <th>Total Votantes</th>
                            <th>Total Votos</th>
                            <th>Suma Votos</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-e14">
                        <tr>
                            <td colspan="11" class="text-center py-5">
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

{{-- Modal Cambiar Estado --}}
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard-check mr-2"></i>Marcar como Revisado
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="estadoE14Id">
                
                <p class="text-muted mb-4">Selecciona el tipo de revisión para este reporte E14:</p>
                
                {{-- Botones de selección --}}
                <div class="d-grid gap-3 mb-4">
                    <button type="button" class="btn btn-lg btn-outline-success py-3 btn-revision d-flex align-items-center" data-tipo="sin-novedad">
                        <i class="bi bi-check-circle-fill mr-3" style="font-size: 1.5rem;"></i>
                        <div class="text-left">
                            <span class="fw-bold d-block">Revisado SIN Novedad</span>
                            <small class="text-muted">Todo correcto, sin observaciones</small>
                        </div>
                    </button>
                    
                    <button type="button" class="btn btn-lg btn-outline-warning py-3 btn-revision d-flex align-items-center" data-tipo="con-novedad">
                        <i class="bi bi-exclamation-triangle-fill mr-3" style="font-size: 1.5rem;"></i>
                        <div class="text-left">
                            <span class="fw-bold d-block">Revisado CON Novedad</span>
                            <small class="text-muted">Requiere documentar observaciones</small>
                        </div>
                    </button>
                </div>
                
                {{-- Textarea para novedad (oculto por defecto) --}}
                <div id="containerNovedad" class="d-none">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-info-circle mr-2"></i>
                        <strong>Describe la novedad encontrada:</strong>
                    </div>
                    <textarea 
                        id="textoNovedad" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Describe detalladamente la novedad encontrada en este reporte E14..."
                        style="resize: none;"></textarea>
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-lightbulb mr-1"></i>
                        Ejemplo: Diferencia de votos, datos ilegibles, inconsistencias, etc.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-lg mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary d-none" id="btnConfirmarEstado">
                    <i class="bi bi-check-lg mr-1"></i>Confirmar Revisión
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
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* =============================================
   ESTILOS PARA FILAS SEGÚN ESTADO
   Estado 0 + Diferencia != 0 = Rojo (pendiente con discrepancia)
   Estado 1 = Verde (revisado sin novedad)
   Estado 2 = Amarillo (revisado con novedad)
============================================= */

/* Rojo - Pendiente con discrepancia (sin revisar) */
.table tbody tr.fila-pendiente-discrepancia {
    background-color: #f8d7da !important;
    border-left: 4px solid #dc3545;
}

.table tbody tr.fila-pendiente-discrepancia:hover {
    background-color: #f1aeb5 !important;
}

/* Verde - Revisado sin novedad */
.table tbody tr.fila-revisado-sin-novedad {
    background-color: #d1e7dd !important;
    border-left: 4px solid #198754;
}

.table tbody tr.fila-revisado-sin-novedad:hover {
    background-color: #badbcc !important;
}

/* Amarillo - Revisado con novedad */
.table tbody tr.fila-revisado-con-novedad {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107;
}

.table tbody tr.fila-revisado-con-novedad:hover {
    background-color: #ffe69c !important;
}

/* Filas sin discrepancia y sin revisar - color normal */
.table tbody tr.fila-normal {
    background-color: rgba(25, 135, 84, 0.05);
}

.table tbody tr.fila-normal:hover {
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

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* Estilos para badges de estado clickeables */
.badge-estado {
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem;
    white-space: nowrap;
}

.badge-estado:hover {
    opacity: 0.85;
    transform: scale(1.05);
}

.badge-estado.bg-success {
    cursor: default;
}

.badge-estado.bg-success:hover {
    transform: none;
    opacity: 1;
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

/* Estilos para botones de revisión en modal */
.btn-revision {
    transition: all 0.3s ease;
    border-width: 2px;
}

.btn-revision:hover {
    transform: translateX(5px);
}

.btn-revision.active {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.btn-revision[data-tipo="sin-novedad"].active {
    background-color: #198754 !important;
    border-color: #198754 !important;
    color: white !important;
}

.btn-revision[data-tipo="sin-novedad"].active .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.btn-revision[data-tipo="con-novedad"].active {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #212529 !important;
}

.btn-revision[data-tipo="con-novedad"].active .text-muted {
    color: rgba(0,0,0,0.6) !important;
}

.d-grid {
    display: grid;
}

.gap-3 {
    gap: 1rem;
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
    let tipoRevisionSeleccionado = null;

    // Datos de índice
    const departamentos = @json($departamentos);
    const municipios = @json($municipios);
    const puestos = @json($puestos);
    const candidatos = @json($candidatos ?? []);

    const $modalVer = window.jQuery('#modalVerE14');
    const $modalEditar = window.jQuery('#modalEditarE14');
    const $modalEstado = window.jQuery('#modalCambiarEstado');

    // =============================================
    // Función para obtener la clase de la fila según estado
    // =============================================
    function getFilaClase(item, diferencia) {
        const estado = parseInt(item.Estado) || 0;

        if (estado === 1) {
            // Revisado sin novedad = Verde
            return 'fila-revisado-sin-novedad';
        } else if (estado === 2) {
            // Revisado con novedad = Amarillo
            return 'fila-revisado-con-novedad';
        } else {
            // Estado 0 = Sin revisar
            if (diferencia !== 0) {
                // Hay discrepancia = Rojo
                return 'fila-pendiente-discrepancia';
            } else {
                // Sin discrepancia = Normal
                return 'fila-pendiente-discrepancia';
            }
        }
    }

    // =============================================
    // Función para generar el badge de estado
    // =============================================
    function generarBadgeEstado(item, diferencia) {
        const estado = parseInt(item.Estado) || 0;

        if (estado === 1) {
            // Revisado sin novedad - badge verde, no clickeable
            return `<span class="badge bg-success badge-estado" title="Revisado sin novedad">
                        <i class="bi bi-check-circle mr-1"></i>Revisado, Sin Novedad
                    </span>`;
        } else if (estado === 2) {
            // Revisado con novedad - badge amarillo, no clickeable
            const novedad = item.NOVEDAD || 'Sin descripción';
            return `<span class="badge bg-warning badge-estado" title="${novedad}">
                        <i class="bi bi-exclamation-triangle mr-1"></i>Revisado, Con Novedad
                    </span>`;
        } else {
            // Estado 0 = Sin revisar
            if (diferencia !== 0) {
                // Hay diferencia => Sin revisar CON discrepancia (rojo)
                return `<span class="badge bg-danger badge-estado" 
                            onclick="abrirModalEstado(${item.ID})" 
                            title="Hay diferencia en los votos. Clic para marcar como revisado">
                            <i class="bi bi-exclamation-circle mr-1"></i>Pendiente
                        </span>`;
            } else {
                // No hay diferencia => Sin revisar (gris)
                return `<span class="badge bg-danger badge-estado" 
                            onclick="abrirModalEstado(${item.ID})" 
                            title="Sin diferencia. Clic para marcar como revisado">
                            <i class="bi bi-exclamation-circle mr-1"></i>Pendiente
                        </span>`;
            }
        }
    }

    // Cargar datos al inicio
    cargarE14s();

    // Filtros
    document.getElementById('filtro-departamento').addEventListener('change', function() {
        actualizarMunicipios();
        actualizarPuestos();
        filtrarTabla();
    });
    document.getElementById('filtro-municipio').addEventListener('change', function() {
        actualizarPuestos();
        filtrarTabla();
    });
    document.getElementById('filtro-puesto').addEventListener('change', filtrarTabla);
    document.getElementById('filtro-e14id').addEventListener('input', filtrarTabla);
    document.getElementById('filtro-mesa').addEventListener('input', filtrarTabla);
    document.getElementById('filtro-coincidencia').addEventListener('change', filtrarTabla);
    document.getElementById('filtro-estado').addEventListener('change', filtrarTabla);

    // Función para actualizar municipios según el departamento seleccionado
    function actualizarMunicipios() {
        const depId = document.getElementById('filtro-departamento').value;
        const selectMunicipio = document.getElementById('filtro-municipio');
        
        // Limpiar opciones actuales
        selectMunicipio.innerHTML = '<option value="">Todos</option>';
        
        if (depId) {
            // Filtrar municipios que pertenecen al departamento seleccionado
            const municipiosFiltrados = municipios.filter(m => m.departamento_id == depId);
            municipiosFiltrados.forEach(mun => {
                const option = document.createElement('option');
                option.value = mun.id;
                option.textContent = mun.nombre;
                option.dataset.departamento = mun.departamento_id;
                selectMunicipio.appendChild(option);
            });
        } else {
            // Si no hay departamento seleccionado, mostrar todos los municipios
            municipios.forEach(mun => {
                const option = document.createElement('option');
                option.value = mun.id;
                option.textContent = mun.nombre;
                option.dataset.departamento = mun.departamento_id;
                selectMunicipio.appendChild(option);
            });
        }
        
        // Resetear puesto cuando cambia el municipio
        actualizarPuestos();
    }

    // Función para actualizar puestos según el departamento y municipio seleccionados
    function actualizarPuestos() {
        const depId = document.getElementById('filtro-departamento').value;
        const munId = document.getElementById('filtro-municipio').value;
        const selectPuesto = document.getElementById('filtro-puesto');
        
        // Limpiar opciones actuales
        selectPuesto.innerHTML = '<option value="">Todos</option>';
        
        let puestosFiltrados = [];
        
        if (munId) {
            // Si hay municipio seleccionado, filtrar puestos por municipio
            puestosFiltrados = puestos.filter(p => p.municipio_id == munId);
        } else if (depId) {
            // Si solo hay departamento, obtener municipios del departamento y luego sus puestos
            const municipiosDelDep = municipios.filter(m => m.departamento_id == depId).map(m => m.id);
            puestosFiltrados = puestos.filter(p => municipiosDelDep.includes(p.municipio_id));
        } else {
            // Si no hay filtros, mostrar todos los puestos
            puestosFiltrados = puestos;
        }
        
        puestosFiltrados.forEach(puesto => {
            const option = document.createElement('option');
            option.value = puesto.id;
            option.textContent = puesto.nombre;
            selectPuesto.appendChild(option);
        });
    }

    // Precargar departamento del Chocó al inicio
    function precargarChoco() {
        const selectDepartamento = document.getElementById('filtro-departamento');
        // Buscar el Chocó en los departamentos (puede ser "Chocó", "CHOCO" o "CHOCÓ")
        const choco = departamentos.find(d => 
            d.nombre.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") === 'choco' ||
            d.nombre.toLowerCase() === 'chocó' ||
            d.nombre.toLowerCase() === 'choco'
        );
        
        if (choco) {
            selectDepartamento.value = choco.id;
            actualizarMunicipios();
        }
    }

    // Inicializar: precargar Chocó
    precargarChoco();

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
                    <td colspan="11" class="text-center py-5 text-danger">
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
        const puestoId = document.getElementById('filtro-puesto').value;
        const e14id = document.getElementById('filtro-e14id').value.toLowerCase();
        const mesa = document.getElementById('filtro-mesa').value.toLowerCase();
        const coincidencia = document.getElementById('filtro-coincidencia').value;
        const estadoFiltro = document.getElementById('filtro-estado').value;

        e14DataFiltrada = e14Data.filter(item => {
            const matchDep = !depId || item.DEPARTAMENTO == depId;
            const matchMun = !munId || item.MUNICIPIO == munId;
            const matchPuesto = !puestoId || item.PUESTO == puestoId;
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

            const matchEstado = estadoFiltro === '' || item.Estado == estadoFiltro;
            
            return matchDep && matchMun && matchPuesto && matchE14 && matchMesa && matchCoincidencia && matchEstado;
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
                    <td colspan="11" class="text-center py-5">
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
            const filaClass = getFilaClase(item, diferencia);
            
            return `
                <tr class="${filaClass}">
                    <td>${dep ? dep.nombre : '-'}</td>
                    <td>${mun ? mun.nombre : '-'}</td>
                    <td>${puesto ? puesto.nombre : '-'}</td>
                    <td><span class="badge bg-primary">${item.MESA || '-'}</span></td>
                    <td>${item['TOTAL_VOTANTES-E11'] || 0}</td>
                    <td>${item['TOTAL_VOTOS-URNA'] || 0}</td>
                    <td>${item['SUMA_VOTOS-E14'] || 0}</td>
                    <td><span class="badge ${badgeClass}">${diferencia >= 0 ? '+' : ''}${diferencia}</span></td>
                    <td>${generarBadgeEstado(item, diferencia)}</td>
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

    // =============================================
    // Abrir modal para cambiar estado
    // =============================================
    window.abrirModalEstado = function(id) {
        document.getElementById('estadoE14Id').value = id;
        document.getElementById('containerNovedad').classList.add('d-none');
        document.getElementById('textoNovedad').value = '';
        document.getElementById('btnConfirmarEstado').classList.add('d-none');
        
        // Resetear botones
        document.querySelectorAll('.btn-revision').forEach(btn => {
            btn.classList.remove('active');
        });
        
        tipoRevisionSeleccionado = null;
        $modalEstado.modal('show');
    };

    // Manejar selección de tipo de revisión
    document.querySelectorAll('.btn-revision').forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.dataset.tipo;
            
            // Resetear todos los botones
            document.querySelectorAll('.btn-revision').forEach(b => b.classList.remove('active'));
            
            // Activar el seleccionado
            this.classList.add('active');
            tipoRevisionSeleccionado = tipo;
            
            // Mostrar/ocultar textarea según tipo
            const containerNovedad = document.getElementById('containerNovedad');
            const btnConfirmar = document.getElementById('btnConfirmarEstado');
            
            if (tipo === 'con-novedad') {
                containerNovedad.classList.remove('d-none');
                btnConfirmar.classList.remove('d-none');
                document.getElementById('textoNovedad').focus();
            } else {
                containerNovedad.classList.add('d-none');
                btnConfirmar.classList.remove('d-none');
            }
        });
    });

    // Confirmar cambio de estado
    document.getElementById('btnConfirmarEstado').addEventListener('click', async () => {
        const id = document.getElementById('estadoE14Id').value;
        const novedad = document.getElementById('textoNovedad').value.trim();
        
        // Validar que si es con novedad, tenga texto
        if (tipoRevisionSeleccionado === 'con-novedad' && !novedad) {
            Swal.fire({
                icon: 'warning',
                title: 'Novedad requerida',
                text: 'Debes describir la novedad encontrada',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Estado: 1 = sin novedad, 2 = con novedad
        const nuevoEstado = tipoRevisionSeleccionado === 'sin-novedad' ? 1 : 2;

        try {
            Swal.fire({
                title: 'Guardando...',
                html: '<div class="spinner-border text-primary"></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });

            await axios.put(`{{ url('testigos/e14') }}/${id}/estado`, {
                Estado: nuevoEstado,
                NOVEDAD: novedad || null
            }, {
                headers: { 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $modalEstado.modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Estado actualizado',
                text: tipoRevisionSeleccionado === 'sin-novedad' 
                    ? 'Marcado como revisado sin novedad' 
                    : 'Marcado como revisado con novedad',
                confirmButtonColor: nuevoEstado === 1 ? '#198754' : '#ffc107',
                timer: 2000
            });

            cargarE14s();

        } catch (err) {
            console.error('Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo actualizar el estado'
            });
        }
    });

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

        // Mostrar estado y novedad
        let estadoHtml = '';
        const estado = parseInt(data.Estado) || 0;
        if (estado === 1) {
            estadoHtml = `<span class="badge bg-success">Revisado sin novedad</span>`;
        } else if (estado === 2) {
            estadoHtml = `
                <span class="badge bg-warning mb-2">Revisado con novedad</span>
                <div class="alert alert-warning mt-2 mb-0">
                    <strong>Novedad:</strong> ${data.NOVEDAD || 'Sin descripción'}
                </div>
            `;
        } else {
            estadoHtml = `<span class="badge bg-secondary">Sin revisar</span>`;
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

            <div class="row">
                <div class="col-md-12">
                    <div class="info-card">
                        <div class="info-label">Estado de Revisión</div>
                        <div class="info-value">${estadoHtml}</div>
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
