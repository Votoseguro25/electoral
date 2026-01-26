<?php

namespace App\Http\Middleware;

use App\Rules\correlacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class validarTestigoReporteCrear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reglas = [
            "departamento" => "required|integer|exists:departamentos,id",
            "municipio" => [
                "required",
                "integer",
                new correlacion(
                    'municipios',
                    'departamento_id',
                    $request->input('departamento'),
                    'El municipio no pertenece al departamento seleccionado'
                )
            ],
            "corregimiento" => [
                "required",
                "integer",
                new correlacion(
                    'corregimientos',
                    'municipio_id',
                    $request->input('municipio'),
                    'El corregimiento no pertenece al municipio seleccionado'
                )
            ],
            "barrio" => [
                "required",
                "integer",
                new Correlacion(
                    'barrios',
                    'corregimiento_id',
                    $request->input('corregimiento'),
                    'El barrio no pertenece al corregimiento seleccionado'
                )
            ],
            "puesto" => [
                "required",
                "integer",
                new correlacion(
                    'puestos',
                    'barrio_id',
                    $request->input('barrio'),
                    'El puesto no pertenece al barrio seleccionado'
                )
            ],
            "mesa" => [
                "required",
                "integer",
                new correlacion(
                    'mesas',
                    'puesto_id',
                    $request->input('puesto'),
                    'La mesa no pertenece al puesto seleccionado'
                )
            ],
            "candidatos" => "required|array",
            "candidatos.*" => "required|integer|min:0",
            "documento_e14" => "required|file|mimes:jpg,jpeg,png|max:10240"
        ];

        $mensajesDepartamento = [
            "departamento.required" => "El campo departamento es obligatorio.",
            "departamento.integer" => "El campo departamento debe ser uno de la lista.",
            "departamento.exists" => "El departamento seleccionado no es válido.",
        ];

        $mensajesMunicipio = [
            "municipio.required" => "El campo municipio es obligatorio.",
            "municipio.integer" => "El campo municipio debe ser uno de la lista.",
            "municipio.exists" => "El municipio seleccionado no es válido.",
        ];

        $mensajesCorregimiento = [
            "corregimiento.required" => "El campo corregimiento es obligatorio.",
            "corregimiento.integer" => "El campo corregimiento debe ser uno de la lista.",
        ];

        $mensajesBarrio = [
            "barrio.required" => "El campo barrio es obligatorio.",
            "barrio.integer" => "El campo barrio debe ser uno de la lista.",
        ];

        $mensajesPuesto = [
            "puesto.required" => "El campo puesto es obligatorio.",
            "puesto.integer" => "El campo puesto debe ser uno de la lista.",
        ];

        $mensajesMesa = [
            "mesa.required" => "El campo mesa es obligatorio.",
            "mesa.integer" => "El campo mesa debe ser uno de la lista.",
        ];

        $mensajesCandidatos = [
            "candidatos.required" => "Debe reportar al menos un candidato.",
            "candidatos.array" => "El formato de los candidatos no es válido.",
            "candidatos.*.required" => "Debe ingresar un valor para cada candidato.",
            "candidatos.*.integer" => "Los votos deben ser un número entero.",
            "candidatos.*.min" => "Los votos no pueden ser negativos.",
            "candidatos.*.exists" => "El candidato seleccionado no es válido.",
        ];

        $mensajesDocumento = [
            "documento_e14.required" => "El documento E14 es obligatorio.",
            "documento_e14.file" => "El archivo debe ser un documento válido.",
            "documento_e14.mimes" => "El documento debe ser PDF, JPG, JPEG o PNG.",
            "documento_e14.max" => "El documento no debe superar los 10MB.",
        ];

        $mensajes = array_merge(
            $mensajesDepartamento,
            $mensajesMunicipio,
            $mensajesCorregimiento,
            $mensajesBarrio,
            $mensajesPuesto,
            $mensajesMesa,
            $mensajesCandidatos,
            $mensajesDocumento
        );

        $validator = \Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {        
            return back()
                ->withErrors($validator->errors())
                ->withInput()
                ->with("error_formulario", true)
                ->with("alerta", [
                    "icon" => "error",
                    "title" => "Error en el formulario",
                    "text" => "Revisa los errores e intenta nuevamente.",
                    "confirmButtonText" => "aceptar"
                ]);
        }

        return $next($request);
    }
}
