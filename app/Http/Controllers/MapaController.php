<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapaController extends Controller
{
    public function __construct() {
    }

    public function mostrarResultado() {
        return view('pages.mapa.resultado');
    }

     /**
     * Limpia un parámetro: si viene vacío, lo convierte en null.
     */
    protected function nullIfEmpty($value)
    {
        $v = is_string($value) ? trim($value) : $value;
        return ($v === '' || $v === null) ? null : $v;
    }

    /**
     * Resultados por departamento (agregado por candidato) con filtros.
     * Parámetros por query:
     *  - ?departamento=
     *  - ?partido_id=
     *  - ?candidato_id=
     */
    public function resultadosPorDepartamento(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $partidoId    = $this->nullIfEmpty($request->query('partido_id'));
        $candidatoId  = $this->nullIfEmpty($request->query('candidato_id'));

        if (!is_null($partidoId)) {
            $partidoId = (int) $partidoId;
        }
        if (!is_null($candidatoId)) {
            $candidatoId = (int) $candidatoId;
        }

        $rows = DB::select(
            'CALL sp_resultados_por_departamento_filtrado(?,?,?)',
            [$departamento, $partidoId, $candidatoId]
        );

        return response()->json($rows);
    }

    /**
     * Ganador por departamento (uno por departamento) con filtros opcionales.
     */
    public function ganadorPorDepartamento(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $partidoId    = $this->nullIfEmpty($request->query('partido_id'));
        $candidatoId  = $this->nullIfEmpty($request->query('candidato_id'));

        if (!is_null($partidoId)) {
            $partidoId = (int) $partidoId;
        }
        if (!is_null($candidatoId)) {
            $candidatoId = (int) $candidatoId;
        }

        $rows = DB::select(
            'CALL sp_ganador_por_departamento_filtrado(?,?,?)',
            [$departamento, $partidoId, $candidatoId]
        );

        return response()->json($rows);
    }


    /**
     * Resultados por municipio (agregado por candidato) con filtros.
     * Parámetros:
     *  - ?departamento=
     *  - ?municipio=
     *  - ?partido_id=
     *  - ?candidato_id=
     */
    public function resultadosPorMunicipio(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $municipio    = $this->nullIfEmpty($request->query('municipio'));
        $partidoId    = $this->nullIfEmpty($request->query('partido_id'));
        $candidatoId  = $this->nullIfEmpty($request->query('candidato_id'));

        if (!is_null($partidoId)) {
            $partidoId = (int) $partidoId;
        }
        if (!is_null($candidatoId)) {
            $candidatoId = (int) $candidatoId;
        }

        $rows = DB::select(
            'CALL sp_resultados_por_municipio_filtrado(?,?,?,?)',
            [$departamento, $municipio, $partidoId, $candidatoId]
        );

        return response()->json($rows);
    }

    /**
     * Ganador por municipio (uno por municipio) con filtros opcionales.
     */
    public function ganadorPorMunicipio(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $municipio    = $this->nullIfEmpty($request->query('municipio'));
        $partidoId    = $this->nullIfEmpty($request->query('partido_id'));
        $candidatoId  = $this->nullIfEmpty($request->query('candidato_id'));

        if (!is_null($partidoId)) {
            $partidoId = (int) $partidoId;
        }
        if (!is_null($candidatoId)) {
            $candidatoId = (int) $candidatoId;
        }

        $rows = DB::select(
            'CALL sp_ganador_por_municipio_filtrado(?,?,?,?)',
            [$departamento, $municipio, $partidoId, $candidatoId]
        );

        return response()->json($rows);
    }


    /**
     * Resumen por departamento (participación, blancos, nulos) con filtros.
     */
    public function resumenDepartamentos(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $partidoId    = $this->nullIfEmpty($request->query('partido_id'));
        $candidatoId  = $this->nullIfEmpty($request->query('candidato_id'));

        if (!is_null($partidoId)) {
            $partidoId = (int) $partidoId;
        }
        if (!is_null($candidatoId)) {
            $candidatoId = (int) $candidatoId;
        }

        $rows = DB::select(
            'CALL sp_resumen_departamentos_filtrado(?,?,?)',
            [$departamento, $partidoId, $candidatoId]
        );

        return response()->json($rows);
    }

    /**
     * Resumen por municipio con filtros.
     */
    public function resumenMunicipios(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $municipio    = $this->nullIfEmpty($request->query('municipio'));
        $partidoId    = $this->nullIfEmpty($request->query('partido_id'));
        $candidatoId  = $this->nullIfEmpty($request->query('candidato_id'));

        if (!is_null($partidoId)) {
            $partidoId = (int) $partidoId;
        }
        if (!is_null($candidatoId)) {
            $candidatoId = (int) $candidatoId;
        }

        $rows = DB::select(
            'CALL sp_resumen_municipios_filtrado(?,?,?,?)',
            [$departamento, $municipio, $partidoId, $candidatoId]
        );

        return response()->json($rows);
    }

    /**
     * Mostrar vista del mapa de votantes registrados
     */
    public function mostrarVotantesRegistrados()
    {
        return view('pages.mapa.votantes-registrados');
    }

    /**
     * Obtener cantidad de votantes registrados por departamento
     */
    public function votantesPorDepartamento(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));

        $query = DB::table('personas')
            ->join('votantes', 'personas.id', '=', 'votantes.persona_id')
            ->join('municipios', 'personas.municipio_id', '=', 'municipios.id')
            ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
            ->select(
                'departamentos.id as departamento_id',
                'departamentos.nombre as nombre_departamento',
                DB::raw('COUNT(DISTINCT personas.id) as total_votantes'),
                DB::raw('SUM(CASE WHEN personas.genero_id = 1 THEN 1 ELSE 0 END) as total_hombres'),
                DB::raw('SUM(CASE WHEN personas.genero_id = 2 THEN 1 ELSE 0 END) as total_mujeres')
            )
            ->groupBy('departamentos.id', 'departamentos.nombre');

        if ($departamento) {
            $query->where('departamentos.nombre', $departamento);
        }

        $rows = $query->get();

        return response()->json($rows);
    }

    /**
     * Obtener cantidad de votantes registrados por municipio
     */
    public function votantesPorMunicipio(Request $request)
    {
        $departamento = $this->nullIfEmpty($request->query('departamento'));
        $municipio = $this->nullIfEmpty($request->query('municipio'));

        $query = DB::table('personas')
            ->join('votantes', 'personas.id', '=', 'votantes.persona_id')
            ->join('municipios', 'personas.municipio_id', '=', 'municipios.id')
            ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
            ->select(
                'municipios.id as municipio_id',
                'municipios.nombre as nombre_municipio',
                'departamentos.nombre as nombre_departamento',
                DB::raw('COUNT(DISTINCT personas.id) as total_votantes'),
                DB::raw('SUM(CASE WHEN personas.genero_id = 1 THEN 1 ELSE 0 END) as total_hombres'),
                DB::raw('SUM(CASE WHEN personas.genero_id = 2 THEN 1 ELSE 0 END) as total_mujeres')
            )
            ->groupBy('municipios.id', 'municipios.nombre', 'departamentos.nombre');

        if ($departamento) {
            $query->where('departamentos.nombre', $departamento);
        }

        if ($municipio) {
            $query->where('municipios.nombre', $municipio);
        }

        $rows = $query->get();

        return response()->json($rows);
    }

}
