@extends('layouts.bootstrap')

@section('titulo', 'Mapa de resultados')

@section('contenido')
    <style>
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

    <div class="row">
        {{-- COLUMNA MAPA --}}
        <div class="col-lg-8 col-md-7 mb-3">
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
        <div class="col-lg-4 col-md-5 mb-3">
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
                                     src="{{ asset('images/default-candidato.png') }}"
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
        // ========= CONFIG MAPAS / RUTAS =========
        const DEPARTAMENTOS_GEOJSON_URL = "{{ asset('resources/js/mapas/colombia_departamentos.geojson') }}";
        const MUNICIPIOS_URL            = "{{ asset('resources/js/mapas/MGN_ANM_MPIOS.json') }}";

        // Base para fotos de candidatos
        const CANDIDATO_IMG_BASE   = "{{ asset('storage/candidatos') }}/";
        const DEFAULT_CANDIDATO_IMG = "{{ asset('images/default-candidato.png') }}";

        const map = L.map("map", {
            zoomControl: true,
            attributionControl: false,
            worldCopyJump: false,
            maxBoundsViscosity: 1.0
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

        // ========= HELPERS =========
        const norm = s => (s || "")
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .toLowerCase().trim();

        function formatNumber(num) {
            if (num === null || num === undefined) return '—';
            const n = Number(num);
            if (isNaN(n)) return '—';
            return n.toLocaleString('es-CO');
        }

        function pickDeptoFields(p) {
            const name =
                ["NOMBRE_DPT","NOMBRE_DEPTO","DPTO_CNMBR","name","shapeName","departamen"]
                    .find(k => k in p);
            const code =
                ["COD_DANE","DPTO_CCDGO","codigo","code","DPTO","cod_dpto"]
                    .find(k => k in p);
            return { nameField: name, codeField: code };
        }

        function pickMunicipioFields(p) {
            const deptoCodeField = ["DPTO_CCDGO","COD_DANE","DPTO","DEPTO","cod_dpto","code"]
                .find(k => k in p) || null;
            const deptoNameField = ["DPTO_CNMBR","NOMBRE_DPT","NOMBRE_DEPTO","NAME_1","shapeName1","departamen","depto","depto_name"]
                .find(k => k in p) || null;
            const muniCodeField  = ["MPIO_CCDGO","COD_MPIO","MPIO","cod_mpio","code_mpio"]
                .find(k => k in p) || null;
            const muniNameField  = ["NOMBRE_MPIO","MPIO_CNMBR","shapeName","NAME_2","municipio","nom_mpio","name"]
                .find(k => k in p) || null;
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

        // ========= ESTADO MAPA =========
        let departamentosLayer = null;
        let municipiosLayer    = null;
        let colombiaView       = null;

        let ALL_MUN = null;
        let MUNI_INDEX_BY_DPTO = new Map();

        // ========= COLORES POR CANDIDATO =========
        const COLOR_PALETTE = [
            "#e91e63", "#3f51b5", "#4caf50", "#ff9800", "#9c27b0",
            "#009688", "#ff5722", "#795548", "#607d8b", "#fbc02d",
            "#8bc34a", "#03a9f4", "#f06292", "#ba68c8", "#7986cb"
        ];

        const candidateColorCache = new Map(); // candidato_id -> color
        let initialDeptName = null;           // primer departamento con datos (para panel inicial)

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

        // ========= MAPAS DE COLORES POR TERRITORIO =========
        const deptWinnerColors = new Map();          // key: nombre_depto_norm -> color
        const muniWinnerColorsByDept = new Map();    // key: depto_norm -> Map(mpio_norm -> color)

        async function loadDeptWinnerColors() {
            if (deptWinnerColors.size > 0) return;

            const res = await fetch('/api/resultados/departamentos/ganador');
            if (!res.ok) {
                console.error('Error al cargar ganadores por departamento');
                return;
            }
            const data = await res.json();

            data.forEach((row, idx) => {
                const key = norm(row.nombre_departamento);
                const color = getColorForCandidate(row.candidato_id, row.candidato_color_hex);
                deptWinnerColors.set(key, color);

                // Primer departamento para mostrar en el panel al inicio
                if (!initialDeptName && row.nombre_departamento) {
                    initialDeptName = row.nombre_departamento;
                }
            });
        }

        async function loadMuniWinnerColors(nombreDpto) {
            const deptKey = norm(nombreDpto);
            if (muniWinnerColorsByDept.has(deptKey)) return;

            const params = new URLSearchParams({ departamento: nombreDpto });
            const res = await fetch('/api/resultados/municipios/ganador?' + params.toString());
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

        // ========= CARGA MUNICIPIOS GEOJSON =========
        async function ensureMunicipiosLoaded() {
            if (ALL_MUN) return;

            const res = await fetch(MUNICIPIOS_URL, { cache: "no-store" });
            if (!res.ok) {
                throw new Error(`No se pudo cargar ${MUNICIPIOS_URL} (HTTP ${res.status})`);
            }

            const data = await res.json();

            if (data.type === "Topology")