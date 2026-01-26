<?php

namespace App\Http\Middleware;

use App\Models\Barrio;
use App\Rules\correlacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class validarBarriosEditar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route()->parameter('id');

        $existe = Barrio::where('id', $id)->exists();

        if (!$existe) {
            return back()
                ->withInput()
                ->with("edit_error_id", $id);
        }

        $reglas = [
            "nombre" => "required|string|max:255",
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
        ];

        // Mensajes de error personalizados
        $mensajes = [
            "nombre.required" => "El campo nombre es obligatorio.",
            "nombre.string" => "El campo nombre debe ser un texto.",
            "nombre.max" => "El campo nombre no debe exceder los 255 caracteres.",
            
            "departamento.required" => "El campo departamento es obligatorio.",
            "departamento.integer" => "El campo departamento debe ser uno de la lista.",
            "departamento.exists" => "El departamento seleccionado no es válido.",
            
            "municipio.required" => "El campo municipio es obligatorio.",
            "municipio.integer" => "El campo municipio debe ser uno de la lista.",
            
            "corregimiento.required" => "El campo corregimiento es obligatorio.",
            "corregimiento.integer" => "El campo corregimiento debe ser uno de la lista.",
        ];

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator->errors())
                ->withInput()
                ->with("edit_error_id", $id);
        }

        return $next($request);
    }
}
