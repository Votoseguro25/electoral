<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class rolesController extends Controller
{
    public function listado(Request $req)
    {
        $buscar = $req->input('buscar');
        
        $roles = Role::when($buscar, function($query, $buscar) {
            return $query->where('nombre', 'like', '%' . $buscar . '%');
        })
        ->orderBy('id', 'desc')
        ->paginate(10)
        ->appends(['buscar' => $buscar]);
        
        return view('pages.roles.listado', compact('roles', 'buscar'));
    }

    public function guardar(Request $req)
    {
        try {
            $slug = $this->generarSlug($req->input('nombre'));

            Role::create([
                'nombre' => $req->input('nombre'),
                'slug' => $slug,
                'descripcion' => $req->input('descripcion'),
            ]);

            return redirect()->route('roles.listado')->with('alerta', [
                "icon" => "success",
                "title" => "Rol creado",
                "text" => "El rol ha sido creado exitosamente",
                "confirmButtonText" => "aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => $th->getMessage(),
                "confirmButtonText" => "aceptar"
            ])->withInput();
        }
    }

    public function modificar(Request $req, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return back()->with('alerta', [
                    "icon" => "error",
                    "title" => "Rol no encontrado",
                    "text" => "El rol que intentas editar no existe",
                    "confirmButtonText" => "aceptar"
                ]);
            }

            $role->nombre = $req->input('nombre');
                        
            $role->descripcion = $req->input('descripcion');
            $role->save();

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Rol modificado",
                "text" => "El rol ha sido modificado exitosamente",
                "confirmButtonText" => "aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => $th->getMessage(),
                "confirmButtonText" => "aceptar"
            ])->withInput();
        }
    }

    public function borrar($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Rol no encontrado",
                "text" => "El rol que intentas eliminar no existe",
                "confirmButtonText" => "aceptar"
            ]);
        }

        // Verificar si el rol tiene usuarios asignados
        if ($role->users()->count() > 0) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "No se puede eliminar",
                "text" => "Este rol tiene usuarios asignados. Reasigna los usuarios antes de eliminar.",
                "confirmButtonText" => "aceptar"
            ]);
        }

        // Evitar eliminar el rol admin
        if ($role->slug === 'admin') {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "No se puede eliminar",
                "text" => "No puedes eliminar el rol de Administrador",
                "confirmButtonText" => "aceptar"
            ]);
        }

        try {
            $role->delete();

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Rol eliminado",
                "text" => "El rol ha sido eliminado exitosamente",
                "confirmButtonText" => "aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente",
                "confirmButtonText" => "aceptar"
            ]);
        }
    }

    /**
     * Generar slug desde el nombre del rol
     * Elimina tildes, convierte a minúsculas y reemplaza espacios con guiones
     */
    private function generarSlug($nombre)
    {
        // Reemplazar caracteres con tildes
        $slug = strtr($nombre, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
            'ü' => 'u', 'Ü' => 'u'
        ]);

        // Convertir a minúsculas
        $slug = strtolower($slug);

        // Reemplazar espacios y caracteres especiales con guiones
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Eliminar guiones al inicio y al final
        $slug = trim($slug, '-');

        return $slug;
    }
}
