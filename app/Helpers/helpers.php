<?php

use App\Helpers\RouteHelper;

if (!function_exists('can_access_route')) {
    /**
     * Verifica si el usuario autenticado puede acceder a una ruta
     * basándose en los middlewares de roles definidos
     *
     * @param string $routeName Nombre de la ruta
     * @return bool
     */
    function can_access_route(string $routeName): bool
    {
        return RouteHelper::canAccessRoute($routeName);
    }
}
