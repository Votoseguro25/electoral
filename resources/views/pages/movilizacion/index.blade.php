@extends('layouts.bootstrap')

@section('titulo', 'Movilizacion Dia D')

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 20px 25px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .page-header h4 {
        margin: 0;
        font-weight: 600;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #eee;
        padding: 15px 20px;
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
    }

    .card-header i {
        margin-right: 8px;
    }

    .card-body {
        padding: 20px;
    }

    /* Estadisticas principales */
    .stat-card {
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        color: white;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .stat-card.danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .stat-card.warning { background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%); }
    .stat-card.info { background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); }

    .stat-card .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
        margin-bottom: 8px;
    }

    .stat-card .stat-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 5px;
    }

    .stat-card .stat-label {
        font-size: 0.8rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .stat-sublabel {
        font-size: 0.7rem;
        opacity: 0.7;
        margin-top: 5px;
    }

    /* Busqueda rapida */
    .search-box {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 12px;
        padding: 20px;
        color: white;
    }

    .search-box .input-group {
        display: flex;
        align-items: stretch;
    }

    .search-box .form-control {
        font-size: 1rem;
        padding: 10px 16px;
        border-radius: 10px 0 0 10px;
        border: 2px solid rgba(255,255,255,0.3);
        border-right: none;
        background: rgba(255,255,255,0.95);
        height: 46px;
        flex: 1;
    }

    .search-box .form-control:focus {
        border-color: white;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
    }

    .search-box .btn-confirmar {
        background: white;
        color: #28a745;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 0 10px 10px 0;
        font-size: 0.95rem;
        height: 46px;
        white-space: nowrap;
    }

    .search-box .btn-confirmar:hover {
        background: #f8f9fa;
    }

    /* Resultado de busqueda */
    .search-result {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
        display: none;
    }

    .search-result.show {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .search-result .persona-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .search-result .persona-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #667eea;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .search-result .badge-voto {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    /* Tabla de pendientes */
    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #eee;
        font-weight: 600;
        font-size: 0.8rem;
        color: #555;
        padding: 10px 12px;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 0.85rem;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .badge-estado {
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Tabla de lideres */
    .lider-row {
        cursor: pointer;
        transition: all 0.2s;
    }

    .lider-row:hover {
        background: #e9ecef !important;
    }

    .lider-progress {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
    }

    .lider-progress-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    /* Filtros */
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .filter-group {
        margin-bottom: 10px;
    }

    .filter-group:last-child {
        margin-bottom: 0;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #666;
        margin-bottom: 4px;
        display: block;
    }

    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        font-size: 0.85rem;
        width: 100%;
        max-width: 100%;
    }

    .form-select:focus, .form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.15);
    }

    .form-check {
        margin: 0;
        padding-left: 1.5rem;
    }

    .form-check-label {
        font-size: 0.8rem;
    }

    /* Botones */
    .btn-danger-gradient {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .btn-danger-gradient:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        color: white;
    }

    .btn-success-gradient {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
    }

    /* Loading */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
    }

    .loading-overlay.active {
        display: flex;
    }

    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.03); }
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    /* Pagination */
    .pagination {
        margin: 0;
    }

    .page-link {
        border-radius: 6px !important;
        margin: 0 2px;
        border: none;
        color: #dc3545;
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
    }

    /* Votos ultima hora */
    .votos-hora-box {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }

    .votos-hora-box .numero {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .votos-hora-box .texto {
        font-size: 0.75rem;
        opacity: 0.9;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .stat-card {
            margin-bottom: 10px;
        }
        .stat-card .stat-number {
            font-size: 1.5rem;
        }
        .stat-card .stat-icon {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 767px) {
        .page-header {
            padding: 15px;
        }
        .search-box {
            padding: 15px;
        }
        .search-box .form-control {
            font-size: 1rem;
            padding: 10px;
        }
        .filter-section {
            padding: 12px;
        }
    }

    /* Auto-refresh indicator */
    .refresh-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 8px 16px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 1000;
    }

    .refresh-indicator .spinner-border {
        width: 0.9rem;
        height: 0.9rem;
    }

    /* Panel lideres scroll */
    .lideres-scroll {
        max-height: 450px;
        overflow-y: auto;
    }

    /* Tabla scroll */
    .tabla-scroll {
        max-height: 350px;
        overflow-y: auto;
    }
</style>
@endsection

@section('contenido')
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-danger" style="width: 3rem; height: 3rem;" role="status"></div>
    <p class="mt-3 text-muted">Procesando...</p>
</div>

<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4><i class="bi bi-bullseye me-2"></i>Movilizacion Dia D</h4>
            <small class="opacity-75">Convierte compromisos en votos reales</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark px-3 py-2" id="horaActual">
                <i class="bi bi-clock me-1"></i>{{ now()->format('H:i:s') }}
            </span>
            <button class="btn btn-light btn-sm" onclick="actualizarEstadisticas()">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
            </button>
        </div>
    </div>

    {{-- Estadisticas Principales --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card primary">
                <i class="bi bi-people stat-icon"></i>
                <div class="stat-number" id="statTotal">{{ number_format($estadisticas['total_votantes']) }}</div>
                <div class="stat-label">Total Votantes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card success">
                <i class="bi bi-check-circle stat-icon"></i>
                <div class="stat-number" id="statVotaron">{{ number_format($estadisticas['ya_votaron']) }}</div>
                <div class="stat-label">Ya Votaron</div>
                <div class="stat-sublabel"><span id="statPorcentaje">{{ $estadisticas['porcentaje_avance'] }}</span>% del total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card danger pulse">
                <i class="bi bi-exclamation-triangle stat-icon"></i>
                <div class="stat-number" id="statPendientes">{{ number_format($estadisticas['pendientes']) }}</div>
                <div class="stat-label">Pendientes</div>
                <div class="stat-sublabel"><span id="statConTelefono">{{ number_format($estadisticas['con_telefono_pendientes']) }}</span> con telefono</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card warning">
                <i class="bi bi-hand-thumbs-up stat-icon"></i>
                <div class="stat-number" id="statComprometidos">{{ number_format($estadisticas['comprometidos']) }}</div>
                <div class="stat-label">Comprometidos</div>
                <div class="stat-sublabel"><span id="statComprometidosVotaron">{{ number_format($estadisticas['comprometidos_votaron']) }}</span> votaron (<span id="statPorcentajeComp">{{ $estadisticas['porcentaje_comprometidos'] }}</span>%)</div>
            </div>
        </div>
    </div>

    {{-- Busqueda Rapida por Cedula --}}
    <div class="search-box mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8 col-md-7">
                <h5 class="mb-2"><i class="bi bi-search me-2"></i>Confirmar Voto por Cedula</h5>
                <div class="input-group">
                    <input type="text" class="form-control" id="inputCedula" placeholder="Ingrese el numero de cedula..." autofocus>
                    <button class="btn btn-confirmar" type="button" id="btnBuscar">
                        <i class="bi bi-search me-1"></i>Buscar
                    </button>
                </div>
            </div>
            <div class="col-lg-4 col-md-5">
                <div class="votos-hora-box">
                    <div class="numero" id="votacionesHora">{{ $estadisticas['votaciones_ultima_hora'] ?? 0 }}</div>
                    <div class="texto">votos en la ultima hora</div>
                </div>
            </div>
        </div>

        {{-- Resultado de busqueda --}}
        <div class="search-result" id="searchResult">
            <div class="persona-info" id="personaInfo">
                {{-- Se llena dinamicamente --}}
            </div>
            <div class="mt-3" id="accionesVoto">
                {{-- Botones de accion --}}
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Panel de Lideres --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge text-primary"></i>Avance por Lider</span>
                    <span class="badge bg-primary">{{ count($lideres) }}</span>
                </div>
                <div class="card-body p-0 lideres-scroll">
                    <table class="table table-hover mb-0 table-sm">
                        <thead>
                            <tr>
                                <th>Lider</th>
                                <th class="text-center" style="width: 90px;">Avance</th>
                                <th class="text-end" style="width: 50px;">Pend.</th>
                            </tr>
                        </thead>
                        <tbody id="tablaLideres">
                            @forelse($lideres as $lider)
                            <tr class="lider-row" onclick="filtrarPorLider({{ $lider['id'] }})">
                                <td>
                                    <strong class="d-block" style="font-size: 0.85rem;">{{ Str::limit($lider['nombre'], 18) }}</strong>
                                    @if($lider['telefono'])
                                    <small class="text-muted"><i class="bi bi-telephone"></i> {{ $lider['telefono'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="lider-progress mb-1">
                                        <div class="lider-progress-bar bg-success" style="width: {{ $lider['porcentaje'] }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $lider['votaron'] }}/{{ $lider['total_votantes'] }}</small>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-danger">{{ $lider['pendientes'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-person-x" style="font-size: 1.5rem; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2 small">No hay lideres registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel de Pendientes --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-list-check text-danger"></i>Votantes Pendientes</span>
                    <div class="d-flex gap-2 align-items-center">
                        <select class="form-select form-select-sm" id="perPageSelect" style="width: 70px;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                        <button class="btn btn-danger-gradient btn-sm" id="btnExportar">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filtros - Reorganizados --}}
                    <div class="filter-section">
                        {{-- Primera fila: Lider y ubicacion --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-3 col-6">
                                <label class="filter-label">Lider</label>
                                <select class="form-select form-select-sm" id="filtroLider">
                                    <option value="">Todos los lideres</option>
                                    @foreach($lideres as $lider)
                                    <option value="{{ $lider['id'] }}">{{ Str::limit($lider['nombre'], 20) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="filter-label">Departamento</label>
                                <select class="form-select form-select-sm" id="filtroDepartamento">
                                    <option value="">Todos</option>
                                    @foreach($departamentos as $depto)
                                    <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="filter-label">Municipio</label>
                                <select class="form-select form-select-sm" id="filtroMunicipio" disabled>
                                    <option value="">Seleccione depto.</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="filter-label">Puesto</label>
                                <select class="form-select form-select-sm" id="filtroPuesto">
                                    <option value="">Todos los puestos</option>
                                    @foreach($puestos as $puesto)
                                    <option value="{{ $puesto->id }}">{{ Str::limit($puesto->nombre, 25) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Segunda fila: Busqueda y checkboxes --}}
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5 col-12">
                                <label class="filter-label">Buscar</label>
                                <input type="text" class="form-control form-control-sm" id="busquedaPendiente" placeholder="Nombre o cedula...">
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="soloComprometidos">
                                        <label class="form-check-label" for="soloComprometidos">Solo comprometidos</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="conTelefono">
                                        <label class="form-check-label" for="conTelefono">Con telefono</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-12 text-md-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" id="btnLimpiarFiltros">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </button>
                                <button class="btn btn-sm btn-danger-gradient" id="btnFiltrar">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Info filtro --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            <strong id="totalFiltrado">0</strong> pendientes encontrados
                        </small>
                    </div>

                    {{-- Tabla --}}
                    <div class="tabla-scroll table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th class="d-none d-md-table-cell">Cedula</th>
                                    <th class="d-none d-lg-table-cell">Telefono</th>
                                    <th class="d-none d-xl-table-cell">Municipio</th>
                                    <th>Estado</th>
                                    <th class="text-center" style="width: 60px;">Accion</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPendientes">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                                        <p class="mb-0 mt-2 text-muted small">Cargando pendientes...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginacion --}}
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" id="paginacionContainer" style="display: none !important;">
                        <small class="text-muted" id="paginacionInfo"></small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginacionLinks"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Indicador de auto-refresh --}}
<div class="refresh-indicator" id="refreshIndicator" style="display: none;">
    <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
    <span class="small">Actualizando...</span>
</div>
@endsection

@section('scripts')
<script>
    const baseUrl = '{{ url("/") }}';
    let currentPage = 1;
    let perPage = 25;
    let autoRefreshInterval = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Cargar pendientes al iniciar
        cargarPendientes();

        // Configurar eventos
        document.getElementById('btnBuscar').addEventListener('click', buscarPorCedula);
        document.getElementById('inputCedula').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') buscarPorCedula();
        });

        document.getElementById('btnFiltrar').addEventListener('click', function() {
            currentPage = 1;
            cargarPendientes();
        });

        document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
        document.getElementById('btnExportar').addEventListener('click', exportarPendientes);

        document.getElementById('perPageSelect').addEventListener('change', function() {
            perPage = parseInt(this.value);
            currentPage = 1;
            cargarPendientes();
        });

        // Filtrar al presionar Enter en busqueda
        document.getElementById('busquedaPendiente').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                cargarPendientes();
            }
        });

        // Cascada departamento -> municipio
        document.getElementById('filtroDepartamento').addEventListener('change', cargarMunicipios);

        // Auto-refresh cada 30 segundos
        iniciarAutoRefresh();

        // Actualizar reloj
        setInterval(actualizarReloj, 1000);
    });

    function actualizarReloj() {
        const ahora = new Date();
        document.getElementById('horaActual').innerHTML =
            '<i class="bi bi-clock me-1"></i>' + ahora.toLocaleTimeString('es-CO');
    }

    function iniciarAutoRefresh() {
        autoRefreshInterval = setInterval(function() {
            document.getElementById('refreshIndicator').style.display = 'flex';
            actualizarEstadisticas();
            setTimeout(function() {
                document.getElementById('refreshIndicator').style.display = 'none';
            }, 1000);
        }, 30000);
    }

    function actualizarEstadisticas() {
        fetch(`${baseUrl}/movilizacion/api/estadisticas`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('statTotal').textContent = formatNumber(data.total_votantes);
                document.getElementById('statVotaron').textContent = formatNumber(data.ya_votaron);
                document.getElementById('statPendientes').textContent = formatNumber(data.pendientes);
                document.getElementById('statComprometidos').textContent = formatNumber(data.comprometidos);
                document.getElementById('statPorcentaje').textContent = data.porcentaje_avance;
                document.getElementById('statComprometidosVotaron').textContent = formatNumber(data.comprometidos_votaron);
                document.getElementById('statPorcentajeComp').textContent = data.porcentaje_comprometidos;
                document.getElementById('statConTelefono').textContent = formatNumber(data.con_telefono_pendientes);
                document.getElementById('votacionesHora').textContent = data.votaciones_ultima_hora || 0;
            })
            .catch(error => console.error('Error actualizando estadisticas:', error));
    }

    function buscarPorCedula() {
        const cedula = document.getElementById('inputCedula').value.trim();
        if (!cedula) {
            alert('Por favor ingrese una cedula');
            return;
        }

        mostrarLoading(true);

        fetch(`${baseUrl}/movilizacion/api/buscar?cedula=${encodeURIComponent(cedula)}`)
            .then(response => response.json())
            .then(data => {
                mostrarLoading(false);
                if (data.success) {
                    mostrarResultadoBusqueda(data.persona);
                } else {
                    alert(data.message);
                    ocultarResultadoBusqueda();
                }
            })
            .catch(error => {
                mostrarLoading(false);
                console.error('Error:', error);
                alert('Error al buscar el votante');
            });
    }

    function mostrarResultadoBusqueda(persona) {
        const container = document.getElementById('searchResult');
        const infoDiv = document.getElementById('personaInfo');
        const accionesDiv = document.getElementById('accionesVoto');

        const inicial = persona.nombre.charAt(0).toUpperCase();
        const estadoClass = persona.reporte_voto ? 'bg-success' : 'bg-danger';
        const estadoTexto = persona.reporte_voto ? 'YA VOTO' : 'PENDIENTE';

        infoDiv.innerHTML = `
            <div class="persona-avatar">${inicial}</div>
            <div class="flex-grow-1">
                <h6 class="mb-1 text-dark">${persona.nombre}</h6>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-credit-card me-1"></i>${persona.cedula}
                    ${persona.telefono ? '<span class="ms-2"><i class="bi bi-telephone me-1"></i>' + persona.telefono + '</span>' : ''}
                </p>
                <small class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i>${persona.municipio}
                    <span class="ms-2"><i class="bi bi-inbox me-1"></i>${persona.mesa}</span>
                    ${persona.lider ? '<span class="ms-2"><i class="bi bi-person-badge me-1"></i>' + persona.lider + '</span>' : ''}
                </small>
            </div>
            <div class="text-end">
                <span class="badge-voto ${estadoClass} text-white">${estadoTexto}</span>
                <br>
                <small class="text-muted">${persona.estado}</small>
            </div>
        `;

        if (persona.reporte_voto) {
            accionesDiv.innerHTML = `
                <button class="btn btn-outline-warning btn-sm" onclick="revertirVoto(${persona.id})">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Revertir Voto (Error)
                </button>
            `;
        } else {
            accionesDiv.innerHTML = `
                <button class="btn btn-success" onclick="confirmarVoto(${persona.id})">
                    <i class="bi bi-check-circle me-1"></i>Confirmar que Ya Voto
                </button>
            `;
        }

        container.classList.add('show');
    }

    function ocultarResultadoBusqueda() {
        document.getElementById('searchResult').classList.remove('show');
    }

    function confirmarVoto(personaId) {
        if (!confirm('Confirmar que este votante ya emitio su voto?')) return;

        mostrarLoading(true);

        fetch(`${baseUrl}/movilizacion/api/confirmar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ persona_id: personaId })
        })
        .then(response => response.json())
        .then(data => {
            mostrarLoading(false);
            if (data.success) {
                alert(data.message);
                if (data.estadisticas) {
                    document.getElementById('statVotaron').textContent = formatNumber(data.estadisticas.ya_votaron);
                    document.getElementById('statPendientes').textContent = formatNumber(data.estadisticas.pendientes);
                    document.getElementById('statPorcentaje').textContent = data.estadisticas.porcentaje_avance;
                }
                document.getElementById('inputCedula').value = '';
                ocultarResultadoBusqueda();
                cargarPendientes();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            mostrarLoading(false);
            console.error('Error:', error);
            alert('Error al confirmar el voto');
        });
    }

    function revertirVoto(personaId) {
        if (!confirm('Seguro que desea revertir este voto? Solo use esta opcion si hubo un error.')) return;

        mostrarLoading(true);

        fetch(`${baseUrl}/movilizacion/api/revertir`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ persona_id: personaId })
        })
        .then(response => response.json())
        .then(data => {
            mostrarLoading(false);
            if (data.success) {
                alert(data.message);
                document.getElementById('inputCedula').value = '';
                ocultarResultadoBusqueda();
                actualizarEstadisticas();
                cargarPendientes();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            mostrarLoading(false);
            console.error('Error:', error);
            alert('Error al revertir el voto');
        });
    }

    function cargarPendientes() {
        const params = new URLSearchParams();
        params.append('page', currentPage);
        params.append('per_page', perPage);

        const liderId = document.getElementById('filtroLider').value;
        const deptoId = document.getElementById('filtroDepartamento').value;
        const municipioId = document.getElementById('filtroMunicipio').value;
        const puestoId = document.getElementById('filtroPuesto').value;
        const soloComprometidos = document.getElementById('soloComprometidos').checked;
        const conTelefono = document.getElementById('conTelefono').checked;
        const busqueda = document.getElementById('busquedaPendiente').value;

        if (liderId) params.append('lider_id', liderId);
        if (deptoId) params.append('departamento_id', deptoId);
        if (municipioId) params.append('municipio_id', municipioId);
        if (puestoId) params.append('puesto_id', puestoId);
        if (soloComprometidos) params.append('solo_comprometidos', 'true');
        if (conTelefono) params.append('con_telefono', 'true');
        if (busqueda) params.append('busqueda', busqueda);

        fetch(`${baseUrl}/movilizacion/api/pendientes?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                mostrarPendientes(data.pendientes);
                document.getElementById('totalFiltrado').textContent = formatNumber(data.total_filtrado);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('tablaPendientes').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            Error al cargar los pendientes
                        </td>
                    </tr>
                `;
            });
    }

    function mostrarPendientes(pendientes) {
        const tbody = document.getElementById('tablaPendientes');
        const data = pendientes?.data || [];

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                        <p class="mb-0 mt-2 text-muted small">No hay pendientes con estos filtros</p>
                    </td>
                </tr>
            `;
            document.getElementById('paginacionContainer').style.display = 'none';
            return;
        }

        let html = '';
        data.forEach(persona => {
            const estado = persona.estado_votante
                ? `<span class="badge-estado" style="background: ${persona.estado_votante.color}20; color: ${persona.estado_votante.color};">${persona.estado_votante.nombre}</span>`
                : '<span class="text-muted">-</span>';

            const lider = persona.votante?.lider?.persona?.nombre
                ? `<small class="d-block text-muted"><i class="bi bi-person-badge"></i> ${persona.votante.lider.persona.nombre.substring(0, 12)}</small>`
                : '';

            html += `
                <tr>
                    <td>
                        <strong style="font-size: 0.85rem;">${persona.nombre || '-'}</strong>
                        ${lider}
                    </td>
                    <td class="d-none d-md-table-cell">${persona.cedula || '-'}</td>
                    <td class="d-none d-lg-table-cell">
                        ${persona.telefono
                            ? `<a href="tel:${persona.telefono}" class="text-decoration-none"><i class="bi bi-telephone"></i> ${persona.telefono}</a>`
                            : '<span class="text-muted">-</span>'}
                    </td>
                    <td class="d-none d-xl-table-cell">${persona.municipio?.nombre || '-'}</td>
                    <td>${estado}</td>
                    <td class="text-center">
                        <button class="btn btn-success btn-sm py-1 px-2" onclick="confirmarVotoRapido(${persona.id}, '${(persona.nombre || '').replace(/'/g, "\\'")}')">
                            <i class="bi bi-check"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Mostrar paginacion
        const container = document.getElementById('paginacionContainer');
        const lastPage = pendientes.last_page || 1;

        if (lastPage > 1) {
            container.style.display = 'flex';
            document.getElementById('paginacionInfo').textContent =
                `${pendientes.from} - ${pendientes.to} de ${pendientes.total}`;
            generarPaginacion(pendientes.current_page, lastPage);
        } else {
            container.style.display = 'none';
        }
    }

    function confirmarVotoRapido(personaId, nombre) {
        if (!confirm(`Confirmar voto de ${nombre}?`)) return;

        fetch(`${baseUrl}/movilizacion/api/confirmar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ persona_id: personaId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.estadisticas) {
                    document.getElementById('statVotaron').textContent = formatNumber(data.estadisticas.ya_votaron);
                    document.getElementById('statPendientes').textContent = formatNumber(data.estadisticas.pendientes);
                    document.getElementById('statPorcentaje').textContent = data.estadisticas.porcentaje_avance;
                }
                cargarPendientes();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al confirmar el voto');
        });
    }

    function filtrarPorLider(liderId) {
        document.getElementById('filtroLider').value = liderId;
        currentPage = 1;
        cargarPendientes();
    }

    function cargarMunicipios() {
        const deptoId = document.getElementById('filtroDepartamento').value;
        const selectMunicipio = document.getElementById('filtroMunicipio');

        if (!deptoId) {
            selectMunicipio.innerHTML = '<option value="">Seleccione depto.</option>';
            selectMunicipio.disabled = true;
            return;
        }

        selectMunicipio.innerHTML = '<option value="">Cargando...</option>';
        selectMunicipio.disabled = true;

        fetch(`${baseUrl}/departamentos/${deptoId}/municipios`)
            .then(response => response.json())
            .then(municipios => {
                let html = '<option value="">Todos los municipios</option>';
                municipios.forEach(muni => {
                    html += `<option value="${muni.id}">${muni.nombre}</option>`;
                });
                selectMunicipio.innerHTML = html;
                selectMunicipio.disabled = false;
            })
            .catch(error => {
                console.error('Error cargando municipios:', error);
                selectMunicipio.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    function limpiarFiltros() {
        document.getElementById('filtroLider').value = '';
        document.getElementById('filtroDepartamento').value = '';
        document.getElementById('filtroMunicipio').innerHTML = '<option value="">Seleccione depto.</option>';
        document.getElementById('filtroMunicipio').disabled = true;
        document.getElementById('filtroPuesto').value = '';
        document.getElementById('soloComprometidos').checked = false;
        document.getElementById('conTelefono').checked = false;
        document.getElementById('busquedaPendiente').value = '';
        currentPage = 1;
        cargarPendientes();
    }

    function exportarPendientes() {
        const params = new URLSearchParams();

        const liderId = document.getElementById('filtroLider').value;
        const deptoId = document.getElementById('filtroDepartamento').value;
        const municipioId = document.getElementById('filtroMunicipio').value;
        const puestoId = document.getElementById('filtroPuesto').value;
        const soloComprometidos = document.getElementById('soloComprometidos').checked;
        const conTelefono = document.getElementById('conTelefono').checked;

        if (liderId) params.append('lider_id', liderId);
        if (deptoId) params.append('departamento_id', deptoId);
        if (municipioId) params.append('municipio_id', municipioId);
        if (puestoId) params.append('puesto_id', puestoId);
        if (soloComprometidos) params.append('solo_comprometidos', 'true');
        if (conTelefono) params.append('con_telefono', 'true');

        window.location.href = `${baseUrl}/movilizacion/exportar?${params.toString()}`;
    }

    function generarPaginacion(currentPageNum, lastPage) {
        const container = document.getElementById('paginacionLinks');
        let html = '';

        if (currentPageNum > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="irAPagina(${currentPageNum - 1}); return false;"><i class="bi bi-chevron-left"></i></a></li>`;
        }

        for (let i = Math.max(1, currentPageNum - 2); i <= Math.min(lastPage, currentPageNum + 2); i++) {
            const active = i === currentPageNum ? 'active' : '';
            html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="irAPagina(${i}); return false;">${i}</a></li>`;
        }

        if (currentPageNum < lastPage) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="irAPagina(${currentPageNum + 1}); return false;"><i class="bi bi-chevron-right"></i></a></li>`;
        }

        container.innerHTML = html;
    }

    function irAPagina(pagina) {
        currentPage = pagina;
        cargarPendientes();
    }

    function formatNumber(num) {
        return (num || 0).toLocaleString('es-CO');
    }

    function mostrarLoading(show) {
        document.getElementById('loadingOverlay').classList.toggle('active', show);
    }
</script>
@endsection
