<?php

namespace App\Http\Controllers;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Corregimiento;
use App\Models\Barrio;
use App\Models\Persona;
use App\Models\Mesa;
use App\Models\Puesto;
use App\Models\Candidato;
use App\Models\Departamento;
use App\Models\Partido;
use App\Models\User;
use App\Models\Municipio;
use App\Models\Testigo;
use App\Models\Genero;
use App\Models\Reportare14;
use App\Models\ReporteVotoCandidato;
use App\Models\ReporteCandidatosVotosCamara;
use App\Models\ReporteVotoPartido;
use App\Exports\VotosExport;
use Maatwebsite\Excel\Facades\Excel;
use Imagick;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use App\Services\GeminiService;
use Exception;

class reporteController extends Controller
{
    
            public function reporteVotos()
        {
            // Traer los municipios para llenar el select
            $municipios = Municipio::orderBy('nombre')->get();
        
            return view('pages.reporte_votos.reporte_votos', compact('municipios'));
        }

        public function apiVotos(Request $request)
        {
            $municipioId = $request->input('municipio_id');
        
            // Consulta base
            $query = DB::table('personas');
        
            if ($municipioId) {
                $query->where('municipio_id', $municipioId);
            }
        
            $votaron = (clone $query)->where('reporte_voto', 1)->count();
            $noVotaron = (clone $query)->where('reporte_voto', 0)->count();
        
            return response()->json([
                'labels' => ['Sí votaron', 'No votaron'],
                'series' => [$votaron, $noVotaron],
            ]);
        }
   
    
    
    public function Candidatos()
    {
        return view('pages.reporte_votos.grafica_candidatos');
    }
    
