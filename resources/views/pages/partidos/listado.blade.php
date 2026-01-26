@extends('layouts.bootstrap')

@section('titulo', 'Partidos')

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Partidos Políticos</strong>

                <div class="mb-3">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
                        <i class="bi bi-plus-circle"></i> Agregar Partido
                    </button>

                    {{-- <form action="{{ route('partidos.listado') }}" method="GET" class="form-inline mt-2">
                        <div class="input-group">
                            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..."
                                value="{{ $buscar ?? '' }}">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="submit">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                @if($buscar)
                                <a href="{{ route('partidos.listado') }}" class="btn btn-secondary">
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
                                <h5 class="modal-title" id="modalCrearLabel">Crear Partido</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('partidos.agregar') }}" id="form-agregar-partido" method="post">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="bi bi-flag"></i></div>
                                                @php
                                                    $hayErrorNombre = $errors->has('nombre') && session('error_crear');
                                                    $valorNombre = session('error_crear') && !$errors->has('nombre') ? old('nombre') : null;
                                                @endphp
                                                <input type="text" id="nombre" name="nombre" placeholder="Nombre del partido"
                                                    maxlength="100"
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
                                            <div class="form-check">
                                                @php
                                                    $hayErrorEstado = $errors->has('estado') && session('error_crear');
                                                    $valorEstado = session('error_crear') && !$errors->has('estado') ? old('estado') : true;
                                                @endphp
                                                <input type="checkbox" id="estado" name="estado" value="1"
                                                    class="{{$hayErrorEstado ? "is-invalid" : null}} form-check-input"
                                                    {{$valorEstado ? 'checked' : ''}}>
                                                <label class="form-check-label" for="estado">
                                                    Activo
                                                </label>
                                            </div>
                                            @if ($hayErrorEstado)
                                                <span class="text-danger mt-2">{{$errors->first('estado')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" form="form-agregar-partido" class="btn btn-primary">Guardar</button>
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
                                <th class="text-center">Estado</th>
                                <th class="text-center">Candidatos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partidos as $partido)
                                <tr>
                                    <td class="text-center align-middle">{{ $partido->nombre }}</td>
                                    <td class="text-center align-middle">
                                        @if($partido->estado)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-info">{{ $partido->candidatos()->count() }}</span>
                                    </td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">
                                        <!-- Botón Editar -->
                                        <button class="btn btn-warning text-white" data-toggle="modal"
                                            data-target="#modalEditar-{{$partido->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="modalEditar-{{$partido->id}}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar Partido</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('partidos.editar', $partido->id) }}" method="post"
                                                            id="form-editar-{{$partido->id}}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon"><i class="bi bi-flag"></i>
                                                                        </div>
                                                                        @php
                                                                            $hayErrorNombre = $errors->has('nombre') && (session('edit_error_id') == $partido->id);
                                                                        @endphp
                                                                        <input type="text" name="nombre"
                                                                            placeholder="Nombre del partido"
                                                                            maxlength="100"
                                                                            value="{{$hayErrorNombre ? old('nombre') : $partido->nombre}}"
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
                                                                    <div class="form-check">
                                                                        @php
                                                                            $hayErrorEstado = $errors->has('estado') && (session('edit_error_id') == $partido->id);
                                                                            $valorEstado = $hayErrorEstado ? old('estado') : $partido->estado;
                                                                        @endphp
                                                                        <input type="checkbox" name="estado" value="1"
                                                                            id="estado-{{$partido->id}}"
                                                                            class="{{$hayErrorEstado ? "is-invalid" : null}} form-check-input"
                                                                            {{$valorEstado ? 'checked' : ''}}>
                                                                        <label class="form-check-label" for="estado-{{$partido->id}}">
                                                                            Activo
                                                                        </label>
                                                                    </div>
                                                                    @if ($hayErrorEstado)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('estado')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" form="form-editar-{{$partido->id}}"
                                                            class="btn btn-primary">Guardar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botón Eliminar -->
                                        <button class="btn btn-danger" data-toggle="modal"
                                            data-target="#modalEliminar-{{$partido->id}}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>

                                        <!-- Modal Eliminar -->
                                        <div class="modal fade" id="modalEliminar-{{$partido->id}}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Eliminar Partido</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Estás seguro de eliminar el partido
                                                            <strong>{{ $partido->nombre }}</strong>?
                                                        </p>
                                                        @if($partido->candidatos()->count() > 0)
                                                            <div class="alert alert-warning">
                                                                Este partido tiene {{ $partido->candidatos()->count() }} candidato(s) asignado(s).
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <form action="{{ route('partidos.borrar', $partido->id) }}" method="post"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                                        </form>
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
                        Mostrando {{ $partidos->count() }} partidos de {{ $partidos->total() }} guardados
                        {{ $partidos->onEachSide(5)->links() }}
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
