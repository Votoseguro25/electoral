@extends('layouts.bootstrap')

@section('titulo', 'Reporte de Candidatos Camara')

@section('contenido')
<style>
    .filtro-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .filtro-card label {
        color: #fff;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 8px;
        display: block;
    }
    .filtro-card .form-select {
        border: none;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 0.95rem;
        background-color: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .filtro-card .form-select:focus {
        box-shadow: 0 4px 20px rgba(255, 255, 255, 0.4);
        outline: none;
    }
    .filtro-card .form-select:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    .btn-limpiar {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.5);
        color: #fff;
        padding: 10px 25px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-limpiar:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: #fff;
        color: #fff;
        transform: translateY(-2px);
    }
    .chart-card {
        background: #fff;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }
    .chart-title {
        color: #333;
        font-weight: 700;
        margin-bottom: 20px;
        text-align: center;
    }
    .refresh-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.8);
    }
    .refresh-indicator .dot {
        width: 8px;
        height: 8px;
        background: #4ade80;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        border-radius: 15px;
        z-index: 10;
    }
    .loading-overlay.active {
        display: flex;
    }
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="container-fluid mt-4 px-4">
    <!-- Filtros -->
    <div class="filtro-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white mb-0 fw-bold">Filtros de Busqueda</h5>
            <div class="refresh-indicator">
                <span class="dot"></span>
                <span>Actualizacion automatica cada 10s</span>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-3">
                <label for="departamento">Departamento</label>
                <select id="departamento" class="form-select">
                    <option value="">Todos los departamentos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="municipio">Municipio</label>
                <select id="municipio" class="form-select">
                    <option value="">Todos los municipios</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="puesto">Puesto</label>
                <select id="puesto" class="form-select">
                    <option value="">Todos los puestos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="mesa">Mesa</label>
                <select id="mesa" class="form-select">
                    <option value="">Todas las mesas</option>
                </select>
            </div>
        </div>
        
        <div class="mt-3 text-end">
            <button id="limpiarFiltros" class="btn btn-limpiar">
                <i class="bi bi-x-circle me-2"></i>Limpiar filtros
            </button>
        </div>
    </div>

    <!-- Grafico -->
    <div class="chart-card position-relative">
        <h4 class="chart-title">Reporte de Votos - Candidatos Camara</h4>
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
        </div>
        <div id="chart" style="width: 100%; min-height: 450px;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
let chart;
let chartRendered = false;
let refreshInterval;

function getFilterParams() {
    const params = new URLSearchParams();
    const dep = document.getElementById('departamento').value;
    const mun = document.getElementById('municipio').value;
    const pue = document.getElementById('puesto').value;
    const mes = document.getElementById('mesa').value;

    if (dep) params.append('departamento', dep);
    if (mun) params.append('municipio', mun);
    if (pue) params.append('puesto', pue);
    if (mes) params.append('mesa', mes);

    return params.toString();
}

function showLoading() {
    document.getElementById('loadingOverlay').classList.add('active');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('active');
}

async function cargarDepartamentos() {
    const res = await fetch("{{ url('/api/camara/departamentos') }}");
    const data = await res.json();
    const sel = document.getElementById('departamento');
    sel.innerHTML = '<option value="">Todos los departamentos</option>';
    data.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.nombre;
        sel.appendChild(opt);
    });
}

async function cargarMunicipios() {
    const dep = document.getElementById('departamento').value;
    const params = dep ? '?departamento=' + dep : '';
    const res = await fetch("{{ url('/api/camara/municipios') }}" + params);
    const data = await res.json();
    const sel = document.getElementById('municipio');
    sel.innerHTML = '<option value="">Todos los municipios</option>';
    data.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.nombre;
        sel.appendChild(opt);
    });
}

async function cargarPuestos() {
    const mun = document.getElementById('municipio').value;
    const params = mun ? '?municipio=' + mun : '';
    const res = await fetch("{{ url('/api/camara/puestos') }}" + params);
    const data = await res.json();
    const sel = document.getElementById('puesto');
    sel.innerHTML = '<option value="">Todos los puestos</option>';
    data.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.nombre;
        sel.appendChild(opt);
    });
}