   public function reporteCandidatos()
{
    $resultados = DB::table('candidatos as c')
        ->leftJoin('reporte_votos_candidatos as rvc', 'c.id', '=', 'rvc.candidato_id')
        ->leftJoin('partido as p', 'c.partido_id', '=', 'p.id')
        ->select(
            'c.id as candidato_id',
            'c.nombre as candidato_nombre',
            'c.color',
            'c.foto',
            DB::raw('COALESCE(p.nombre, "Sin partido") as partido_nombre'),
            DB::raw('SUM(rvc.votos) as total_votos')
        )
        ->groupBy('c.id', 'c.nombre', 'c.color', 'c.foto', 'p.nombre')
        ->orderByDesc(DB::raw('SUM(rvc.votos)'))
        ->get();

    $partidos = [];
    $candidatos = [];
    $fotos = [];

    foreach ($resultados as $row) {
        if (!in_array($row->candidato_nombre, $candidatos)) {
            $candidatos[] = $row->candidato_nombre;
        }

        if (!isset($partidos[$row->partido_nombre])) {
            $partidos[$row->partido_nombre] = [];
        }

        $partidos[$row->partido_nombre][$row->candidato_nombre] = (int) ($row->total_votos ?? 0);

        $fotos[$row->candidato_nombre] = [
            'foto' => $row->foto,
            'color' => $row->color ?? '#999999',
        ];
    }

    // Completar candidatos faltantes en todos los partidos
    foreach ($partidos as &$p) {
        foreach ($candidatos as $c) {
            if (!isset($p[$c])) {
                $p[$c] = 0;
            }
        }
    }

    return response()->json([
        'partidos' => $partidos,
        'candidatos' => $candidatos,
        'fotos' => $fotos
    ]);
}




    
    public function CandidatosCamara()
    {
        return view('pages.reporte_votos.grafica_candidatosCamara');
    }
    
public function reporteCandidatosCamara()
{
    $resultados = DB::table('Votos_candidatosCam as vc')
        ->join('candidatos as c', 'c.id', '=', 'vc.candidato')
        ->join('partido as p', 'p.id', '=', 'c.partido_id')
        ->select(
            'c.nombre as candidato',
            'p.nombre as partido',
            'c.color as color',
            DB::raw('SUM(vc.votos) as total_votos')
        )
        ->groupBy('c.nombre', 'p.nombre', 'c.color')
        ->orderByDesc(DB::raw('SUM(vc.votos)'))
        ->get();

    return response()->json($resultados);
}

public function CandidatosCamaraFiltros()
{
    return view('pages.reporte_votos.grafica_candidatosCamaraFiltros');
}

// Obtener TODOS los departamentos
public function getDepartamentosCamara()
{
    $departamentos = DB::table('departamentos')
        ->select('id', 'nombre')
        ->orderBy('nombre')
        ->get();

    return response()->json($departamentos);
}

// Obtener TODOS los municipios, filtrar por departamento si se selecciona
public function getMunicipiosCamara(Request $request)
{
    $query = DB::table('municipios')
        ->select('id', 'nombre');

    if ($request->filled('departamento')) {
        $query->where('departamento_id', $request->departamento);
    }

    return response()->json($query->orderBy('nombre')->get());
}

// Obtener TODOS los puestos, filtrar por municipio si se selecciona
public function getPuestosCamara(Request $request)
{
    $query = DB::table('puestos')
        ->select('id', 'nombre');

    if ($request->filled('municipio')) {
        $query->where('municipio_id', $request->municipio);
    }

    return response()->json($query->orderBy('nombre')->get());
}

// Obtener mesas SOLO de reportare14, filtradas por departamento, municipio y puesto
public function getMesasCamara(Request $request)
{
    $query = DB::table('reportare14')
        ->select('MESA as id', 'MESA as nombre')
        ->distinct()
        ->whereNotNull('MESA')
        ->where('MESA', '!=', '');

    if ($request->filled('departamento')) {
        $query->where('DEPARTAMENTO', $request->departamento);
    }

    if ($request->filled('municipio')) {
        $query->where('MUNICIPIO', $request->municipio);
    }

    if ($request->filled('puesto')) {
        $query->where('PUESTO', $request->puesto);
    }

    return response()->json($query->orderBy('MESA')->get());
}

// Reporte con filtros
public function reporteCandidatosCamaraFiltros(Request $request)
{
    $query = DB::table('Votos_candidatosCam as vc')
        ->join('reportare14 as r', 'r.ID', '=', 'vc.e14')
        ->join('candidatos as c', 'c.id', '=', 'vc.candidato')
        ->join('partido as p', 'p.id', '=', 'c.partido_id')
        ->select(
            'c.nombre as candidato',
            'p.nombre as partido',
            'c.color as color',
            DB::raw('SUM(vc.votos) as total_votos')
        );

    if ($request->filled('departamento')) {
        $query->where('r.DEPARTAMENTO', $request->departamento);
    }

    if ($request->filled('municipio')) {
        $query->where('r.MUNICIPIO', $request->municipio);
    }

    if ($request->filled('puesto')) {
        $query->where('r.PUESTO', $request->puesto);
    }

    if ($request->filled('mesa')) {
        $query->where('r.MESA', $request->mesa);
    }

    $resultados = $query
        ->groupBy('c.nombre', 'p.nombre', 'c.color')
        ->orderByDesc(DB::raw('SUM(vc.votos)'))
        ->get();

    return response()->json($resultados);
}



     public function Partidos()
    {
        return view('pages.reporte_votos.grafica_partidos');
    }

    public function reportePartidos()
    {
         $partidos = DB::table('reporte_votos_candidatos as rvc')
            ->join('candidatos as c', 'c.id', '=', 'rvc.candidato_id')
            ->join('partido as p', 'c.partido_id', '=', 'p.id')
            ->select(
                'p.id as partido_id',
                'p.nombre as partido_nombre',
                DB::raw('COUNT(DISTINCT c.id) as total_candidatos'),
                DB::raw('SUM(rvc.votos) as total_votos')
            )
            ->groupBy('p.id', 'p.nombre')
            ->orderByDesc('total_votos')
            ->get();

        return response()->json([
            'partidos' => $partidos
        ]);
    }
    
     public function PartidosCamara()
    {
        return view('pages.reporte_votos.grafica_partidosCamara');
    }

