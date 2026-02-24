<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Mesa;
use App\Models\Puesto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class usuariosController extends Controller
{
    //
    public function loginVista()
    {
        return view('pages.acceso.login');
    }

    public function registrarVista()
    {
        $usuarios = User::with('mesa.puesto.municipio.departamento')
            ->orderBy('id', 'desc')
            ->paginate(10);
        $roles = Role::all();
        $departamentos = Departamento::orderBy('nombre')->get();
        return view('pages.acceso.registrar', compact('usuarios', 'roles', 'departamentos'));
    }

    public function registrarUsuario(Request $req)
    {
        try {
            $userPassword = Hash::make($req->input('password'), [
                'rounds' => 12
            ]);

            $role = Role::find($req->input('role'));
            $esTestigo = $role && $role->slug === 'testigo';

            User::create([
                'name'     => $req->input('name'),
                'email'    => $req->input('email'),
                'password' => $userPassword,
                'role_id'  => $req->input('role'),
                'mesa_id'  => $esTestigo ? $req->input('mesa_id') : null,
            ]);

            return redirect()->route('usuarios.registrar.vista')->with('alerta', [
                "icon"              => "success",
                "title"             => "Usuario registrado",
                "text"              => "El usuario ha sido registrado exitosamente",
                "confirmButtonText" => "aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon"              => "error",
                "title"             => "Error en el servidor",
                "text"              => "Espera unos minutos e intenta nuevamente",
                "confirmButtonText" => "aceptar"
            ])->withInput($req->except(['password']));
        }
    }

    public function getPuestosPorMunicipio(Request $req)
    {
        $municipioId = $req->query('municipio_id');
        $puestos = Puesto::where('municipio_id', $municipioId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        return response()->json($puestos);
    }

    public function getMesasPorPuesto(Request $req)
    {
        $puestoId = $req->query('puesto_id');
        $mesas = Mesa::where('puesto_id', $puestoId)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion']);
        return response()->json($mesas);
    }

    public function loginUsuario(Request $req)
    {
        $credentials = $req->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Credenciales incorrectas",
                "text" => "Por favor, verifique su correo y contraseña",
                "confirmButtonText" => "aceptar"
            ])->withInput($req->only('email'));
        }

        $req->session()->regenerate();

        return redirect()->intended(route('inicio'));
    }

    public function logoutUsuario()
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()->route('usuarios.login.vista');
    }

    public function editarUsuario(Request $req, $id)
    {        
        try {
            $user = User::find($id);

            $user->name    = $req->input('name_editar');
            $user->email   = $req->input('email_editar');
            $user->role_id = $req->input('role_editar');

            $role = Role::find($req->input('role_editar'));
            $user->mesa_id = ($role && $role->slug === 'testigo')
                ? $req->input('mesa_id_editar')
                : null;

            if ($req->input('password_editar')) {
                $user->password = Hash::make($req->input('password_editar'), [
                    'rounds' => 12
                ]);
            }

            $user->save();

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Usuario modificado",
                "text" => "El usuario ha sido modificado exitosamente",
                "confirmButtonText" => "aceptar"
            ]);
        } catch (\Throwable $th) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Error en el servidor",
                "text" => "Espera unos minutos e intenta nuevamente",
                "confirmButtonText" => "aceptar"
            ])->withInput($req->except(['password']));
        }
    }

    public function borrarUsuario($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Usuario no encontrado",
                "text" => "El usuario que intentas eliminar no existe",
                "confirmButtonText" => "aceptar"
            ]);
        }

        if (Auth::id() == $usuario->id) {
            return back()->with('alerta', [
                "icon" => "error",
                "title" => "Acción no permitida",
                "text" => "No puedes eliminar tu propio usuario",
                "confirmButtonText" => "aceptar"
            ]);
        }

        try {
            $usuario->delete();

            return back()->with('alerta', [
                "icon" => "success",
                "title" => "Usuario eliminado",
                "text" => "El usuario ha sido eliminado exitosamente",
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
}
