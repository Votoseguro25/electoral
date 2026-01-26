<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Lista de slugs de roles permitidos
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('usuarios.login.vista')
                ->with('error', 'Debes iniciar sesión para acceder.');
        }

        // Siempre agregar "admin" a la lista de roles permitidos
        if (!in_array('admin', $roles)) {
            $roles[] = 'admin';
        }

        $user = auth()->user();

        // Verificar si el usuario tiene alguno de los roles permitidos
        $hasPermission = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