    public function reportePartidosCamara()
{
    $partidos = DB::table('reporte_votos_camara as rvc')
        ->join('partido as p', 'rvc.Partido', '=', 'p.id')
        ->select(
            'p.id as partido_id',
            'p.nombre as partido_nombre',
            DB::raw('SUM(rvc.votos) as total_votos')
        )
        ->groupBy('p.id', 'p.nombre')
        ->orderByDesc(DB::raw('SUM(rvc.votos)'))
        ->get();

    return response()->json([
        'partidos' => $partidos
    ]);
}


   
    public function exportAll(Request $request)
    {
        $filters = $request->only(['corregimiento_id', 'barrio_id', 'mesa_id', 'genero_id']);
        return Excel::download(new VotosExport($filters), 'reporte_votacion.xlsx');
    }
    
    public function buscarVotante(Request $request)
{
    $cedula = $request->input('voto');
    $persona = Persona::where('cedula', $cedula)->first();

    if (!$persona) {
        return redirect()->route('votantes.reportarvoto.vista')->with('alerta', [
            'icon' => 'error',
            'title' => 'No encontrado',
            'text' => 'No existe ningún votante con la cédula ingresada.',
            'confirmButtonText' => 'Intentar de nuevo'
        ]);
    }

    if ($persona->reporte_voto == 1) {
        return redirect()->route('votantes.reportarvoto.vista')->with('alerta', [
            'icon' => 'warning',
            'title' => 'Voto ya registrado',
            'text' => 'El votante ' . $persona->nombre . ' ya tiene registrado su voto.',
            'confirmButtonText' => 'Aceptar'
        ]);
    }

    // Si existe y no ha votado → mandar datos para confirmar
    return redirect()->route('votantes.reportarvoto.vista')->with([
        'confirmacion' => true,
        'cedula' => $persona->cedula,
        'nombre' => $persona->nombre
    ]);
}

public function confirmarVoto(Request $request)
{
    $cedula = $request->input('cedula');
    $persona = Persona::where('cedula', $cedula)->first();

    if ($persona && $persona->reporte_voto == 0) {
        $persona->reporte_voto = 1;
        $persona->save();

        return redirect()->route('votantes.reportarvoto.vista')->with('alerta', [
            'icon' => 'success',
            'title' => 'Voto reportado',
            'text' => 'El voto de ' . $persona->nombre . ' ha sido registrado correctamente.',
            'confirmButtonText' => 'Aceptar'
        ]);
    }

    return redirect()->route('votantes.reportarvoto.vista')->with('alerta', [
        'icon' => 'error',
        'title' => 'Error',
        'text' => 'No se pudo registrar el voto. Inténtalo nuevamente.',
        'confirmButtonText' => 'Aceptar'
    ]);
}








