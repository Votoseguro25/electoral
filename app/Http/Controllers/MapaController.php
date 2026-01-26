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

}
