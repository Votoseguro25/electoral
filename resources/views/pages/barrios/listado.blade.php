@extends('layouts.bootstrap')

@section('titulo', 'Barrios')

@section('css')
    <style>
        div.opciones {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            gap: 8px;
            margin-top: 10px;
        }
    </style>
@endsection

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Barrios</strong>

                <div class="opciones">
                    <button type="button" id="btn-abrir-crear" class="btn btn-primary" data-toggle="modal"
                        data-target="#modalCrear">
                        Agregar barrio
                        <i class="bi bi-plus-circle"></i>
                    </button>
                </div>

                <!-- Modal para registrar -->
                <div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="modalCrearLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCrearLabel">Agregar barrio</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('barrios.agregar') }}" method="post" class="form-horizontal"
                                    id="formulario-agregar">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="nombre-group">
                                                <div class="input-group-addon"><i class="bi bi-alphabet"></i></div>

                                                @php
                                                    $hayErrorNombre = $errors->has('nombre') && session('error_crear');
                                                    $valorNombre = session('error_crear') && !$errors->has('nombre') ? old('nombre') : null;
                                                @endphp

                                                <input type="text" maxlength="255" id="nombre" name="nombre"
                                                    placeholder="Nombre del barrio" value="{{$valorNombre}}"
                                                    class="{{$hayErrorNombre ? "is-invalid" : null}} form-control">
                                            </div>

                                            @if ($hayErrorNombre)
                                                <span class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="departamento-group">
                                                <div class="input-group-addon"><i class="bi bi-globe-americas"></i></div>

                                                @php
                                                    $hayErrorDepartamento = $errors->has('departamento') && session('error_crear');
                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('departamento')) {
                                                        $idSelected = old('departamento');
                                                    }
                                                @endphp

                                                <select name="departamento" id="departamento"
                                                    class="{{$hayErrorDepartamento ? "is-invalid" : null}} form-control">
                                                    <option selected disabled>Departamento</option>
                                                    @foreach ($departamentos as $departamento)
                                                        <option {{$idSelected == $departamento->id ? "selected" : null}}
                                                            value="{{ $departamento->id }}">{{ $departamento->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($hayErrorDepartamento)
                                                <span class="text-danger mt-2">{{$errors->first('departamento')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="municipio-group">
                                                <div class="input-group-addon"><i class="fa-solid fa-location-dot"></i></div>

                                                @php
                                                    $hayErrorMunicipio = $errors->has('municipio') && session('error_crear');
                                                @endphp

                                                <select name="municipio" id="municipio"
                                                    class="{{$hayErrorMunicipio ? "is-invalid" : null}} form-control">
                                                    <option selected disabled>Municipio</option>
                                                </select>
                                            </div>
                                            @if ($hayErrorMunicipio)
                                                <span class="text-danger mt-2">{{$errors->first('municipio')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="corregimiento-group">
                                                <div class="input-group-addon"><i class="fa-solid fa-map-location-dot"></i></div>

                                                @php
                                                    $hayErrorCorregimiento = $errors->has('corregimiento') && session('error_crear');
                                                @endphp

                                                <select name="corregimiento" id="corregimiento"
                                                    class="{{$hayErrorCorregimiento ? "is-invalid" : null}} form-control">
                                                    <option selected disabled>Corregimiento</option>
                                                </select>
                                            </div>
                                            @if ($hayErrorCorregimiento)
                                                <span class="text-danger mt-2">{{$errors->first('corregimiento')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary" form="formulario-agregar">Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin modal para registrar -->

                <div class="card-body">
                    <table id="bootstrap-data-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Corregimiento</th>
                                <th class="text-center">Municipio</th>
                                <th class="text-center">Departamento</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barrios as $barrio)
                                <tr>
                                    <td class="text-nowrap text-center">{{ $barrio->nombre }}</td>
                                    <td class="text-nowrap text-center">{{ $barrio->corregimiento->nombre }}</td>
                                    <td class="text-nowrap text-center">{{ $barrio->corregimiento->municipio->nombre }}</td>
                                    <td class="text-nowrap text-center">{{ $barrio->corregimiento->municipio->departamento->nombre }}</td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">

                                        <button type="button" id="btn-modal-editar-{{$barrio->id}}"
                                            class="btn btn-warning text-white btn-modal-editar" data-toggle="modal"
                                            data-target="#modalEditar-{{$barrio->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEditar-{{$barrio->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEditar-{{$barrio->id}}Label" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEditar-{{$barrio->id}}Label">Editar barrio {{$barrio->nombre}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('barrios.editar', $barrio->id) }}"
                                                            method="post" class="form-horizontal"
                                                            id="formulario-editar-{{$barrio->id}}">
                                                            @method('PUT')
                                                            @csrf
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="nombre-group-{{$barrio->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-alphabet"></i></div>

                                                                        @php
                                                                            $hayErrorNombre = $errors->has('nombre') && (session('edit_error_id') == $barrio->id);
                                                                        @endphp

                                                                        <input type="text" maxlength="255"
                                                                            id="nombre-{{$barrio->id}}" name="nombre"
                                                                            placeholder="Nombre del barrio"
                                                                            value="{{$hayErrorNombre ? old('nombre') : $barrio->nombre}}"
                                                                            class="{{$hayErrorNombre ? "is-invalid" : null}} form-control nombre-editar">
                                                                    </div>
                                                                    @if ($hayErrorNombre)
                                                                        <span class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="departamento-group-{{$barrio->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-globe-americas"></i></div>

                                                                        @php
                                                                            $hayErrorDepartamento = $errors->has('departamento') && (session('edit_error_id') == $barrio->id);
                                                                            
                                                                            $idSelected = -1;

                                                                            if ($hayErrorDepartamento) {
                                                                                $idSelected = $barrio->corregimiento->municipio->departamento_id;
                                                                            } else {
                                                                                $idSelected = old('departamento', $barrio->corregimiento->municipio->departamento_id);
                                                                            }
                                                                        @endphp
        
                                                                        <select name="departamento" id="departamento-{{$barrio->id}}"
                                                                            class="{{$hayErrorDepartamento ? "is-invalid" : null}} form-control departamento-editar">
                                                                            <option selected disabled>Departamento</option>
                                                                            @foreach ($departamentos as $departamento)
                                                                                <option {{$idSelected == $departamento->id ? "selected" : null}} value="{{ $departamento->id }}">
                                                                                    {{ $departamento->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorDepartamento)
                                                                        <span class="text-danger mt-2">{{$errors->first('departamento')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="municipio-group-{{$barrio->id}}">
                                                                        <div class="input-group-addon"><i class="fa-solid fa-location-dot"></i></div>

                                                                        @php
                                                                            $hayErrorMunicipio = $errors->has('municipio') && (session('edit_error_id') == $barrio->id);
                                                                        @endphp

                                                                        <select name="municipio" id="municipio-{{$barrio->id}}"
                                                                            class="{{$hayErrorMunicipio ? "is-invalid" : ""}} form-control municipio-editar"
                                                                            data-barrio-municipio="{{$hayErrorMunicipio ? $barrio->corregimiento->municipio_id : old('municipio', $barrio->corregimiento->municipio_id)}}">
                                                                            <option selected disabled>Municipio</option>
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorMunicipio)
                                                                        <span class="text-danger mt-2">{{$errors->first('municipio')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="corregimiento-group-{{$barrio->id}}">
                                                                        <div class="input-group-addon"><i class="fa-solid fa-map-location-dot"></i></div>

                                                                        @php
                                                                            $hayErrorCorregimiento = $errors->has('corregimiento') && (session('edit_error_id') == $barrio->id);
                                                                        @endphp

                                                                        <select name="corregimiento" id="corregimiento-{{$barrio->id}}"
                                                                            class="{{$hayErrorCorregimiento ? "is-invalid" : ""}} form-control corregimiento-editar"
                                                                            data-barrio-corregimiento="{{$hayErrorCorregimiento ? $barrio->corregimiento_id : old('corregimiento', $barrio->corregimiento_id)}}">
                                                                            <option selected disabled>Corregimiento</option>
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorCorregimiento)
                                                                        <span class="text-danger mt-2">{{$errors->first('corregimiento')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary"
                                                            form="formulario-editar-{{$barrio->id}}">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-danger" data-toggle="modal"
                                            data-target="#modalEliminar-{{$barrio->id}}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEliminar-{{$barrio->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEliminar-{{$barrio->id}}Title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEliminar-{{$barrio->id}}Title">
                                                            Borrar barrio
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="font-weight-bold">¿Estás seguro de que quieres borrar
                                                            el barrio {{$barrio->nombre}}?</span>
                                                        <form action="{{ route('barrios.borrar', $barrio->id) }}"
                                                            method="post" id="formulario-borrar-{{$barrio->id}}">
                                                            @csrf
                                                            @method('delete')
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" form="formulario-borrar-{{$barrio->id}}"
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
                        Mostrando {{ $barrios->count() }} barrios de {{ $barrios->total() }} guardados
                        {{ $barrios->onEachSide(5)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{env('APP_URL')}}/public/resources/js/barrios/correlacion.js?{{ time() }}"></script>
    
    <script>
        @if(session('error_crear'))
            document.getElementById('btn-abrir-crear').click();
            const oldMunicipio = {{ old('municipio', -1)}};
            const oldCorregimiento = {{ old('corregimiento', -1) }};
        @endif
        
        @if(session('edit_error_id'))
            document.addEventListener('DOMContentLoaded', () => {
                const edit_error_id = {{session('edit_error_id', -1)}};
                document.getElementById(`btn-modal-editar-${edit_error_id}`).click();
            })
        @endif
        
        @if (session('alerta'))
            Swal.fire({
                icon: "{{ session('alerta.icon') }}",
                title: "{{ session('alerta.title') }}",
                text: "{{ session('alerta.text') }}",
                confirmButtonText: "{{ session('alerta.confirmButtonText') }}"
            });
        @endif
    </script>
@endsection
