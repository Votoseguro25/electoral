@extends('layouts.bootstrap')

@section('titulo', 'Roles')

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Roles del Sistema</strong>

                <div class="mb-3">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
                        <i class="bi bi-plus-circle"></i> Agregar Rol
                    </button>


                    {{-- <form action="{{ route('roles.listado') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..."
                                value="{{ $buscar ?? '' }}">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="submit">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                @if($buscar)
                                <a href="{{ route('roles.listado') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                                @endif
                            </div>
                        </div>
                    </form> --}}
                </div>

                <!-- Modal para crear -->
                <div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="modalCrearLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCrearLabel">Crear Rol</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('roles.agregar') }}" id="form-agregar-rol" method="post">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-tag"></i></div>
                                                @php
                                                    $hayErrorNombre = $errors->has('nombre') && session('error_crear');
                                                    $valorNombre = session('error_crear') && !$errors->has('nombre') ? old('nombre') : null;
                                                @endphp
                                                <input type="text" id="nombre" name="nombre" placeholder="Nombre del rol"
                                                    value="{{$valorNombre}}"
                                                    class="{{$hayErrorNombre ? "is-invalid" : null}} form-control" required>
                                            </div>
                                            @if ($hayErrorNombre)
                                                <span class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-card-text"></i></div>
                                                @php
                                                    $hayErrorDescripcion = $errors->has('descripcion') && session('error_crear');
                                                    $valorDescripcion = session('error_crear') && !$errors->has('descripcion') ? old('descripcion') : null;
                                                @endphp
                                                <textarea id="descripcion" name="descripcion" rows="3"
                                                    placeholder="Descripción del rol (opcional)"
                                                    class="{{$hayErrorDescripcion ? "is-invalid" : null}} form-control">{{$valorDescripcion}}</textarea>
                                            </div>
                                            @if ($hayErrorDescripcion)
                                                <span class="text-danger mt-2">{{$errors->first('descripcion')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" form="form-agregar-rol" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin modal para crear -->

                <div class="card-body">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Slug</th>
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Usuarios</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="text-center align-middle">{{ $role->nombre }}</td>
                                    <td class="text-center align-middle"><code>{{ $role->slug }}</code></td>
                                    <td class="text-center align-middle">{{ $role->descripcion ?? 'Sin descripción' }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-info">{{ $role->users()->count() }}</span>
                                    </td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">
                                        <!-- Botón Editar -->
                                        <button class="btn btn-warning text-white" data-toggle="modal"
                                            data-target="#modalEditar-{{$role->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="modalEditar-{{$role->id}}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar Rol</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('roles.editar', $role->id) }}" method="post"
                                                            id="form-editar-{{$role->id}}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i class="bi bi-tag"></i>
                                                                        </div>
                                                                        @php
                                                                            $hayErrorNombre = $errors->has('nombre') && (session('edit_error_id') == $role->id);
                                                                        @endphp
                                                                        <input type="text" name="nombre"
                                                                            placeholder="Nombre del rol"
                                                                            value="{{$hayErrorNombre ? old('nombre') : $role->nombre}}"
                                                                            class="{{$hayErrorNombre ? "is-invalid" : null}} form-control"
                                                                            required>
                                                                    </div>
                                                                    @if ($hayErrorNombre)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-card-text"></i></div>
                                                                        @php
                                                                            $hayErrorDescripcion = $errors->has('descripcion') && (session('edit_error_id') == $role->id);
                                                                        @endphp
                                                                        <textarea name="descripcion" rows="3"
                                                                            placeholder="Descripción"
                                                                            class="{{$hayErrorDescripcion ? "is-invalid" : null}} form-control">{{$hayErrorDescripcion ? old('descripcion') : $role->descripcion}}</textarea>
                                                                    </div>
                                                                    @if ($hayErrorDescripcion)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('descripcion')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" form="form-editar-{{$role->id}}"
                                                            class="btn btn-primary">Guardar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botón Eliminar -->
                                        @if($role->slug !== 'admin')
                                            <button class="btn btn-danger" data-toggle="modal"
                                                data-target="#modalEliminar-{{$role->id}}">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>

                                            <!-- Modal Eliminar -->
                                            <div class="modal fade" id="modalEliminar-{{$role->id}}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Eliminar Rol</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de eliminar el rol
                                                                <strong>{{ $role->nombre }}</strong>?
                                                            </p>
                                                            @if($role->users()->count() > 0)
                                                                <div class="alert alert-warning">
                                                                    Este rol tiene {{ $role->users()->count() }} usuario(s) asignado(s).
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Cancelar</button>
                                                            <form action="{{ route('roles.borrar', $role->id) }}" method="post"
                                                                style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if (session('error_crear'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelector('[data-target="#modalCrear"]').click();
            });
        </script>
    @endif

    @if (session('edit_error_id'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelector('[data-target="#modalEditar-{{ session('edit_error_id') }}"]').click();
            });
        </script>
    @endif

    @if (session('alerta'))
        <script>
            Swal.fire({
                icon: "{{ session('alerta.icon') }}",
                title: "{{ session('alerta.title') }}",
                text: "{{ session('alerta.text') }}",
                confirmButtonText: "{{ session('alerta.confirmButtonText') }}"
            });
        </script>
    @endif
@endsection