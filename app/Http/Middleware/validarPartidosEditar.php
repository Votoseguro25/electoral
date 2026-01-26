<?php

namespace App\Http\Middleware;

use App\Models\Partido;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rule;
use Validator;

class validarPartidosEditar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route()->parameter('id');

        $existe = Partido::where('id', $id)->exists();

        if (!$existe) {
            return back()
                ->withInput()
                ->with("edit_error_id", $id);
        }

        $reglas = [
            "nombre" => [
                "required",
                "string",
                "max:100",
                Rule::unique('partido', 'nombre')->ignore($id)
            ],
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
                ->with("edit_error_id", $id);
        }

        return $next($request);
    }
}
