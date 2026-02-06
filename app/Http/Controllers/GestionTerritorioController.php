<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Puesto;
use App\Models\Mesa;
use App\Models\Barrio;
use App\Models\Persona;
use App\Exports\CoberturaTerritorioExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class GestionTerritorioController extends Controller
{
    /**
     * Vista principal del módulo de Gestión de Territorio
     */
    public function index()
    {
        $estadisticas = $this->getEstadisticasGenerales();
        $departamentos = $this->getDepartamentosConEstadisticas();

        return view('pages.territorio.index', compact('estadisticas', 'departamentos'));
    }

/**
     * API: Obtener municipios de un departamento con estadísticas
     */
    public function getMunicipios($departamentoId)
    {
        $municipios = Municipio::where('departamento_id', $departamentoId)
            ->withCount(['votantes', 'puestos'])
            ->orderBy('nombre')
            ->get()
            ->map(function ($municipio) {
                // Contar mesas del municipio
                $totalMesas = Mesa::whereHas('puesto', function($q) use ($municipio) {
                    $q->where('municipio_id', $municipio->id);
                })->count();

                // Contar mesas con testigos
                $mesasConTestigos = 0;
                if (Schema::hasTable('testigos')) {
                    $mesasConTestigos = Mesa::whereHas('puesto', function($q) use ($municipio) {
                        $q->where('municipio_id', $municipio->id);
                    })->whereHas('testigos')->count();
                }

                // Contar líderes del municipio
                $totalLideres = 0;
                if (Schema::hasTable('lideres')) {
                    $totalLideres = DB::table('lideres')
                        ->join('personas', 'lideres.persona_id', '=', 'personas.id')
                        ->where('personas.municipio_id', $municipio->id)
                        ->count();
                }

                // Calcular índice de cobertura
                $indiceCobertura = $this->calcularIndiceCoberturaDepartamento(
                    $municipio->votantes_count,
                    $totalMesas,
                    $mesasConTestigos,
                    $totalLideres
                );

                return [
                    'id' => $municipio->id,
                    'nombre' => $municipio->nombre,
                    'total_votantes' => $municipio->votantes_count,
                    'total_puestos' => $municipio->puestos_count,
                    'total_mesas' => $totalMesas,
                    'mesas_con_testigos' => $mesasConTestigos,
                    'total_lideres' => $totalLideres,
                    'indice_cobertura' => $indiceCobertura,
                    'nivel_cobertura' => $this->getNivelCobertura($indiceCobertura),
                ];
            });

        return response()->json($municipios);
    }

/**
     * API: Obtener puestos de un municipio con estadísticas
     */
    public function getPuestos($municipioId)
    {
        $puestos = Puesto::where('municipio_id', $municipioId)
            ->withCount('mesas')
            ->orderBy('nombre')
            ->get()
            ->map(function ($puesto) {
                // Obtener IDs de mesas del puesto
                $mesaIds = Mesa::where('puesto_id', $puesto->id)->pluck('id');
                $totalVotantes = Persona::whereIn('mesa_id', $mesaIds)->count();

                // Contar mesas con testigos
                $mesasConTestigos = 0;
                if (Schema::hasTable('testigos')) {
                    $mesasConTestigos = Mesa::where('puesto_id', $puesto->id)
                        ->whereHas('testigos')
                        ->count();
                }

                // Contar votantes que ya votaron
                $votantesQueVotaron = 0;
                if (Schema::hasColumn('personas', 'reporte_voto')) {
                    $votantesQueVotaron = Persona::whereIn('mesa_id', $mesaIds)
                        ->where('reporte_voto', true)
                        ->count();
                }

                // Calcular porcentaje de participación
                $porcentajeParticipacion = $totalVotantes > 0 
                    ? round(($votantesQueVotaron / $totalVotantes) * 100, 1) 
                    : 0;

                // Calcular índice de cobertura del puesto
                $indiceCobertura = $puesto->mesas_count > 0 
                    ? round(($mesasConTestigos / $puesto->mesas_count) * 100, 1) 
                    : 0;

                return [
                    'id' => $puesto->id,
                    'nombre' => $puesto->nombre,
                    'direccion' => $puesto->direccion,
                    'total_mesas' => $puesto->mesas_count,
                    'total_votantes' => $totalVotantes,
                    'mesas_con_testigos' => $mesasConTestigos,
                    'votantes_participacion' => $votantesQueVotaron,
                    'porcentaje_participacion' => $porcentajeParticipacion,
                    'indice_cobertura' => $indiceCobertura,
                    'cantidad_hombres' => $puesto->cantidad_hombres ?? 0,
                    'cantidad_mujeres' => $puesto->cantidad_mujeres ?? 0,
                ];
            });

        return response()->json($puestos);
    }

/**
     * API: Obtener mesas de un puesto con estadísticas
     */
    public function getMesas($puestoId)
    {
        $mesas = Mesa::where('puesto_id', $puestoId)
            ->withCount(['votantes', 'testigos'])
            ->orderBy('descripcion')
            ->get()
            ->map(function ($mesa) {
                $votaron = Persona::where('mesa_id', $mesa->id)
                    ->where('reporte_voto', true)
                    ->count();

                // Contar líderes asignados a esta mesa
                $lideresAsignados = 0;
                if (Schema::hasTable('lideres')) {
                    $lideresAsignados = DB::table('lideres')
                        ->join('votantes', 'lideres.id', '=', 'votantes.lider_id')
                        ->join('personas', 'votantes.persona_id', '=', 'personas.id')
                        ->where('personas.mesa_id', $mesa->id)
                        ->distinct('lideres.id')
                        ->count();
                }

                // Calcular densidad de votantes por líder
                $densidadLider = $lideresAsignados > 0 
                    ? round($mesa->votantes_count / $lideresAsignados, 1) 
                    : 0;

                // Calcular porcentaje de participación
                $porcentajeParticipacion = $mesa->votantes_count > 0 
                    ? round(($votaron / $mesa->votantes_count) * 100, 1) 
                    : 0;

                // Determinar estado de la mesa
                $estadoMesa = $this->getEstadoMesa($mesa->testigos_count, $porcentajeParticipacion);

                return [
                    'id' => $mesa->id,
                    'descripcion' => $mesa->descripcion,
                    'total_votantes' => $mesa->votantes_count,
                    'votaron' => $votaron,
                    'pendientes' => $mesa->votantes_count - $votaron,
                    'tiene_testigo' => $mesa->testigos_count > 0,
                    'cantidad_testigos' => $mesa->testigos_count,
                    'lideres_asignados' => $lideresAsignados,
                    'densidad_lider' => $densidadLider,
                    'porcentaje_participacion' => $porcentajeParticipacion,
                    'estado_mesa' => $estadoMesa,
                ];
            });

        return response()->json($mesas);
    }

    /**
     * Determinar estado de una mesa según cobertura y participación
     */
    private function getEstadoMesa($testigosCount, $participacion)
    {
        if ($testigosCount == 0) {
            return 'sin_testigo';
        } elseif ($participacion >= 80) {
            return 'optima';
        } elseif ($participacion >= 60) {
            return 'buena';
        } elseif ($participacion >= 40) {
            return 'regular';
        } else {
            return 'baja';
        }
    }

/**
     * API: Obtener barrios de un municipio
     */
    public function getBarrios($municipioId)
    {
        if (!Schema::hasTable('barrios')) {
            return response()->json([]);
        }

        $barrios = Barrio::where('municipio_id', $municipioId)
            ->withCount('personas')
            ->orderBy('nombre')
            ->get()
            ->map(function ($barrio) {
                // Contar votantes en el barrio
                $votantesCount = 0;
                if (Schema::hasTable('votantes')) {
                    $votantesCount = DB::table('votantes')
                        ->join('personas', 'votantes.persona_id', '=', 'personas.id')
                        ->where('personas.barrio_id', $barrio->id)
                        ->count();
                }

                // Contar líderes en el barrio
                $lideresCount = 0;
                if (Schema::hasTable('lideres')) {
                    $lideresCount = DB::table('lideres')
                        ->join('personas', 'lideres.persona_id', '=', 'personas.id')
                        ->where('personas.barrio_id', $barrio->id)
                        ->count();
                }

                // Calcular índice de cobertura del barrio
                $indiceCobertura = $barrio->personas_count > 0 
                    ? round(($votantesCount / $barrio->personas_count) * 100, 1) 
                    : 0;

                // Determinar prioridad del barrio
                $prioridad = $this->getPrioridadBarrio($barrio->personas_count, $votantesCount, $lideresCount);

                return [
                    'id' => $barrio->id,
                    'nombre' => $barrio->nombre,
                    'total_personas' => $barrio->personas_count,
                    'votantes_count' => $votantesCount,
                    'lideres_count' => $lideresCount,
                    'indice_cobertura' => $indiceCobertura,
                    'prioridad' => $prioridad,
                    'latitud' => $barrio->latitud,
                    'longitud' => $barrio->longitud,
                ];
            });

        return response()->json($barrios);
    }

    /**
     * Determinar prioridad de un barrio según su cobertura
     */
    private function getPrioridadBarrio($totalPersonas, $votantesCount, $lideresCount)
    {
        if ($totalPersonas == 0) {
            return 'sin_datos';
        }

        $coberturaVotantes = ($votantesCount / $totalPersonas) * 100;
        $densidadLider = $lideresCount > 0 ? $totalPersonas / $lideresCount : 999;

        if ($coberturaVotantes < 30 || $densidadLider > 100) {
            return 'critica';
        } elseif ($coberturaVotantes < 50 || $densidadLider > 75) {
            return 'alta';
        } elseif ($coberturaVotantes < 70 || $densidadLider > 50) {
            return 'media';
        } else {
            return 'baja';
        }
    }

/**
     * API: Resumen de cobertura territorial
     */
    public function resumenCobertura()
    {
        $cobertura = [];

        // Municipios con votantes
        $municipiosConVotantes = Municipio::whereHas('votantes')->count();
        $totalMunicipios = Municipio::count();

        // Puestos con mesas asignadas
        $puestosConMesas = Puesto::whereHas('mesas')->count();
        $totalPuestos = Puesto::count();

        // Mesas con testigos
        $mesasConTestigos = 0;
        $totalMesas = Mesa::count();
        if (Schema::hasTable('testigos')) {
            $mesasConTestigos = Mesa::whereHas('testigos')->count();
        }

        // Nuevas métricas de cobertura
        $lideresActivos = 0;
        $totalLideres = 0;
        $barriosCubiertos = 0;
        $totalBarrios = 0;
        
        if (Schema::hasTable('lideres')) {
            $lideresActivos = DB::table('lideres')
                ->join('votantes', 'lideres.id', '=', 'votantes.lider_id')
                ->distinct('lideres.id')
                ->count();
            $totalLideres = DB::table('lideres')->count();
        }
        
        if (Schema::hasTable('barrios')) {
            $barriosCubiertos = Barrio::whereHas('personas')->count();
            $totalBarrios = Barrio::count();
        }

        // Cobertura por departamento
        $coberturaPorDepartamento = $this->getCoberturaPorDepartamento();

        // Zonas críticas (baja cobertura)
        $zonasCriticas = $this->getZonasCriticas();

        $cobertura = [
            'municipios' => [
                'con_votantes' => $municipiosConVotantes,
                'total' => $totalMunicipios,
                'porcentaje' => $totalMunicipios > 0 ? round(($municipiosConVotantes / $totalMunicipios) * 100, 1) : 0,
            ],
            'puestos' => [
                'con_mesas' => $puestosConMesas,
                'total' => $totalPuestos,
                'porcentaje' => $totalPuestos > 0 ? round(($puestosConMesas / $totalPuestos) * 100, 1) : 0,
            ],
            'mesas' => [
                'con_testigos' => $mesasConTestigos,
                'total' => $totalMesas,
                'porcentaje' => $totalMesas > 0 ? round(($mesasConTestigos / $totalMesas) * 100, 1) : 0,
            ],
            'lideres' => [
                'activos' => $lideresActivos,
                'total' => $totalLideres,
                'porcentaje' => $totalLideres > 0 ? round(($lideresActivos / $totalLideres) * 100, 1) : 0,
            ],
            'barrios' => [
                'cubiertos' => $barriosCubiertos,
                'total' => $totalBarrios,
                'porcentaje' => $totalBarrios > 0 ? round(($barriosCubiertos / $totalBarrios) * 100, 1) : 0,
            ],
            'por_departamento' => $coberturaPorDepartamento,
            'zonas_criticas' => $zonasCriticas,
            'indice_general' => $this->calcularIndiceGeneralCobertura($cobertura),
        ];

        return response()->json($cobertura);
    }

    /**
     * Guardar nuevo puesto
     */
    public function guardarPuesto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'municipio_id' => 'required|exists:municipios,id',
        ], [
            'nombre.required' => 'El nombre del puesto es requerido',
            'municipio_id.required' => 'Debe seleccionar un municipio',
            'municipio_id.exists' => 'El municipio seleccionado no existe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $puesto = Puesto::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'municipio_id' => $request->municipio_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Puesto creado exitosamente',
            'puesto' => $puesto,
        ]);
    }

    /**
     * Actualizar puesto
     */
    public function actualizarPuesto(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $puesto = Puesto::findOrFail($id);
        $puesto->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Puesto actualizado exitosamente',
        ]);
    }

    /**
     * Guardar nueva mesa
     */
    public function guardarMesa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
            'puesto_id' => 'required|exists:puestos,id',
        ], [
            'descripcion.required' => 'La descripción de la mesa es requerida',
            'puesto_id.required' => 'Debe seleccionar un puesto',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $mesa = Mesa::create([
            'descripcion' => $request->descripcion,
            'puesto_id' => $request->puesto_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada exitosamente',
            'mesa' => $mesa,
        ]);
    }

    /**
     * Actualizar mesa
     */
    public function actualizarMesa(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $mesa = Mesa::findOrFail($id);
        $mesa->update([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada exitosamente',
        ]);
    }

    /**
     * Eliminar mesa
     */
    public function eliminarMesa($id)
    {
        $mesa = Mesa::findOrFail($id);

        // Verificar si tiene votantes asignados
        if ($mesa->votantes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la mesa porque tiene votantes asignados',
            ], 400);
        }

        $mesa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mesa eliminada exitosamente',
        ]);
    }

