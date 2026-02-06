<?php

namespace App\Http\Controllers;

use App\Models\PlantillaMensaje;
use App\Models\CampanaComunicacion;
use App\Models\MensajeEnviado;
use App\Models\ConfiguracionWhatsapp;
use App\Models\RespuestaWhatsapp;
use App\Models\Persona;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Services\WhatsAppService;
use App\Jobs\EnviarCampanaMasiva;
use App\Jobs\ProcesarCampanaProgramada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComunicacionController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Vista principal del modulo de Comunicacion
     */
    public function index()
    {
        $estadisticas = $this->getEstadisticasGenerales();
        $campanasRecientes = CampanaComunicacion::with('plantilla')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $plantillasActivas = PlantillaMensaje::activas()->count();
        $whatsappStats = $this->whatsappService->getEstadisticas();

        return view('pages.comunicacion.index', compact(
            'estadisticas',
            'campanasRecientes',
            'plantillasActivas',
            'whatsappStats'
        ));
    }

    /**
     * Vista de plantillas
     */
    public function plantillas()
    {
        $plantillas = PlantillaMensaje::orderBy('created_at', 'desc')->paginate(10);
        return view('pages.comunicacion.plantillas', compact('plantillas'));
    }

    /**
     * Guardar plantilla (crear nueva)
     */
    public function guardarPlantilla(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:whatsapp,sms,email',
            'contenido' => 'required|string',
            'categoria' => 'nullable|string|max:100',
            'segmento_objetivo' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $plantilla = PlantillaMensaje::create([
            'nombre' => $request->nombre,
            'codigo' => Str::slug($request->nombre) . '-' . Str::random(6),
            'tipo' => $request->tipo,
            'contenido' => $request->contenido,
            'categoria' => $request->categoria,
            'segmento_objetivo' => $request->segmento_objetivo,
            'creado_por' => auth()->id(),
        ]);

        // Extraer variables del contenido
        $plantilla->variables = $plantilla->getVariablesDisponibles();
        $plantilla->save();

        return response()->json([
            'success' => true,
            'message' => 'Plantilla creada exitosamente',
            'plantilla' => $plantilla
        ]);
    }

    /**
     * Actualizar plantilla
     */
    public function actualizarPlantilla(Request $request, $id)
    {
        $plantilla = PlantillaMensaje::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'contenido' => 'required|string',
            'categoria' => 'nullable|string|max:100',
            'segmento_objetivo' => 'nullable|string|max:50',
            'activa' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $plantilla->update([
            'nombre' => $request->nombre,
            'contenido' => $request->contenido,
            'categoria' => $request->categoria,
            'segmento_objetivo' => $request->segmento_objetivo,
            'activa' => $request->activa ?? $plantilla->activa,
        ]);

        $plantilla->variables = $plantilla->getVariablesDisponibles();
        $plantilla->save();

        return response()->json([
            'success' => true,
            'message' => 'Plantilla actualizada exitosamente'
        ]);
    }

    /**
     * Eliminar plantilla
     */
    public function eliminarPlantilla($id)
    {
        $plantilla = PlantillaMensaje::findOrFail($id);

        // Verificar si tiene campanas asociadas
        if ($plantilla->campanas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la plantilla porque tiene campañas asociadas'
            ], 400);
        }

        $plantilla->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plantilla eliminada exitosamente'
        ]);
    }

    /**
     * Vista de campanas
     */
    public function campanas()
    {
        $campanas = CampanaComunicacion::with('plantilla')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $plantillas = PlantillaMensaje::activas()->get();
        $departamentos = Departamento::orderBy('nombre')->get();

        return view('pages.comunicacion.campanas', compact('campanas', 'plantillas', 'departamentos'));
    }

    /**
     * Obtener plantilla por ID (API)
     */
    public function getPlantilla($id)
    {
        $plantilla = PlantillaMensaje::findOrFail($id);
        return response()->json($plantilla);
    }

    /**
     * Guardar campana (crear nueva)
     */
    public function guardarCampana(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'plantilla_id' => 'required|exists:plantillas_mensaje,id',
            'segmento' => 'required|string',
            'filtros' => 'nullable|array',
            'programada' => 'boolean',
            'fecha_programada' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $plantilla = PlantillaMensaje::findOrFail($request->plantilla_id);

        // Calcular total de destinatarios
        $totalDestinatarios = $this->contarDestinatarios($request->segmento, $request->filtros ?? []);

        $campana = CampanaComunicacion::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'plantilla_id' => $request->plantilla_id,
            'tipo' => $plantilla->tipo,
            'segmento' => $request->segmento,
            'filtros' => $request->filtros,
            'total_destinatarios' => $totalDestinatarios,
            'programada' => $request->programada ?? false,
            'fecha_programada' => $request->programada ? $request->fecha_programada : null,
            'estado' => $request->programada ? 'programada' : 'borrador',
            'creado_por' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaña creada exitosamente',
            'campana' => $campana->load('plantilla')
        ]);
    }

    /**
     * Actualizar campana
     */
    public function actualizarCampana(Request $request, $id)
    {
        $campana = CampanaComunicacion::findOrFail($id);

        if (!in_array($campana->estado, ['borrador', 'programada'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar campañas en estado borrador o programada'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'plantilla_id' => 'required|exists:plantillas_mensaje,id',
            'segmento' => 'required|string',
            'filtros' => 'nullable|array',
            'programada' => 'boolean',
            'fecha_programada' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $totalDestinatarios = $this->contarDestinatariosPrivado($request->segmento, $request->filtros ?? []);

        $campana->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'plantilla_id' => $request->plantilla_id,
            'segmento' => $request->segmento,
            'filtros' => $request->filtros,
            'total_destinatarios' => $totalDestinatarios,
            'programada' => $request->programada ?? false,
            'fecha_programada' => $request->programada ? $request->fecha_programada : null,
            'estado' => $request->programada ? 'programada' : 'borrador',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaña actualizada exitosamente',
            'campana' => $campana->load('plantilla')
        ]);
    }

    /**
     * Eliminar campana
     */
    public function eliminarCampana($id)
    {
        $campana = CampanaComunicacion::findOrFail($id);

        if (!in_array($campana->estado, ['borrador', 'cancelada'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar campañas en estado borrador o cancelada'
            ], 400);
        }

        // Eliminar mensajes asociados
        $campana->mensajes()->delete();
        $campana->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaña eliminada exitosamente'
        ]);
    }

    /**
     * Obtener campana por ID (API)
     */
    public function getCampana($id)
    {
        $campana = CampanaComunicacion::with(['plantilla', 'creador'])->findOrFail($id);
        return response()->json($campana);
    }

    /**
     * Obtener detalle de campana
     */
    public function detalleCampana($id)
    {
        $campana = CampanaComunicacion::with(['plantilla', 'creador'])->findOrFail($id);

        // Metricas adicionales
        $metricas = [
            'porcentaje_entrega' => $campana->porcentaje_entrega,
            'porcentaje_lectura' => $campana->porcentaje_lectura,
            'porcentaje_respuesta' => $campana->porcentaje_respuesta,
            'porcentaje_fallo' => $campana->porcentaje_fallo,
        ];

        // Ultimos mensajes
        $ultimosMensajes = $campana->mensajes()
            ->with('persona')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'campana' => $campana,
            'metricas' => $metricas,
            'ultimos_mensajes' => $ultimosMensajes
        ]);
    }

    /**
     * Iniciar campana
     */
    public function iniciarCampana($id)
    {
        $campana = CampanaComunicacion::findOrFail($id);

        if (!$campana->puedeIniciar()) {
            return response()->json([
                'success' => false,
                'message' => 'La campaña no puede iniciarse en su estado actual'
            ], 400);
        }

        // Verificar configuracion de WhatsApp
        if (!$this->whatsappService->estaConfigurado()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp no está configurado correctamente'
            ], 400);
        }

        $campana->estado = 'en_proceso';
        $campana->fecha_inicio = now();
        $campana->save();

        // Crear mensajes pendientes
        $this->crearMensajesPendientes($campana);

        // Disparar job para envio masivo
        EnviarCampanaMasiva::dispatch($campana);

        return response()->json([
            'success' => true,
            'message' => 'Campaña iniciada exitosamente'
        ]);
    }

    /**
     * Pausar campana
     */
    public function pausarCampana($id)
    {
        $campana = CampanaComunicacion::findOrFail($id);

        if (!$campana->puedePausar()) {
            return response()->json([
                'success' => false,
                'message' => 'La campaña no puede pausarse'
            ], 400);
        }

        $campana->estado = 'pausada';
        $campana->save();

        return response()->json([
            'success' => true,
            'message' => 'Campaña pausada exitosamente'
        ]);
    }

    /**
     * Cancelar campana
     */
    public function cancelarCampana($id)
    {
        $campana = CampanaComunicacion::findOrFail($id);

        if (!$campana->puedeCancelar()) {
            return response()->json([
                'success' => false,
                'message' => 'La campaña no puede cancelarse'
            ], 400);
        }

        $campana->estado = 'cancelada';
        $campana->fecha_fin = now();
        $campana->save();

        // Cancelar mensajes pendientes
        $campana->mensajes()->where('estado', 'pendiente')->update([
            'estado' => 'fallido',
            'error_mensaje' => 'Campaña cancelada'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaña cancelada exitosamente'
        ]);
    }

    /**
     * Vista de respuestas/inbox
     */
    public function inbox()
    {
        $conversaciones = RespuestaWhatsapp::with('persona')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $estadisticas = [
            'no_leidos' => RespuestaWhatsapp::where('leido', false)->count(),
            'total' => RespuestaWhatsapp::count(),
        ];

        return view('pages.comunicacion.inbox', compact('conversaciones', 'estadisticas'));
    }

    /**
     * Marcar mensaje como leido
     */
    public function marcarLeido($id)
    {
        $respuesta = RespuestaWhatsapp::findOrFail($id);
        $respuesta->update(['leido' => true, 'leido_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Obtener conversacion (mensajes)
     */
    public function getConversacion($id)
    {
        $respuesta = RespuestaWhatsapp::with('persona')->findOrFail($id);

        // Obtener historial de mensajes con este telefono
        $mensajesEnviados = MensajeEnviado::where('telefono', $respuesta->telefono)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($m) {
                return [
                    'tipo' => 'sent',
                    'contenido' => $m->contenido_enviado,
                    'fecha' => $m->enviado_at?->format('d/m H:i') ?? $m->created_at->format('d/m H:i'),
                    'estado' => $m->estado,
                ];
            });

        $mensajesRecibidos = RespuestaWhatsapp::where('telefono', $respuesta->telefono)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($m) {
                return [
                    'tipo' => 'received',
                    'contenido' => $m->mensaje,
                    'fecha' => $m->created_at->format('d/m H:i'),
                    'estado' => 'received',
                ];
            });

        // Combinar y ordenar por fecha
        $mensajes = $mensajesEnviados->concat($mensajesRecibidos)
            ->sortBy('fecha')
            ->values();

        return response()->json([
            'nombre' => $respuesta->persona?->nombre ?? $respuesta->nombre_contacto,
            'telefono' => $respuesta->telefono,
            'avatar' => strtoupper(substr($respuesta->persona?->nombre ?? $respuesta->telefono, 0, 2)),
            'mensajes' => $mensajes,
        ]);
    }

    /**
     * Enviar respuesta a un contacto
     */
    public function enviarRespuesta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversacion_id' => 'required|exists:respuestas_whatsapp,id',
            'mensaje' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $respuesta = RespuestaWhatsapp::findOrFail($request->conversacion_id);

        // Enviar respuesta via WhatsApp
        $resultado = $this->whatsappService->enviarMensajeTexto(
            $respuesta->telefono,
            $request->mensaje
        );

        if ($resultado['success']) {
            $respuesta->update([
                'respondido' => true,
                'respondido_at' => now(),
                'respuesta_enviada' => $request->mensaje,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar respuesta: ' . ($resultado['error'] ?? 'Error desconocido')
        ], 500);
    }

    /**
     * Obtener perfil del contacto
     */
    public function getPerfilContacto($id)
    {
        $respuesta = RespuestaWhatsapp::with('persona.municipio')->findOrFail($id);
        $persona = $respuesta->persona;

        $mensajesEnviados = MensajeEnviado::where('telefono', $respuesta->telefono)->count();
        $mensajesRecibidos = RespuestaWhatsapp::where('telefono', $respuesta->telefono)->count();
        $campanas = MensajeEnviado::where('telefono', $respuesta->telefono)
            ->distinct('campana_id')
            ->count('campana_id');

        return response()->json([
            'nombre' => $persona?->nombre ?? $respuesta->nombre_contacto,
            'telefono' => $respuesta->telefono,
            'avatar' => strtoupper(substr($persona?->nombre ?? $respuesta->telefono, 0, 2)),
            'cedula' => $persona?->cedula,
            'municipio' => $persona?->municipio?->nombre,
            'mensajes_enviados' => $mensajesEnviados,
            'mensajes_recibidos' => $mensajesRecibidos,
            'campanas' => $campanas,
            'ultimo_mensaje' => $respuesta->created_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Probar conexion con WhatsApp API
     */
    public function testConexion()
    {
        try {
            $resultado = $this->whatsappService->testConexion();
            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensaje de prueba
     */
    public function enviarPrueba(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string',
            'mensaje' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $resultado = $this->whatsappService->enviarMensajeTexto(
            $request->telefono,
            $request->mensaje
        );

        return response()->json($resultado);
    }

    /**
     * Obtener logs de actividad
     */
    public function getLogs()
    {
        // Por ahora retornar logs simulados
        // En produccion se puede implementar un sistema de logs real
        $logs = collect([]);

        // Obtener ultimos mensajes enviados como logs
        $mensajes = MensajeEnviado::orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($mensajes as $mensaje) {
            $logs->push([
                'tipo' => $mensaje->estado === 'fallido' ? 'error' : ($mensaje->estado === 'enviado' ? 'success' : 'info'),
                'mensaje' => "Mensaje a {$mensaje->telefono}: {$mensaje->estado}",
                'fecha' => $mensaje->updated_at->format('d/m H:i'),
            ]);
        }

        return response()->json(['logs' => $logs]);
    }

    /**
     * Contar destinatarios (endpoint publico)
     */
    public function contarDestinatarios(Request $request)
    {
        $segmento = $request->get('segmento', 'todos');
        $filtros = $request->get('filtros', []);

        $total = $this->contarDestinatariosPrivado($segmento, $filtros);

        return response()->json(['total' => $total]);
    }

    /**
     * Preview de plantilla
     */
    public function previewPlantilla(Request $request)
    {
        $plantillaId = $request->get('plantilla_id');
        $plantilla = PlantillaMensaje::findOrFail($plantillaId);

        // Datos de ejemplo para preview
        $datosEjemplo = [
            'nombre' => 'Juan Perez',
            'cedula' => '12345678',
            'telefono' => '+57 300 123 4567',
            'municipio' => 'Bogota',
        ];

        $contenidoRenderizado = $plantilla->renderizar($datosEjemplo);

        return response()->json([
            'success' => true,
            'contenido' => $contenidoRenderizado,
            'variables' => $plantilla->variables,
        ]);
    }

    /**
     * Webhook verification (GET)
     */
    public function webhookVerify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $this->whatsappService->verificarWebhookToken($token)) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Responder mensaje (legacy)
     */
    public function responderMensaje(Request $request, $id)
    {
        $respuesta = RespuestaWhatsapp::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Enviar respuesta via WhatsApp
        $resultado = $this->whatsappService->enviarMensajeTexto(
            $respuesta->telefono,
            $request->mensaje
        );

        if ($resultado['success']) {
            $respuesta->marcarRespondido($request->mensaje);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar respuesta: ' . ($resultado['error'] ?? 'Error desconocido')
        ], 500);
    }

    /**
     * Configuracion de WhatsApp
     */
    public function configuracion()
    {
        $configuracion = ConfiguracionWhatsapp::first();
        $whatsappStats = $this->whatsappService->getEstadisticas();

        $estadisticas = [
            'mensajes_hoy' => $whatsappStats['mensajes_enviados_hoy'] ?? 0,
            'limite_diario' => $whatsappStats['limite_diario'] ?? 0,
            'disponibles' => $whatsappStats['mensajes_restantes'] ?? 0,
            'tasa_entrega' => $this->calcularTasaEntrega(),
        ];

        $logsRecientes = collect([]);

        return view('pages.comunicacion.configuracion', compact('configuracion', 'estadisticas', 'logsRecientes'));
    }

    /**
     * Guardar configuracion de WhatsApp
     */
    public function guardarConfiguracion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number_id' => 'required|string',
            'business_account_id' => 'required|string',
            'access_token' => 'required|string',
            'webhook_verify_token' => 'nullable|string',
            'api_version' => 'nullable|string',
            'limite_diario' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $config = ConfiguracionWhatsapp::first();

        if ($config) {
            $config->update($request->only([
                'phone_number_id',
                'business_account_id',
                'access_token',
                'webhook_verify_token',
                'api_version',
                'limite_diario',
            ]));
        } else {
            ConfiguracionWhatsapp::create($request->only([
                'phone_number_id',
                'business_account_id',
                'access_token',
                'webhook_verify_token',
                'api_version',
                'limite_diario',
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada exitosamente'
        ]);
    }

    /**
     * Webhook de WhatsApp
     */
    public function webhook(Request $request)
    {
        // Verificacion del webhook
        if ($request->isMethod('get')) {
            $mode = $request->get('hub_mode');
            $token = $request->get('hub_verify_token');
            $challenge = $request->get('hub_challenge');

            if ($mode === 'subscribe' && $this->whatsappService->verificarWebhookToken($token)) {
                return response($challenge, 200);
            }

            return response('Forbidden', 403);
        }

        // Procesar webhook POST
        $this->whatsappService->procesarWebhook($request->all());

        return response('OK', 200);
    }

    /**
     * API: Obtener estadisticas generales
     */
    public function apiEstadisticas()
    {
        return response()->json($this->getEstadisticasGenerales());
    }

    /**
     * API: Previsualizar destinatarios
     */
    public function previsualizarDestinatarios(Request $request)
    {
        $segmento = $request->get('segmento', 'todos');
        $filtros = $request->get('filtros', []);

        $total = $this->contarDestinatariosPrivado($segmento, $filtros);
        $muestra = $this->obtenerMuestraDestinatarios($segmento, $filtros, 5);

        return response()->json([
            'total' => $total,
            'muestra' => $muestra
        ]);
    }

    /**
     * API: Obtener municipios por departamento
     */
    public function getMunicipios($departamentoId)
    {
        $municipios = Municipio::where('departamento_id', $departamentoId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($municipios);
    }

    // ================================
    // Metodos privados
    // ================================

    private function getEstadisticasGenerales(): array
    {
        $hoy = now()->startOfDay();
        $esteMes = now()->startOfMonth();

        return [
            'campanas_activas' => CampanaComunicacion::activas()->count(),
            'campanas_hoy' => CampanaComunicacion::whereDate('created_at', $hoy)->count(),
            'mensajes_enviados_hoy' => MensajeEnviado::whereDate('enviado_at', $hoy)->count(),
            'mensajes_enviados_mes' => MensajeEnviado::whereDate('enviado_at', '>=', $esteMes)->count(),
            'tasa_entrega' => $this->calcularTasaEntrega(),
            'tasa_lectura' => $this->calcularTasaLectura(),
            'tasa_respuesta' => $this->calcularTasaRespuesta(),
            'respuestas_pendientes' => RespuestaWhatsapp::noLeidos()->count(),
        ];
    }

    private function calcularTasaEntrega(): float
    {
        $enviados = MensajeEnviado::whereIn('estado', ['enviado', 'entregado', 'leido', 'respondido'])->count();
        $entregados = MensajeEnviado::whereIn('estado', ['entregado', 'leido', 'respondido'])->count();

        return $enviados > 0 ? round(($entregados / $enviados) * 100, 1) : 0;
    }

    private function calcularTasaLectura(): float
    {
        $entregados = MensajeEnviado::whereIn('estado', ['entregado', 'leido', 'respondido'])->count();
        $leidos = MensajeEnviado::whereIn('estado', ['leido', 'respondido'])->count();

        return $entregados > 0 ? round(($leidos / $entregados) * 100, 1) : 0;
    }

    private function calcularTasaRespuesta(): float
    {
        $leidos = MensajeEnviado::whereIn('estado', ['leido', 'respondido'])->count();
        $respondidos = MensajeEnviado::where('estado', 'respondido')->count();

        return $leidos > 0 ? round(($respondidos / $leidos) * 100, 1) : 0;
    }

    private function contarDestinatariosPrivado(string $segmento, array $filtros): int
    {
        $query = $this->buildDestinatariosQuery($segmento, $filtros);
        return $query->count();
    }

    private function obtenerMuestraDestinatarios(string $segmento, array $filtros, int $limite): array
    {
        $query = $this->buildDestinatariosQuery($segmento, $filtros);
        return $query->limit($limite)->get(['id', 'nombre', 'telefono', 'cedula'])->toArray();
    }

    private function buildDestinatariosQuery(string $segmento, array $filtros)
    {
        $query = Persona::whereNotNull('telefono')
            ->where('telefono', '!=', '');

        // Filtrar por segmento
        switch ($segmento) {
            case 'votantes':
                $query->whereHas('votante');
                break;
            case 'lideres':
                $query->whereHas('lider');
                break;
            case 'testigos':
                // Asumiendo que hay relacion con testigos
                $query->whereHas('testigo', function($q) {});
                break;
            case 'no_votaron':
                $query->where(function($q) {
                    $q->whereNull('reporte_voto')->orWhere('reporte_voto', false);
                });
                break;
            case 'ya_votaron':
                $query->where('reporte_voto', true);
                break;
        }

        // Aplicar filtros adicionales
        if (!empty($filtros['departamento_id'])) {
            $query->whereHas('municipio', function($q) use ($filtros) {
                $q->where('departamento_id', $filtros['departamento_id']);
            });
        }

        if (!empty($filtros['municipio_id'])) {
            $query->where('municipio_id', $filtros['municipio_id']);
        }

        if (!empty($filtros['genero_id'])) {
            $query->where('genero_id', $filtros['genero_id']);
        }

        return $query;
    }

    private function crearMensajesPendientes(CampanaComunicacion $campana): void
    {
        $plantilla = $campana->plantilla;
        $query = $this->buildDestinatariosQuery($campana->segmento, $campana->filtros ?? []);

        $query->chunk(100, function($personas) use ($campana, $plantilla) {
            $mensajes = [];

            foreach ($personas as $persona) {
                // Renderizar contenido con variables
                $contenido = $plantilla->renderizar([
                    'nombre' => $persona->nombre,
                    'cedula' => $persona->cedula,
                    'telefono' => $persona->telefono,
                    'municipio' => $persona->municipio?->nombre ?? '',
                ]);

                $mensajes[] = [
                    'campana_id' => $campana->id,
                    'persona_id' => $persona->id,
                    'telefono' => $persona->telefono,
                    'contenido_enviado' => $contenido,
                    'estado' => 'pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            MensajeEnviado::insert($mensajes);
        });
    }
}
