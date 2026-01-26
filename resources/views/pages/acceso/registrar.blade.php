@extends('layouts.bootstrap')

@section('titulo', 'Registrar Usuario')

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Usuarios</strong>

                <!-- Modal para registrar -->
                <button type="button" id="btn-abrir-crear" class="btn btn-primary" data-toggle="modal"
                    data-target="#agregarModal">
                    Agregar usuario
                    <i class="bi bi-person-add"></i>
                </button>

                <div class="modal fade" id="agregarModal" tabindex="-1" role="dialog" aria-labelledby="agregarModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="agregarModalLabel">Agregar un usuario</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('usuarios.registrar') }}" id="form-agregar-usuario" method="post">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-alphabet"></i></div>

                                                <input type="text"
                                                    class="form-control {{ $errors->has('name') ? 'is-invalid' : null }}"
                                                    placeholder="nombre" id="name" name="name" required
                                                    value="{{ old('name') }}" />
                                            </div>

                                            @if ($errors->has('name'))
                                                <div class="text-danger mt-2">
                                                    {{ $errors->first('name') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-envelope"></i></div>
                                                <input type="email"
                                                    class="form-control {{ $errors->has('email') ? 'is-invalid' : null }}"
                                                    placeholder="correo" id="email" name="email" required
                                                    value="{{ old('email') }}" />
                                            </div>

                                            @if ($errors->has('email'))
                                                <div class="text-danger mt-2">
                                                    {{ $errors->first('email') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-lock"></i></div>
                                                <input type="password"
                                                    class="form-control {{ $errors->has('password') ? 'is-invalid' : null }}"
                                                    placeholder="contraseña" id="password" name="password" required>
                                            </div>

                                            @if ($errors->has('password'))
                                                <div class="text-danger mt-2">
                                                    {{ $errors->first('password') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-shield-check"></i></div>
                                                <select name="role" id="role"
                                                    class="form-control {{ $errors->has('role') ? 'is-invalid' : null }}" required>
                                                    <option value="" selected disabled>Seleccione un rol</option>
                                                    @foreach ($roles as $rol)
                                                        <option value="{{ $rol->id }}" {{ old('role') == $rol->id ? 'selected' : '' }}>
                                                            {{ $rol->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @if ($errors->has('role'))
                                                <div class="text-danger mt-2">
                                                    {{ $errors->first('role') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">cerrar</button>
                                <button type="submit" form="form-agregar-usuario" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin modal para registrar -->

                <form method="get" action="#" id="buscador-form" class="d-flex mb-3 mt-3 flex-row">
                    <input type="text" name="consulta" id="buscador" class="form-control" value="{{ $consulta ?? ''}}"
                        placeholder="Escriba nombre o correo">

                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
                <div class="card-body">
                    <table id="bootstrap-data-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Correo</th>
                                <th class="text-center">Rol</th>
                                <th class="text-center">Última vez visto</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $usuario)
                                <tr>
                                    <td class="text-center align-middle">{{ $usuario->name }}</td>
                                    <td class="text-center align-middle">{{ $usuario->email }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-primary">{{ $usuario->role->nombre ?? 'Sin rol' }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        {{ $usuario->last_seen() ?? 'Nunca' }}
                                    </td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">
                                        <button class="btn btn-warning text-white" id="btn-modal-editar-{{$usuario->id}}"
                                            data-toggle="modal" data-target="#modalEditar-{{$usuario->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEditar-{{$usuario->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEditarLabel-{{$usuario->id}}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEditarLabel-{{$usuario->id}}">Editar
                                                            usuario</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('usuarios.editar', $usuario->id) }}"
                                                            id="form-editar-usuario" method="post">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-alphabet"></i></div>
                                                                        @php
                                                                            $nameValue = old('name_editar', $usuario->name);
                                                                        @endphp

                                                                        <input type="text"
                                                                            class="form-control {{ $errors->has('name_editar') ? 'is-invalid' : null }}"
                                                                            placeholder="nombre" id="name_editar"
                                                                            name="name_editar" required
                                                                            value="{{ $nameValue }}" />
                                                                    </div>

                                                                    @if ($errors->has('name_editar'))
                                                                        <div class="text-danger mt-2">
                                                                            {{ $errors->first('name_editar') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-envelope"></i></div>

                                                                        @php
                                                                            $emailValue = old('email_editar', $usuario->email);
                                                                        @endphp
                                                                        <input type="email"
                                                                            class="form-control {{ $errors->has('email_editar') ? 'is-invalid' : null }}"
                                                                            placeholder="correo" id="email_editar"
                                                                            name="email_editar" required
                                                                            value="{{ $emailValue }}" />
                                                                    </div>

                                                                    @if ($errors->has('email_editar'))
                                                                        <div class="text-danger mt-2">
                                                                            {{ $errors->first('email_editar') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-lock"></i></div>
                                                                        <input type="password"
                                                                            class="form-control {{ $errors->has('password_editar') ? 'is-invalid' : null }}"
                                                                            placeholder="contraseña" id="password_editar"
                                                                            name="password_editar">
                                                                    </div>

                                                                    @if ($errors->has('password_editar'))
                                                                        <div class="text-danger mt-2">
                                                                            {{ $errors->first('password_editar') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i class="bi bi-shield-check"></i></div>
                                                                        @php
                                                                            $roleValue = old('role_editar', $usuario->role_id);
                                                                        @endphp
                                                                        <select name="role_editar" id="role_editar"
                                                                            class="form-control {{ $errors->has('role_editar') ? 'is-invalid' : null }}" required>
                                                                            <option value="" disabled>Seleccione un rol</option>
                                                                            @foreach ($roles as $rol)
                                                                                <option value="{{ $rol->id }}" 
                                                                                    {{ $roleValue == $rol->id ? 'selected' : '' }}>
                                                                                    {{ $rol->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    @if ($errors->has('role_editar'))
                                                                        <div class="text-danger mt-2">
                                                                            {{ $errors->first('role_editar') }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">cerrar</button>
                                                        <button type="submit" form="form-editar-usuario"
                                                            class="btn btn-primary">editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn btn-danger" data-toggle="modal"
                                            data-target="#modalEliminar-{{$usuario->id}}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEliminar-{{$usuario->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEliminar-{{$usuario->id}}Title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEliminar-{{$usuario->id}}Title">
                                                            Borrar un usuario
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="font-weight-bold">¿Estás seguro de que quieres borrar
                                                            al usuario {{$usuario->name}}?</span>
                                                        <form action="{{ route('usuarios.borrar', $usuario->id) }}"
                                                            method="post" id="formulario-borrar-{{$usuario->id}}">
                                                            @csrf
                                                            @method('delete')
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" form="formulario-borrar-{{$usuario->id}}"
                                                            class="btn btn-danger">Borrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        Mostrando {{ $usuarios->count() }} usuarios de {{ $usuarios->total() }} guardados
                        {{ $usuarios->onEachSide(5)->links() }}
                    </div>
                </div>
            </div>
        </div>
@endsection

    @section('scripts')
        <script>
            @if(session('error_crear'))
                document.getElementById('btn-abrir-crear').click();
            @endif

            @if (session('alerta'))
                Swal.fire({
                    icon: "{{ session('alerta.icon') }}",
                    title: "{{ session('alerta.title') }}",
                    text: "{{ session('alerta.text') }}",
                    confirmButtonText: "{{ session('alerta.confirmButtonText') }}"
                });
            @endif

            @if(session('edit_error_id'))
                document.addEventListener('DOMContentLoaded', () => {
                    const edit_error_id = {{session('edit_error_id', -1)}};
                    document.getElementById(`btn-modal-editar-${edit_error_id}`).click();
                })
            @endif
        </script>
    @endsection