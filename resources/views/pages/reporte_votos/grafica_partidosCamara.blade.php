@extends('layouts.bootstrap')

@section('titulo', 'Reporte Partidos')

@section('contenido')
<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">🗳️ Reporte de Votos por Partido</h2>
        <div id="chart" style="width: 100%;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
async function cargarGrafico() {
    try {
        const response = await fetch("{{ url('/api/reporte-partidosCamara') }}");
        const data = await response.json();

        // Obtener nombres de partidos y votos
        const categorias = data.partidos.map(p => p.partido_nombre);
        const votos = data.partidos.map(p => parseInt(p.total_votos));

        // Función para generar colores aleatorios
        function generarColorAleatorio() {
            const letras = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letras[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Crear un color por partido
        const colores = categorias.map(() => generarColorAleatorio());

        // Configuración del gráfico
        const options = {
            chart: { type: 'bar', height: 450 },
            series: [{ name: 'Votos', data: votos }],
            xaxis: { categories: categorias, title: { text: 'Partidos' } },
            yaxis: { title: { text: 'Total de Votos' } },
            dataLabels: { enabled: true },
            tooltip: { y: { formatter: val => val + " votos" } },
            colors: colores
        };

        const chartEl = document.querySelector("#chart");
        chartEl.innerHTML = "";
        const chart = new ApexCharts(chartEl, options);
        chart.render();

    } catch (error) {
        console.error(error);
        document.querySelector("#chart").innerHTML = "<p class='text-center text-danger'>No se pudieron cargar los datos.</p>";
    }
}

// Inicializar gráfico al cargar la página
document.addEventListener("DOMContentLoaded", () => {
    cargarGrafico();
    // Refrescar cada 50 segundos
    setInterval(cargarGrafico, 5000);
});
</script>
@endsection
