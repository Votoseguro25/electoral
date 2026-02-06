@extends('layouts.bootstrap')

@section('titulo', 'Mapa de Votantes Registrados')

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

        /* Banner principal */
        .banner-votantes {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: -20px -15px 20px -15px;
            padding: 20px;
        }

        .banner-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            padding: 20px 30px;
        }

        .banner-badge {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .banner-title {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 10px 0;
        }
    </style>

    {{-- Banner principal --}}
    <div class="banner-votantes">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="banner-card text-center">
                        <div class="banner-badge">
                            <i class="bi bi-people-fill"></i> Mapa de Votantes
                        </div>
                        <h1 class="banner-title">
                            Votantes Registrados por Territorio
                        </h1>
                        <p class="text-muted mb-0">Visualiza la distribución de votantes registrados por departamento y municipio</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- COLUMNA MAPA --}}
        <div class="col-lg-8 col-md-7 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mapa de Votantes Registrados</h5>

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
                        Haz clic en el mapa para ver información detallada.
                    </small>
                </div>

                <div class="card-body">
                    {{-- INFORMACIÓN PRINCIPAL --}}
                    <div id="info-datos-wrapper" style="display:none;">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="material-symbols-outlined" style="font-size: 32px; color: #667eea;">
                                    groups
                                </span>
                                <div class="ms-3">
                                    <h3 class="mb-0" id="info-total">0</h3>
                                    <small class="text-muted">Total de votantes registrados</small>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>

                    {{-- DISTRIBUCIÓN POR GÉNERO --}}
                    <div id="info-genero-wrapper" style="display:none;">
                        <p class="text-muted mb-2">Distribución por género</p>
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2">
                                <i class="material-symbols-outlined" style="font-size: 18px; color: #137fec; vertical-align: middle;">man</i>
                                <strong>Hombres:</strong> <span id="info-hombres">—</span>
                                <span class="text-muted" id="info-hombres-pct"></span>
                            </li>
                            <li>
                                <i class="material-symbols-outlined" style="font-size: 18px; color: #e91e63; vertical-align: middle;">woman</i>
                                <strong>Mujeres:</strong> <span id="info-mujeres">—</span>
                                <span class="text-muted" id="info-mujeres-pct"></span>
                            </li>
                        </ul>
                        <hr>
                    </div>

                    {{-- TABLA MUNICIPIOS (cuando se selecciona departamento) --}}
                    <div id="info-tabla-wrapper" style="display:none;">
                        <p class="text-muted mb-2">Municipios</p>
                        <div class="table-responsive" style="max-height: 300px; overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Municipio</th>
                                        <th class="text-end">Votantes</th>
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const DEPARTAMENTOS_GEOJSON_URL = "{{ asset('public/js/mapas/colombia_departamentos.geojson') }}";
        const MUNICIPIOS_URL = "{{ asset('public/js/mapas/MGN_ANM_MPIOS.json') }}";

        // ========= MAPA =========
        const map = L.map("map", {
            zoomControl: true,
            attributionControl: false,
            worldCopyJump: false,
            maxBoundsViscosity: 1.0,
            scrollWheelZoom: false,
            doubleClickZoom: true,
            touchZoom: true,
            boxZoom: true,
            keyboard: true,
            dragging: true
        });

        const btnBack = document.getElementById("btnBack");
        const titleEl = document.getElementById("title");
        const legend = document.getElementById("legend");
        const legendT = document.getElementById("legendTitle");
        const legendI = document.getElementById("legendInfo");
        const errBox = document.getElementById("err");

        function showErr(msg) {
            errBox.textContent = msg;
            errBox.style.display = "block";
            setTimeout(() => errBox.style.display = "none", 5000);
        }

        // Función mejorada de normalización
        const norm = s => {
            if (!s) return "";
            return String(s)
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "") // Quitar acentos
                .replace(/\([^)]*\)/g, '') // Eliminar texto entre paréntesis
                .toLowerCase()
                .trim()
                .replace(/\s+/g, " "); // Normalizar espacios múltiples a uno solo
        };

        // Función para intentar coincidencias flexibles
        function findMuniDataFlexible(nombreMuni, dataMap) {
            if (!nombreMuni) return null;

            const normalizado = norm(nombreMuni);

            // Intento 1: Coincidencia exacta
            if (dataMap.has(normalizado)) {
                return dataMap.get(normalizado);
            }

            // Intento 2: Buscar sin palabras comunes como "municipio de", "de", etc.
            const sinPalabrasComunes = normalizado
                .replace(/^(el|la|los|las|de|del)\s+/g, '')
                .replace(/\s+(municipio|ciudad|villa)$/g, '')
                .replace(/\s+(de|del)\s+/g, ' ')
                .trim();

            for (let [key, value] of dataMap) {
                const keySinComunes = key
                    .replace(/^(el|la|los|las|de|del)\s+/g, '')
                    .replace(/\s+(municipio|ciudad|villa)$/g, '')
                    .replace(/\s+(de|del)\s+/g, ' ')
                    .trim();

                if (keySinComunes === sinPalabrasComunes) {
                    return value;
                }
            }

            // Intento 3: Coincidencia sin artículos ni preposiciones intermedias
            const soloNucleo = normalizado
                .replace(/^(el|la|los|las)\s+/g, '')
                .replace(/\s+(de|del|san|santa)\s+/g, ' ')
                .trim();

            for (let [key, value] of dataMap) {
                const keyNucleo = key
                    .replace(/^(el|la|los|las)\s+/g, '')
                    .replace(/\s+(de|del|san|santa)\s+/g, ' ')
                    .trim();

                if (keyNucleo === soloNucleo) {
                    return value;
                }
            }

            // Intento 4: Coincidencia parcial (contiene) - más estricta
            for (let [key, value] of dataMap) {
                // Solo si uno contiene al otro y la diferencia es pequeña
                const minLen = Math.min(key.length, normalizado.length);
                const maxLen = Math.max(key.length, normalizado.length);

                if ((key.includes(normalizado) || normalizado.includes(key)) &&
                    (maxLen - minLen) <= 5 && minLen >= 4) {
                    return value;
                }
            }

            return null;
        }

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
        let municipiosLayer = null;
        let colombiaView = null;
        let SAN_INSET_BOUNDS = null;

        let ALL_MUN = null;
        let MUNI_INDEX_BY_DPTO = new Map();

        const deptDataCache = new Map();
        const muniDataCacheByDept = new Map();

        // Gradiente de colores basado en cantidad de votantes
        function getColorByTotal(total, max) {
            if (max === 0) return '#e0e0e0';
            const ratio = total / max;
            // Gradiente de azul claro a azul oscuro
            if (ratio > 0.75) return '#1a237e'; // Azul muy oscuro
            if (ratio > 0.5) return '#3949ab';  // Azul oscuro
            if (ratio > 0.25) return '#5c6bc0'; // Azul medio
            if (ratio > 0.1) return '#90caf9';  // Azul claro
            return '#bbdefb';                   // Azul muy claro
        }

        async function loadDeptData() {
            if (deptDataCache.size > 0) return;
            const res = await fetch('{{ url('/api/votantes/departamentos') }}');
            if (!res.ok) {
                console.error('Error al cargar datos de departamentos');
                return;
            }
            const data = await res.json();
            data.forEach(row => {
                const key = norm(row.nombre_departamento);
                deptDataCache.set(key, row);
            });
        }

        async function loadMuniData(nombreDpto) {
            const deptKey = norm(nombreDpto);
            if (muniDataCacheByDept.has(deptKey)) return;
            const params = new URLSearchParams({ departamento: nombreDpto });
            const res = await fetch('{{ url('/api/votantes/municipios') }}?' + params.toString());
            if (!res.ok) {
                console.error('Error al cargar datos de municipios para', nombreDpto);
                return;
            }
            const data = await res.json();
            const mp = new Map();
            data.forEach(row => {
                const muniKey = norm(row.nombre_municipio);
                mp.set(muniKey, row);

                // También agregar variaciones comunes para mejorar coincidencias
                const variaciones = [
                    muniKey.replace(/^(el|la|los|las|de|del)\s+/g, ''),
                    muniKey.replace(/\s+(municipio|ciudad|villa)$/g, ''),
                    muniKey.replace(/\s+(de|del)\s+/g, ' '),
                    // Variación sin artículos ni preposiciones
                    muniKey
                        .replace(/^(el|la|los|las)\s+/g, '')
                        .replace(/\s+(de|del|san|santa)\s+/g, ' ')
                        .trim(),
                    // Variación solo con palabras clave
                    muniKey
                        .replace(/^(el|la|los|las|de|del)\s+/g, '')
                        .replace(/\s+(de|del)\s+/g, ' ')
                        .replace(/\s+(municipio|ciudad|villa)$/g, '')
                        .trim()
                ];
                variaciones.forEach(v => {
                    if (v && v !== muniKey && v.length > 2) {
                        mp.set(v, row);
                    }
                });
            });
            muniDataCacheByDept.set(deptKey, mp);
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
                document.getElementById('info-empty').style.display = 'none';
                document.getElementById('info-datos-wrapper').style.display = 'none';
                document.getElementById('info-genero-wrapper').style.display = 'none';
                document.getElementById('info-tabla-wrapper').style.display = 'none';
            },
            showEmpty(msg = 'No hay datos') {
                document.getElementById('info-loading').style.display = 'none';
                document.getElementById('info-empty').style.display = 'block';
                document.getElementById('info-empty').textContent = msg;
                document.getElementById('info-datos-wrapper').style.display = 'none';
                document.getElementById('info-genero-wrapper').style.display = 'none';
                document.getElementById('info-tabla-wrapper').style.display = 'none';
            },
            showAll() {
                document.getElementById('info-loading').style.display = 'none';
                document.getElementById('info-empty').style.display = 'none';
                document.getElementById('info-datos-wrapper').style.display = 'block';
                document.getElementById('info-genero-wrapper').style.display = 'block';
                document.getElementById('info-tabla-wrapper').style.display = 'block';
            },
            showDeptOnly() {
                document.getElementById('info-loading').style.display = 'none';
                document.getElementById('info-empty').style.display = 'none';
                document.getElementById('info-datos-wrapper').style.display = 'block';
                document.getElementById('info-genero-wrapper').style.display = 'block';
                document.getElementById('info-tabla-wrapper').style.display = 'block';
            },
            showMuniOnly() {
                document.getElementById('info-loading').style.display = 'none';
                document.getElementById('info-empty').style.display = 'none';
                document.getElementById('info-datos-wrapper').style.display = 'block';
                document.getElementById('info-genero-wrapper').style.display = 'block';
                document.getElementById('info-tabla-wrapper').style.display = 'none';
            }
        };

        async function cargarInfoDepartamento(nombreDepartamento) {
            infoPanel.showLoading();
            document.getElementById('info-title').textContent = nombreDepartamento;
            document.getElementById('info-subtitle').textContent = 'Votantes registrados por departamento';

            try {
                const params = new URLSearchParams({ departamento: nombreDepartamento });
                const [deptRes, muniRes] = await Promise.all([
                    fetch('{{ url('/api/votantes/departamentos') }}?' + params.toString()),
                    fetch('{{ url('/api/votantes/municipios') }}?' + params.toString())
                ]);

                const deptData = await deptRes.json();
                const muniData = await muniRes.json();

                if (!deptData.length) {
                    infoPanel.showEmpty('No hay datos');
                    return;
                }

                const dept = deptData[0];
                const total = Number(dept.total_votantes || 0);
                const hombres = Number(dept.total_hombres || 0);
                const mujeres = Number(dept.total_mujeres || 0);
                const hombresPct = total > 0 ? ((hombres / total) * 100).toFixed(1) + '%' : '';
                const mujeresPct = total > 0 ? ((mujeres / total) * 100).toFixed(1) + '%' : '';

                document.getElementById('info-total').textContent = formatNumber(total);
                document.getElementById('info-hombres').textContent = formatNumber(hombres);
                document.getElementById('info-hombres-pct').textContent = hombresPct ? `(${hombresPct})` : '';
                document.getElementById('info-mujeres').textContent = formatNumber(mujeres);
                document.getElementById('info-mujeres-pct').textContent = mujeresPct ? `(${mujeresPct})` : '';

                const tbody = document.getElementById('info-tabla-body');
                tbody.innerHTML = '';

                muniData.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.nombre_municipio}</td>
                        <td class="text-end">${formatNumber(row.total_votantes)}</td>
                    `;
                    tbody.appendChild(tr);
                });

                infoPanel.showDeptOnly();
            } catch (e) {
                console.error(e);
                infoPanel.showEmpty('Error al cargar datos');
            }
        }

        async function cargarInfoMunicipio(nombreDepartamento, nombreMunicipio) {
            infoPanel.showLoading();
            document.getElementById('info-title').textContent = `${nombreMunicipio} (${nombreDepartamento})`;
            document.getElementById('info-subtitle').textContent = 'Votantes registrados por municipio';

            try {
                const params = new URLSearchParams({
                    departamento: nombreDepartamento,
                    municipio: nombreMunicipio
                });

                const res = await fetch('{{ url('/api/votantes/municipios') }}?' + params.toString());
                const data = await res.json();

                if (!data.length) {
                    infoPanel.showEmpty('No hay datos de votantes para este municipio.');
                    return;
                }

                const muni = data[0];
                const total = Number(muni.total_votantes || 0);
                const hombres = Number(muni.total_hombres || 0);
                const mujeres = Number(muni.total_mujeres || 0);
                const hombresPct = total > 0 ? ((hombres / total) * 100).toFixed(1) + '%' : '';
                const mujeresPct = total > 0 ? ((mujeres / total) * 100).toFixed(1) + '%' : '';

                document.getElementById('info-total').textContent = formatNumber(total);
                document.getElementById('info-hombres').textContent = formatNumber(hombres);
                document.getElementById('info-hombres-pct').textContent = hombresPct ? `(${hombresPct})` : '';
                document.getElementById('info-mujeres').textContent = formatNumber(mujeres);
                document.getElementById('info-mujeres-pct').textContent = mujeresPct ? `(${mujeresPct})` : '';

                infoPanel.showMuniOnly();
            } catch (e) {
                console.error(e);
                infoPanel.showEmpty('Error al cargar los datos del municipio.');
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

            SAN_INSET_BOUNDS = null;

            if (typeof sanInsetLayer !== "undefined" && sanInsetLayer) {
                map.removeLayer(sanInsetLayer);
                sanInsetLayer = null;
            }

            // Limpiar panel de información cuando regresa a Colombia
            document.getElementById('info-title').textContent = 'Selecciona un departamento o municipio';
            document.getElementById('info-subtitle').textContent = 'Haz clic en el mapa para ver información detallada.';
            infoPanel.showEmpty('No hay datos para mostrar. Selecciona un territorio en el mapa.');

            if (fromBack && departamentosLayer && colombiaView) {
                map.addLayer(departamentosLayer);
                map.setView(colombiaView.center, colombiaView.zoom);
                map.invalidateSize();
                return;
            }

            if (departamentosLayer) {
                map.removeLayer(departamentosLayer);
                departamentosLayer = null;
            }

            try {
                await loadDeptData();

                const res = await fetch(DEPARTAMENTOS_GEOJSON_URL, { cache: "no-store" });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const gj = await res.json();
                if (!gj.features?.length) throw new Error("GeoJSON vacío");

                const Fd = pickDeptoFields(gj.features[0].properties || {});
                const nameField = Fd.nameField;
                const codeField = Fd.codeField;

                gj.features.forEach(f => {
                    const p = f.properties || {};
                    const n = (nameField && p[nameField]) ? p[nameField] : (p.name || "");
                    p.__deptName = String(n || "");
                });

                // Calcular el máximo para el gradiente
                let maxTotal = 0;
                deptDataCache.forEach(data => {
                    const total = Number(data.total_votantes || 0);
                    if (total > maxTotal) maxTotal = total;
                });

                const sanIdx = gj.features.findIndex(f => isSanAndresFeature(f.properties));
                const sanFeature = sanIdx >= 0 ? gj.features[sanIdx] : null;

                const mainlandFeatures = sanFeature ? gj.features.filter((_, i) => i !== sanIdx) : gj.features;
                const mainlandLayerTmp = L.geoJSON({ type: "FeatureCollection", features: mainlandFeatures });
                const mainlandBounds = mainlandLayerTmp.getBounds();

                let sanInsetFC = null;
                if (sanFeature) {
                    // Transformación para San Andrés (igual que en el mapa de resultados)
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

                    const transformed = cloneFeatureWithTransformedGeometry(sanFeature, fn);
                    sanInsetFC = { type: "FeatureCollection", features: [transformed] };
                    sanFeature.properties.__hide = true;
                }

                const styleDepartamentosFeature = (feature) => {
                    const p = feature.properties || {};
                    if (p.__hide) return { weight: 0, color: "transparent", fillOpacity: 0 };

                    const nombre = p.__deptName || p[nameField] || p.name || "";
                    const key = norm(nombre);
                    const data = deptDataCache.get(key);
                    const total = data ? Number(data.total_votantes || 0) : 0;

                    // Si no hay datos, usar color gris claro
                    if (!data || total === 0) {
                        return { weight: 1.4, color: "#ffffff", fillColor: "#e0e0e0", fillOpacity: 0.5 };
                    }

                    const color = getColorByTotal(total, maxTotal);
                    return { weight: 1.4, color: "#ffffff", fillColor: color, fillOpacity: 0.75 };
                };

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

                        const key = norm(nombre);
                        const data = deptDataCache.get(key);
                        const total = data ? formatNumber(data.total_votantes) : '0';

                        layer.bindTooltip(`${nombre}<br>Votantes: ${total}`, { sticky: true });
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

                if (sanInsetFC) {
                    sanInsetLayer = L.geoJSON(sanInsetFC, {
                        style: () => ({
                            weight: 2.2,
                            color: "#ffffff",
                            fillColor: "#5c6bc0",
                            fillOpacity: 0.95
                        }),
                        onEachFeature: (feature, layer) => {
                            const p = feature.properties || {};
                            const nombreDepto = p.__deptName || "Archipiélago de San Andrés, Providencia y Santa Catalina";

                            const key = norm(nombreDepto);
                            const data = deptDataCache.get(key);
                            const total = data ? formatNumber(data.total_votantes) : '0';

                            layer.bindTooltip(`${nombreDepto}<br>Votantes: ${total}`, { sticky: true });

                            const codRaw = codeField ? p[codeField] : undefined;
                            const cod = codRaw != null
                                ? (/^\d+$/.test(String(codRaw)) ? String(codRaw).padStart(2, "0") : String(codRaw))
                                : "88";

                            layer.on({
                                click: () => {
                                    loadMunicipios(cod, nombreDepto, null);
                                    cargarInfoDepartamento(nombreDepto);
                                },
                                mouseover: () => layer.setStyle({ weight: 3.0 }),
                                mouseout: () => layer.setStyle({ weight: 2.2 })
                            });

                            const b = layer.getBounds();
                            SAN_INSET_BOUNDS = SAN_INSET_BOUNDS ? SAN_INSET_BOUNDS.extend(b) : b;
                        }
                    }).addTo(map);
                }

                map.fitBounds(mainlandBounds, { padding: [40, 40] });
                map.setMinZoom(2);

                colombiaView = { center: map.getCenter(), zoom: map.getZoom() };
                map.invalidateSize();

            } catch (err) {
                console.error(err);
                showErr(`Error: ${err.message}`);
            }
        }

        async function loadMunicipios(codDpto, nombreDpto, bounds) {
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
                await loadMuniData(nombreDpto);

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
                const muniDataMap = muniDataCacheByDept.get(deptKey) || new Map();

                // Calcular el máximo para el gradiente de municipios
                let maxTotal = 0;
                muniDataMap.forEach(data => {
                    const total = Number(data.total_votantes || 0);
                    if (total > maxTotal) maxTotal = total;
                });

                // Logging de municipios sin coincidencia
                const municipiosSinDatos = [];

                const styleMunicipiosFeature = (feature) => {
                    const p = feature.properties || {};
                    const nombre = p[muniNameField] || "Municipio";
                    const data = findMuniDataFlexible(nombre, muniDataMap);
                    const total = data ? Number(data.total_votantes || 0) : 0;

                    // Si no hay datos, registrar para debugging y usar color gris
                    if (!data && nombre !== "Municipio") {
                        municipiosSinDatos.push(nombre);
                        return { weight: 0.8, color: "#ffffff", fillColor: "#e0e0e0", fillOpacity: 0.5 };
                    }

                    const color = getColorByTotal(total, maxTotal);
                    return { weight: 0.8, color: "#ffffff", fillColor: color, fillOpacity: 0.78 };
                };

                municipiosLayer = L.geoJSON(muniGeoJSON, {
                    style: styleMunicipiosFeature,
                    onEachFeature: (feature, layer) => {
                        const p = feature.properties || {};
                        const nombre = p[muniNameField] || "Municipio";
                        const data = findMuniDataFlexible(nombre, muniDataMap);
                        const total = data ? formatNumber(data.total_votantes) : '0';

                        layer.bindTooltip(`${nombre}<br>Votantes: ${total}`, { sticky: true });
                        layer.on({
                            mouseover: (e) => highlightFeature(e, styleMunicipiosFeature),
                            mouseout: (e) => resetHighlight(e, styleMunicipiosFeature),
                            click: (e) => {
                                L.popup().setLatLng(e.latlng).setContent(`<b>${nombre}</b>`).openOn(map);
                                cargarInfoMunicipio(nombreDpto, nombre);
                            }
                        });
                    }
                }).addTo(map);

                // Mostrar municipios sin datos en consola para debugging
                if (municipiosSinDatos.length > 0) {
                    // Eliminar duplicados
                    const municipiosUnicos = [...new Set(municipiosSinDatos)];
                    console.warn(`⚠️ ${municipiosUnicos.length} municipios sin votantes registrados en ${nombreDpto}:`, municipiosUnicos);

                    // Mostrar solo los nombres base de los municipios en BD (sin variaciones)
                    const municipiosBD = Array.from(new Set(
                        Array.from(muniDataMap.values()).map(d => d.nombre_municipio)
                    ));
                    console.info(`📊 ${municipiosBD.length} municipios con votantes en BD:`, municipiosBD);
                    console.info('💡 Los municipios en gris no tienen votantes registrados en la base de datos');
                }

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
@endsection
