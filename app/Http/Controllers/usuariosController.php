<?php

namespace App\Http\Controllers;

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
        $usuarios = User::orderBy('id', 'desc')->paginate(10);
        $roles = Role::all();
        return view('pages.acceso.registrar', compact('usuarios', 'roles'));
    }

    public function registrarUsuario(Request $req)
    {
        try {
            $userPassword = Hash::make($req->input('password'), [
                'rounds' => 12
            ]);

            User::create([
                'name' => $req->input('name'),
                'email' => $req->input('email'),
                'password' => $userPassword,
                'role_id' => $req->input('role'),
            ]);

            return redirect()->route('usuarios.registrar.vista')->with('alerta', [
                "icon" => "success",
                "title" => "Usuario registrado",
                "text" => "El usuario ha sido registrado exitosamente",
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

            $user->name = $req->input('name_editar');
            $user->email = $req->input('email_editar');
            $user->role_id = $req->input('role_editar');

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
