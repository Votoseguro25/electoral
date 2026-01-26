<?php

namespace App\Http\Controllers;

use App\Models\Campana;
use App\Models\Candidato;
use App\Models\Departamento;
use App\Models\Partido;
use App\Models\TipoEleccion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;

class candidatosController extends Controller
{
    public function listado()
    {
        $partidos = Partido::all();
        $tiposEleccion = TipoEleccion::all();
        $candidatos = Candidato::with(['partido', 'campanas.tipoEleccion'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $departamentos = Departamento::all();

        return view('pages.candidatos.listado', [
            'partidos' => $partidos,
            'tiposEleccion' => $tiposEleccion,
            'candidatos' => $candidatos,
            'departamentos' => $departamentos
        ]);
    }

    public function guardar(Request $req)
    {        
        try {
            $data = [
                'nombre' => strtoupper($req->input('nombre')),
                'Tarjeton' => $req->input('Tarjeton'),
                'color' => $req->input('color'),
                'partido_id' => $req->input('partido') ?: null,
            ];

            // Manejo de la foto
            if ($req->hasFile('foto')) {
                $archivo = $req->file('foto');
                $extension = $archivo->getClientOriginalExtension();
                $uuid = Str::uuid();
                $nombreArchivo = "{$uuid}.{$extension}";

                // Guardar archivo
                Storage::disk('custom_disk')->put($nombreArchivo, $archivo->get());
                $data['foto'] = $nombreArchivo;
            }

            $candidato = Candidato::create($data);
            Campana::create([
                'tipo_eleccion_id' => $req->input('tipo_eleccion'),
                'departamento_id' => $req->input('departamento'),
                'municipio_id' => $req->input('municipio') ?: null,
                'candidato_id' => $candidato->id,
            ]);

            return redirect()->route('candidatos.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha guardado correctamente",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {            
            return redirect()->route('candidatos.listado')->with('alerta', [
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
            $candidato = Candidato::where('id', $id)->first();

            if ($candidato && $candidato->foto) {
                // Eliminar foto si existe
                if (Storage::disk('custom_disk')->exists($candidato->foto)) {
                    Storage::disk('custom_disk')->delete($candidato->foto);
                }
            }

            $candidato->delete();

            return redirect()->route('candidatos.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha eliminado correctamente",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('candidatos.listado')->with('alerta', [
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
            $candidato = Candidato::where('id', $id)->with('campanas')->first();
            $campana = Campana::where('candidato_id', $id)->first();
            
            $data = [
                'nombre' => strtoupper($req->input('nombre')),
                'Tarjeton' => $req->input('Tarjeton'),
                'color' => $req->input('color'),
                'partido_id' => $req->input('partido') ?: null,                
            ];

            // Manejo de la foto
            if ($req->hasFile('foto')) {
                // Eliminar foto anterior si existe
                if ($candidato->foto && Storage::disk('custom_disk')->exists($candidato->foto)) {
                    Storage::disk('custom_disk')->delete($candidato->foto);
                }

                $archivo = $req->file('foto');
                $extension = $archivo->getClientOriginalExtension();
                $uuid = Str::uuid();
                $nombreArchivo = "{$uuid}.{$extension}";

                // Guardar nuevo archivo
                Storage::disk('custom_disk')->put($nombreArchivo, $archivo->get());
                $data['foto'] = $nombreArchivo;
            }

            $candidato->update($data);
            $campana->update([
                'tipo_eleccion_id' => $req->input('tipo_eleccion'),
                'departamento_id' => $req->input('departamento'),
                'municipio_id' => $req->input('municipio') ?: null,
            ]);

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Se ha modificado correctamente al candidato " . $req->input('nombre'),
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('candidatos.listado')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente.",
                "confirmButtonText" => "aceptar"
            ]);
        }
    }
}
