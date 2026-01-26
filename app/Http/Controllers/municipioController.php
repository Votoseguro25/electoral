<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use Illuminate\Http\Request;

class municipioController extends Controller
{
    //
    public function obtenerMunicipios($departamentoId)
    {
        $municipios = Municipio::where('departamento_id', $departamentoId)
            ->with('puestos.mesas')
            ->get();

        return response()->json($municipios);
    }

    public function obtenerCorregimientos($municipioId)
    {
        $municipio = Municipio::with('corregimientos')->find($municipioId);
        
        if (!$municipio) {
            return response()->json([], 404);
        }

        return response()->json($municipio->corregimientos);
    }
}
