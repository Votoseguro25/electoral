@extends('layouts.bootstrap')

@section('titulo', 'Mapa de resultados')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endsection

@section('contenido')
    <style>
        .card-custom {
            border: 1px solid #d6d6d6;
            border-radius: 0.75rem;
            background-color: white;
            transition: box-shadow 0.2s ease-in-out;
            text-align: center;
        }

        .card-custom:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .material-symbols-outlined {
            font-size: 40px;
            color: #137fec;
        }

        .card-equal {
            height: 100%;
        }

        #map {
            height: 600px;
            width: 100%;
            background: #eef0f5;
        }

        .toolbar {
            z-index: 1000;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
            padding: 8px 10px;
            font-family: system-ui, Arial, sans-serif;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .toolbar .btn {
            border: 1px solid #ddd;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            background: #fff;
        }

        .toolbar .btn[disabled] {
            opacity: .5;
            pointer-events: none;
        }

        .legend {
            position: absolute;
            right: 12px;
            top: 12px;
            z-index: 1000;
            background: white;
            border-radius: 12px;
            padding: 10px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
            font-family: system-ui, Arial, sans-serif;
            font-size: 13px;
        }

        .error {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 16px;
            z-index: 1200;
            max-width: 90%;
            background: #fff3f3;
            color: #a30000;
            border: 1px solid #ffb3b3;
            padding: 8px 12px;
            border-radius: 10px;
            font-family: system-ui, Arial, sans-serif;
            box-shadow: 0 4px 16px rgba(0,0,0,.15);
            display: none;
        }

        /* Avatares y color */
        .candidato-avatar-lg {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
        }

        .candidato-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .candidato-color-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
    </style>

    {{-- Cards de estadísticas --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-2 mb-3">
            <div class="card-custom card-equal p-3 d-flex flex-column align-items-center">
                <span class="material-symbols-outlined">groups</span>
                <h2 class="h4 font-weight-bold mt-2 mb-1">{{ number_format($totalRegistrados, 0, ',', '.') }}</h2>
                <p class="text-muted small m-0">Total de personas registradas</p>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card-custom card-equal p-3 d-flex flex-column align-items-center">
                <span class="material-symbols-outlined">man</span>
                <h2 class="h4 font-weight-bold mt-2 mb-1">{{ number_format($totalHombres, 0, ',', '.') }}</h2>
                <p class="text-muted small m-0">Cantidad de hombres</p>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card-custom card-equal p-3 d-flex flex-column align-items-center">
                <span class="material-symbols-outlined">woman</span>
                <h2 class="h4 font-weight-bold mt-2 mb-1">{{ number_format($totalMujeres, 0, ',', '.') }}</h2>
                <p class="text-muted small m-0">Cantidad de mujeres</p>
            </div>
        </div>

        
        
         <div class="col-md-2 mb-3">
            <div class="card-custom card-equal p-3 d-flex flex-column align-items-center">
                <span class="material-symbols-outlined">emoji_events</span>
                <h2 class="h4 font-weight-bold mt-2 mb-1">{{ number_format($totalVotantes, 0, ',', '.') }}</h2>
                <p class="text-muted small m-0">Cantidad de personas habilitadas</p>
            </div>
        </div>
        
        <div class="col-md-2 mb-3">
            <div class="card-custom card-equal p-3 d-flex flex-column align-items-center">
                <span class="material-symbols-outlined">how_to_vote</span>
                <h2 class="h4 font-weight-bold mt-2 mb-1">{{ number_format($abstencion, 0, ',', '.') }}</h2>
                <p class="text-muted small m-0">Valor Abstención</p>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card-custom card-equal p-3 d-flex flex-column align-items-center">
                <span class="material-symbols-outlined">emoji_events</span>
                <h2 class="h4 font-weight-bold mt-2 mb-1">{{ number_format($minimoParaGanar, 0, ',', '.') }}</h2>
                <p class="text-muted small m-0">Meta</p>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- COLUMNA MAPA --}}
        <div class="col-lg-7 col-md-7 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mapa de resultados</h5>

                    <div class="toolbar">
                        <button id="btnBack" class="btn btn-sm btn-outline-secondary" disabled>← Colombia</button>
                        <div id="title" class="ms-2 fw-semibold">Colombia: Departamentos</div>
                    </div>
                </div>

                <div class="card-body p-0" style="position: relative;">
                    <div id="map"></div>

                    <div class="legend" id="legend" style="display:none">
                        <b id="legendTitle">Municipios</b>
                        <div id="legendInfo" style="margin-top:4px;color:#555"></div>
                    </div>

                    <div id="err" class="error"></div>
                </div>
            </div>
        </div>

        {{-- COLUMNA PANEL DE INFORMACIÓN --}}
        <div class="col-lg-5 col-md-5 mb-3">
            <div class="card shadow-sm" id="info-panel">
                <div class="card-header">
                    <h6 class="mb-0" id="info-title">Selecciona un departamento o municipio</h6>
                    <small class="text-muted d-block" id="info-subtitle">
                        Haz clic en el mapa para ver resultados detallados.
                    </small>
                </div>

                <div class="card-body">
                    {{-- GANADOR --}}
                    <div id="info-ganador-wrapper" style="display:none;">
                        <p class="text-muted mb-1">Ganador</p>

                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <img id="info-ganador-foto"
                                     src="{{ env('APP_URL') }}/public/images/candidatos/default-candidato.png"
                                     alt="Foto candidato"
                                     class="candidato-avatar-lg border">
                            </div>
                            <div>
                                <h5 class="mb-1" id="info-ganador-nombre">—</h5>
                                <p class="mb-2">
                                    <span class="badge" id="info-ganador-partido">Partido</span>
                                </p>
                            </div>
                        </div>

                        <p class="mb-2">
                            <strong>Votos:</strong>
                            <span id="info-ganador-votos">0</span>
                            <span class="text-muted" id="info-ganador-porcentaje"></span>
                        </p>
                        <hr>
                    </div>

                    {{-- RESUMEN --}}
                    <div id="info-resumen-wrapper" style="display:none;">
                        <p class="text-muted mb-1">Resumen general</p>
                        <ul class="list-unstyled mb-3 small">
                            <li><strong>Mesas informadas:</strong> <span id="info-mesas">—</span></li>
                            <li><strong>Votantes inscritos:</strong> <span id="info-inscritos">—</span></li>
                            <li><strong>Sufragantes:</strong> <span id="info-sufragantes">—</span></li>
                            <li><strong>Participación:</strong> <span id="info-participacion">—</span></li>
                            <li><strong>Votos en blanco:</strong> <span id="info-blanco">—</span></li>
                            <li><strong>Votos nulos:</strong> <span id="info-nulos">—</span></li>
                        </ul>
                        <hr>
                    </div>

                    {{-- TABLA CANDIDATOS --}}
                    <div id="info-tabla-wrapper" style="display:none;">
                        <p class="text-muted mb-1">Candidatos</p>
                        <div class="table-responsive" style="max-height: 260px; overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Candidato</th>
                                        <th class="text-end">Votos</th>
                                        <th class="text-end">% territorio</th>
                                    </tr>
                                </thead>
                                <tbody id="info-tabla-body">
                                    {{-- filas generadas por JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- SIN DATOS --}}
                    <div id="info-empty" class="text-muted small text-center py-4">
                        No hay datos para mostrar. Selecciona un territorio en el mapa.
                    </div>

                    {{-- CARGANDO --}}
                    <div id="info-loading" class="text-center py-4" style="display:none;">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span class="ms-2 small">Cargando datos...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Leaflet --}}
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

     <script>
        const DEPARTAMENTOS_GEOJSON_URL = "{{ env('APP_URL') }}/public/js/mapas/colombia_departamentos.geojson";
        const MUNICIPIOS_URL            = "{{ env('APP_URL') }}/public/js/mapas/MGN_ANM_MPIOS.json";
        const CANDIDATO_IMG_BASE   = "{{ asset('public/images/candidatos') }}/";
        const DEFAULT_CANDIDATO_IMG = "{{ env('APP_URL') }}/public/images/candidatos/default-candidato.png";

        // ========= MAPA CON SCROLL DESHABILITADO =========
        const map = L.map("map", {
            zoomControl: true,
            attributionControl: false,
            worldCopyJump: false,
            maxBoundsViscosity: 1.0,
            scrollWheelZoom: false,  // ← DESACTIVA zoom con scroll
            doubleClickZoom: true,
            touchZoom: true,
            boxZoom: true,
            keyboard: true,
            dragging: true
        });

        const btnBack = document.getElementById("btnBack");
        const titleEl = document.getElementById("title");
        const legend  = document.getElementById("legend");
        const legendT = document.getElementById("legendTitle");
        const legendI = document.getElementById("legendInfo");
        const errBox  = document.getElementById("err");

        function showErr(msg) {
            errBox.textContent = msg;
            errBox.style.display = "block";
            setTimeout(() => errBox.style.display = "none", 5000);
        }

        const norm = s => (s || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();

        function formatNumber(num) {
            if (num === null || num === undefined) return '—';
            const n = Number(num);
            if (isNaN(n)) return '—';
            return n.toLocaleString('es-CO');
        }

        function pickDeptoFields(p) {
            const name = ["NOMBRE_DPT","NOMBRE_DEPTO","DPTO_CNMBR","name","shapeName","departamen"].find(k => k in p);
            const code = ["COD_DANE","DPTO_CCDGO","codigo","code","DPTO","cod_dpto"].find(k => k in p);
            return { nameField: name, codeField: code };
        }

        function pickMunicipioFields(p) {
            const deptoCodeField = ["DPTO_CCDGO","COD_DANE","DPTO","DEPTO","cod_dpto","code"].find(k => k in p) || null;
            const deptoNameField = ["DPTO_CNMBR","NOMBRE_DPT","NOMBRE_DEPTO","NAME_1","shapeName1","departamen","depto","depto_name"].find(k => k in p) || null;
            const muniCodeField  = ["MPIO_CCDGO","COD_MPIO","MPIO","cod_mpio","code_mpio"].find(k => k in p) || null;
            const muniNameField  = ["NOMBRE_MPIO","MPIO_CNMBR","shapeName","NAME_2","municipio","nom_mpio","name"].find(k => k in p) || null;
            return { deptoCodeField, deptoNameField, muniCodeField, muniNameField };
        }

        function highlightFeature(e, styleFn) {
            const base = styleFn(e.target.feature);
            e.target.setStyle({
                weight: (base.weight || 1) + 0.8,
                fillOpacity: Math.min(1, (base.fillOpacity || 0.5) + 0.15)
            });
            if (e.target.bringToFront) e.target.bringToFront();
        }

        function resetHighlight(e, styleFn) {
            e.target.setStyle(styleFn(e.target.feature));
        }

        function isSanAndresFeature(props) {
            const n = norm(props?.NOMBRE_DPT || props?.__deptName || "");
            const code = String(props?.DPTO ?? props?.COD_DANE ?? props?.DPTO_CCDGO ?? "").trim();
            return code === "88" || n.includes("san andres") || n.includes("archipielago") || n.includes("providencia") || n.includes("santa catalina");
        }

        function transformCoords(coords, fn) {
            if (typeof coords[0] === "number") {
                const [lng, lat] = coords;
                return fn(lng, lat);
            }
            return coords.map(c => transformCoords(c, fn));
        }

        function cloneFeatureWithTransformedGeometry(feature, fn) {
            return {
                type: "Feature",
                properties: { ...(feature.properties || {}), __inset: true },
                geometry: {
                    type: feature.geometry.type,
                    coordinates: transformCoords(feature.geometry.coordinates, fn)
                }
            };
        }

        let departamentosLayer = null;
        let municipiosLayer    = null;
        let colombiaView       = null;
        let SAN_INSET_BOUNDS   = null;

        let ALL_MUN = null;
        let MUNI_INDEX_BY_DPTO = new Map();

        const COLOR_PALETTE = [
            "#e91e63", "#3f51b5", "#4caf50", "#ff9800", "#9c27b0",
            "#009688", "#ff5722", "#795548", "#607d8b", "#fbc02d",
            "#8bc34a", "#03a9f4", "#f06292", "#ba68c8", "#7986cb"
        ];

        const candidateColorCache = new Map();
        let initialDeptName = null;

        function normalizeHexColor(hex) {
            if (!hex) return null;
            let c = String(hex).trim();
            if (!c.startsWith('#')) c = '#' + c;
            if (!/^#[0-9A-Fa-f]{6}$/.test(c)) return null;
            return c;
        }

        function getRandomColorFromPalette() {
            const idx = Math.floor(Math.random() * COLOR_PALETTE.length);
            return COLOR_PALETTE[idx];
        }

        function getColorForCandidate(candidatoId, colorHexFromApi = null) {
            const apiColor = normalizeHexColor(colorHexFromApi);
            if (apiColor) {
                candidateColorCache.set(candidatoId, apiColor);
                return apiColor;
            }
            if (candidateColorCache.has(candidatoId)) {
                return candidateColorCache.get(candidatoId);
            }
            const color = getRandomColorFromPalette();
            candidateColorCache.set(candidatoId, color);
            return color;
        }

        function getTextColorForBg(hexColor) {
            const c = normalizeHexColor(hexColor) || '#000000';
            const r = parseInt(c.substr(1, 2), 16);
            const g = parseInt(c.substr(3, 2), 16);
            const b = parseInt(c.substr(5, 2), 16);
            const luminance = (0.299*r + 0.587*g + 0.114*b) / 255;
            return luminance > 0.6 ? '#000000' : '#ffffff';
        }

        function applyCandidateColorToUI(colorHex) {
            const color = normalizeHexColor(colorHex) || '#7e57c2';
            const badge = document.getElementById('info-ganador-partido');
            const nameEl = document.getElementById('info-ganador-nombre');
            if (!badge || !nameEl) return;

            const textColor = getTextColorForBg(color);
            badge.style.backgroundColor = color;
            badge.style.borderColor     = color;
            badge.style.color           = textColor;
            nameEl.style.color          = color;
        }

        const deptWinnerColors = new Map();
        const muniWinnerColorsByDept = new Map();

        async function loadDeptWinnerColors() {
            if (deptWinnerColors.size > 0) return;
            const res = await fetch('{{ env('APP_URL') }}/api/resultados/departamentos/ganador');
            if (!res.ok) {
                console.error('Error al cargar ganadores por departamento');
                return;
            }
            const data = await res.json();
            data.forEach((row, idx) => {
                const key = norm(row.nombre_departamento);
                const color = getColorForCandidate(row.candidato_id, row.candidato_color_hex);
                deptWinnerColors.set(key, color);
                if (!initialDeptName && row.nombre_departamento) {
                    initialDeptName = row.nombre_departamento;
                }
            });
        }

        async function loadMuniWinnerColors(nombreDpto) {
            const deptKey = norm(nombreDpto);
            if (muniWinnerColorsByDept.has(deptKey)) return;
            const params = new URLSearchParams({ departamento: nombreDpto });
            const res = await fetch('{{ env('APP_URL') }}/api/resultados/municipios/ganador?' + params.toString());
            if (!res.ok) {
                console.error('Error al cargar ganadores por municipio para', nombreDpto);
                return;
            }
            const data = await res.json();
            const mp = new Map();
            data.forEach(row => {
                const muniKey = norm(row.nombre_municipio);
                const color = getColorForCandidate(row.candidato_id, row.candidato_color_hex);
                mp.set(muniKey, color);
            });
            muniWinnerColorsByDept.set(deptKey, mp);
        }

        async function ensureMunicipiosLoaded() {
            if (ALL_MUN) return;
            const res = await fetch(MUNICIPIOS_URL, { cache: "no-store" });
            if (!res.ok) throw new Error(`No se pudo cargar ${MUNICIPIOS_URL}`);
            const data = await res.json();
            if (data.type === "Topology") throw new Error("TopoJSON no soportado");
            if (data.type !== "FeatureCollection") throw new Error("Debe ser GeoJSON FeatureCollection");

            ALL_MUN = data;
            const sample = ALL_MUN.features[0]?.properties || {};
            const F = pickMunicipioFields(sample);
            if (!F.deptoCodeField && !F.deptoNameField) {
                throw new Error("No se encontraron campos de departamento");
            }
            ALL_MUN.__fields = F;

            ALL_MUN.features.forEach(f => {
                const p = f.properties || {};
                let key = null;
                if (F.deptoCodeField && p[F.deptoCodeField] != null) {
                    const val = String(p[F.deptoCodeField]).trim();
                    key = /^\d+$/.test(val) ? val.padStart(2, "0") : val;
                } else if (F.deptoNameField && p[F.deptoNameField]) {
                    key = `name:${norm(p[F.deptoNameField])}`;
                }
                if (!key) return;
                if (!MUNI_INDEX_BY_DPTO.has(key)) MUNI_INDEX_BY_DPTO.set(key, []);
                MUNI_INDEX_BY_DPTO.get(key).push(f);
            });
        }

        const infoPanel = {
            showLoading() {
                document.getElementById('info-loading').style.display = 'block';
                document.getElementById('info-empty').style.display   = 'none';
                document.getElementById('info-ganador-wrapper').style.display = 'none';
                document.getElementById('info-resumen-wrapper').style.display = 'none';
                document.getElementById('info-tabla-wrapper').style.display   = 'none';
            },
            showEmpty(msg = 'No hay datos') {
                document.getElementById('info-loading').style.display = 'none';
                document.getElementById('info-empty').style.display   = 'block';
                document.getElementById('info-empty').textContent     = msg;
                document.getElementById('info-ganador-wrapper').style.display = 'none';
                document.getElementById('info-resumen-wrapper').style.display = 'none';
                document.getElementById('info-tabla-wrapper').style.display   = 'none';
            },
            showAll() {
                document.getElementById('info-loading').style.display = 'none';
                document.getElementById('info-empty').style.display   = 'none';
                document.getElementById('info-ganador-wrapper').style.display = 'block';
                document.getElementById('info-resumen-wrapper').style.display = 'block';
                document.getElementById('info-tabla-wrapper').style.display   = 'block';
            }
        };

        async function cargarInfoDepartamento(nombreDepartamento) {
            infoPanel.showLoading();
            document.getElementById('info-title').textContent    = nombreDepartamento;
            document.getElementById('info-subtitle').textContent = 'Resultados por departamento';

            try {
                const params = new URLSearchParams({ departamento: nombreDepartamento });
                const [ganadorRes, resumenRes, listaRes] = await Promise.all([
                    fetch('{{ env('APP_URL') }}/api/resultados/departamentos/ganador?' + params.toString()),
                    fetch('{{ env('APP_URL') }}/api/resultados/departamentos/resumen?' + params.toString()),
                    fetch('{{ env('APP_URL') }}/api/resultados/departamentos?' + params.toString())
                ]);

                const ganadorData = await ganadorRes.json();
                const resumenData = await resumenRes.json();
                const listaData   = await listaRes.json();

                if (!ganadorData.length || !resumenData.length || !listaData.length) {
                    infoPanel.showEmpty('No hay datos');
                    return;
                }

                const ganador = ganadorData[0];
                const resumen = resumenData[0];
                const totalValidos = Number(resumen.total_votos_validos || 0);
                const votosGanador = Number(ganador.votos_candidato || 0);
                const porcentajeGanador = totalValidos > 0 ? ((votosGanador / totalValidos) * 100).toFixed(1) + '%' : '';

                const fotoUrl = ganador.candidato_foto ? (CANDIDATO_IMG_BASE + ganador.candidato_foto) : DEFAULT_CANDIDATO_IMG;
                const fotoEl = document.getElementById('info-ganador-foto');
                if (fotoEl) fotoEl.src = fotoUrl;

                document.getElementById('info-ganador-nombre').textContent = `${ganador.candidato_nombre} ${ganador.candidato_apellido}`;
                document.getElementById('info-ganador-partido').textContent = ganador.partido_nombre;
                document.getElementById('info-ganador-votos').textContent = formatNumber(votosGanador);
                document.getElementById('info-ganador-porcentaje').textContent = porcentajeGanador ? ` (${porcentajeGanador})` : '';

                const ganadorColor = getColorForCandidate(ganador.candidato_id, ganador.candidato_color_hex);
                applyCandidateColorToUI(ganadorColor);

                document.getElementById('info-mesas').textContent = formatNumber(resumen.mesas_informadas);
                document.getElementById('info-inscritos').textContent = formatNumber(resumen.total_votantes_inscritos);
                document.getElementById('info-sufragantes').textContent = formatNumber(resumen.total_sufragantes);
                let participacionDepto = Number(resumen.participacion_pct);
                document.getElementById('info-participacion').textContent = !isNaN(participacionDepto) ? participacionDepto.toFixed(1) + '%' : '—';
                document.getElementById('info-blanco').textContent = formatNumber(resumen.total_votos_blanco);
                document.getElementById('info-nulos').textContent = formatNumber(resumen.total_votos_nulos);

                const tbody = document.getElementById('info-tabla-body');
                tbody.innerHTML = '';

                listaData.forEach(row => {
                    const tr = document.createElement('tr');
                    const votosRow = Number(row.votos_candidato || 0);
                    const porcentajeRow = totalValidos > 0 ? ((votosRow / totalValidos) * 100).toFixed(1) + '%' : '—';
                    const colorCandidato = getColorForCandidate(row.candidato_id, row.candidato_color_hex);
                    const fotoRow = row.candidato_foto ? (CANDIDATO_IMG_BASE + row.candidato_foto) : DEFAULT_CANDIDATO_IMG;

                    tr.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="candidato-color-dot" style="background-color:${colorCandidato};"></span>
                                <img src="${fotoRow}" class="candidato-avatar border me-2" alt="Foto">
                                <div>
                                    <div>${row.candidato_nombre} ${row.candidato_apellido}</div>
                                    <small class="text-muted">${row.partido_nombre || ''}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">${formatNumber(votosRow)}</td>
                        <td class="text-end">${porcentajeRow}</td>
                    `;
                    tbody.appendChild(tr);
                });

                infoPanel.showAll();
            } catch (e) {
                console.error(e);
                infoPanel.showEmpty('Error al cargar datos');
            }
        }

        async function loadDepartamentos(fromBack = false) {
  if (municipiosLayer) {
    map.removeLayer(municipiosLayer);
    municipiosLayer = null;
  }

  btnBack.disabled = true;
  titleEl.textContent = "Colombia: Departamentos";
  legend.style.display = "none";

  // Reset bounds del inset
  SAN_INSET_BOUNDS = null;

  // Si tienes una referencia global para el inset, límpiala para evitar duplicados
  if (typeof sanInsetLayer !== "undefined" && sanInsetLayer) {
    map.removeLayer(sanInsetLayer);
    sanInsetLayer = null;
  }

  if (fromBack && departamentosLayer && colombiaView) {
    map.addLayer(departamentosLayer);
    map.setView(colombiaView.center, colombiaView.zoom);
    map.invalidateSize();
    if (initialDeptName) cargarInfoDepartamento(initialDeptName);
    return;
  }

  if (departamentosLayer) {
    map.removeLayer(departamentosLayer);
    departamentosLayer = null;
  }

  try {
    await loadDeptWinnerColors();

    const res = await fetch(DEPARTAMENTOS_GEOJSON_URL, { cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const gj = await res.json();
    if (!gj.features?.length) throw new Error("GeoJSON vacío");

    const Fd = pickDeptoFields(gj.features[0].properties || {});
    const nameField = Fd.nameField;
    const codeField = Fd.codeField;

    // Normaliza __deptName
    gj.features.forEach(f => {
      const p = f.properties || {};
      const n = (nameField && p[nameField]) ? p[nameField] : (p.name || "");
      p.__deptName = String(n || "");
    });

    // Buscar San Andrés (DPTO 88)
    const sanIdx = gj.features.findIndex(f => isSanAndresFeature(f.properties));
    const sanFeature = sanIdx >= 0 ? gj.features[sanIdx] : null;

    // Bounds continente (sin San Andrés real)
    const mainlandFeatures = sanFeature ? gj.features.filter((_, i) => i !== sanIdx) : gj.features;
    const mainlandLayerTmp = L.geoJSON({ type: "FeatureCollection", features: mainlandFeatures });
    const mainlandBounds = mainlandLayerTmp.getBounds();

    // -------- Helpers: quedarse con 2 polígonos más grandes y separarlos en 2 features --------
    function ringArea(coords) {
      let area = 0;
      for (let i = 0, j = coords.length - 1; i < coords.length; j = i++) {
        const xi = coords[i][0], yi = coords[i][1];
        const xj = coords[j][0], yj = coords[j][1];
        area += (xj * yi - xi * yj);
      }
      return Math.abs(area / 2);
    }

    function polygonArea(polygonCoords) {
      const outer = polygonCoords[0] ? ringArea(polygonCoords[0]) : 0;
      const holes = polygonCoords.slice(1).reduce((s, r) => s + ringArea(r), 0);
      return Math.max(0, outer - holes);
    }

    function bboxOfCoords(coords, bbox = { minLng: 999, minLat: 999, maxLng: -999, maxLat: -999 }) {
      if (Array.isArray(coords) && typeof coords[0] === "number") {
        const lng = coords[0];
        const lat = coords[1];
        bbox.minLng = Math.min(bbox.minLng, lng);
        bbox.minLat = Math.min(bbox.minLat, lat);
        bbox.maxLng = Math.max(bbox.maxLng, lng);
        bbox.maxLat = Math.max(bbox.maxLat, lat);
        return bbox;
      }
      coords.forEach(c => bboxOfCoords(c, bbox));
      return bbox;
    }

    function centerOfPolygon(polyCoords) {
      // polyCoords = [ outerRing, hole1, ...]
      const b = bboxOfCoords(polyCoords);
      return { lng: (b.minLng + b.maxLng) / 2, lat: (b.minLat + b.maxLat) / 2 };
    }

    function transformCoords(coords, fn) {
      if (Array.isArray(coords) && typeof coords[0] === "number") {
        const lng = coords[0];
        const lat = coords[1];
        return fn(lng, lat); // siempre [lng, lat]
      }
      return coords.map(c => transformCoords(c, fn));
    }

    // Split: MultiPolygon -> 2 Features (San Andrés / Providencia) según lat (norte->sur)
    function splitInsetIntoTwoFeatures(insetFeature) {
      const geom = insetFeature.geometry;
      if (!geom) return null;

      let polys = [];
      if (geom.type === "MultiPolygon") {
        // coords: [ poly1, poly2, ... ] donde poly = [rings]
        polys = geom.coordinates.slice();
      } else if (geom.type === "Polygon") {
        polys = [geom.coordinates];
      } else {
        return null;
      }

      // quedarse con los 2 más grandes
      const top2 = polys
        .map(poly => ({ poly, area: polygonArea(poly) }))
        .sort((a, b) => b.area - a.area)
        .slice(0, 2)
        .map(x => x.poly);

      // ordenar por lat centro (norte->sur): arriba = Providencia, abajo = San Andrés (aprox)
      const ordered = top2
        .map(poly => ({ poly, c: centerOfPolygon(poly) }))
        .sort((a, b) => b.c.lat - a.c.lat);

      const providenciaPoly = ordered[0]?.poly;
      const sanAndresPoly   = ordered[1]?.poly;

      const baseProps = { ...(insetFeature.properties || {}) };

      const fProv = {
        type: "Feature",
        properties: { ...baseProps, __inset: true, __islandName: "Providencia y Santa Catalina" },
        geometry: { type: "Polygon", coordinates: providenciaPoly }
      };

      const fSan = {
        type: "Feature",
        properties: { ...baseProps, __inset: true, __islandName: "San Andrés" },
        geometry: { type: "Polygon", coordinates: sanAndresPoly }
      };

      return { type: "FeatureCollection", features: [fProv, fSan] };
    }

    // -------- Crear inset transformado --------
    let sanInsetFC = null;

    if (sanFeature) {
      const sanTmp = L.geoJSON(sanFeature);
      const sanCenter = sanTmp.getBounds().getCenter();

      const target = L.latLng(13.5, -78.9);
      const sizeScale = 30;
      const spacingScale = 0.25;
      const fineOffset = { lat: 0.0, lng: 0.0 };

      const fn = (lng, lat) => {
        const dlng = lng - sanCenter.lng;
        const dlat = lat - sanCenter.lat;

        const dlngTight = dlng * spacingScale;
        const dlatTight = dlat * spacingScale;

        const lngScaled = sanCenter.lng + dlngTight * sizeScale;
        const latScaled = sanCenter.lat + dlatTight * sizeScale;

        const lngMoved = lngScaled + (target.lng - sanCenter.lng) + fineOffset.lng;
        const latMoved = latScaled + (target.lat - sanCenter.lat) + fineOffset.lat;

        return [lngMoved, latMoved];
      };

      // 1) transformar todo el dpto 88 como antes
      const transformed = cloneFeatureWithTransformedGeometry(sanFeature, fn);

      // 2) partirlo en 2 features (solo 2 polígonos principales)
      sanInsetFC = splitInsetIntoTwoFeatures(transformed);

      // ocultar el real para que no afecte bounds
      sanFeature.properties.__hide = true;
    }

    // -------- style departamentos (ganadores) --------
    const styleDepartamentosFeature = (feature) => {
      const p = feature.properties || {};
      if (p.__hide) return { weight: 0, color: "transparent", fillOpacity: 0 };

      const nombre = p.__deptName || p[nameField] || p.name || "";
      const key = norm(nombre);
      const color = deptWinnerColors.get(key) || "#7e57c2";

      return { weight: 1.4, color: "#ffffff", fillColor: color, fillOpacity: 0.75 };
    };

    // -------- capa principal --------
    departamentosLayer = L.geoJSON(gj, {
      style: styleDepartamentosFeature,
      onEachFeature: (feature, layer) => {
        const p = feature.properties || {};
        if (p.__hide) return;

        const nombre = p.__deptName || "Departamento";
        const codRaw = codeField ? p[codeField] : undefined;
        const cod = codRaw != null
          ? (/^\d+$/.test(String(codRaw)) ? String(codRaw).padStart(2, "0") : String(codRaw))
          : undefined;

        layer.bindTooltip(nombre, { sticky: true });
        layer.on({
          mouseover: (e) => highlightFeature(e, styleDepartamentosFeature),
          mouseout: (e) => resetHighlight(e, styleDepartamentosFeature),
          click: () => {
            loadMunicipios(cod, nombre, layer.getBounds());
            cargarInfoDepartamento(nombre);
          }
        });
      }
    }).addTo(map);

    // -------- inset dibujado (2 features) --------
    if (sanInsetFC) {
      sanInsetLayer = L.geoJSON(sanInsetFC, {
        style: () => ({
          weight: 2.2,
          color: "#ffffff",
          fillColor: "#ff6b6b",
          fillOpacity: 0.95
        }),
        onEachFeature: (feature, layer) => {
          const p = feature.properties || {};

          // nombre del "departamento" sigue siendo el mismo (para municipios)
          const nombreDepto = p.__deptName || "Archipiélago de San Andrés, Providencia y Santa Catalina";

          // tooltip distinto por isla
          const isla = p.__islandName || "Isla";
          layer.bindTooltip(isla, { sticky: true });

          const codRaw = codeField ? p[codeField] : undefined;
          const cod = codRaw != null
            ? (/^\d+$/.test(String(codRaw)) ? String(codRaw).padStart(2, "0") : String(codRaw))
            : "88";

          layer.on({
            // ✅ NO ZOOM: NO pasar bounds
            click: () => {
              loadMunicipios(cod, nombreDepto, null);
              cargarInfoDepartamento(nombreDepto);
            },
            mouseover: () => layer.setStyle({ weight: 3.0 }),
            mouseout: () => layer.setStyle({ weight: 2.2 })
          });

          // acumular bounds del inset para botón 🏝️
          const b = layer.getBounds();
          SAN_INSET_BOUNDS = SAN_INSET_BOUNDS ? SAN_INSET_BOUNDS.extend(b) : b;
        }
      }).addTo(map);
    }

    // vista al continente
    map.fitBounds(mainlandBounds, { padding: [40, 40] });
    map.setMinZoom(2);

    colombiaView = { center: map.getCenter(), zoom: map.getZoom() };
    map.invalidateSize();

    if (initialDeptName) cargarInfoDepartamento(initialDeptName);

  } catch (err) {
    console.error(err);
    showErr(`Error: ${err.message}`);
  }
}

        // ========= CARGAR INFO POR MUNICIPIO =========
        async function cargarInfoMunicipio(nombreDepartamento, nombreMunicipio) {
            infoPanel.showLoading();

            document.getElementById('info-title').textContent =
                `${nombreMunicipio} (${nombreDepartamento})`;
            document.getElementById('info-subtitle').textContent = 'Resultados por municipio';

            try {
                const params = new URLSearchParams({
                    departamento: nombreDepartamento,
                    municipio: nombreMunicipio
                });

                const [ganadorRes, resumenRes, listaRes] = await Promise.all([
                    fetch('{{ env('APP_URL') }}/api/resultados/municipios/ganador?' + params.toString()),
                    fetch('{{ env('APP_URL') }}/api/resultados/municipios/resumen?' + params.toString()),
                    fetch('{{ env('APP_URL') }}/api/resultados/municipios?' + params.toString())
                ]);

                const ganadorData = await ganadorRes.json();
                const resumenData = await resumenRes.json();
                const listaData   = await listaRes.json();

                if (!ganadorData.length || !resumenData.length || !listaData.length) {
                    infoPanel.showEmpty('No hay datos de resultados para este municipio.');
                    return;
                }

                const ganador = ganadorData[0];
                const resumen = resumenData[0];

                const totalValidos = Number(resumen.total_votos_validos || 0);
                const votosGanador = Number(ganador.votos_candidato || 0);

                const porcentajeGanador = totalValidos > 0
                    ? ((votosGanador / totalValidos) * 100).toFixed(1) + '%'
                    : '';

                // Foto ganador
                const fotoGanador = ganador.candidato_foto;
                const fotoUrl = fotoGanador
                    ? (CANDIDATO_IMG_BASE + fotoGanador)
                    : DEFAULT_CANDIDATO_IMG;

                const fotoEl = document.getElementById('info-ganador-foto');
                if (fotoEl) {
                    fotoEl.src = fotoUrl;
                }

                document.getElementById('info-ganador-nombre').textContent =
                    `${ganador.candidato_nombre} ${ganador.candidato_apellido}`;
                document.getElementById('info-ganador-partido').textContent =
                    ganador.partido_nombre;
                document.getElementById('info-ganador-votos').textContent =
                    formatNumber(votosGanador);
                document.getElementById('info-ganador-porcentaje').textContent =
                    porcentajeGanador ? ` (${porcentajeGanador} de votos válidos)` : '';

                // Color del candidato ganador
                const ganadorColor = getColorForCandidate(
                    ganador.candidato_id,
                    ganador.candidato_color_hex
                );
                applyCandidateColorToUI(ganadorColor);

                document.getElementById('info-mesas').textContent =
                    formatNumber(resumen.mesas_informadas);
                document.getElementById('info-inscritos').textContent =
                    formatNumber(resumen.total_votantes_inscritos);
                document.getElementById('info-sufragantes').textContent =
                    formatNumber(resumen.total_sufragantes);

                let participacionMpio = Number(resumen.participacion_pct);
                document.getElementById('info-participacion').textContent =
                    !isNaN(participacionMpio)
                        ? participacionMpio.toFixed(1) + '%'
                        : '—';

                document.getElementById('info-blanco').textContent =
                    formatNumber(resumen.total_votos_blanco);
                document.getElementById('info-nulos').textContent =
                    formatNumber(resumen.total_votos_nulos);

                const tbody = document.getElementById('info-tabla-body');
                tbody.innerHTML = '';

                const totalTerritorio = totalValidos;

                listaData.forEach(row => {
                    const tr = document.createElement('tr');

                    const votosRow = Number(row.votos_candidato || 0);
                    const porcentajeRow = totalTerritorio > 0
                        ? ((votosRow / totalTerritorio) * 100).toFixed(1) + '%'
                        : '—';

                    const colorCandidato = getColorForCandidate(
                        row.candidato_id,
                        row.candidato_color_hex
                    );

                    const fotoRow = row.candidato_foto
                        ? (CANDIDATO_IMG_BASE + row.candidato_foto)
                        : DEFAULT_CANDIDATO_IMG;

                    tr.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="candidato-color-dot" style="background-color:${colorCandidato};"></span>
                                <img src="${fotoRow}" class="candidato-avatar border me-2" alt="Foto">
                                <div>
                                    <div>${row.candidato_nombre} ${row.candidato_apellido}</div>
                                    <small class="text-muted">${row.partido_nombre || ''}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">${formatNumber(votosRow)}</td>
                        <td class="text-end">${porcentajeRow}</td>
                    `;

                    tbody.appendChild(tr);
                });

                infoPanel.showAll();
            } catch (e) {
                console.error(e);
                infoPanel.showEmpty('Error al cargar los datos del municipio.');
            }
        }

        async function loadMunicipios(codDpto, nombreDpto, bounds) {
            
            console.log("loadMunicipios", codDpto, nombreDpto, bounds);

            
            if (departamentosLayer) {
                map.removeLayer(departamentosLayer);
                departamentosLayer = null;
            }

            btnBack.disabled = false;
            titleEl.textContent = `Departamento: ${nombreDpto} — Municipios`;
            legend.style.display = "block";
            legendT.textContent = nombreDpto;
            legendI.textContent = "Haz clic en un municipio";

            if (municipiosLayer) {
                map.removeLayer(municipiosLayer);
                municipiosLayer = null;
            }

            try {
                await ensureMunicipiosLoaded();
                await loadMuniWinnerColors(nombreDpto);

                const F = ALL_MUN.__fields;

                let keyByCode = null, keyByName = null;
                if (codDpto != null) {
                    const val = String(codDpto).trim();
                    keyByCode = /^\d+$/.test(val) ? val.padStart(2, "0") : val;
                }
                if (nombreDpto) keyByName = `name:${norm(nombreDpto)}`;

                let feats = [];
                if (keyByCode && MUNI_INDEX_BY_DPTO.has(keyByCode)) {
                    feats = MUNI_INDEX_BY_DPTO.get(keyByCode);
                } else if (keyByName && MUNI_INDEX_BY_DPTO.has(keyByName)) {
                    feats = MUNI_INDEX_BY_DPTO.get(keyByName);
                } else {
                    feats = ALL_MUN.features.filter(f => {
                        const p = f.properties || {};
                        const codeOk = F.deptoCodeField && (String(p[F.deptoCodeField]).padStart(2, "0") === keyByCode);
                        const nameOk = F.deptoNameField && (norm(p[F.deptoNameField]) === norm(nombreDpto));
                        return codeOk || nameOk;
                    });
                }

                if (!feats.length) {
                    showErr(`No hay municipios para ${nombreDpto}`);
                    await loadDepartamentos();
                    return;
                }

                const muniNameField = F.muniNameField || ["NOMBRE_MPIO","MPIO_CNMBR","shapeName","NAME_2","municipio","nom_mpio","name"].find(k => k in (feats[0]?.properties || {})) || "name";
                const muniGeoJSON = { type: "FeatureCollection", features: feats };
                const deptKey = norm(nombreDpto);
                const muniColorMap = muniWinnerColorsByDept.get(deptKey) || new Map();

                const styleMunicipiosFeature = (feature) => {
                    const p = feature.properties || {};
                    const nombre = p[muniNameField] || "Municipio";
                    const key = norm(nombre);
                    const color = muniColorMap.get(key) || "#90caf9";
                    return { weight: 0.8, color: "#ffffff", fillColor: color, fillOpacity: 0.78 };
                };

                municipiosLayer = L.geoJSON(muniGeoJSON, {
                    style: styleMunicipiosFeature,
                    onEachFeature: (feature, layer) => {
                        const p = feature.properties || {};
                        const nombre = p[muniNameField] || "Municipio";
                        layer.bindTooltip(nombre, { sticky: true });
                        layer.on({
                            mouseover: (e) => highlightFeature(e, styleMunicipiosFeature),
                            mouseout:  (e) => resetHighlight(e, styleMunicipiosFeature),
                            click: (e) => {
                                L.popup().setLatLng(e.latlng).setContent(`<b>${nombre}</b>`).openOn(map);
                                cargarInfoMunicipio(nombreDpto, nombre);
                            }
                        });
                    }
                }).addTo(map);

                if (bounds) map.fitBounds(bounds, { padding: [10, 10] });
                else map.fitBounds(municipiosLayer.getBounds(), { padding: [10, 10] });
            } catch (err) {
                console.error(err);
                showErr(`Error: ${err.message}`);
                await loadDepartamentos();
            }
        }

        btnBack.addEventListener("click", () => loadDepartamentos(true));
        loadDepartamentos();
    </script>

    <script>
        @if(session('error_crear'))
            document.getElementById('btn-abrir-crear')?.click();
        @endif

        @if(session('edit_error_id'))
            document.addEventListener('DOMContentLoaded', () => {
                const edit_error_id = {{ session('edit_error_id', -1) }};
                const btn = document.getElementById(`btn-modal-editar-${edit_error_id}`);
                if (btn) btn.click();
            });
        @endif

        @if (session('alerta'))
            Swal.fire({
                icon: "{{ session('alerta.icon') }}",
                title: "{{ session('alerta.title') }}",
                text: "{{ session('alerta.text') }}",
                confirmButtonText: "{{ session('alerta.confirmButtonText') }}"
            });
        @endif
    </script>

    {{-- Tu lógica previa de modales y alertas --}}
    <script>
        @if(session('error_crear'))
            document.getElementById('btn-abrir-crear')?.click();
        @endif

        @if(session('edit_error_id'))
            document.addEventListener('DOMContentLoaded', () => {
                const edit_error_id = {{ session('edit_error_id', -1) }};
                const btn = document.getElementById(`btn-modal-editar-${edit_error_id}`);
                if (btn) btn.click();
            });
        @endif

        @if (session('alerta'))
            Swal.fire({
                icon: "{{ session('alerta.icon') }}",
                title: "{{ session('alerta.title') }}",
                text: "{{ session('alerta.text') }}",
                confirmButtonText: "{{ session('alerta.confirmButtonText') }}"
            });
        @endif
    </script>
@endsection
