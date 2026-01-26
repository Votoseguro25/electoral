<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class validarRolesCrear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reglas = [
            "nombre" => "required|string|max:100|unique:roles,nombre",
            "descripcion" => "nullable|string|max:500"
        ];

        $mensajesNombre = [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.string' => 'El nombre del rol debe ser un texto.',
            'nombre.max' => 'El nombre del rol no debe exceder los 100 caracteres.',
            'nombre.unique' => 'Ya existe un rol con este nombre.',
        ];

        $mensajesDescripcion = [
            'descripcion.string' => 'La descripción debe ser un texto.',
            'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
        ];

        $mensajes = [
            ...$mensajesNombre,
            ...$mensajesDescripcion
        ];

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator->errors())
                ->withInput()
                ->with("error_crear", true);
        }

        return $next($request);
    }
}
