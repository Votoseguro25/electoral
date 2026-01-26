<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Lider;
use App\Models\Votante;
use App\Exports\VotantesLiderExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Symfony\Component\Panther\Client;
use App\Models\Compromiso;
use App\Models\Genero;
use App\Models\Municipio;
use App\Models\Puesto;
use Illuminate\Http\Request;
use App\Models\Persona;

class LiderController extends Controller
{
    public function listado(Request $req)
    {
        $lideres = Lider::orderBy('id', 'desc')->paginate(10);

        return view('pages.lider.lider', [            
            "lideres" => $lideres,
        ]);
    }

    /**
     * Crea un nuevo líder asociado a una persona existente.
     * @param int $id ID de la persona a asociar como líder.
     * @return Lider El líder creado.
     */
    public function guardar(int $id)
    {
        return Lider::create([
            "persona_id" => $id
        ]);
    }

    /**
     * Busca un líder por el ID de la persona asociada.
     * @param int $id ID de la persona.
     * @return Lider|null El líder encontrado o null si no existe.
     */
    public function buscarLider(int $id)
    {
        return Lider::where("persona_id", $id)->first();
    }

    /**
     * Descarga un Excel con los votantes de un líder específico.
     * @param int $id ID del líder.
     */
    public function descargarVotantes($id)
    {
        $lider = Lider::find($id);

        if (!$lider) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Líder no encontrado",
                "text" => "El líder que intentas consultar no existe",
                "confirmButtonText" => "Aceptar"
            ]);
        }

        $liderNombre = $lider->nombre;
        $cantidadVotantes = Votante::where('lider_id', $id)->count();

        if ($cantidadVotantes === 0) {
            return back()->with('alerta', [
                "icon" => "info",
                "title" => "Sin votantes",
                "text" => "Este líder no tiene votantes asignados",
                "confirmButtonText" => "Aceptar"
            ]);
        }

        $nombreArchivo = 'Votantes_' . str_replace(' ', '_', $liderNombre) . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new VotantesLiderExport($id, $liderNombre), $nombreArchivo);
    }
}