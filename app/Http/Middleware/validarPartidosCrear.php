<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class validarPartidosCrear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reglas = [
            "nombre" => "required|string|max:100|unique:partido,nombre",
            "estado" => "nullable|boolean"
        ];

        $mensajes = [
            'nombre.required' => 'El nombre del partido es obligatorio.',
            'nombre.string' => 'El nombre del partido debe ser un texto.',
            'nombre.max' => 'El nombre del partido no debe exceder los 100 caracteres.',
            'nombre.unique' => 'Ya existe un partido con este nombre.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
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
