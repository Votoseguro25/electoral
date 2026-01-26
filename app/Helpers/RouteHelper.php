<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class RouteHelper
{
    /**
     * Verifica si el usuario autenticado puede acceder a una ruta específica
     * basándose en los middlewares de roles definidos en la ruta
     *
     * @param string $routeName Nombre de la ruta
     * @return bool
     */
    public static function canAccessRoute(string $routeName): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $route = Route::getRoutes()->getByName($routeName);

        if (!$route) {
            return false;
        }

        // Obtener todos los middlewares de la ruta
        $middlewares = $route->gatherMiddleware();

        // Buscar el middleware 'role' y extraer los roles permitidos
        $allowedRoles = [];
        foreach ($middlewares as $middleware) {
            if (is_string($middleware) && strpos($middleware, 'role:') === 0) {
                // Extraer los roles después de 'role:'
                $rolesString = substr($middleware, 5); // Remover 'role:'
                $allowedRoles = array_merge($allowedRoles, explode(',', $rolesString));
            }
        }

        // Si no hay middleware 'role', permitir acceso (ruta pública o solo con auth)
        if (empty($allowedRoles)) {
            return true;
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        return auth()->user()->hasAnyRole($allowedRoles);
    }
}
