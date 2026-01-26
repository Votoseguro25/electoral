@extends('layouts.bootstrap')

@section('titulo', 'Reporte Votantes')

@section('contenido')
<div class="col-md-12 text-center">
    <div class="card card-reporte">
        <div class="card-header">

            <strong class="card-title">Reporte Votantes</strong>

            <!-- 🗳️ Título centrado -->
            <h1 style="text-align: center; margin: 20px 0;">🗳️ Reporte votos</h1>

            <!-- 🌍 Filtro de municipio -->
            <!--<div style="max-width: 400px; margin: 0 auto 20px;">-->
            <!--    <select id="municipioSelect" class="form-control" style="font-size: 1rem; padding: 10px;">-->
            <!--        <option value="">-- Todos los municipios --</option>-->
            <!--        @foreach($municipios as $municipio)-->
            <!--            <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>-->
            <!--        @endforeach-->
            <!--    </select>-->
            <!--</div>-->

            <!-- 📥 Botón de exportar Excel -->
            <form action="{{ route('export.todos') }}" method="GET" style="margin-bottom: 20px;">
                <button 
                    type="submit" 
                    class="btn btn-success w-100" 
                    style="display: block; width: 100%; font-size: 1.2rem; font-weight: 600; padding: 12px; text-align: center; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='#28a745'; this.style.boxShadow='0 0 10px rgba(40,167,69,0.6)';"
                    onmouseout="this.style.backgroundColor='#198754'; this.style.boxShadow='none';"
                >
                    📥 Exportar Excel
                </button>
            </form>

            <!-- 📊 Gráfico -->
            <div id="votoChart" style="max-width: 500px; margin: 0 auto;"></div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    let chart;

    // 🔹 Función para obtener los datos desde el backend
    async function fetchData() {
        const response = await fetch("{{ url('/api/votos') }}");
        return await response.json();
    }

    // 🔹 Renderiza el gráfico con los datos actuales
    async function renderChart() {
        const data = await fetchData();
        const total = data.series.reduce((a, b) => a + b, 0);

        const options = {
            chart: {
                type: 'pie',
                height: 380,
                animations: {
                    enabled: true,
                    speed: 700
                }
            },
            labels: data.labels,
            series: data.series,
            colors: ['#0DBD25', '#BF0F0F'], // verde y rojo
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    const value = data.series[opts.seriesIndex];
                    const percent = ((value / total) * 100).toFixed(1);
                    return `${percent}% (${value})`;
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        const percent = ((val / total) * 100).toFixed(1);
                        return `${percent}% (${val} personas)`;
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '14px',
                fontWeight: 600
            }
        };

        // 🔁 Si ya existe el gráfico, lo actualizamos
        if (chart) {
            chart.updateOptions(options);
            chart.updateSeries(data.series);
        } else {
            chart = new ApexCharts(document.querySelector("#votoChart"), options);
            chart.render();
        }
    }

    // 🔄 Cargar y actualizar cada 10 segundos
    document.addEventListener('DOMContentLoaded', async () => {
        await renderChart();

        setInterval(async () => {
            console.log("🔁 Actualizando gráfico...");
            await renderChart();
        }, 10000); // cada 10 segundos
    });
</script>
@endsection

