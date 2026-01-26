<?php

namespace App\Http\Middleware;

use App\Models\Persona;
use App\Rules\correlacion;
use App\Rules\liderDiferente;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Validation\Rule;

class validarVotantesEditar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route()->parameter('id');

        if ($request->has('lider') && $request->input('lider') === '0') {
            $request->merge(['lider' => null]);
        }

        $existe = Persona::where('id', $id)->exists();

        if (!$existe) {
            return back()
                ->withInput()
                ->with("edit_error_id", $id);
        }

        $reglas = [
            "nombre" => "required|string|max:255",
            "cedula" => [
                "required",
                "string",
                "max:255",
                Rule::unique('personas')->ignore($id)
            ],
            "telefono" => "required|string|max:10",
            "profesion" => "nullable|string|max:255",
            "departamento" => "required|integer|exists:departamentos,id",
            "municipio" => [
                "required",
                "integer",
                "exists:municipios,id",
                new correlacion(
                    'municipios',
                    'departamento_id',
                    $request->input('departamento'),
                    'El municipio no pertenece al departamento seleccionado'
                )
            ],
            "puesto" => [
                "required",
                "integer",
                new correlacion(
                    'puestos',
                    'municipio_id',
                    $request->input('municipio'),
                    'El puesto no pertenece al municipio seleccionado'
                )
            ],
            "mesa" => [
                "required",
                "integer",
                new correlacion(
                    'mesas',
                    'puesto_id',
                    $request->input('puesto'),
                    'La mesa no pertenece al puesto seleccionado'
                )
            ],
            "compromiso" => "required|integer|exists:compromisos,id",
            "lider" => [
                "nullable",
                "integer",
                "exists:lideres,id",
                new liderDiferente($id)
            ],
            "genero" => "required|integer|exists:generos,id",
        ];


        // Mensajes de error personalizados
        $mensajesNombre = [
            "nombre.required" => "El campo nombre es obligatorio.",
            "nombre.string" => "El campo nombre debe ser un texto.",
            "nombre.max" => "El campo nombre no debe exceder los 255 caracteres."
        ];

        $mensajesCedula = [
            "cedula.required" => "El campo cédula es obligatorio.",
            "cedula.string" => "El campo cédula debe ser una cadena de texto.",
            "cedula.max" => "El campo cédula no debe exceder los 255 caracteres.",
            "cedula.unique" => "La cédula ya está registrada."
        ];

        $mensajesTelefono = [
            "telefono.required" => "El campo teléfono es obligatorio.",
            "telefono.string" => "El campo teléfono debe ser una cadena de texto.",
            "telefono.max" => "El campo teléfono no debe exceder los 10 digitos.",
        ];

        $mensajesProfesion = [
            "profesion.string" => "El campo profesión debe ser una cadena de texto.",
            "profesion.max" => "El campo profesión no debe exceder los 255 caracteres.",
        ];

        $mensajesDepartamento = [
            "departamento.required" => "El campo departamento es obligatorio.",
            "departamento.integer" => "El campo departamento debe ser uno de la lista.",
            "departamento.exists" => "El departamento seleccionado no es válido.",
        ];

        $mensajesMunicipio = [
            "municipio.required" => "El campo municipio es obligatorio.",
            "municipio.integer" => "El campo municipio debe ser uno de la lista.",
            "municipio.exists" => "El municipio seleccionado no es válido.",
        ];

        $mensajesPuesto = [
            "puesto.required" => "El campo puesto es obligatorio.",
            "puesto.integer" => "El campo puesto debe ser uno de la lista.",
        ];

        $mensajesMesa = [
            "mesa.required" => "El campo mesa es obligatorio.",
            "mesa.integer" => "El campo mesa debe ser uno de la lista.",
        ];

        $mensajesCompromiso = [
            "compromiso.required" => "El campo compromiso es obligatorio.",
            "compromiso.integer" => "El campo compromiso debe ser uno de la lista.",
            "compromiso.exists" => "El compromiso seleccionado no es válido.",
        ];

        $mensajesLider = [
            "lider.integer" => "El campo líder debe ser uno de la lista.",
            "lider.exists" => "Seleccionaste un líder que no existe."
        ];

        $mensajesGenero = [
            "genero.required" => "El campo género es obligatorio.",
            "genero.integer" => "El campo género debe ser uno de la lista.",
            "genero.exists" => "El género seleccionado no es válido.",
        ];

        $mensajes = [
            ...$mensajesNombre,
            ...$mensajesCedula,
            ...$mensajesTelefono,
            ...$mensajesProfesion,
            ...$mensajesDepartamento,
            ...$mensajesMunicipio,
            ...$mensajesPuesto,
            ...$mensajesMesa,
            ...$mensajesCompromiso,
            ...$mensajesLider,
            ...$mensajesGenero
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
