<?php

namespace App\Http\Middleware;

use App\Models\Candidato;
use App\Rules\correlacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class validarCandidatosEditar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route()->parameter('id');

        // Convertir partido vacío a null
        if ($request->has('partido') && ($request->input('partido') === '0' || $request->input('partido') === '')) {
            $request->merge(['partido' => null]);
        }

        $existe = Candidato::where('id', $id)->exists();

        if (!$existe) {
            return back()
                ->withInput()
                ->with("edit_error_id", $id);
        }

        $reglas = [
            "nombre" => "required|string|max:150",
            "Tarjeton" => "required|integer|min:1",
            "foto" => "nullable|image|mimes:jpeg,jpg,png,gif|max:5120", // 5MB max
            "color" => "required|string|regex:/^#[0-9A-Fa-f]{6}$/",
            "partido" => "nullable|integer|exists:partido,id",
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
            "tipo_eleccion" => "required|integer|exists:tipo_eleccion,id",
        ];

        // Mensajes de error personalizados
        $mensajes = [
            "nombre.required" => "El campo nombre es obligatorio.",
            "nombre.string" => "El campo nombre debe ser un texto.",
            "nombre.max" => "El campo nombre no debe exceder los 150 caracteres.",

            "Tarjeton.required" => "El campo Tarjetón es obligatorio.",
            "Tarjeton.integer" => "El campo Tarjetón debe ser un número entero.",
            "Tarjeton.min" => "El campo Tarjetón debe ser mayor a 0.",
            "Tarjeton.unique" => "Este número de Tarjetón ya está en uso por otro candidato.",

            "foto.image" => "El archivo debe ser una imagen.",
            "foto.mimes" => "La foto debe ser de tipo: jpeg, jpg, png o gif.",
            "foto.max" => "La foto no debe superar los 5MB.",

            "color.required" => "El campo color es obligatorio.",
            "color.string" => "El campo color debe ser un texto.",
            "color.regex" => "El color debe tener el formato hexadecimal válido (#RRGGBB).",

            "partido.integer" => "El campo partido debe ser uno de la lista.",
            "partido.exists" => "El partido seleccionado no es válido.",

            "departamento.required" => "El campo departamento es obligatorio.",
            "departamento.integer" => "El campo departamento debe ser uno de la lista.",
            "departamento.exists" => "El departamento seleccionado no es válido.",

            "municipio.required" => "El campo municipio es obligatorio.",
            "municipio.integer" => "El campo municipio debe ser uno de la lista.",            

            "tipo_eleccion.required" => "El campo tipo de elección es obligatorio.",
            "tipo_eleccion.integer" => "El campo tipo de elección debe ser uno de la lista.",
            "tipo_eleccion.exists" => "El tipo de elección seleccionado no es válido.",
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