    public function AK()
    {
        return view('pages.reporte_votos.AK');
    }

    
    public function process(Request $request)
    {
        $request->validate([
            'documento_e14' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'prompt' => 'required|string'
        ]);

        try {
            $file = $request->file('documento_e14');
            $gemini = new GeminiService();
            $result = $gemini->analizarArchivo($file, $request->prompt);

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generate(Request $request, GeminiService $gemini)
    {
        $prompt = $request->input('prompt', 'Dime un chiste sobre programadores');
        $text = $gemini->generateText($prompt);

        return response()->json(['result' => $text]);
    }
    
    
    
   public function crearReporteE14()
{
    $departamentos = Departamento::orderBy('nombre')->get();
    $municipios = Municipio::orderBy('nombre')->get();
    $puestos = Puesto::orderBy('nombre')->get();
    $mesas = Mesa::orderBy('descripcion')->get();
    $candidatos = Candidato::orderBy('nombre', 'asc')
                           ->orderBy('apellido', 'asc')
                           ->get();

    return view('pages.testigos.reportare14', compact(
        'departamentos',
        'municipios',
        'puestos',
        'mesas',
        'candidatos'
    ));
}

    /**
     * Procesa el archivo con la IA (Gemini)
     */



    public function procesarIA(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
        ]);

        try {
            $file = $request->file('file');

            // 🔥 PROMPT Estandarizado para resultados consistentes
            $prompt = <<<PROMPT
Analiza el documento E-14 (Acta de Escrutinio de los Jurados de Votación)
y devuelve **únicamente un objeto JSON válido** con esta estructura exacta.

No escribas texto fuera del JSON, ni comentarios. 
Respeta los nombres de los campos **tal como aparecen aquí**. 
Si un valor no está visible, colócalo como null.

{
  "documento": {
    "tipo": "string",
    "formulario": "string",
    "codigo_barras": "string",
    "version": "string",
    "paginacion": "string",
    "entidad_emisora": "string"
  },
  "eleccion": {
    "tipo": "string",
    "fecha": "YYYY-MM-DD",
    "cargo": "string"
  },
  "ubicacion": {
    "departamento": "string",
    "municipio": "string",
    "zona": "string",
    "puesto": "string",
    "mesa": "string",
    "lugar_votacion": "string"
  },
  "resumen_votacion": {
    "total_votantes_formulario_E11": "number",
    "total_votos_alcalde_en_la_urna": "number"
  },
  "resultados_votacion": {
    "candidatos": [
      {
        "numero": "number",
        "nombre": "string",
        "agrupacion_politica": "string",
        "votos": "number"
      }
    ],
    "votos_en_blanco": "number",
    "votos_nulos": "number",
    "votos_no_marcados": "number",
    "suma_total_votos": "number"
  },
  "constancias_jurados": {
    "hubo_recuento_de_votos": "boolean",
    "jurados": [
      { "posicion": "number", "nombre": "string", "cc": "string" }
    ]
  }
}

Asegúrate de:
- No cambiar los nombres de los campos.
- No incluir texto fuera del JSON.
- Si no puedes leer un valor, devuelve `null`.
PROMPT;

            // 🔹 Instancia del servicio Gemini
            $gemini = new GeminiService();

            // 🔹 Procesa el archivo con el prompt estandarizado
            $resultado = $gemini->analizarArchivo($file, $prompt);

            // 🔹 Devuelve el resultado en JSON al frontend
            return response()->json($resultado);

        } catch (Exception $e) {
            return response()->json([
                'error' => '❌ Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Guarda el reporte E14 con la información extraída y/o editada.
     */
public function guardarReporteE14(Request $request)
{
    $usuarioId = auth()->user()->id;
    $testigo = User::find($usuarioId);

    if (!$testigo) {
        return response()->json([
            'success' => false,
            'message' => 'No se encontró un testigo asociado a tu usuario.'
        ], 400);
    }

    // Validación
    try {
        $validated = $request->validate([
            'departamento_id' => 'required|exists:departamentos,id',
            'municipio_id' => 'required|exists:municipios,id',
            'puesto_id' => 'required|exists:puestos,id',
            'mesa' => 'nullable|string|max:50',
            'votos_blanco' => 'nullable|integer|min:0',
            'votos_nulos' => 'nullable|integer|min:0',
            'votos_no_marcados' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'candidatos' => 'nullable|array',
            'candidatos.*' => 'nullable|integer|min:0',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        // -----------------------------
        // Guardar archivo en /public/e14/
        // -----------------------------
        $archivoPath = null;
        $rutaPublica = public_path('e14'); // carpeta pública

        if (!file_exists($rutaPublica)) {
            mkdir($rutaPublica, 0775, true); // crear carpeta si no existe
        }

        if ($request->hasFile('file')) {
            $archivo = $request->file('file');
            $nombreArchivo = uniqid() . '.' . $archivo->getClientOriginalExtension();
            $archivo->move($rutaPublica, $nombreArchivo);
            $archivoPath = 'e14/' . $nombreArchivo; // ruta relativa para DB y frontend
        }

        // -----------------------------
        // Procesar JSON de la IA
        // -----------------------------
        $jsonIA = json_decode($request->json_ia, true);

        $votosBlanco = (int)($request->votos_blanco ?? 0);
        $votosNulos = (int)($request->votos_nulos ?? 0);
        $votosNoMarcados = (int)($request->votos_no_marcados ?? 0);

        $sumaVotosCandidatos = $request->candidatos ? collect($request->candidatos)->sum() : 0;
        $sumaVotosE14 = $sumaVotosCandidatos + $votosBlanco + $votosNulos + $votosNoMarcados;

        // -----------------------------
        // Crear reporte E14
        // -----------------------------
        $e14 = Reportare14::create([
            'E14_ID' => 'E14-' . uniqid(),
            'DEPARTAMENTO' => $request->departamento_id,
            'MUNICIPIO' => $request->municipio_id,
            'PUESTO' => $request->puesto_id,
            'MESA' => $request->mesa ?? 'Sin mesa',
            'TOTAL_VOTANTES-E11' => (int)($jsonIA['resumen_votacion']['total_votantes_formulario_E11'] ?? 0),
            'TOTAL_VOTOS-URNA' => (int)($jsonIA['resumen_votacion']['total_votos_alcalde_en_la_urna'] ?? 0),
            'VOTOS_BLANCO' => $votosBlanco,
            'VOTOS_NULOS' => $votosNulos,
            'SUMA_VOTOS-E14' => $sumaVotosE14,
            'OBSERVACION' => $request->observaciones ?? '',
            'ARCHIVO' => $archivoPath,
            'TESTIGO' => $testigo->id,
        ]);

        // -----------------------------
        // Guardar votos de candidatos
        // -----------------------------
        if ($request->candidatos && is_array($request->candidatos)) {
            foreach ($request->candidatos as $candidatoId => $votos) {
                ReporteVotoCandidato::create([
                    'testigo_id' => $testigo->id,
                    'candidato_id' => $candidatoId,
                    'votos' => (int)$votos,
                    'e14' => $e14->ID,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'El reporte E14 se guardó correctamente.',
            'e14_id' => $e14->ID,
            'archivo_url' => url($archivoPath) // URL pública para descargar
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar el reporte: ' . $e->getMessage()
        ], 500);
    }
}







public function index()
    {
        $departamentos = DB::table('departamentos')->get();
        $municipios = DB::table('municipios')->get();
        $puestos = DB::table('puestos')->get();

        return view('pages.testigos.e14s', compact('departamentos', 'municipios', 'puestos'));
    }

    public function listar()
    {
        try {
            $e14s = DB::table('reportare14')->orderBy('ID', 'desc')->get();
            return response()->json(['data' => $e14s]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

    public function ver($id)
    {
        try {
            $e14 = DB::table('reportare14')->where('ID', $id)->first();
            
            if (!$e14) {
                return response()->json(['error' => 'No encontrado'], 404);
            }

            return response()->json(['data' => $e14]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

public function actualizar(Request $request, $id)
{
    try {
        $affected = DB::update("
            UPDATE reportare14 SET
                MESA = ?,
                `TOTAL_VOTANTES-E11` = ?,
                `TOTAL_VOTOS-URNA` = ?,
                `SUMA_VOTOS-E14` = ?,
                VOTOS_BLANCO = ?,
                VOTOS_NULOS = ?,
                OBSERVACION = ?
            WHERE ID = ?
        ", [
            $request->input('MESA'),
            (int) $request->input('TOTAL_VOTANTES-E11'),
            (int) $request->input('TOTAL_VOTOS-URNA'),
            (int) $request->input('SUMA_VOTOS-E14'),
            (int) $request->input('VOTOS_BLANCO', 0),
            (int) $request->input('VOTOS_NULOS', 0),
            $request->input('OBSERVACION'),
            (int) $id
        ]);

        return response()->json(['message' => 'Actualizado correctamente', 'affected' => $affected]);
        
    } catch (\Exception $e) {
        \Log::error('Error en actualizar: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function eliminar($id)
    {
        try {
            DB::table('reportare14')->where('ID', $id)->delete();
            return response()->json(['message' => 'Eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar'], 500);
        }
    }




















  
public function crearReporteE14Camara()
{
    $departamentos = Departamento::orderBy('nombre')->get();
    $municipios = Municipio::orderBy('nombre')->get();
    $puestos = Puesto::orderBy('nombre')->get();
    $mesas = Mesa::orderBy('descripcion')->get();
    $candidatos = Candidato::orderBy('nombre', 'asc')
                          ->get();
    $Partido = Partido::orderBy('nombre', 'asc')->get();

    // <CHANGE> Pasar candidatos a la vista
    return view('pages.testigos.reportare14Camara', compact(
        'departamentos',
        'municipios',
        'puestos',
        'mesas',
        'candidatos',  // Corregido: era 'candidato', ahora es 'candidatos'
        'Partido'
    ));
}

public function procesarIACamara(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
    ]);

    try {
        $file = $request->file('file');

        $prompt = <<<PROMPT
Analiza el formulario E-14 proporcionado y genera UNICAMENTE un JSON valido.

CONTRATO CANONICO (OBLIGATORIO):
- La salida DEBE seguir EXACTAMENTE la estructura definida abajo
- NO se permite agregar, quitar, renombrar ni reordenar campos
- TODOS los campos deben existir SIEMPRE
- Ningun campo puede ser null
- Si un dato no existe, usar "" , 0 o arreglos vacios
- NO incluir texto explicativo ni comentarios
- NO corregir ni ajustar valores del formulario
- Todos los calculos deben hacerse nuevamente y reportarse

REGLAS DE CALCULO:
- votos_calculados = votos_solo_agrupacion + suma(votos de candidatos)
- diferencia = votos_calculados - votos_reportados
- Si diferencia != 0, generar advertencia obligatoria
- Detectar concentracion de votos si un candidato supera 80% del total del partido
- Validar suma de partidos contra total urna E-14 y E-11

OBTENER IDENTIFICACION DEL FORMULARIO:
- Es obligatorio obtener la identificacion del formulario E-14.
- La identificación es un número de serie único impreso bajo su código de barras en la parte superior.
- Estrictamente solo debes usar ese número como valor del campo "identificacion". No incluyas texto adicional ni etiquetas.

USA LA SIGUIENTE PLANTILLA BASE.
SOLO REEMPLAZA LOS VALORES.
NO CAMBIES LLAVES NI ORDEN.

{
"identificacion":"",
  "ubicacion": {
    "departamento": "",
    "municipio": "",
    "lugar_votacion": "",
    "mesa": ""
  },
  "resultados_votacion": {
    "Partido": [
      {
        "nombre": "",
        "votos_reportados": 0,
        "votos_calculados": 0,
        "diferencia": 0,
        "detalle_preferente": {
          "votos_solo_agrupacion": 0,
          "candidatos": [
            {
              "numero": "",
              "votos": 0
            }
          ]
        },
        "score_confiabilidad": {
          "valor": 0,
          "nivel": ""
        }
      }
    ],
    "votos_en_blanco": 0,
    "votos_nulos": 0,
    "votos_no_marcados": 0,
    "suma_total_votos": 0,
    "advertencias_validacion": [
      {
        "tipo": "",
        "partido": "",
        "candidato": "",
        "votos_reportados": 0,
        "votos_calculados": 0,
        "diferencia": 0
      }
    ],
    "validacion_global": {
      "suma_votos_partidos": 0,
      "total_urna_E14": 0,
      "total_votantes_E11": 0,
      "diferencia_detectada": 0
    }
  },
  "resumen_votacion": {
    "total_votos_urna": 0,
    "total_votantes_E11": 0
  }
}

SALIDA:
- JSON puro
- Sin texto adicional

PROMPT;

        $gemini = new GeminiService();
        $resultado = $gemini->analizarArchivo($file, $prompt);

        return response()->json($resultado);

    } catch (Exception $e) {
        return response()->json([
            'error' => 'Error al procesar el archivo: ' . $e->getMessage()
        ], 500);
    }
}

public function guardarReporteE14Camara(Request $request)
{
    $usuarioId = auth()->user()->id;
    $testigo = User::find($usuarioId);

    if (!$testigo) {
        return response()->json([
            'success' => false,
            'message' => 'No se encontró un testigo asociado a tu usuario.'
        ], 400);
    }

    // Validación
    try {
        $validated = $request->validate([
            'departamento_id' => 'required|exists:departamentos,id',
            'municipio_id' => 'required|exists:municipios,id',
            'puesto_id' => 'required|exists:puestos,id',
            'mesa' => 'nullable|string|max:50',
            'votos_blanco' => 'nullable|integer|min:0',
            'votos_nulos' => 'nullable|integer|min:0',
            'votos_no_marcados' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'Partido' => 'nullable|array',
            'Partido.*' => 'nullable|integer|min:0',
            // <CHANGE> Validación para votos de candidatos individuales
            'Candidatos' => 'nullable|array',
            'Candidatos.*' => 'nullable|integer|min:0',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();        
        // -----------------------------
        // Procesar JSON de la IA
        // -----------------------------
        $jsonIA = json_decode($request->json_ia, true);

        if(Reportare14::where('Formulario_Identificacion', $jsonIA['identificacion'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ya se ha subido este formulario E-14 previamente.'
            ], 400);
        }

        // -----------------------------
        // Guardar archivo en /public/e14/
        // -----------------------------
        $archivoPath = null;
        $rutaPublica = public_path('e14');

        if (!file_exists($rutaPublica)) {
            mkdir($rutaPublica, 0775, true);
        }

        if ($request->hasFile('file')) {
            $archivo = $request->file('file');
            $nombreArchivo = uniqid() . '.' . $archivo->getClientOriginalExtension();
            $archivo->move($rutaPublica, $nombreArchivo);
            $archivoPath = 'e14/' . $nombreArchivo;
        }

        $votosBlanco = (int)($request->votos_blanco ?? 0);
        $votosNulos = (int)($request->votos_nulos ?? 0);
        $votosNoMarcados = (int)($request->votos_no_marcados ?? 0);

        // <CHANGE> Calcular suma incluyendo votos de candidatos individuales
        $sumaVotosPartidos = $request->Partido ? collect($request->Partido)->sum() : 0;
        $sumaVotosCandidatos = $request->Candidatos ? collect($request->Candidatos)->sum() : 0;
        $sumaVotosE14 = $sumaVotosPartidos + $votosBlanco + $votosNulos + $votosNoMarcados;

        // -----------------------------
        // Crear reporte E14
        // -----------------------------
        $e14 = Reportare14::create([
            'E14_ID' => 'E14-' . uniqid(),
            "Formulario_Identificacion" => $jsonIA['identificacion'] ?? 'Sin identificación',
            'DEPARTAMENTO' => $request->departamento_id,
            'MUNICIPIO' => $request->municipio_id,
            'PUESTO' => $request->puesto_id,
            'MESA' => $request->mesa ?? 'Sin mesa',
            'TOTAL_VOTANTES-E11' => (int)($jsonIA['resumen_votacion']['total_votantes_E11'] ?? 0),
            'TOTAL_VOTOS-URNA' => (int)($jsonIA['resumen_votacion']['total_votos_urna'] ?? 0),
            'VOTOS_BLANCO' => $votosBlanco,
            'VOTOS_NULOS' => $votosNulos,
            'SUMA_VOTOS-E14' => $sumaVotosE14,
            'OBSERVACION' => $request->observaciones ?? '',
            'ARCHIVO' => $archivoPath,
            'TESTIGO' => $testigo->id,
        ]);

        // -----------------------------
        // Guardar votos por partido
        // -----------------------------
        if ($request->Partido && is_array($request->Partido)) {
            foreach ($request->Partido as $partidoId => $votos) {
                ReporteVotoPartido::create([
                    'Partido' => $partidoId,
                    'votos' => (int) $votos,
                    'e14' => $e14->ID,
                ]);
            }
        }

        // <CHANGE> -----------------------------
        // Guardar votos de candidatos individuales
        // en la tabla Votos_candidatosCam
        // -----------------------------
        if ($request->Candidatos && is_array($request->Candidatos)) {
            foreach ($request->Candidatos as $candidatoId => $votos) {
                $votosInt = (int) $votos;
                // Solo guardar si hay votos (mayor a 0)
                if ($votosInt > 0) {
                    ReporteCandidatosVotosCamara::create([
                        'candidato' => $candidatoId,
                        'e14' => $e14->ID,
                        'votos' => $votosInt,
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'El reporte E14 se guardó correctamente.',
            'e14_id' => $e14->ID,
            'archivo_url' => url($archivoPath)
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar el reporte: ' . $e->getMessage()
        ], 500);
    }
}



}


