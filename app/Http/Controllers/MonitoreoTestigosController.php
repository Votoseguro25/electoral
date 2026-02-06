<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Testigo;
use App\Models\Mesa;
use App\Models\Puesto;
use App\Models\Municipio;
use App\Models\Departamento;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class MonitoreoTestigosController extends Controller
{
    /**
     * Vista principal del módulo de Monitoreo de Testigos
     */
    public function index()
    {
        $estadisticas = $this->getEstadisticasGenerales();
        $departamentos = Departamento::orderBy('nombre')->get();
        $diaElecciones = $this->getDiaElecciones();
        $horaInicio = $this->getHoraInicio();
        $horaFin = $this->getHoraFin();
        $estaActivo = $this->getEstadoDiaElecciones();

        return view('pages.monitoreo-testigos.index', compact(
            'estadisticas', 
            'departamentos', 
            'diaElecciones',
            'horaInicio', 
            'horaFin',
            'estaActivo'
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
     * API: Obtener listado de testigos (usuarios con rol testigo)
     */
    public function getTestigos(Request $request)
    {
        $query = User::whereHas('role', function ($q) {
            $q->where('slug', 'testigo');
        });

        // Filtro por búsqueda
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('name', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }

        // Filtro por estado (activo/inactivo)
        if ($request->filled('estado')) {
            $tiempoLimite = now()->subMinutes(10);
            if ($request->estado === 'activo') {
                $query->whereExists(function ($q) use ($tiempoLimite) {
                    $q->select(DB::raw(1))
                      ->from('sessions')
                      ->whereColumn('sessions.user_id', 'users.id')
                      ->where('last_activity', '>=', $tiempoLimite->timestamp);
                });
            } else {
                $query->whereNotExists(function ($q) use ($tiempoLimite) {
                    $q->select(DB::raw(1))
                      ->from('sessions')
                      ->whereColumn('sessions.user_id', 'users.id')
                      ->where('last_activity', '>=', $tiempoLimite->timestamp);
                });
            }
        }

        $testigos = $query->orderBy('name')->get()->map(function ($user) {
            $lastSeen = $user->last_seen();
            $estaActivo = $lastSeen && $lastSeen->diffInMinutes(now()) < 10;

            // Contar reportes E14 del testigo
            $reportesE14 = Testigo::where('usuario', $user->name)->count();

            return [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'esta_activo' => $estaActivo,
                'ultima_actividad' => $lastSeen ? $lastSeen->diffForHumans() : 'Nunca',
                'ultima_actividad_fecha' => $lastSeen ? $lastSeen->format('Y-m-d H:i:s') : null,
                'reportes_e14' => $reportesE14,
            ];
        });

        return response()->json([
            'testigos' => $testigos,
            'total' => $testigos->count(),
            'activos' => $testigos->where('esta_activo', true)->count(),
        ]);
    }

    /**
     * API: Obtener mesas con estado de reporte E14
     */
    public function getMesasEstado(Request $request)
    {
        $query = Mesa::with(['puesto.municipio.departamento']);

        // Filtro por departamento
        if ($request->filled('departamento_id')) {
            $query->whereHas('puesto.municipio', function ($q) use ($request) {
                $q->where('departamento_id', $request->departamento_id);
            });
        }

        // Filtro por municipio
        if ($request->filled('municipio_id')) {
            $query->whereHas('puesto', function ($q) use ($request) {
                $q->where('municipio_id', $request->municipio_id);
            });
        }

        // Filtro por puesto
        if ($request->filled('puesto_id')) {
            $query->where('puesto_id', $request->puesto_id);
        }

        // Filtro por estado de reporte
        if ($request->filled('estado_reporte')) {
            if ($request->estado_reporte === 'con_reporte') {
                $query->whereHas('testigos');
            } else {
                $query->whereDoesntHave('testigos');
            }
        }

        $perPage = $request->get('per_page', 25);
        $mesas = $query->orderBy('descripcion')->paginate($perPage);

        $mesasData = $mesas->getCollection()->map(function ($mesa) {
            $reporte = Testigo::where('mesa_id', $mesa->id)->first();

            return [
                'id' => $mesa->id,
                'descripcion' => $mesa->descripcion,
                'puesto' => $mesa->puesto->nombre ?? 'N/A',
                'municipio' => $mesa->puesto->municipio->nombre ?? 'N/A',
                'departamento' => $mesa->puesto->municipio->departamento->nombre ?? 'N/A',
                'tiene_reporte' => $reporte !== null,
                'reporte' => $reporte ? [
                    'id' => $reporte->id,
                    'cantidad_votos' => $reporte->cantidad_votos,
                    'usuario' => $reporte->usuario,
                    'tiene_archivo' => !empty($reporte->archivo),
                    'observaciones' => $reporte->observaciones,
                ] : null,
            ];
        });

        return response()->json([
            'mesas' => $mesasData,
            'pagination' => [
                'total' => $mesas->total(),
                'per_page' => $mesas->perPage(),
                'current_page' => $mesas->currentPage(),
                'last_page' => $mesas->lastPage(),
                'from' => $mesas->firstItem(),
                'to' => $mesas->lastItem(),
            ],
        ]);
    }

    /**
     * API: Obtener resumen por puesto
     */
    public function getResumenPuestos(Request $request)
    {
        $query = Puesto::with('municipio.departamento')
            ->withCount('mesas');

        // Filtro por departamento
        if ($request->filled('departamento_id')) {
            $query->whereHas('municipio', function ($q) use ($request) {
                $q->where('departamento_id', $request->departamento_id);
            });
        }

        // Filtro por municipio
        if ($request->filled('municipio_id')) {
            $query->where('municipio_id', $request->municipio_id);
        }

        $puestos = $query->orderBy('nombre')->get()->map(function ($puesto) {
            $mesaIds = Mesa::where('puesto_id', $puesto->id)->pluck('id');
            $mesasConReporte = Testigo::whereIn('mesa_id', $mesaIds)->distinct('mesa_id')->count('mesa_id');
            $totalMesas = $puesto->mesas_count;
            $porcentaje = $totalMesas > 0 ? round(($mesasConReporte / $totalMesas) * 100, 1) : 0;

            return [
                'id' => $puesto->id,
                'nombre' => $puesto->nombre,
                'municipio' => $puesto->municipio->nombre ?? 'N/A',
                'departamento' => $puesto->municipio->departamento->nombre ?? 'N/A',
                'total_mesas' => $totalMesas,
                'mesas_con_reporte' => $mesasConReporte,
                'mesas_pendientes' => $totalMesas - $mesasConReporte,
                'porcentaje' => $porcentaje,
                'estado' => $porcentaje >= 100 ? 'completo' : ($porcentaje >= 50 ? 'parcial' : 'pendiente'),
            ];
        });

        // Ordenar por porcentaje ascendente (primero los que faltan más)
        $puestos = $puestos->sortBy('porcentaje')->values();

        return response()->json([
            'puestos' => $puestos,
            'resumen' => [
                'total_puestos' => $puestos->count(),
                'completos' => $puestos->where('estado', 'completo')->count(),
                'parciales' => $puestos->where('estado', 'parcial')->count(),
                'pendientes' => $puestos->where('estado', 'pendiente')->count(),
            ],
        ]);
    }

    /**
     * API: Ver detalle de un reporte E14
     */
    public function verReporte($id)
    {
        $testigo = Testigo::with(['mesa.puesto.municipio.departamento', 'candidatos'])
            ->findOrFail($id);

        return response()->json([
            'id' => $testigo->id,
            'mesa' => [
                'id' => $testigo->mesa->id,
                'descripcion' => $testigo->mesa->descripcion,
                'puesto' => $testigo->mesa->puesto->nombre ?? 'N/A',
                'municipio' => $testigo->mesa->puesto->municipio->nombre ?? 'N/A',
            ],
            'cantidad_votos' => $testigo->cantidad_votos,
            'usuario' => $testigo->usuario,
            'observaciones' => $testigo->observaciones,
            'archivo' => $testigo->archivo,
            'votos_candidatos' => $testigo->candidatos->map(function ($candidato) {
                return [
                    'nombre' => $candidato->nombre ?? 'N/A',
                    'partido' => $candidato->partido->nombre ?? 'N/A',
                    'votos' => $candidato->pivot->votos,
                ];
            }),
        ]);
    }

    /**
     * API: Obtener municipios por departamento
     */
    public function getMunicipios($departamentoId)
    {
        $municipios = Municipio::where('departamento_id', $departamentoId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($municipios);
    }

    /**
     * API: Obtener puestos por municipio
     */
    public function getPuestos($municipioId)
    {
        $puestos = Puesto::where('municipio_id', $municipioId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($puestos);
    }

/**
     * Obtener estadísticas generales
     */
    private function getEstadisticasGenerales()
    {
        $estadisticas = [
            'total_testigos' => 0,
            'testigos_activos' => 0,
            'total_mesas' => 0,
            'mesas_con_reporte' => 0,
            'mesas_pendientes' => 0,
            'porcentaje_cobertura' => 0,
            'total_votos_reportados' => 0,
            'reportes_ultima_hora' => 0,
            'ultima_actualizacion' => now()->format('H:i:s'),
        ];

        try {
            // Contar testigos (usuarios con rol testigo)
            $rolTestigo = Role::where('slug', 'testigo')->first();
            if ($rolTestigo) {
                $estadisticas['total_testigos'] = User::where('role_id', $rolTestigo->id)->count();

                // Testigos activos (sesión en los últimos 10 minutos)
                $tiempoLimite = now()->subMinutes(10)->timestamp;
                $estadisticas['testigos_activos'] = DB::table('sessions')
                    ->join('users', 'sessions.user_id', '=', 'users.id')
                    ->where('users.role_id', $rolTestigo->id)
                    ->where('sessions.last_activity', '>=', $tiempoLimite)
                    ->distinct('users.id')
                    ->count('users.id');
            }

            // Mesas
            $estadisticas['total_mesas'] = Mesa::count();
            $estadisticas['mesas_con_reporte'] = Testigo::distinct('mesa_id')->count('mesa_id');
            $estadisticas['mesas_pendientes'] = $estadisticas['total_mesas'] - $estadisticas['mesas_con_reporte'];

            // Porcentaje cobertura
            if ($estadisticas['total_mesas'] > 0) {
                $estadisticas['porcentaje_cobertura'] = round(
                    ($estadisticas['mesas_con_reporte'] / $estadisticas['total_mesas']) * 100,
                    1
                );
            }

            // Total votos reportados
            $estadisticas['total_votos_reportados'] = Testigo::sum('cantidad_votos');

            // Reportes última hora
            if (Schema::hasColumn('testigo', 'created_at')) {
                $estadisticas['reportes_ultima_hora'] = Testigo::where('created_at', '>=', now()->subHour())->count();
            }

        } catch (\Exception $e) {
            // En caso de error, mantener valores por defecto
        }

        return $estadisticas;
    }

    /**
     * Obtener configuración del día de elecciones
     */
    private function getDiaElecciones()
    {
        // Obtener de la configuración del sistema o usar valor por defecto
        $config = DB::table('configuracion_sistema')->where('clave', 'dia_elecciones')->first();
        return $config ? $config->valor : Carbon::now()->format('Y-m-d');
    }

    /**
     * Obtener hora de inicio de elecciones
     */
    private function getHoraInicio()
    {
        $config = DB::table('configuracion_sistema')->where('clave', 'hora_inicio')->first();
        return $config ? $config->valor : '06:00:00';
    }

    /**
     * Obtener hora de fin de elecciones
     */
    private function getHoraFin()
    {
        $config = DB::table('configuracion_sistema')->where('clave', 'hora_fin')->first();
        return $config ? $config->valor : '18:00:00';
    }

    /**
     * Verificar si el día de elecciones está activo
     */
    private function verificarEstadoDiaElecciones()
    {
        $diaElecciones = $this->getDiaElecciones();
        $hoy = Carbon::now()->format('Y-m-d');
        $horaInicio = $this->getHoraInicio();
        $horaFin = $this->getHoraFin();
        
        if ($diaElecciones !== $hoy) {
            return false;
        }
        
        $ahora = Carbon::now();
        $inicio = Carbon::parse($diaElecciones . ' ' . $horaInicio);
        $fin = Carbon::parse($diaElecciones . ' ' . $horaFin);
        
        return $ahora->between($inicio, $fin);
    }

    /**
     * API: Controlar el día de elecciones
     */
    public function controlarDiaElecciones(Request $request)
    {
        $accion = $request->input('accion');
        $dia = $request->input('dia');
        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');

        try {
            if ($accion === 'iniciar' || $accion === 'activar') {
                // Activar día de elecciones
                DB::table('configuracion_sistema')->updateOrInsert(
                    ['clave' => 'dia_elecciones'],
                    ['valor' => $dia, 'updated_at' => now()]
                );
                DB::table('configuracion_sistema')->updateOrInsert(
                    ['clave' => 'hora_inicio'],
                    ['valor' => $horaInicio, 'updated_at' => now()]
                );
                DB::table('configuracion_sistema')->updateOrInsert(
                    ['clave' => 'hora_fin'],
                    ['valor' => $horaFin, 'updated_at' => now()]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Día de elecciones activado exitosamente',
                    'accion' => 'activado'
                ]);
            } 
            elseif ($accion === 'detener' || $accion === 'desactivar') {
                // Desactivar día de elecciones
                DB::table('configuracion_sistema')->where('clave', 'dia_elecciones')->delete();
                DB::table('configuracion_sistema')->where('clave', 'hora_inicio')->delete();
                DB::table('configuracion_sistema')->where('clave', 'hora_fin')->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Día de elecciones desactivado exitosamente',
                    'accion' => 'desactivado'
                ]);
            }
            elseif ($accion === 'extender') {
                // Extender tiempo
                DB::table('configuracion_sistema')->updateOrInsert(
                    ['clave' => 'hora_fin'],
                    ['valor' => $horaFin, 'updated_at' => now()]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Tiempo extendido exitosamente',
                    'accion' => 'extendido'
                ]);
            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Acción no reconocida'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al controlar el día de elecciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener estado del día de elecciones
     */
    public function getEstadoDiaElecciones()
    {
        return response()->json([
            'dia' => $this->getDiaElecciones(),
            'hora_inicio' => $this->getHoraInicio(),
            'hora_fin' => $this->getHoraFin(),
            "esta_activo" => $this->verificarEstadoDiaElecciones(),
            'hora_actual' => now()->format('H:i:s'),
            'fecha_actual' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * API: Obtener puestos por municipio
     */
    public function getPuestosPorMunicipio($municipioId)
    {
        $puestos = Puesto::where('municipio_id', $municipioId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($puestos);
    }

    /**
     * API: Obtener mesas con paginación
     */
    public function getMesasPaginadas(Request $request)
    {
        $query = Mesa::with(['puesto.municipio.departamento']);

        // Filtros
        if ($request->filled('departamento_id')) {
            $query->whereHas('puesto.municipio', function ($q) use ($request) {
                $q->where('departamento_id', $request->departamento_id);
            });
        }

        if ($request->filled('municipio_id')) {
            $query->whereHas('puesto', function ($q) use ($request) {
                $q->where('municipio_id', $request->municipio_id);
            });
        }

        if ($request->filled('puesto_id')) {
            $query->where('puesto_id', $request->puesto_id);
        }

        if ($request->filled('estado_reporte')) {
            if ($request->estado_reporte === 'con_reporte') {
                $query->whereHas('testigos');
            } else {
                $query->whereDoesntHave('testigos');
            }
        }

        $perPage = $request->get('per_page', 25);
        $page = $request->get('page', 1);
        
        $mesas = $query->orderBy('descripcion')->paginate($perPage, ['*'], 'page', $page);

        $mesasData = $mesas->getCollection()->map(function ($mesa) {
            $reporte = Testigo::where('mesa_id', $mesa->id)->first();

            return [
                'id' => $mesa->id,
                'descripcion' => $mesa->descripcion,
                'puesto' => $mesa->puesto->nombre ?? 'N/A',
                'municipio' => $mesa->puesto->municipio->nombre ?? 'N/A',
                'departamento' => $mesa->puesto->municipio->departamento->nombre ?? 'N/A',
                'tiene_reporte' => $reporte !== null,
                'reporte' => $reporte ? [
                    'id' => $reporte->id,
                    'cantidad_votos' => $reporte->cantidad_votos,
                    'usuario' => $reporte->usuario,
                    'tiene_archivo' => !empty($reporte->archivo),
                    'observaciones' => $reporte->observaciones,
                ] : null,
            ];
        });

        return response()->json([
            'mesas' => $mesasData,
            'pagination' => [
                'total' => $mesas->total(),
                'per_page' => $mesas->perPage(),
                'current_page' => $mesas->currentPage(),
                'last_page' => $mesas->lastPage(),
                'from' => $mesas->firstItem(),
                'to' => $mesas->lastItem(),
            ],
        ]);
    }

    /**
     * API: Exportar datos
     */
    public function exportarDatos()
    {
        try {
            $datos = [
                'estadisticas' => $this->getEstadisticasGenerales(),
                'testigos' => $this->getTestigos(new Request())->getData()->testigos,
                'resumen_puestos' => $this->getResumenPuestos(new Request())->getData()->puestos,
                'fecha_exportacion' => now()->format('Y-m-d H:i:s'),
            ];

            $filename = 'monitoreo_testigos_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            return response($datos)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener resumen general para notificaciones
     */
    public function getResumenGeneral()
    {
        $estadisticas = $this->getEstadisticasGenerales();
        
        return response()->json([
            'resumen' => [
                'total_testigos' => $estadisticas['total_testigos'],
                'testigos_activos' => $estadisticas['testigos_activos'],
                'total_mesas' => $estadisticas['total_mesas'],
                'mesas_con_reporte' => $estadisticas['mesas_con_reporte'],
                'porcentaje_cobertura' => $estadisticas['porcentaje_cobertura'],
                'total_votos' => $estadisticas['total_votos_reportados'],
                'estado_dia' => $this->verificarEstadoDiaElecciones() ? 'Activo' : 'Inactivo',
            ],
            'alertas' => [
                'testigos_inactivos' => $estadisticas['total_testigos'] - $estadisticas['testigos_activos'],
                'mesas_pendientes' => $estadisticas['mesas_pendientes'],
                'cobertura_baja' => $estadisticas['porcentaje_cobertura'] < 50,
            ]
        ]);
    }

    /**
     * API: Notificar fin del día
     */
    public function notificarFinDia(Request $request)
    {
        $motivo = $request->input('motivo', 'Finalización programada');
        $auto = $request->input('auto', false);

        try {
            // Enviar notificación a todos los testigos activos
            $rolTestigo = Role::where('slug', 'testigo')->first();
            if ($rolTestigo) {
                $testigosActivos = User::where('role_id', $rolTestigo->id)->get();
                
                foreach ($testigosActivos as $testigo) {
                    // Aquí podría integrarse con sistema de notificaciones
                    // Por ahora, solo registramos en log
                    DB::table('notificaciones')->insert([
                        'usuario_id' => $testigo->id,
                        'tipo' => 'fin_dia',
                        'mensaje' => "El día de elecciones ha finalizado. Motivo: {$motivo}",
                        'leida' => false,
                        'created_at' => now()
                    ]);
                }
            }

            // Desactivar automáticamente el día de elecciones
            DB::table('configuracion_sistema')->where('clave', 'dia_elecciones')->delete();
            DB::table('configuracion_sistema')->where('clave', 'hora_inicio')->delete();
            DB::table('configuracion_sistema')->where('clave', 'hora_fin')->delete();

            return response()->json([
                'success' => true,
                'message' => 'Día finalizado y notificaciones enviadas',
                'testigos_notificados' => $testigosActivos->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al notificar fin del día: ' . $e->getMessage()
            ], 500);
        }
    }
}