/**
     * Obtener estadísticas generales
     */
    private function getEstadisticasGenerales()
    {
        $estadisticas = [
            'total_departamentos' => 0,
            'total_municipios' => 0,
            'total_puestos' => 0,
            'total_mesas' => 0,
            'total_barrios' => 0,
            'total_votantes' => 0,
            'total_lideres' => 0,
            'mesas_con_testigos' => 0,
            'cobertura_testigos' => 0,
            'votantes_activos' => 0,
            'participacion_general' => 0,
            'indice_cobertura_general' => 0,
        ];

        try {
            $estadisticas['total_departamentos'] = Departamento::count();
            $estadisticas['total_municipios'] = Municipio::count();
            $estadisticas['total_puestos'] = Puesto::count();
            $estadisticas['total_mesas'] = Mesa::count();
            $estadisticas['total_votantes'] = Persona::count();

            // Contar líderes
            if (Schema::hasTable('lideres')) {
                $estadisticas['total_lideres'] = DB::table('lideres')->count();
            }

            // Contar barrios
            if (Schema::hasTable('barrios')) {
                $estadisticas['total_barrios'] = Barrio::count();
            }

            // Contar mesas con testigos
            if (Schema::hasTable('testigos')) {
                $estadisticas['mesas_con_testigos'] = Mesa::whereHas('testigos')->count();
                if ($estadisticas['total_mesas'] > 0) {
                    $estadisticas['cobertura_testigos'] = round(
                        ($estadisticas['mesas_con_testigos'] / $estadisticas['total_mesas']) * 100,
                        1
                    );
                }
            }

            // Contar votantes activos (con reporte de voto)
            if (Schema::hasColumn('personas', 'reporte_voto')) {
                $estadisticas['votantes_activos'] = Persona::where('reporte_voto', true)->count();
                if ($estadisticas['total_votantes'] > 0) {
                    $estadisticas['participacion_general'] = round(
                        ($estadisticas['votantes_activos'] / $estadisticas['total_votantes']) * 100,
                        1
                    );
                }
            }

            // Calcular índice general de cobertura
            $indices = [];
            if ($estadisticas['cobertura_testigos'] > 0) {
                $indices[] = $estadisticas['cobertura_testigos'];
            }
            if ($estadisticas['total_lideres'] > 0 && $estadisticas['total_votantes'] > 0) {
                $lideresOptimos = ceil($estadisticas['total_votantes'] / 50);
                $indices[] = min(($estadisticas['total_lideres'] / $lideresOptimos) * 100, 100);
            }
            if ($estadisticas['participacion_general'] > 0) {
                $indices[] = $estadisticas['participacion_general'];
            }

            if (!empty($indices)) {
                $estadisticas['indice_cobertura_general'] = round(array_sum($indices) / count($indices), 1);
            }

        } catch (\Exception $e) {
            // En caso de error, mantener valores por defecto
        }

        return $estadisticas;
    }

