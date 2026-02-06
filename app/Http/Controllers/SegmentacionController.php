<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Genero;
use App\Models\EstadoVotante;
use App\Models\Lider;
use App\Models\Mesa;
use App\Models\Puesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VotantesSegmentadosExport;

class SegmentacionController extends Controller
{
    /**
     * Mostrar vista principal de segmentación
     */
    public function index()
    {
        // Cargar datos para los filtros con manejo de errores
        $departamentos = Schema::hasTable('departamentos') ? Departamento::orderBy('nombre')->get() : collect([]);
        $municipios = Schema::hasTable('municipios') ? Municipio::orderBy('nombre')->get() : collect([]);
        $generos = Schema::hasTable('generos') ? Genero::all() : collect([]);
        $estados = Schema::hasTable('estados_votante') ? EstadoVotante::orderBy('orden')->get() : collect([]);
        $lideres = $this->getLideresConNombre();
        $mesas = Schema::hasTable('mesas') ? Mesa::orderBy('id')->get() : collect([]);
        $puestos = Schema::hasTable('puestos') ? Puesto::orderBy('nombre')->get() : collect([]);

        // Estadísticas generales
        $totalPersonas = Schema::hasTable('personas') ? Persona::count() : 0;

        return view('pages.segmentacion.index', compact(
            'departamentos',
            'municipios',
            'generos',
            'estados',
            'lideres',
            'mesas',
            'puestos',
            'totalPersonas'
        ));
    }

    /**
     * Obtener municipios por departamento (API)
     */
    public function getMunicipiosByDepartamento($departamentoId)
    {
        $municipios = Municipio::where('departamento_id', $departamentoId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($municipios);
    }

    /**
     * Filtrar votantes según criterios
     */
    public function filtrar(Request $request)
    {
        try {
            $query = Persona::query();

            // Filtro por municipio
            if ($request->filled('municipio_id')) {
                $query->where('municipio_id', $request->municipio_id);
            }

            // Filtro por género
            if ($request->filled('genero_id')) {
                $query->where('genero_id', $request->genero_id);
            }

            // Filtro por estado de votante (solo si existe la columna)
            if ($request->filled('estado_votante_id') && Schema::hasColumn('personas', 'estado_votante_id')) {
                $query->where('estado_votante_id', $request->estado_votante_id);
            }

            // Filtro por mesa
            if ($request->filled('mesa_id')) {
                $query->where('mesa_id', $request->mesa_id);
            }

            // Filtro por líder (a través de la tabla votantes)
            if ($request->filled('lider_id') && Schema::hasTable('votantes')) {
                $query->whereHas('votante', function ($q) use ($request) {
                    $q->where('lider_id', $request->lider_id);
                });
            }

            // Filtro por reporte de voto (solo si existe la columna)
            if ($request->filled('reporte_voto') && Schema::hasColumn('personas', 'reporte_voto')) {
                $query->where('reporte_voto', $request->reporte_voto === 'si' ? true : false);
            }

            // Filtro por nombre o cédula (búsqueda)
            if ($request->filled('busqueda')) {
                $busqueda = $request->busqueda;
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('cedula', 'like', "%{$busqueda}%");

                    if (Schema::hasColumn('personas', 'telefono')) {
                        $q->orWhere('telefono', 'like', "%{$busqueda}%");
                    }
                });
            }

            // Filtro por rango de fechas de registro
            if ($request->filled('fecha_desde') && Schema::hasColumn('personas', 'created_at')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta') && Schema::hasColumn('personas', 'created_at')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            // Filtro por teléfono (con o sin teléfono)
            if ($request->filled('tiene_telefono') && Schema::hasColumn('personas', 'telefono')) {
                if ($request->tiene_telefono === 'si') {
                    $query->whereNotNull('telefono')->where('telefono', '!=', '');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('telefono')->orWhere('telefono', '');
                    });
                }
            }

            // Obtener estadísticas del segmento
            $estadisticas = $this->getEstadisticasSegmento(clone $query);

            // Determinar relaciones a cargar
            $relations = ['municipio', 'genero', 'mesa'];
            if (Schema::hasColumn('personas', 'estado_votante_id') && Schema::hasTable('estados_votante')) {
                $relations[] = 'estadoVotante';
            }

            // Paginación
            $perPage = $request->get('per_page', 25);
            $votantes = $query->with($relations)
                ->orderBy('nombre')
                ->paginate($perPage);

