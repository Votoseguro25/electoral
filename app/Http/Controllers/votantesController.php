<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Lider;
use App\Models\Votante;
use App\Models\Compromiso;
use App\Models\Genero;
use App\Models\Puesto;
use Illuminate\Http\Request;
use App\Models\Persona;

class votantesController extends Controller
{
    public function listado(Request $req)
    {
        $generos = Genero::all();
        $departamentos = Departamento::all();
        $compromisos = Compromiso::all();
        $puestos = Puesto::all();
        $lideres = Lider::all();

        if ($req->query('consulta') || $req->query('departamento') || $req->query('municipio') || $req->query('lider')) {
            return $this->buscador(
                $req,
                $generos,
                $departamentos,
                $compromisos,
                $puestos,
                $lideres
            );
        }

        $votantes = Votante::orderBy('id', 'desc')->paginate(10);

        return view('pages.votantes.listado', [
            "departamentos" => $departamentos,
            "compromisos" => $compromisos,
            "puestos" => $puestos,
            "votantes" => $votantes,
            "generos" => $generos,
            "lideres" => $lideres
        ]);
    }

    public function guardar(Request $req)
    {
        try {
            $personas = Persona::create([
                "nombre" => strtoupper($req->input('nombre')),
                "cedula" => $req->input('cedula'),
                "telefono" => $req->input('telefono'),
                "profesion" => $req->input('profesion') ? strtoupper($req->input('profesion')) : null,
                "municipio_id" => $req->input('municipio'),
                "mesa_id" => $req->input('mesa'),
                "genero_id" => $req->input('genero'),
                "reporte_voto" => '0'
            ]);

            $personas->votante()->create([
                "compromiso_id" => $req->input('compromiso'),
                "lider_id" => $req->input('lider'),
            ]);

            if (!$req->input('lider')) {
                Lider::create([
                    "persona_id" => $personas->id,
                ]);
            }

            return redirect()->route('votantes.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha guardado correctamente",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('votantes.listado')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => $th->getMessage(),
                "confirmButtonText" => "aceptar"
            ]);
        }
    }

    public function borrar(int $id)
    {
        try {
            Persona::where('id', $id)->first()->delete();

            return redirect()->route('votantes.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha eliminado correctamente",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('votantes.listado')->with('alerta', [
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
            Persona::where('id', $id)->first()->update([
                "cedula" => $req->input('cedula'),
                "nombre" => $req->input('nombre'),
                "telefono" => $req->input('telefono'),
                "profesion" => $req->input('profesion') ? strtoupper($req->input('profesion')) : null,
                "municipio_id" => $req->input('municipio'),
                "mesa_id" => $req->input('mesa'),
                "genero_id" => $req->input('genero'),
            ]);

            Votante::where('persona_id', $id)->first()->update([
                "compromiso_id" => $req->input("compromiso"),
                "lider_id" => $req->input("lider"),
            ]);

            if ($req->input('lider')) {
                Lider::where('persona_id', $id)->delete();
            }

            if (!$req->input('lider') && !Lider::where('persona_id', $id)->exists()) {
                Lider::create([
                    "persona_id" => $id,
                ]);
            }

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Se ha modificado correctamente al votante " . $req->input('nombre'),
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('votantes.listado')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => $th->getMessage(),
                "confirmButtonText" => "aceptar"
            ]);
        }
    }

    public function buscador(Request $req, $generos, $departamentos, $compromisos, $puestos, $lideres)
    {
        $query = Votante::leftJoin('personas', 'votantes.persona_id', '=', 'personas.id')
            ->leftJoin('municipios', 'personas.municipio_id', '=', 'municipios.id')
            ->select('votantes.*', 'personas.*', 'votantes.id as id');

        // Filtro por texto (cédula o nombre)
        if ($req->query('consulta')) {
            $query->where('personas.cedula', 'like', '%' . $req->query('consulta') . '%')
                ->orWhere('personas.nombre', 'like', '%' . $req->query('consulta') . '%');
        }

        // Filtro por departamento
        if ($req->query('departamento')) {
            $query->where('municipios.departamento_id', $req->query('departamento'));
        }

        // Filtro por municipio
        if ($req->query('municipio')) {
            $query->where('personas.municipio_id', $req->query('municipio'));
        }

        // Filtro por lider
        if ($req->query('lider')) {
            $query->where('votantes.lider_id', $req->query('lider'));
        }

        $busqueda = $query->orderBy('votantes.id', 'desc')->paginate(10);

        return view('pages.votantes.listado', [
            "departamentos" => $departamentos,
            "compromisos" => $compromisos,
            "puestos" => $puestos,
            "votantes" => $busqueda,
            "generos" => $generos,
            "lideres" => $lideres,
        ]);
    }

    public function reportarVotoVista()
    {
        return view('pages.votantes.reportarvoto');
    }
}