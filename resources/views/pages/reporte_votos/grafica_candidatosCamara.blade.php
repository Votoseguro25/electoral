@extends('layouts.bootstrap')

@section('titulo', 'Reporte de Candidatos')

@section('contenido')
<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">🗳️ Reporte de Voto Candidatos Camara</h2>

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
    const response = await fetch("{{ url('/api/reporte-candidatosCamara') }}?t=" + Date.now());
    return await response.json();
}

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
let chart;
let chartRendered = false;

async function obtenerDatos() {
    const response = await fetch("{{ url('/api/reporte-candidatosCamara') }}");
    return await response.json();
}



document.addEventListener("DOMContentLoaded", () => {
    cargarGrafico();
    setInterval(cargarGrafico, 10000);
});
</script>



@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
let chart;
let chartRendered = false;

async function obtenerDatos() {
    const response = await fetch("{{ url('/api/reporte-candidatosCamara') }}?t=" + Date.now());
    return await response.json();
}

async function cargarGrafico() {
    const data = await obtenerDatos();

    // 📌 candidatos unicos
    const candidatos = [...new Set(data.map(d => d.candidato))];

    // 📌 agrupar por partido
    const partidos = {};
    data.forEach(row => {
        if (!partidos[row.partido]) {
            partidos[row.partido] = {
                name: row.partido,
                data: Array(candidatos.length).fill(0),
                color: row.color
            };
        }

        const index = candidatos.indexOf(row.candidato);
        partidos[row.partido].data[index] = row.total_votos;
    });

    const series = Object.values(partidos);

    const options = {
        chart: {
            type: 'bar',
            stacked: true,
            height: 450
        },
        series: series,
        xaxis: {
            categories: candidatos,
            labels: {
                rotate: -45,
                style: { fontSize: '10px' }
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 6
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '11px',
                colors: ['#fff']
            },
            formatter: function (val) {
                return val > 0 ? val : '';
            }
        },
        legend: {
            position: 'top'
        },
        yaxis: {
            title: {
                text: 'Total de votos'
            }
        }
    };

    const chartEl = document.querySelector("#chart");

    if (!chartRendered) {
        chart = new ApexCharts(chartEl, options);
        chart.render();
        chartRendered = true;
    } else {
        chart.updateOptions({
            xaxis: { categories: candidatos }
        });
        chart.updateSeries(series);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    cargarGrafico();
    setInterval(cargarGrafico, 10000);
});
</script>
@endsection

@endsection