async function cargarMesas() {
    const params = getFilterParams();
    const res = await fetch("{{ url('/api/camara/mesas') }}?" + params);
    const data = await res.json();
    const sel = document.getElementById('mesa');
    sel.innerHTML = '<option value="">Todas las mesas</option>';
    data.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.nombre;
        sel.appendChild(opt);
    });
}

async function cargarGrafico(showLoadingIndicator = true) {
    if (showLoadingIndicator) showLoading();
    
    try {
        const params = getFilterParams();
        const url = "{{ url('/api/reportecandidatosCamara') }}" + (params ? '?' + params : '');
        const res = await fetch(url);
        const data = await res.json();

        const candidatos = [...new Set(data.map(d => d.candidato))];
        const partidos = {};
        
        data.forEach(row => {
            if (!partidos[row.partido]) {
                partidos[row.partido] = {
                    name: row.partido,
                    data: Array(candidatos.length).fill(0),
                    color: row.color || '#667eea'
                };
            }
            partidos[row.partido].data[candidatos.indexOf(row.candidato)] = parseInt(row.total_votos);
        });

        const series = Object.values(partidos);
        const options = {
            chart: { 
                type: 'bar', 
                stacked: true, 
                height: 450,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        reset: false
                    }
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 500
                }
            },
            series: series.length > 0 ? series : [],
            xaxis: { 
                categories: candidatos, 
                labels: { 
                    rotate: -45, 
                    style: { fontSize: '11px', fontWeight: 500 },
                    trim: true,
                    maxHeight: 120
                }
            },
            plotOptions: { 
                bar: { 
                    borderRadius: 4,
                    columnWidth: '70%'
                }
            },
            dataLabels: { 
                enabled: true, 
                style: { fontSize: '10px', colors: ['#fff'], fontWeight: 600 },
                formatter: v => v > 0 ? v : ''
            },
            legend: { 
                position: 'top',
                fontSize: '12px',
                fontWeight: 500,
                markers: { radius: 3 }
            },
            yaxis: { 
                title: { 
                    text: 'Total de votos',
                    style: { fontSize: '13px', fontWeight: 600 }
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(val) {
                        return val + " votos";
                    }
                }
            },
            noData: { 
                text: 'No hay datos disponibles', 
                align: 'center', 
                verticalAlign: 'middle', 
                style: { fontSize: '16px', color: '#888' }
            }
        };

        if (!chartRendered) {
            chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
            chartRendered = true;
        } else {
            chart.updateOptions({ xaxis: { categories: candidatos } });
            chart.updateSeries(series.length > 0 ? series : []);
        }
    } catch (error) {
        console.error('Error cargando grafico:', error);
    } finally {
        hideLoading();
    }
}

function limpiarFiltros() {
    document.getElementById('departamento').value = '';
    document.getElementById('municipio').value = '';
    document.getElementById('puesto').value = '';
    document.getElementById('mesa').value = '';
    cargarMunicipios();
    cargarPuestos();
    cargarMesas();
    cargarGrafico();
}

function iniciarActualizacionAutomatica() {
    // Actualizar cada 10 segundos
    refreshInterval = setInterval(() => {
        cargarGrafico(false); // Sin mostrar loading en actualizacion automatica
    }, 10000);
}

function detenerActualizacionAutomatica() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    cargarDepartamentos();
    cargarMunicipios();
    cargarPuestos();
    cargarMesas();
    cargarGrafico();
    
    // Iniciar actualizacion automatica
    iniciarActualizacionAutomatica();

    document.getElementById('departamento').addEventListener('change', () => {
        document.getElementById('municipio').value = '';
        document.getElementById('puesto').value = '';
        document.getElementById('mesa').value = '';
        cargarMunicipios();
        cargarPuestos();
        cargarMesas();
        cargarGrafico();
    });

    document.getElementById('municipio').addEventListener('change', () => {
        document.getElementById('puesto').value = '';
        document.getElementById('mesa').value = '';
        cargarPuestos();
        cargarMesas();
        cargarGrafico();
    });

    document.getElementById('puesto').addEventListener('change', () => {
        document.getElementById('mesa').value = '';
        cargarMesas();
        cargarGrafico();
    });

    document.getElementById('mesa').addEventListener('change', cargarGrafico);
    document.getElementById('limpiarFiltros').addEventListener('click', limpiarFiltros);
});

// Detener actualizacion cuando se sale de la pagina
window.addEventListener('beforeunload', detenerActualizacionAutomatica);
</script>
@endsection