<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Votante;
use App\Models\Lider;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Puesto;
use App\Models\Mesa;
use App\Models\EstadoVotante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PendientesVotarExport;

class MovilizacionController extends Controller
{
    /**
     * Vista principal del módulo Movilización Día D
     */
    public function index()
    {
        // Obtener estadísticas generales
        $estadisticas = $this->getEstadisticasGenerales();

        // Obtener líderes para el filtro
        $lideres = $this->getLideresConEstadisticas();

        // Obtener departamentos para filtro
        $departamentos = Schema::hasTable('departamentos') ? Departamento::orderBy('nombre')->get() : collect([]);

        // Obtener municipios para filtro (vacío inicialmente, se carga por AJAX)
        $municipios = collect([]);

        // Obtener puestos para filtro
        $puestos = Schema::hasTable('puestos') ? Puesto::orderBy('nombre')->get() : collect([]);

        return view('pages.movilizacion.index', compact(
            'estadisticas',
            'lideres',
            'departamentos',
            'municipios',
            'puestos'
        ));
    }

    /**
     * API: Obtener estadísticas en tiempo real
     */
    public function estadisticas()
    {
        return response()->json($this->getEstadisticasGenerales());
    }

    /**
     * API: Buscar votante por cédula para confirmar voto
     */
    public function buscarPorCedula(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula' => 'required|string|min:5|max:15',
        ], [
            'cedula.required' => 'La cédula es requerida',
            'cedula.min' => 'La cédula debe tener al menos 5 caracteres',
            'cedula.max' => 'La cédula no puede tener más de 15 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $cedula = preg_replace('/[^0-9]/', '', $request->cedula);

        $persona = Persona::where('cedula', $cedula)
            ->with(['municipio', 'genero', 'mesa', 'estadoVotante', 'votante.lider.Persona'])
            ->first();

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún votante con la cédula: ' . $cedula,
            ], 404);
        }

        // Obtener información del líder si existe
        $liderNombre = null;
        if ($persona->votante && $persona->votante->lider && $persona->votante->lider->Persona) {
            $liderNombre = $persona->votante->lider->Persona->nombre;
        }

        return response()->json([
            'success' => true,
            'persona' => [
                'id' => $persona->id,
                'nombre' => $persona->nombre,
                'cedula' => $persona->cedula,
                'telefono' => $persona->telefono,
                'municipio' => $persona->municipio->nombre ?? 'N/A',
                'genero' => $persona->genero->nombre ?? 'N/A',
                'mesa' => $persona->mesa->descripcion ?? 'N/A',
                'estado' => $persona->estadoVotante->nombre ?? 'Sin estado',
                'estado_color' => $persona->estadoVotante->color ?? '#6c757d',
                'reporte_voto' => (bool) $persona->reporte_voto,
                'lider' => $liderNombre,
            ],
        ]);
    }

    /**
     * API: Confirmar que un votante ya votó
     */
    public function confirmarVoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'persona_id' => 'required|integer|exists:personas,id',
        ], [
            'persona_id.required' => 'El ID de la persona es requerido',
            'persona_id.exists' => 'La persona no existe en el sistema',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $persona = Persona::find($request->persona_id);

        if ($persona->reporte_voto) {
            return response()->json([
                'success' => false,
                'message' => 'Este votante ya tiene registrado su voto',
            ], 400);
        }

        $persona->reporte_voto = true;
        $persona->updated_at = now();
        $persona->save();

        return response()->json([
            'success' => true,
            'message' => 'Voto confirmado exitosamente para ' . $persona->nombre,
            'estadisticas' => $this->getEstadisticasGenerales(),
        ]);
    }

    /**
     * API: Revertir confirmación de voto (en caso de error)
     */
    public function revertirVoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'persona_id' => 'required|integer|exists:personas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $persona = Persona::find($request->persona_id);

        if (!$persona->reporte_voto) {
            return response()->json([
                'success' => false,
                'message' => 'Este votante no tiene registrado ningún voto',
            ], 400);
        }

        $persona->reporte_voto = false;
        $persona->updated_at = now();
        $persona->save();

        return response()->json([
            'success' => true,
            'message' => 'Voto revertido para ' . $persona->nombre,
            'estadisticas' => $this->getEstadisticasGenerales(),
        ]);
    }

    /**
     * API: Obtener listado de pendientes por votar
     */
    public function pendientes(Request $request)
    {
        $query = Persona::where('reporte_voto', false);

        // Solo comprometidos (estado_votante_id = 4) si se especifica
        if ($request->filled('solo_comprometidos') && $request->solo_comprometidos === 'true') {
            $query->where('estado_votante_id', 4);
        }

        // Filtro por líder
        if ($request->filled('lider_id')) {
            $query->whereHas('votante', function ($q) use ($request) {
                $q->where('lider_id', $request->lider_id);
            });
        }

        // Filtro por departamento (a través de municipio)
        if ($request->filled('departamento_id')) {
            $query->whereHas('municipio', function ($q) use ($request) {
                $q->where('departamento_id', $request->departamento_id);
            });
        }

        // Filtro por municipio
        if ($request->filled('municipio_id')) {
            $query->where('municipio_id', $request->municipio_id);
        }

        // Filtro por puesto (a través de mesa)
        if ($request->filled('puesto_id') && Schema::hasColumn('mesas', 'puesto_id')) {
            $query->whereHas('mesa', function ($q) use ($request) {
                $q->where('puesto_id', $request->puesto_id);
            });
        }

        // Filtro por mesa
        if ($request->filled('mesa_id')) {
            $query->where('mesa_id', $request->mesa_id);
        }

        // Filtro con teléfono (para llamadas)
        if ($request->filled('con_telefono') && $request->con_telefono === 'true') {
            $query->whereNotNull('telefono')->where('telefono', '!=', '');
        }

        // Búsqueda por nombre o cédula
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('cedula', 'like', "%{$busqueda}%");
            });
        }

        // Cargar relaciones
        $relations = ['municipio', 'genero', 'mesa', 'estadoVotante'];
        if (Schema::hasTable('votantes')) {
            $relations[] = 'votante.lider.Persona';
        }

        // Paginación
        $perPage = $request->get('per_page', 25);
        $pendientes = $query->with($relations)
            ->orderBy('nombre')
            ->paginate($perPage);

        // Estadísticas del filtro actual
        $totalFiltrado = (clone $query)->count();

        return response()->json([
            'pendientes' => $pendientes,
            'total_filtrado' => $totalFiltrado,
        ]);
    }

    /**
     * API: Obtener estadísticas por líder
     */
    public function estadisticasPorLider()
    {
        $lideres = $this->getLideresConEstadisticas();
        return response()->json(['lideres' => $lideres]);
    }

    /**
     * API: Obtener mesas por puesto
     */
    public function getMesasPorPuesto($puestoId)
    {
        if (!Schema::hasTable('mesas') || !Schema::hasColumn('mesas', 'puesto_id')) {
            return response()->json([]);
        }

        $mesas = Mesa::where('puesto_id', $puestoId)
            ->orderBy('id')
            ->get(['id', 'descripcion']);

        return response()->json($mesas);
    }

    /**
     * Exportar listado de pendientes a Excel
     */
    public function exportarPendientes(Request $request)
    {
        $query = Persona::where('reporte_voto', false);

        // Aplicar mismos filtros que pendientes()
        if ($request->filled('solo_comprometidos') && $request->solo_comprometidos === 'true') {
            $query->where('estado_votante_id', 4);
        }

        if ($request->filled('lider_id')) {
            $query->whereHas('votante', function ($q) use ($request) {
                $q->where('lider_id', $request->lider_id);
            });
        }

        if ($request->filled('departamento_id')) {
            $query->whereHas('municipio', function ($q) use ($request) {
                $q->where('departamento_id', $request->departamento_id);
            });
        }

        if ($request->filled('municipio_id')) {
            $query->where('municipio_id', $request->municipio_id);
        }

        if ($request->filled('puesto_id') && Schema::hasColumn('mesas', 'puesto_id')) {
            $query->whereHas('mesa', function ($q) use ($request) {
                $q->where('puesto_id', $request->puesto_id);
            });
        }

        if ($request->filled('mesa_id')) {
            $query->where('mesa_id', $request->mesa_id);
        }

        if ($request->filled('con_telefono') && $request->con_telefono === 'true') {
            $query->whereNotNull('telefono')->where('telefono', '!=', '');
        }

        $relations = ['municipio', 'genero', 'mesa', 'estadoVotante'];
        $query->with($relations)->orderBy('nombre');

        $filename = 'pendientes_votar_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new PendientesVotarExport($query), $filename);
    }

    /**
     * Obtener estadísticas generales
     */
    private function getEstadisticasGenerales()
    {
        $estadisticas = [
            'total_votantes' => 0,
            'comprometidos' => 0,
            'ya_votaron' => 0,
            'pendientes' => 0,
            'porcentaje_avance' => 0,
            'comprometidos_votaron' => 0,
            'comprometidos_pendientes' => 0,
            'porcentaje_comprometidos' => 0,
            'con_telefono_pendientes' => 0,
            'ultima_actualizacion' => now()->format('H:i:s'),
        ];

        try {
            // Total de votantes registrados
            $estadisticas['total_votantes'] = Persona::count();

            // Comprometidos (estado_votante_id = 4)
            if (Schema::hasColumn('personas', 'estado_votante_id')) {
                $estadisticas['comprometidos'] = Persona::where('estado_votante_id', 4)->count();
            }

            // Ya votaron
            if (Schema::hasColumn('personas', 'reporte_voto')) {
                $estadisticas['ya_votaron'] = Persona::where('reporte_voto', true)->count();
                $estadisticas['pendientes'] = $estadisticas['total_votantes'] - $estadisticas['ya_votaron'];

                // Porcentaje de avance general
                if ($estadisticas['total_votantes'] > 0) {
                    $estadisticas['porcentaje_avance'] = round(
                        ($estadisticas['ya_votaron'] / $estadisticas['total_votantes']) * 100,
                        1
                    );
                }

                // Comprometidos que ya votaron
                if (Schema::hasColumn('personas', 'estado_votante_id')) {
                    $estadisticas['comprometidos_votaron'] = Persona::where('estado_votante_id', 4)
                        ->where('reporte_voto', true)
                        ->count();

                    $estadisticas['comprometidos_pendientes'] = $estadisticas['comprometidos'] - $estadisticas['comprometidos_votaron'];

                    // Porcentaje de comprometidos
                    if ($estadisticas['comprometidos'] > 0) {
                        $estadisticas['porcentaje_comprometidos'] = round(
                            ($estadisticas['comprometidos_votaron'] / $estadisticas['comprometidos']) * 100,
                            1
                        );
                    }
                }

                // Pendientes con teléfono (para llamadas)
                if (Schema::hasColumn('personas', 'telefono')) {
                    $estadisticas['con_telefono_pendientes'] = Persona::where('reporte_voto', false)
                        ->whereNotNull('telefono')
                        ->where('telefono', '!=', '')
                        ->count();
                }
            }

            // Avance por hora (últimas votaciones)
            $estadisticas['votaciones_ultima_hora'] = Persona::where('reporte_voto', true)
                ->where('updated_at', '>=', now()->subHour())
                ->count();

        } catch (\Exception $e) {
            // En caso de error, devolver estadísticas vacías
        }

        return $estadisticas;
    }

    /**
     * Obtener líderes con sus estadísticas
     */
    private function getLideresConEstadisticas()
    {
        if (!Schema::hasTable('lideres') || !Schema::hasTable('votantes')) {
            return collect([]);
        }

        try {
            return DB::table('lideres')
                ->select([
                    'lideres.id as lider_id',
                    'lider_persona.nombre as lider_nombre',
                    'lider_persona.telefono as lider_telefono'
                ])
                ->selectRaw('COUNT(DISTINCT votantes.id) as total_votantes')
                ->selectRaw('COALESCE(SUM(CASE WHEN p.reporte_voto = 1 THEN 1 ELSE 0 END), 0) as votaron')
                ->selectRaw('COALESCE(SUM(CASE WHEN p.reporte_voto = 0 OR p.reporte_voto IS NULL THEN 1 ELSE 0 END), 0) as pendientes')
                ->selectRaw('COALESCE(SUM(CASE WHEN p.estado_votante_id = 4 THEN 1 ELSE 0 END), 0) as comprometidos')
                ->selectRaw('COALESCE(SUM(CASE WHEN p.estado_votante_id = 4 AND p.reporte_voto = 1 THEN 1 ELSE 0 END), 0) as comprometidos_votaron')
                ->join('personas as lider_persona', 'lideres.persona_id', '=', 'lider_persona.id')
                ->leftJoin('votantes', 'lideres.id', '=', 'votantes.lider_id')
                ->leftJoin('personas as p', 'votantes.persona_id', '=', 'p.id')
                ->groupBy('lideres.id', 'lider_persona.nombre', 'lider_persona.telefono')
                ->orderByDesc('pendientes')
                ->get()
                ->map(function ($lider) {
                    $porcentaje = $lider->total_votantes > 0
                        ? round(($lider->votaron / $lider->total_votantes) * 100, 1)
                        : 0;

                    return [
                        'id' => $lider->lider_id,
                        'nombre' => $lider->lider_nombre ?? 'Líder #' . $lider->lider_id,
                        'telefono' => $lider->lider_telefono ?? null,
                        'total_votantes' => $lider->total_votantes,
                        'votaron' => $lider->votaron,
                        'pendientes' => $lider->pendientes,
                        'comprometidos' => $lider->comprometidos,
                        'comprometidos_votaron' => $lider->comprometidos_votaron,
                        'porcentaje' => $porcentaje,
                    ];
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}
