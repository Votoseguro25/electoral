<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Departamento;
use App\Models\ReporteVotoCandidato;
use App\Models\Testigo;
use Auth;
use DB;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Storage;

class testigoController extends Controller
{
    public function reportarVista()
    {
        $departamentos = Departamento::all();
        $candidatos = Candidato::all();

        return view('pages.testigos.reportare14', [
            'departamentos' => $departamentos,
            'candidatos' => $candidatos
        ]);
    }

    public function reportadosVista()
    {
        $testigos = Testigo::with('candidatos')->find(1);

        return view('pages.testigos.reportados', [
            'testigos' => $testigos
        ]);
    }

    public function guardarReporte(Request $req)
    {
        DB::beginTransaction();
        $nombreArchivo = null;

        try {
            $testigo = Testigo::create([
                'mesa_id' => $req->input('mesa'),
                'cantidad_votos' => array_sum($req->input('candidatos')),
                'observaciones' => $req->input('observaciones'),
                'archivo' => $nombreArchivo = $this->guardarArchivo($req->file('documento_e14')),
                'usuario' => Auth::user()->name,
            ]);

            $keysCandidatos = array_keys($req->input('candidatos'));

            $votosData = [];
            foreach ($keysCandidatos as $candidatoId) {
                $votosData[$candidatoId] = ['votos' => $req->input("candidatos.{$candidatoId}")];
            }

            $testigo->candidatos()->sync($votosData);

            DB::commit();

            return redirect()->route('testigos.reportare14.vista')->with('alerta', [
                "icon" => "success",
                "title" => "Se ha registrado correctamente el reporte E14",
                "text" => "",
                "confirmButtonText" => "cerrar"
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->eliminarArchivo($nombreArchivo);

            return redirect()->route('testigos.reportare14.vista')->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente.",
                "confirmButtonText" => "aceptar"
            ]);
        }
    }

    private function guardarArchivo($file)
    {
        $extension = $file->getClientOriginalExtension();
        $uuidArchivo = Uuid::uuid4();
        $nombreArchivo = "{$uuidArchivo}.{$extension}";

        Storage::disk('reportes_e14_disk')->put($nombreArchivo, $file->get());

        return $nombreArchivo;
    }

    private function eliminarArchivo($nombreArchivo)
    {
        Storage::disk('reportes_e14_disk')->delete($nombreArchivo);
    }
}
