@extends('layouts.bootstrap')

@section('titulo', 'Reporte de Candidatos')

@section('contenido')
<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">🗳️ Reporte de Votos por Candidato y Partido</h2>

        <div id="chart" style="width: 100%;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
let chart;
let chartRendered = false;

async function obtenerDatos() {
    const response = await fetch("{{ url('/api/reporte-candidatos') }}?t=" + Date.now());
    return await response.json();
}

async function cargarGrafico() {
    try {
        const data = await obtenerDatos();

        const partidos = data.partidos;
        const rawCandidatos = data.candidatos;
        const candidatos = [...new Set(rawCandidatos.map(c => c.trim()))];

        // 🧩 Normalizar datos
        const partidosNormalizados = {};
        for (const [nombrePartido, votosCandidatos] of Object.entries(partidos)) {
            partidosNormalizados[nombrePartido] = {};
            candidatos.forEach(c => {
                const limpio = c.trim();
                partidosNormalizados[nombrePartido][limpio] = votosCandidatos[c] ?? votosCandidatos[limpio] ?? 0;
            });
        }

        if (!partidosNormalizados["Sin partido"]) {
            partidosNormalizados["Sin partido"] = {};
            candidatos.forEach(c => partidosNormalizados["Sin partido"][c] = 0);
        }

        const series = Object.entries(partidosNormalizados).map(([nombre, votosObj]) => ({
            name: nombre,
            data: candidatos.map(c => votosObj[c] || 0)
        }));

        const coloresPartidos = [
            '#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0',
            '#546E7A', '#26A69A', '#9C27B0', '#F9A825', '#BF360C'
        ];

        const options = {
            series: series,
            chart: {
                type: 'bar',
                height: 600,
                stacked: false,
                toolbar: { show: true },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 8,
                    columnWidth: '85%'
                }
            },
            dataLabels: {
                enabled: true,
                formatter: val => val > 0 ? val : '',
                style: { fontWeight: 'bold', colors: ['#000'] }
            },
            xaxis: {
                categories: candidatos,
                labels: { rotate: -45, style: { fontSize: '7px', fontWeight: 600 } },
                title: { text: 'Candidatos' }
            },
            yaxis: {
                title: { text: 'Total de votos' }
            },
            tooltip: {
                y: { formatter: val => val + " votos" }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontWeight: 600
            },
            colors: coloresPartidos,
            fill: { opacity: 1 }
        };

        const chartEl = document.querySelector("#chart");

        // 🧠 Primera carga → crear gráfico
        if (!chartRendered) {
            chart = new ApexCharts(chartEl, options);
            await chart.render();
            chartRendered = true;
        } else {
            // 🔁 Si ya existe → actualizar datos
            await chart.updateOptions({
                xaxis: { categories: candidatos },
                legend: { show: true }
            });
            await chart.updateSeries(series, true);
        }

        // ✨ Efecto visual al actualizar
        chartEl.style.transition = "background-color 0.8s ease";
        chartEl.style.backgroundColor = "#e6f7ff";
        setTimeout(() => chartEl.style.backgroundColor = "transparent", 800);

        // 🕒 Mostrar última actualización
        actualizarEtiquetaTiempo();

    } catch (error) {
        console.error("Error al cargar gráfico:", error);
        document.querySelector("#chart").innerHTML = "<p class='text-danger text-center'>❌ No se pudieron cargar los datos.</p>";
    }
}

function actualizarEtiquetaTiempo() {
    const now = new Date();
    const hora = now.toLocaleTimeString();
    const lbl = document.getElementById("ultima-actualizacion");
    if (lbl) lbl.textContent = `Última actualización: ${hora}`;
}

// 🔁 Cargar y refrescar cada 10 segundos
document.addEventListener("DOMContentLoaded", () => {
    const etiqueta = document.createElement("p");
    etiqueta.id = "ultima-actualizacion";
    etiqueta.className = "text-center text-muted mt-3";
    etiqueta.textContent = "Última actualización: —";
    document.querySelector(".card").appendChild(etiqueta);

    cargarGrafico();
    setInterval(cargarGrafico, 10000); // ⏳ cada 10 segundos
});
</script>
@endsection


