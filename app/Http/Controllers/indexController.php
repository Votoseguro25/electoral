<?php

namespace App\Http\Controllers;

use App\Models\Votante;
use App\Models\Persona;
use App\Models\datos;
use Illuminate\Http\Request;

class indexController extends Controller
{
    //
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return view('pages.mapa.vistaLogo');
        }

        $totalRegistrados = Persona::count();
        $totalHombres = Persona::where('genero_id', '1')->count();
        $totalMujeres = Persona::where('genero_id', '2')->count();

        $totalVotantes = datos::where('departamento', 'CHOCO')->sum('total');

        //$TotalReal=DATOS::where('departamento','CHOCO')->sum('total');

        $totalVotaron = Persona::where('reporte_voto', true)->count();

        $abstencion = $totalVotantes > 0
            ? round(($totalVotantes - $totalVotantes * 0.47), 1)
            : 0;

        $minimoParaGanar = $totalVotantes > 0
            ? floor($abstencion * 0.1983)
            : 0;

        return view('pages.mapa.resultado', compact(
            'totalRegistrados',
            'totalHombres',
            'totalMujeres',
            'abstencion',
            'minimoParaGanar',
            'totalVotantes'
        ));
    }
}
