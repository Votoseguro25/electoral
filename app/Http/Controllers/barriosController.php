<?php

namespace App\Http\Controllers;

use App\Models\Barrio;
use App\Models\Corregimiento;
use App\Models\Departamento;
use Illuminate\Http\Request;

class barriosController extends Controller
{
    public function listado()
    {
        $departamentos = Departamento::all();
        $barrios = Barrio::with(['corregimiento.municipio.departamento'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pages.barrios.listado', [
            'departamentos' => $departamentos,
            'barrios' => $barrios
        ]);
    }

    public function guardar(Request $req)
    {
        try {
            Barrio::create([
                'nombre' => strtoupper($req->input('nombre')),
                'corregimiento_id' => $req->input('corregimiento'),
            ]);

            return redirect()->route('barrios.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha guardado correctamente",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {            
            return redirect()->route('barrios.listado')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente.",
                "confirmButtonText" => "aceptar"
            ]);
        }
    }

    public function borrar(int $id)
    {
        try {
            $barrio = Barrio::where('id', $id)->first();
            $barrio->delete();

            return redirect()->route('barrios.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha eliminado correctamente",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('barrios.listado')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente.",
                "confirmButtonText" => "aceptar"
            ]);
        }
    }

    public function modificar(int $id, Request $req)
    {
        try {
            $barrio = Barrio::where('id', $id)->first();

            $barrio->update([
                'nombre' => strtoupper($req->input('nombre')),
                'corregimiento_id' => $req->input('corregimiento'),
            ]);

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Se ha modificado correctamente el barrio " . $req->input('nombre'),
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('barrios.listado')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente.",
                "confirmButtonText" => "aceptar"
            ]);
        }
    }
}
