<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class validarUsuariosCrear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reglas = [
            "name" => "required|string|max:255|unique:users,name",
            "email" => "required|string|email|max:255|unique:users,email",
            "password" => "required|string|min:8",
            "role" => "required|integer|exists:roles,id"
        ];

        $mensajesName = [
            'name.required' => 'El nombre de usuario es obligatorio.',
            'name.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'name.max' => 'El nombre de usuario no debe exceder los 255 caracteres.',
            'name.unique' => 'El nombre de usuario ya está en uso.',
        ];

        $mensajesEmail = [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está en uso.',
        ];

        $mensajesPassword = [
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.'
        ];

        $mensajesRole = [
            'role.required' => 'El rol es obligatorio.',
            'role.integer' => 'El rol debe ser uno de la lista.',
            'role.exists' => 'El rol seleccionado no es válido.'
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
                ->with("error_crear", true);
        }

        return $next($request);
    }
}
