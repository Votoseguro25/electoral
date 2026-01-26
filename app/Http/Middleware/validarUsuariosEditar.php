<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class validarUsuariosEditar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route()->parameter('id');

        $reglas = [
            "name_editar" => [
                "required",
                "string",
                "max:255",
                Rule::unique('users', 'name')->ignore($id)
            ],
            "email_editar" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique('users', 'email')->ignore($id)
            ],
            "password_editar" => "nullable|string|min:8",
            "role_editar" => "required|integer|exists:roles,id"
        ];

        $mensajesName = [
            'name_editar.required' => 'El nombre de usuario es obligatorio.',
            'name_editar.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'name_editar.max' => 'El nombre de usuario no debe exceder los 255 caracteres.',
            'name_editar.unique' => 'El nombre de usuario ya está en uso.',
        ];

        $mensajesEmail = [
            'email_editar.required' => 'El correo electrónico es obligatorio.',
            'email_editar.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email_editar.email' => 'El correo electrónico no es válido.',
            'email_editar.unique' => 'El correo electrónico ya está en uso.',
        ];

        $mensajesPassword = [
            'password_editar.string' => 'La contraseña debe ser una cadena de texto.',
            'password_editar.min' => 'La contraseña debe tener al menos 8 caracteres.'
        ];

        $mensajesRole = [
            'role_editar.required' => 'El rol es obligatorio.',
            'role_editar.integer' => 'El rol debe ser uno de la lista.',
            'role_editar.exists' => 'El rol seleccionado no es válido.'
        ];

        $mensajes = [
            ...$mensajesName,
            ...$mensajesEmail,
            ...$mensajesPassword,
            ...$mensajesRole
        ];

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator->errors())
                ->withInput($request->except(['password']))
                ->with("edit_error_id", $id);
        }
        
        return $next($request);
    }
}
