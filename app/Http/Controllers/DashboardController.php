<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Lider;
use App\Models\Municipio;
use App\Models\MetaElectoral;
use App\Models\EstadoVotante;
use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Mostrar el Dashboard principal de KPIs
     */
    public function index()
    {
        $configuracion = ConfiguracionSistema::getActiva();
        $metaGeneral = MetaElectoral::getMetaGeneral();

        // Estadísticas generales con manejo de errores
        $totalPersonas = 0;
        $totalHombres = 0;
        $totalMujeres = 0;
        $totalVotaron = 0;

        if (Schema::hasTable('personas')) {
            $totalPersonas = Persona::count();

            if (Schema::hasColumn('personas', 'genero_id')) {
                $totalHombres = Persona::where('genero_id', 1)->count();
                $totalMujeres = Persona::where('genero_id', 2)->count();
            }

            if (Schema::hasColumn('personas', 'reporte_voto')) {
                $totalVotaron = Persona::where('reporte_voto', true)->count();
            }
        }

        // Comprometidos (estado_votante_id = 4 o por nombre)
        $totalComprometidos = 0;
        if (Schema::hasTable('estados_votante') && Schema::hasColumn('personas', 'estado_votante_id')) {
            $estadoComprometido = EstadoVotante::getComprometido();
            $totalComprometidos = $estadoComprometido
                ? Persona::where('estado_votante_id', $estadoComprometido->id)->count()
                : 0;
        }

        // Calcular porcentaje de avance
        $metaVotos = $metaGeneral->meta_votos ?? 50000;
        $porcentajeAvance = $metaVotos > 0
            ? round(($totalComprometidos / $metaVotos) * 100, 1)
            : 0;

        // Conteo por estados
        $estadosConteo = collect([]);
        if (Schema::hasTable('estados_votante')) {
            $estadosConteo = EstadoVotante::getConteosPorEstado();
        }

        // Top 10 líderes por cantidad de votantes asignados
        $topLideres = $this->getTopLideres(10);

        // Estadísticas por municipio
        $estadisticasMunicipios = $this->getEstadisticasPorMunicipio();

        // Datos para gráfico de tendencia (últimos 30 días)
        $tendenciaCompromisos = $this->getTendenciaCompromisos(30);

        // Votantes pendientes
        $totalNoVotaron = $totalPersonas - $totalVotaron;

        return view('pages.dashboard.index', compact(
            'configuracion',
            'metaGeneral',
            'totalPersonas',
            'totalHombres',
            'totalMujeres',
            'totalComprometidos',
            'metaVotos',
            'porcentajeAvance',
            'estadosConteo',
            'topLideres',
            'estadisticasMunicipios',
            'tendenciaCompromisos',
            'totalVotaron',
            'totalNoVotaron'
        ));
    }

    /**
     * Obtener top líderes con más votantes comprometidos
     */
    private function getTopLideres($limite = 10)
    {
        // Verificar si la tabla votantes existe
        if (!Schema::hasTable('votantes') || !Schema::hasTable('lideres')) {
            return collect([]);
        }

        return Lider::select('lideres.*')
            ->selectRaw('COUNT(DISTINCT v.id) as total_votantes')
            ->selectRaw('COALESCE(SUM(CASE WHEN p.estado_votante_id = 4 THEN 1 ELSE 0 END), 0) as comprometidos')
            ->leftJoin('votantes as v', 'lideres.id', '=', 'v.lider_id')
            ->leftJoin('personas as p', 'v.persona_id', '=', 'p.id')
            ->groupBy('lideres.id')
            ->orderByDesc('comprometidos')
            ->limit($limite)
            ->with('Persona') // Cargar la relación
            ->get();
    }

    /**
     * Obtener estadísticas por municipio
     */
    private function getEstadisticasPorMunicipio()
    {
        // Verificar si las tablas existen
        if (!Schema::hasTable('municipios') || !Schema::hasTable('personas')) {
            return collect([]);
        }

        // Verificar si personas tiene municipio_id
        if (!Schema::hasColumn('personas', 'municipio_id')) {
            return collect([]);
        }

        return Municipio::select('municipios.*')
            ->selectRaw('COUNT(DISTINCT p.id) as total_personas')
            ->selectRaw('COALESCE(SUM(CASE WHEN p.estado_votante_id = 4 THEN 1 ELSE 0 END), 0) as comprometidos')
            ->selectRaw('COALESCE(SUM(CASE WHEN p.reporte_voto = 1 THEN 1 ELSE 0 END), 0) as votaron')
            ->leftJoin('personas as p', 'municipios.id', '=', 'p.municipio_id')
            ->groupBy('municipios.id')
            ->having('total_personas', '>', 0)
            ->orderByDesc('total_personas')
            ->limit(10)
            ->get();
    }

    /**
     * Obtener tendencia de compromisos por día
     * Nota: Si la tabla personas no tiene created_at, retorna datos vacíos
     */
    private function getTendenciaCompromisos($dias = 30)
    {
        // Verificar si la tabla tiene la columna created_at
        if (!Schema::hasColumn('personas', 'created_at')) {
            // Retornar datos vacíos para los últimos N días
            $resultado = [];
            for ($i = $dias; $i >= 0; $i--) {
                $resultado[] = [
                    'fecha' => now()->subDays($i)->format('Y-m-d'),
                    'total' => 0
                ];
            }
            return $resultado;
        }

        $fechaInicio = now()->subDays($dias);

        $datos = Persona::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $fechaInicio)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Llenar días sin datos
        $resultado = [];
        for ($i = $dias; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $dato = $datos->firstWhere('fecha', $fecha);
            $resultado[] = [
                'fecha' => $fecha,
                'total' => $dato ? $dato->total : 0
            ];
        }

        return $resultado;
    }

    /**
     * API para actualizar datos del dashboard en tiempo real
     */
    public function apiEstadisticas()
    {
        $metaGeneral = MetaElectoral::getMetaGeneral();
        $estadoComprometido = EstadoVotante::getComprometido();

        $totalPersonas = Persona::count();
        $totalComprometidos = $estadoComprometido
            ? Persona::where('estado_votante_id', $estadoComprometido->id)->count()
            : 0;

        $metaVotos = $metaGeneral->meta_votos ?? 50000;
        $porcentajeAvance = $metaVotos > 0
            ? round(($totalComprometidos / $metaVotos) * 100, 1)
            : 0;

        $totalVotaron = Persona::where('reporte_voto', true)->count();

        return response()->json([
            'total_personas' => $totalPersonas,
            'total_comprometidos' => $totalComprometidos,
            'meta_votos' => $metaVotos,
            'porcentaje_avance' => $porcentajeAvance,
            'total_votaron' => $totalVotaron,
            'estados' => EstadoVotante::getConteosPorEstado(),
        ]);
    }

    /**
     * Mostrar formulario para editar metas
     */
    public function editarMetas()
    {
        $metaGeneral = MetaElectoral::getMetaGeneral();
        $metasPorMunicipio = MetaElectoral::getMetasPorMunicipio();
        $municipios = Municipio::orderBy('nombre')->get();

        return view('pages.dashboard.metas', compact('metaGeneral', 'metasPorMunicipio', 'municipios'));
    }

    /**
     * Actualizar meta general
     */
    public function actualizarMeta(Request $request)
    {
        $request->validate([
            'meta_votos' => 'required|integer|min:1',
            'meta_compromisos' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $meta = MetaElectoral::getMetaGeneral();

        if (!$meta) {
            $meta = new MetaElectoral();
            $meta->es_meta_general = true;
            $meta->activo = true;
        }

        $meta->meta_votos = $request->meta_votos;
        $meta->meta_compromisos = $request->meta_compromisos;
        $meta->descripcion = $request->descripcion;
        $meta->save();

        return redirect()->route('dashboard.metas')->with('alerta', [
            'icon' => 'success',
            'title' => 'Meta actualizada',
            'text' => 'La meta ha sido actualizada correctamente.',
            'confirmButtonText' => 'Aceptar'
        ]);
    }
}