/**
     * Obtener departamentos con estadísticas
     */
    private function getDepartamentosConEstadisticas()
    {
        try {
            return Departamento::select('departamentos.id', 'departamentos.nombre')
                ->withCount('municipios')
                ->orderBy('nombre')
                ->get()
                ->map(function ($depto) {
                    // Contar votantes del departamento
                    $totalVotantes = Persona::whereHas('municipio', function ($q) use ($depto) {
                        $q->where('departamento_id', $depto->id);
                    })->count();

                    // Contar puestos del departamento
                    $totalPuestos = Puesto::whereHas('municipio', function ($q) use ($depto) {
                        $q->where('departamento_id', $depto->id);
                    })->count();

                    // Contar mesas del departamento
                    $totalMesas = Mesa::whereHas('puesto', function ($q) use ($depto) {
                        $q->whereHas('municipio', function ($q2) use ($depto) {
                            $q2->where('departamento_id', $depto->id);
                        });
                    })->count();

                    // Contar testigos del departamento
                    $mesasConTestigos = 0;
                    if (Schema::hasTable('testigos')) {
                        $mesasConTestigos = Mesa::whereHas('puesto', function ($q) use ($depto) {
                            $q->whereHas('municipio', function ($q2) use ($depto) {
                                $q2->where('departamento_id', $depto->id);
                            });
                        })->whereHas('testigos')->count();
                    }

                    // Contar líderes del departamento
                    $totalLideres = 0;
                    if (Schema::hasTable('lideres')) {
                        $totalLideres = DB::table('lideres')
                            ->join('personas', 'lideres.persona_id', '=', 'personas.id')
                            ->join('municipios', 'personas.municipio_id', '=', 'municipios.id')
                            ->where('municipios.departamento_id', $depto->id)
                            ->count();
                    }

                    // Calcular índice de cobertura del departamento
                    $indiceCobertura = $this->calcularIndiceCoberturaDepartamento(
                        $totalVotantes, 
                        $totalMesas, 
                        $mesasConTestigos, 
                        $totalLideres
                    );

                    return [
                        'id' => $depto->id,
                        'nombre' => $depto->nombre,
                        'total_municipios' => $depto->municipios_count,
                        'total_votantes' => $totalVotantes,
                        'total_puestos' => $totalPuestos,
                        'total_mesas' => $totalMesas,
                        'mesas_con_testigos' => $mesasConTestigos,
                        'total_lideres' => $totalLideres,
                        'indice_cobertura' => $indiceCobertura,
                        'nivel_cobertura' => $this->getNivelCobertura($indiceCobertura),
                    ];
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Obtener cobertura por departamento
     */
    private function getCoberturaPorDepartamento()
    {
        try {
            return Departamento::select('id', 'nombre')
                ->with(['municipios' => function($q) {
                    $q->withCount(['votantes', 'puestos']);
                }])
                ->get()
                ->map(function ($depto) {
                    $totalVotantes = $depto->municipios->sum('votantes_count');
                    $totalPuestos = $depto->municipios->sum('puestos_count');
                    
                    $mesasConTestigos = 0;
                    $totalMesas = 0;
                    
                    if (Schema::hasTable('testigos')) {
                        $mesasData = Mesa::whereHas('puesto.municipio', function($q) use ($depto) {
                            $q->where('departamento_id', $depto->id);
                        })->withCount('testigos')->get();
                        
                        $totalMesas = $mesasData->count();
                        $mesasConTestigos = $mesasData->where('testigos_count', '>', 0)->count();
                    }

                    return [
                        'id' => $depto->id,
                        'nombre' => $depto->nombre,
                        'votantes' => $totalVotantes,
                        'puestos' => $totalPuestos,
                        'mesas' => [
                            'total' => $totalMesas,
                            'con_testigos' => $mesasConTestigos,
                            'porcentaje_cobertura' => $totalMesas > 0 ? round(($mesasConTestigos / $totalMesas) * 100, 1) : 0
                        ]
                    ];
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Identificar zonas críticas con baja cobertura
     */
    private function getZonasCriticas()
    {
        $zonasCriticas = [];

        try {
            // Municipios con baja cobertura de testigos
            if (Schema::hasTable('testigos')) {
                $municipiosCriticos = DB::table('municipios')
                    ->select('municipios.id', 'municipios.nombre', 'departamentos.nombre as departamento')
                    ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
                    ->leftJoin('puestos', 'municipios.id', '=', 'puestos.municipio_id')
                    ->leftJoin('mesas', 'puestos.id', '=', 'mesas.puesto_id')
                    ->leftJoin('testigo', 'mesas.id', '=', 'testigo.mesa_id')
                    ->groupBy('municipios.id', 'municipios.nombre', 'departamentos.nombre')
                    ->havingRaw('COUNT(DISTINCT mesas.id) > 0')
                    ->havingRaw('COUNT(DISTINCT testigo.id) / COUNT(DISTINCT mesas.id) < 0.3')
                    ->limit(5)
                    ->get();

                $zonasCriticas['municipios_baja_testigos'] = $municipiosCriticos->toArray();
            }

            // Puestos sin mesas asignadas
            $puestosSinMesas = Puesto::whereDoesntHave('mesas')
                ->with('municipio.departamento')
                ->limit(5)
                ->get()
                ->map(function ($puesto) {
                    return [
                        'id' => $puesto->id,
                        'nombre' => $puesto->nombre,
                        'municipio' => $puesto->municipio->nombre,
                        'departamento' => $puesto->municipio->departamento->nombre
                    ];
                });

            $zonasCriticas['puestos_sin_mesas'] = $puestosSinMesas->toArray();

            // Municipios con muchos votantes pero pocos líderes
            if (Schema::hasTable('lideres')) {
                $municipiosSinLideres = DB::table('municipios')
                    ->select('municipios.id', 'municipios.nombre', 'departamentos.nombre as departamento')
                    ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
                    ->leftJoin('personas', 'municipios.id', '=', 'personas.municipio_id')
                    ->leftJoin('votantes', 'personas.id', '=', 'votantes.persona_id')
                    ->leftJoin('lideres', 'votantes.lider_id', '=', 'lideres.id')
                    ->groupBy('municipios.id', 'municipios.nombre', 'departamentos.nombre')
                    ->havingRaw('COUNT(DISTINCT personas.id) > 100')
                    ->havingRaw('COUNT(DISTINCT lideres.id) < 2')
                    ->limit(5)
                    ->get();

                $zonasCriticas['municipios_pocos_lideres'] = $municipiosSinLideres->toArray();
            }

        } catch (\Exception $e) {
            // En caso de error, retornar array vacío
        }

        return $zonasCriticas;
    }

    /**
     * Calcular índice general de cobertura
     */
    private function calcularIndiceGeneralCobertura($cobertura)
    {
        $indices = [];

        if (isset($cobertura['municipios']['porcentaje'])) {
            $indices[] = $cobertura['municipios']['porcentaje'];
        }
        if (isset($cobertura['puestos']['porcentaje'])) {
            $indices[] = $cobertura['puestos']['porcentaje'];
        }
        if (isset($cobertura['mesas']['porcentaje'])) {
            $indices[] = $cobertura['mesas']['porcentaje'];
        }
        if (isset($cobertura['lideres']['porcentaje'])) {
            $indices[] = $cobertura['lideres']['porcentaje'];
        }

        if (empty($indices)) {
            return 0;
        }

        return round(array_sum($indices) / count($indices), 1);
    }

    /**
     * Calcular índice de cobertura de un departamento
     */
    private function calcularIndiceCoberturaDepartamento($votantes, $mesas, $mesasConTestigos, $lideres)
    {
        $indices = [];

        // Cobertura de testigos en mesas
        if ($mesas > 0) {
            $indices[] = ($mesasConTestigos / $mesas) * 100;
        }

        // Cobertura de líderes (cada líder debería cubrir aproximadamente 50 votantes)
        if ($votantes > 0 && $lideres > 0) {
            $lideresOptimos = ceil($votantes / 50);
            $indices[] = min(($lideres / $lideresOptimos) * 100, 100);
        }

        if (empty($indices)) {
            return 0;
        }

        return round(array_sum($indices) / count($indices), 1);
    }

    /**
     * Obtener nivel de cobertura según el índice
     */
    private function getNivelCobertura($indice)
    {
        if ($indice >= 80) {
            return 'optima';
        } elseif ($indice >= 60) {
            return 'buena';
        } elseif ($indice >= 40) {
            return 'regular';
        } elseif ($indice >= 20) {
            return 'baja';
        } else {
            return 'critica';
        }
    }

    /**
     * API: Optimizar cobertura territorial
     */
    public function optimizarCobertura()
    {
        $recomendaciones = [];

        try {
            // Análisis de cobertura de testigos
            if (Schema::hasTable('testigos')) {
                $mesasSinTestigos = Mesa::whereDoesntHave('testigos')
                    ->with(['puesto.municipio.departamento'])
                    ->limit(10)
                    ->get();

                $recomendaciones['testigos'] = [
                    'prioridad' => 'alta',
                    'mensaje' => 'Asignar testigos a mesas sin cobertura',
                    'cantidad' => $mesasSinTestigos->count(),
                    'detalles' => $mesasSinTestigos->map(function ($mesa) {
                        return [
                            'mesa_id' => $mesa->id,
                            'mesa_descripcion' => $mesa->descripcion,
                            'puesto' => $mesa->puesto->nombre,
                            'municipio' => $mesa->puesto->municipio->nombre,
                            'departamento' => $mesa->puesto->municipio->departamento->nombre,
                        ];
                    })
                ];
            }

            // Análisis de distribución de líderes
            if (Schema::hasTable('lideres')) {
                $municipiosConSobrecarga = DB::table('municipios')
                    ->select('municipios.*', 'departamentos.nombre as departamento')
                    ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
                    ->join('personas', 'municipios.id', '=', 'personas.municipio_id')
                    ->leftJoin('votantes', 'personas.id', '=', 'votantes.persona_id')
                    ->leftJoin('lideres', 'votantes.lider_id', '=', 'lideres.id')
                    ->groupBy('municipios.id', 'departamentos.nombre')
                    ->havingRaw('COUNT(DISTINCT personas.id) > 0')
                    ->havingRaw('COUNT(DISTINCT lideres.id) > 0')
                    ->havingRaw('COUNT(DISTINCT personas.id) / COUNT(DISTINCT lideres.id) > 100')
                    ->limit(5)
                    ->get();

                $recomendaciones['lideres'] = [
                    'prioridad' => 'media',
                    'mensaje' => 'Redistribuir líderes en municipios con sobrecarga',
                    'cantidad' => $municipiosConSobrecarga->count(),
                    'detalles' => $municipiosConSobrecarga->map(function ($municipio) {
                        return [
                            'municipio_id' => $municipio->id,
                            'municipio_nombre' => $municipio->nombre,
                            'departamento' => $municipio->departamento,
                        ];
                    })
                ];
            }

            // Análisis de puestos sin mesas
            $puestosSinMesas = Puesto::whereDoesntHave('mesas')
                ->with('municipio.departamento')
                ->limit(10)
                ->get();

            if ($puestosSinMesas->isNotEmpty()) {
                $recomendaciones['mesas'] = [
                    'prioridad' => 'alta',
                    'mensaje' => 'Crear mesas en puestos sin asignación',
                    'cantidad' => $puestosSinMesas->count(),
                    'detalles' => $puestosSinMesas->map(function ($puesto) {
                        return [
                            'puesto_id' => $puesto->id,
                            'puesto_nombre' => $puesto->nombre,
                            'municipio' => $puesto->municipio->nombre,
                            'departamento' => $puesto->municipio->departamento->nombre,
                        ];
                    })
                ];
            }

            // Análisis de zonas con baja participación
            if (Schema::hasColumn('personas', 'reporte_voto')) {
                $zonasBajaParticipacion = DB::table('municipios')
                    ->select('municipios.*', 'departamentos.nombre as departamento')
                    ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
                    ->join('personas', 'municipios.id', '=', 'personas.municipio_id')
                    ->groupBy('municipios.id', 'departamentos.nombre')
                    ->havingRaw('COUNT(DISTINCT personas.id) > 50')
                    ->havingRaw('SUM(CASE WHEN personas.reporte_voto = 1 THEN 1 ELSE 0 END) / COUNT(DISTINCT personas.id) < 0.3')
                    ->limit(5)
                    ->get();

                if ($zonasBajaParticipacion->isNotEmpty()) {
                    $recomendaciones['participacion'] = [
                        'prioridad' => 'media',
                        'mensaje' => 'Intensificar movilización en zonas con baja participación',
                        'cantidad' => $zonasBajaParticipacion->count(),
                        'detalles' => $zonasBajaParticipacion->map(function ($municipio) {
                            return [
                                'municipio_id' => $municipio->id,
                                'municipio_nombre' => $municipio->nombre,
                                'departamento' => $municipio->departamento,
                            ];
                        })
                    ];
                }
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar recomendaciones: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'recomendaciones' => $recomendaciones,
            'fecha_analisis' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * API: Exportar reporte de cobertura
     */
    public function exportarCobertura(Request $request)
    {
        $tipo = $request->get('tipo', 'completo');
        $formato = $request->get('formato', 'json');

        try {
            // Exportar a Excel
            if ($formato === 'excel' || $formato === 'xlsx') {
                $filename = "cobertura_territorial_{$tipo}_" . date('Y-m-d_H-i-s') . ".xlsx";
                return Excel::download(new CoberturaTerritorioExport($tipo), $filename);
            }

            $datos = [];

            switch ($tipo) {
                case 'resumen':
                    $datos = $this->getResumenCobertura();
                    break;
                case 'detallado':
                    $datos = $this->getReporteDetalladoCobertura();
                    break;
                case 'critico':
                    $datos = $this->getZonasCriticas();
                    break;
                default:
                    $datos = $this->getReporteCompletoCobertura();
            }

            if ($formato === 'csv') {
                return $this->exportarCSV($datos, $tipo);
            }

            return response()->json([
                'success' => true,
                'datos' => $datos,
                'tipo' => $tipo,
                'formato' => $formato,
                'generado' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de cobertura
     */
    private function getResumenCobertura()
    {
        return [
            'estadisticas_generales' => $this->getEstadisticasGenerales(),
            'resumen_cobertura' => $this->resumenCobertura()->getData(true),
        ];
    }

    /**
     * Obtener reporte detallado de cobertura
     */
    private function getReporteDetalladoCobertura()
    {
        return [
            'departamentos' => $this->getDepartamentosConEstadisticas(),
            'cobertura_por_departamento' => $this->getCoberturaPorDepartamento(),
            'zonas_criticas' => $this->getZonasCriticas(),
        ];
    }

    /**
     * Obtener reporte completo de cobertura
     */
    private function getReporteCompletoCobertura()
    {
        return [
            'estadisticas_generales' => $this->getEstadisticasGenerales(),
            'departamentos' => $this->getDepartamentosConEstadisticas(),
            'cobertura_por_departamento' => $this->getCoberturaPorDepartamento(),
            'zonas_criticas' => $this->getZonasCriticas(),
            'recomendaciones' => $this->optimizarCobertura()->getData(true),
        ];
    }

    /**
     * Exportar datos a CSV
     */
    private function exportarCSV($datos, $tipo)
    {
        $filename = "cobertura_territorial_{$tipo}_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($datos) {
            $file = fopen('php://output', 'w');
            
            // Encabezado CSV
            fputcsv($file, ['Reporte de Cobertura Territorial']);
            fputcsv($file, ['Generado:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Escribir datos según el tipo
            if (isset($datos['estadisticas_generales'])) {
                fputcsv($file, ['Estadísticas Generales']);
                foreach ($datos['estadisticas_generales'] as $key => $value) {
                    fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
                }
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
