<?php

namespace App\Http\Middleware;

use App\Models\Role;
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
            "name"    => "required|string|max:255|unique:users,name",
            "email"   => "required|string|max:255|unique:users,email",
            "password" => "required|string|min:8",
            "role"    => "required|integer|exists:roles,id"
        ];

        // Validar mesa_id si el rol seleccionado tiene slug 'testigo'
        $role = Role::find($request->input('role'));
        if ($role && $role->slug === 'testigo') {
            $reglas['mesa_id'] = 'required|integer|exists:mesas,id';
        }

        $mensajesName = [
            'name.required' => 'El nombre de usuario es obligatorio.',
            'name.string'   => 'El nombre de usuario debe ser una cadena de texto.',
            'name.max'      => 'El nombre de usuario no debe exceder los 255 caracteres.',
            'name.unique'   => 'El nombre de usuario ya está en uso.',
        ];

        $mensajesEmail = [
            'email.required' => 'El usuario de acceso es obligatorio.',
            'email.string'   => 'El usuario de acceso debe ser una cadena de texto.',            
            'email.unique'   => 'El usuario de acceso ya está en uso.',
        ];

        $mensajesPassword = [
            'password.required' => 'La contraseña es obligatoria.',
            'password.string'   => 'La contraseña debe ser una cadena de texto.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.'
        ];

        $mensajesRole = [
            'role.required' => 'El rol es obligatorio.',
            'role.integer'  => 'El rol debe ser uno de la lista.',
            'role.exists'   => 'El rol seleccionado no es válido.'
        ];

        $mensajesMesa = [
            'mesa_id.required' => 'Debe asignar una mesa al testigo.',
            'mesa_id.integer'  => 'La mesa seleccionada no es válida.',
            'mesa_id.exists'   => 'La mesa seleccionada no existe.',
        ];

        $mensajes = [
            ...$mensajesName,
            ...$mensajesEmail,
            ...$mensajesPassword,
            ...$mensajesRole,
            ...$mensajesMesa,
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