            return response()->json([
                'votantes' => $votantes,
                'estadisticas' => $estadisticas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al filtrar: ' . $e->getMessage(),
                'votantes' => ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1],
                'estadisticas' => [
                    'total' => 0,
                    'hombres' => 0,
                    'mujeres' => 0,
                    'con_telefono' => 0,
                    'sin_telefono' => 0,
                    'votaron' => 0,
                    'no_votaron' => 0,
                    'por_estado' => [],
                    'por_municipio' => [],
                ],
            ], 500);
        }
    }

    /**
     * Exportar votantes segmentados a Excel
     */
    public function exportar(Request $request)
    {
        $query = $this->buildQuery($request);

        $filename = 'votantes_segmentados_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new VotantesSegmentadosExport($query), $filename);
    }

    /**
     * Obtener estadísticas del segmento filtrado
     */
    private function getEstadisticasSegmento($query)
    {
        $estadisticas = [
            'total' => 0,
            'hombres' => 0,
            'mujeres' => 0,
            'con_telefono' => 0,
            'sin_telefono' => 0,
            'votaron' => 0,
            'no_votaron' => 0,
            'por_estado' => [],
            'por_municipio' => [],
        ];

        try {
            $total = $query->count();
            $estadisticas['total'] = $total;

            if ($total === 0) {
                return $estadisticas;
            }

            // Por género
            if (Schema::hasColumn('personas', 'genero_id')) {
                $porGenero = (clone $query)->select('genero_id', DB::raw('count(*) as total'))
                    ->groupBy('genero_id')
                    ->pluck('total', 'genero_id')
                    ->toArray();

                $estadisticas['hombres'] = $porGenero[1] ?? 0;
                $estadisticas['mujeres'] = $porGenero[2] ?? 0;
            }

            // Con/sin teléfono
            if (Schema::hasColumn('personas', 'telefono')) {
                $estadisticas['con_telefono'] = (clone $query)
                    ->whereNotNull('telefono')
                    ->where('telefono', '!=', '')
                    ->count();
                $estadisticas['sin_telefono'] = $total - $estadisticas['con_telefono'];
            }

            // Votaron / no votaron
            if (Schema::hasColumn('personas', 'reporte_voto')) {
                $estadisticas['votaron'] = (clone $query)->where('reporte_voto', true)->count();
                $estadisticas['no_votaron'] = $total - $estadisticas['votaron'];
            }

            // Por estado de votante
            if (Schema::hasColumn('personas', 'estado_votante_id') && Schema::hasTable('estados_votante')) {
                $porEstado = (clone $query)
                    ->select('estado_votante_id', DB::raw('count(*) as total'))
                    ->groupBy('estado_votante_id')
                    ->get();

                $estados = EstadoVotante::pluck('nombre', 'id');
                foreach ($porEstado as $item) {
                    if ($item->estado_votante_id) {
                        $estadisticas['por_estado'][] = [
                            'nombre' => $estados[$item->estado_votante_id] ?? 'Sin estado',
                            'total' => $item->total,
                        ];
                    }
                }
            }

            // Por municipio (top 5)
            if (Schema::hasColumn('personas', 'municipio_id')) {
                $porMunicipio = (clone $query)
                    ->select('municipio_id', DB::raw('count(*) as total'))
                    ->groupBy('municipio_id')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get();

                $municipios = Municipio::pluck('nombre', 'id');
                foreach ($porMunicipio as $item) {
                    if ($item->municipio_id) {
                        $estadisticas['por_municipio'][] = [
                            'nombre' => $municipios[$item->municipio_id] ?? 'Sin municipio',
                            'total' => $item->total,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // En caso de error, devolver estadísticas vacías
        }

        return $estadisticas;
    }

    /**
     * Construir query con filtros para exportación
     */
    private function buildQuery(Request $request)
    {
        $query = Persona::query();

        if ($request->filled('municipio_id')) {
            $query->where('municipio_id', $request->municipio_id);
        }
        if ($request->filled('genero_id')) {
            $query->where('genero_id', $request->genero_id);
        }
        if ($request->filled('estado_votante_id')) {
            $query->where('estado_votante_id', $request->estado_votante_id);
        }
        if ($request->filled('mesa_id')) {
            $query->where('mesa_id', $request->mesa_id);
        }
        if ($request->filled('lider_id')) {
            $query->whereHas('votante', function ($q) use ($request) {
                $q->where('lider_id', $request->lider_id);
            });
        }
        if ($request->filled('reporte_voto')) {
            $query->where('reporte_voto', $request->reporte_voto === 'si');
        }
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('cedula', 'like', "%{$busqueda}%");
            });
        }
        if ($request->filled('tiene_telefono')) {
            if ($request->tiene_telefono === 'si') {
                $query->whereNotNull('telefono')->where('telefono', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('telefono')->orWhere('telefono', '');
                });
            }
        }

        return $query->with(['municipio', 'genero', 'mesa', 'estadoVotante'])->orderBy('nombre');
    }

    /**
     * Obtener líderes con nombre de persona
     */
    private function getLideresConNombre()
    {
        if (!Schema::hasTable('lideres')) {
            return collect([]);
        }

        return Lider::select('lideres.*')
            ->join('personas', 'lideres.persona_id', '=', 'personas.id')
            ->orderBy('personas.nombre')
            ->get()
            ->map(function ($lider) {
                // Usar el id real de la tabla lideres
                $liderId = $lider->getAttributes()['id'];
                $nombre = $lider->Persona->nombre ?? 'Líder #' . $liderId;
                return [
                    'id' => $liderId,
                    'nombre' => $nombre,
                ];
            })
            ->values();
    }
}
