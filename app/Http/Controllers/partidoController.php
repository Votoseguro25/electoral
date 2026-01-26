<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use Illuminate\Http\Request;

class partidoController extends Controller
{
    public function listado(Request $req)
    {
        $buscar = $req->input('buscar');
        
        $partidos = Partido::when($buscar, function($query, $buscar) {
            return $query->where('nombre', 'like', '%' . $buscar . '%');
        })
        ->orderBy('id', 'desc')
        ->paginate(10)
        ->appends(['buscar' => $buscar]);
        
        return view('pages.partidos.listado', compact('partidos', 'buscar'));
    }

    public function guardar(Request $req)
    {
        try {
            Partido::create([
                'nombre' => strtoupper($req->input('nombre')),
                'estado' => $req->input('estado') ? true : false,
            ]);

            return redirect()->route('partidos.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Partido creado",
                "text" => "El partido ha sido creado exitosamente",
                "confirmButtonText" => "Aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => $th->getMessage(),
                "confirmButtonText" => "Aceptar"
            ])->withInput();
        }
    }

    public function modificar(Request $req, $id)
    {
        try {
            $partido = Partido::find($id);

            if (!$partido) {
                return back()->with('alerta', [
                    "icon" => "error",
                    "title" => "Partido no encontrado",
                    "text" => "El partido que intentas editar no existe",
                    "confirmButtonText" => "Aceptar"
                ]);
            }

            $partido->nombre = strtoupper($req->input('nombre'));
            $partido->estado = $req->input('estado') ? true : false;
            $partido->save();

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Partido modificado",
                "text" => "El partido ha sido modificado exitosamente",
                "confirmButtonText" => "Aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => $th->getMessage(),
                "confirmButtonText" => "Aceptar"
            ])->withInput();
        }
    }

    public function borrar($id)
    {
        $partido = Partido::find($id);

        if (!$partido) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Partido no encontrado",
                "text" => "El partido que intentas eliminar no existe",
                "confirmButtonText" => "Aceptar"
            ]);
        }

        // Verificar si el partido tiene candidatos asignados
        if ($partido->candidatos()->count() > 0) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "No se puede eliminar",
                "text" => "Este partido tiene candidatos asignados. Reasigna los candidatos antes de eliminar.",
                "confirmButtonText" => "Aceptar"
            ]);
        }

        try {
            $partido->delete();

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Partido eliminado",
                "text" => "El partido ha sido eliminado exitosamente",
                "confirmButtonText" => "Aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente",
                "confirmButtonText" => "Aceptar"
            ]);
        }
    }
}
