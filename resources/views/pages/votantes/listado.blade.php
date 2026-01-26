@extends('layouts.bootstrap')

@section('titulo', 'votantes')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>                        
        div.parent {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 8px;
        }
            

        div.parent > :first-child {
            grid-column: span 2 / span 2;            
        }

        div.opciones {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(1, 1fr);
            gap: 8px;
        }
        
        .input-group .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }
        
        .select2-container--bootstrap4 .select2-selection {
            height: calc(1.5em + 0.75rem + 2px) !important;            
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem) !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        
        .select2-container--open {
            z-index: 9999 !important;
        }

        .select2-dropdown {
            z-index: 9999 !important;
        }
        
        .modal-content .select2-container {
            z-index: 10;
        }

        .modal-content .select2-dropdown {
            z-index: 10001 !important;
        }
        
        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
        }
        
        .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #6c757d;
        }
        
        .input-group > .select2-container {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }

        .modal.show .select2-container--bootstrap4 .select2-dropdown {
            z-index: 1056 !important;
        }

        .select2-container--bootstrap4.select2-container--open .select2-dropdown--below {
            border-top: none;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .select2-container--bootstrap4.select2-container--open .select2-dropdown--above {
            border-bottom: none;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .select2-container--bootstrap4 .select2-selection__clear {
            margin-right: 0.5rem;
        }

        .select2-container--bootstrap4 .select2-results__option {
            padding: 0.375rem 0.75rem;
        }
    </style>
@endsection

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Votantes</strong>

                <div class="opciones">
                    <button type="button" id="btn-abrir-crear" class="btn btn-primary" data-toggle="modal"
                        data-target="#modalCrear">
                        Agregar votante
                        <i class="bi bi-person-add"></i>
                    </button>
    
                    <button type="button" id="btn-abrir-buscador" class="btn btn-primary" data-toggle="modal"
                        data-target="#modalBuscador">
                        Buscar <i class="bi bi-search"></i> 
                    </button>
                </div>


                <!-- Modal para registrar -->
                <div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="modalCrearLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCrearLabel">Agregar votante</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('votantes.agregar') }}" method="post" class="form-horizontal"
                                    id="formulario-agregar">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group position-relative" id="cedula-group">
                                                <div class="input-group-addon"><i class="bi bi-hash"></i></div>
                                                @php
                                                    $hayErrorCedula = $errors->has('cedula') && session('error_crear');
                                                    $valorCedula = session('error_crear') && !$errors->has('cedula') ? old('cedula') : null;
                                                @endphp

                                                <input type="text" maxlength="255" id="cedula" name="cedula"
                                                    placeholder="Cédula" value="{{$valorCedula}}"
                                                    class="{{$hayErrorCedula ? "is-invalid" : null}} form-control">
                                            </div>
                                            @if ($hayErrorCedula)
                                                <span class="text-danger mt-2">{{$errors->first('cedula')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="nombre-group">
                                                <div class="input-group-addon"><i class="bi bi-alphabet"></i></div>

                                                @php
                                                    $hayErrorNombre = $errors->has('nombre') && session('error_crear');
                                                    $valorNombre = session('error_crear') && !$errors->has('nombre') ? old('nombre') : null;
                                                @endphp

                                                <input disabled type="text" maxlength="255" id="nombre" name="nombre"
                                                    placeholder="Nombre completo" value="{{$valorNombre}}"
                                                    class="{{$hayErrorNombre ? "is-invalid" : null}} form-control">
                                            </div>

                                            @if ($hayErrorNombre)
                                                <span class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="telefono-group">
                                                <div class="input-group-addon"><i class="bi bi-phone"></i></div>

                                                @php
                                                    $hayErrorTelefono = $errors->has('telefono') && session('error_crear');
                                                    $valorTelefono = session('error_crear') && !$errors->has('telefono') ? old('telefono') : null;
                                                @endphp

                                                <input type="number" id="telefono" name="telefono" placeholder="Telefono"
                                                    value="{{$valorTelefono}}"
                                                    class="{{$hayErrorTelefono ? "is-invalid" : null}} form-control">
                                            </div>
                                            @if ($hayErrorTelefono)
                                                <span class="text-danger mt-2">{{$errors->first('telefono')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="profesion-group">
                                                <div class="input-group-addon"><i class="bi bi-briefcase"></i></div>

                                                @php
                                                    $hayErrorProfesion = $errors->has('profesion') && session('error_crear');
                                                    $valorProfesion = session('error_crear') && !$errors->has('profesion') ? old('profesion') : null;
                                                @endphp

                                                <input type="text" id="profesion" name="profesion" placeholder="Profesión (Opcional)"
                                                    value="{{$valorProfesion}}"
                                                    class="{{$hayErrorProfesion ? "is-invalid" : null}} form-control">
                                            </div>
                                            @if ($hayErrorProfesion)
                                                <span class="text-danger mt-2">{{$errors->first('profesion')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="genero-group">
                                                <div class="input-group-addon"><i class="bi bi-gender-ambiguous"></i></div>

                                                @php
                                                    $hayErrorGenero = $errors->has('genero') && session('error_crear');

                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('genero')) {
                                                        $idSelected = old('genero');
                                                    }
                                                @endphp

                                                <select name="genero" id="genero"
                                                    class="{{$hayErrorGenero ? "is-invalid" : null}} form-control">
                                                    <option selected disabled>Genero</option>
                                                    @foreach ($generos as $genero)
                                                        <option {{$idSelected == $genero->id ? "selected" : null}}
                                                            value="{{ $genero->id }}">{{ $genero->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($hayErrorGenero)
                                                <span class="text-danger mt-2">{{$errors->first('genero')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="departamento-group">
                                                <div class="input-group-addon"><i class="bi bi-globe-americas"></i>
                                                </div>

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
                                                <div class="input-group-addon"><i class="fa-solid fa-location-dot"></i>
                                                </div>

                                                @php
                                                    $hayErrorMunicipio = $errors->has('municipio') && session('error_crear');                                                                                                       
                                                @endphp

                                                <select name="municipio" id="municipio"
                                                    class="{{$hayErrorMunicipio ? "is-invalid" : null}} form-control">
                                                    <option value="" selected disabled>Municipio</option>
                                                </select>
                                            </div>
                                            @if ($hayErrorMunicipio)
                                                <span class="text-danger mt-2">{{$errors->first('municipio')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="puesto-group">
                                                <div class="input-group-addon"><i class="fa-solid fa-building-user"></i>
                                                </div>
                                                @php
                                                    $hayErrorPuesto = $errors->has('puesto') && session('error_crear');
                                                @endphp

                                                <select name="puesto" id="puesto"
                                                    class="{{ $hayErrorPuesto ? "is-invalid" : null }} form-control">
                                                    <option value="" selected disabled>Puesto de votación</option>
                                                </select>
                                            </div>
                                            @if ($hayErrorPuesto)
                                                <span class="text-danger mt-2">{{$errors->first('puesto')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="mesa-group">
                                                <div class="input-group-addon"><i class="fa-solid fa-person-booth"></i>
                                                </div>

                                                @php
                                                    $hayErrorMesa = $errors->has('mesa') && session('error_crear');
                                                @endphp

                                                <select name="mesa" id="mesa"
                                                    class="{{ $hayErrorMesa ? "is-invalid" : null }} form-control">
                                                    <option value="" selected disabled>Mesa de votación</option>
                                                </select>
                                            </div>
                                            @if ($hayErrorMesa)
                                                <span class="text-danger mt-2">{{$errors->first('mesa')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="compromiso-group">
                                                <div class="input-group-addon"><i class="bi bi-person-heart"></i></div>

                                                @php
                                                    $hayErrorCompromiso = $errors->has('compromiso') && session('error_crear');
                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('compromiso')) {
                                                        $idSelected = old('compromiso');
                                                    }
                                                @endphp

                                                <select name="compromiso" id="compromiso"
                                                    class="{{ $hayErrorCompromiso ? "is-invalid" : null }} form-control">
                                                    <option selected disabled>Compromiso</option>
                                                    @foreach ($compromisos as $compromiso)
                                                        <option {{ $idSelected == $compromiso->id ? "selected" : null }}
                                                            value="{{ $compromiso->id }}">{{ $compromiso->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($hayErrorCompromiso)
                                                <span class="text-danger mt-2">{{$errors->first('compromiso')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="lider-group">
                                                <div class="input-group-addon"><i class="fa fa-user"></i></div>

                                                @php
                                                    $hayErrorLider = $errors->has('lider') && session('error_crear');
                                                    $valorLider = session('error_crear') && !$errors->has('lider') ? old('lider') : null;
                                                @endphp

                                                <select name="lider" id="lider" class="{{ $hayErrorLider ? "is-invalid" : null }} form-control select2-lider">                                                    
                                                    <option selected disabled>--- Quién recomienda ---</option>
                                                    <option value="0">No aplica</option>
                                                    @foreach ($lideres as $lider)
                                                        <option {{ $valorLider == $lider->lider_id ? "selected" : null }} value="{{ $lider->lider_id }}">{{ $lider->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($hayErrorLider)
                                                <span class="text-danger mt-2">{{$errors->first('lider')}}</span>
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

                {{-- modal para buscar --}}            
                <div class="modal fade" id="modalBuscador" tabindex="-1" role="dialog" aria-labelledby="modalBuscadorTitle"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalBuscadorTitle">Buscador</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="get" action="{{route('votantes.listado')}}" id="buscador" class="mb-3 mt-3">
                                    <div class="parent">

                                        <input type="text" name="consulta" class="form-control" value="{{ request('consulta') ?? '' }}"
                                            placeholder="Escriba nombre o cédula">

                                        <select name="departamento" class="form-control">
                                            <option disabled {{ request('departamento') ? null : 'selected' }}>--- Filtro departamento ---</option>
                                            @foreach ($departamentos as $departamento)
                                            <option value="{{ $departamento->id }}" {{ request('departamento')==$departamento->id ?
                                                'selected' : '' }}>
                                                {{ $departamento->nombre }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <select name="municipio" class="form-control" data-municipio-seleccionado="{{ request('municipio') ?? '' }}">
                                            <option disabled {{ request('municipio') ? null : 'selected' }}>--- Filtro municipio ---</option>
                                        </select>

                                        <select name="lider" class="form-control select2-lider-buscador">                                            
                                            <option disabled {{ request('lider') ? null : 'selected' }}>--- Filtro lider ---</option>
                                            @foreach ($lideres as $lider)
                                            <option value="{{ $lider->lider_id }}" {{ request('lider')==$lider->lider_id ? 'selected' :
                                                '' }}>
                                                {{ $lider->nombre }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">                                  
                                <a href="{{ route('votantes.listado') }}" class="btn btn-danger">Quitar filtros</a>
                                <button type="submit" class="btn btn-primary" form="buscador">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- fin modal para buscar --}}
                
                <div class="card-body">
                    <table id="bootstrap-data-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Cedula</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Telefono</th>
                                <th class="text-center">Compromiso</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($votantes as $votante)
                                <tr>
                                    <td class="text-nowrap text-center">{{ $votante->cedula }}</td>
                                    <td class="text-nowrap text-center">{{ $votante->nombre }}</td>
                                    <td class="text-nowrap text-center">{{ $votante->telefono }}</td>
                                    <td class="text-nowrap text-center">{{ $votante->compromiso->nombre}}</td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">

                                        <button type="button" id="btn-modal-editar-{{$votante->id}}"
                                            class="btn btn-warning text-white btn-modal-editar" data-toggle="modal"
                                            data-target="#modalEditar-{{$votante->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEditar-{{$votante->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEditar-{{$votante->id}}Label" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEditar-{{$votante->id}}Label">Editar al
                                                            votante {{$votante->nombre}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('votantes.editar', $votante->persona->id) }}"
                                                            method="post" class="form-horizontal"
                                                            id="formulario-editar-{{$votante->id}}">
                                                            @method('PUT')
                                                            @csrf
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="cedula-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-hash"></i></div>

                                                                        @php
                                                                            $hayErrorCedula = $errors->has('cedula') && (session('edit_error_id') == $votante->id);
                                                                        @endphp

                                                                        <input type="text" maxlength="255"
                                                                            id="cedula-{{$votante->id}}" name="cedula"
                                                                            placeholder="Cédula"
                                                                            value="{{$hayErrorCedula ? $votante->cedula : old('cedula', $votante->cedula)}}"
                                                                            class="{{$hayErrorCedula ? "is-invalid" : null}} form-control cedula-editar">
                                                                    </div>

                                                                    @if ($hayErrorCedula)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('cedula')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="nombre-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-alphabet"></i></div>

                                                                        @php
                                                                            $hayErrorNombre = $errors->has('nombre') && (session('edit_error_id') == $votante->id);
                                                                        @endphp

                                                                        <input type="text" maxlength="255"
                                                                            id="nombre-{{$votante->id}}" name="nombre"
                                                                            placeholder="Nombre completo"
                                                                            value="{{$hayErrorNombre ? old('nombre') : $votante->nombre}}"
                                                                            class="{{$hayErrorNombre ? "is-invalid" : null}} form-control nombre-editar">
                                                                    </div>
                                                                    @if ($hayErrorNombre)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group"
                                                                        id="telefono-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-phone"></i></div>

                                                                        @php
                                                                            $hayErrorTelefono = $errors->has('telefono') && (session('edit_error_id') == $votante->id);
                                                                        @endphp

                                                                        <input type="number" id="telefono-{{$votante->id}}"
                                                                            name="telefono" placeholder="Telefono"
                                                                            value="{{$hayErrorTelefono ? old('telefono') : $votante->telefono}}"
                                                                            class="{{$hayErrorTelefono ? "is-invalid" : null}} form-control telefono-editar">
                                                                    </div>
                                                                    @if ($hayErrorTelefono)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('telefono')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group"
                                                                        id="profesion-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-briefcase"></i></div>

                                                                        @php
                                                                            $hayErrorProfesion = $errors->has('profesion') && (session('edit_error_id') == $votante->id);
                                                                        @endphp

                                                                        <input type="text" id="profesion-{{$votante->id}}"
                                                                            name="profesion" placeholder="Profesión (Opcional)"
                                                                            value="{{$hayErrorProfesion ? old('profesion') : $votante->profesion}}"
                                                                            class="{{$hayErrorProfesion ? "is-invalid" : null}} form-control profesion-editar">
                                                                    </div>
                                                                    @if ($hayErrorProfesion)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('profesion')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="genero-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-gender-ambiguous"></i></div>

                                                                        @php
                                                                            $hayErrorGenero = $errors->has('genero') && (session('edit_error_id') == $votante->id);

                                                                            $idSelected = -1;

                                                                            if ($hayErrorGenero) {
                                                                                $idSelected = $votante->genero->id;
                                                                            } else {
                                                                                $idSelected = old('genero', $votante->genero->id);
                                                                            }                                                                    
                                                                        @endphp

                                                                        <select name="genero" id="genero-{{$votante->id}}"
                                                                            class="{{$hayErrorGenero ? "is-invalid" : null}} form-control genero-editar">
                                                                            <option selected disabled>Género</option>
                                                                            @foreach ($generos as $genero)
                                                                                <option {{$idSelected == $genero->id ? "selected" : null}} value="{{ $genero->id }}">
                                                                                    {{ $genero->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorGenero)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('genero')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group"
                                                                        id="departamento-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-globe-americas"></i></div>

                                                                        @php
                                                                            $hayErrorDepartamento = $errors->has('departamento') && (session('edit_error_id') == $votante->id);
                                                                            
                                                                            $idSelected = -1;

                                                                            if ($hayErrorDepartamento) {
                                                                                $idSelected = $votante->departamento_id;
                                                                            } else {
                                                                                $idSelected = old('departamento', $votante->departamento_id);
                                                                            }
                                                                        @endphp
        
                                                                        <select name="departamento" id="departamento-{{$votante->id}}"
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
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('departamento')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group"
                                                                        id="municipio-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="fa-solid fa-location-dot"></i></div>

                                                                        @php
                                                                            $hayErrorMunicipio = $errors->has('municipio') && (session('edit_error_id') == $votante->id);                                                                            
                                                                        @endphp

                                                                        <select name="municipio" id="municipio-{{$votante->id}}"
                                                                            class="{{$hayErrorMunicipio ? "is-invalid" : ""}} form-control municipio-editar"
                                                                            data-votante-municipio="{{$hayErrorMunicipio ? $votante->municipio_id : old('municipio', $votante->municipio_id)}}">
                                                                            <option value="" selected disabled>Municipio</option>                                                                            
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorMunicipio)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('municipio')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="puesto-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="fa-solid fa-building-user"></i></div>
                                                                        @php
                                                                            $hayErrorPuesto = $errors->has('puesto') && (session('edit_error_id') == $votante->id);
                                                                        @endphp

                                                                        <select name="puesto" id="puesto-{{$votante->id}}"
                                                                            data-votante-puesto="{{$hayErrorPuesto ? $votante->mesa->puesto->id : old('puesto', $votante->mesa->puesto->id)}}"
                                                                            class="{{$hayErrorPuesto ? "is-invalid" : ""}} form-control puesto-editar">
                                                                            <option value="" selected disabled>Puesto de votación</option>
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorPuesto)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('puesto')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="mesa-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="fa-solid fa-person-booth"></i></div>

                                                                        @php
                                                                            $hayErrorMesa = $errors->has('mesa') && (session('edit_error_id') == $votante->id);
                                                                        @endphp

                                                                        <select name="mesa" id="mesa-{{$votante->id}}"
                                                                            data-votante-mesa="{{$hayErrorMesa ? $votante->mesa_id : old('mesa', $votante->mesa_id)}}"
                                                                            class="{{$hayErrorMesa ? "is-invalid" : ""}} form-control mesa-editar">
                                                                            <option value="" selected disabled>Mesa de votación</option>
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorMesa)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('mesa')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group"
                                                                        id="compromiso-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="bi bi-person-heart"></i></div>
                                                                        @php
                                                                            $hayErrorCompromiso = $errors->has('compromiso') && (session('edit_error_id') == $votante->id);

                                                                            if ($hayErrorCompromiso) {
                                                                                $idSelected = $votante->compromiso_id;
                                                                            } else {
                                                                                $idSelected = old('compromiso', $votante->compromiso_id);
                                                                            }                                                                    
                                                                        @endphp

                                                                        <select name="compromiso"
                                                                            id="compromiso-{{$votante->id}}"
                                                                            class="{{$hayErrorCompromiso ? "is-invalid" : null}} form-control compromiso-editar">
                                                                            <option selected disabled>Compromiso</option>
                                                                            @foreach ($compromisos as $compromiso)
                                                                                <option {{$idSelected == $compromiso->id ? "selected" : null}} value="{{ $compromiso->id }}">
                                                                                    {{ $compromiso->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorCompromiso)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('compromiso')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group"
                                                                        id="lider-group-{{$votante->id}}">
                                                                        <div class="input-group-addon"><i
                                                                                class="fa fa-user"></i></div>

                                                                        @php
                                                                            $hayErrorLider = $errors->has('lider') && (session('edit_error_id') == $votante->id);
                                                                            $valorLider = $hayErrorLider ? $votante->lider_id : old('lider', $votante->lider_id);
                                                                        @endphp
                                                                        
                                                                        <select name="lider" id="lider-select-{{$votante->id}}" class="{{$hayErrorLider ? "is-invalid" : null}} form-control select2-lider-editar">
                                                                            <option disabled>--- Quién recomienda ---</option>
                                                                            <option value="0">No aplica</option>
                                                                            @foreach ($lideres as $lider)
                                                                                @if ($lider->persona_id != $votante->persona_id)
                                                                                    <option {{ $valorLider == $lider->lider_id ? "selected" : null }} value="{{ $lider->lider_id }}">{{ $lider->nombre }}</option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorLider)
                                                                        <span
                                                                            class="text-danger mt-2">{{$errors->first('lider')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary"
                                                            form="formulario-editar-{{$votante->id}}">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-danger" data-toggle="modal"
                                            data-target="#modalEliminar-{{$votante->id}}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEliminar-{{$votante->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEliminar-{{$votante->id}}Title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEliminar-{{$votante->id}}Title">
                                                            Borrar un votante
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="font-weight-bold">¿Estás seguro de que quieres borrar
                                                            al votante {{$votante->nombre}}?</span>
                                                        <form action="{{ route('votantes.borrar', $votante->persona->id) }}"
                                                            method="post" id="formulario-borrar-{{$votante->id}}">
                                                            @csrf
                                                            @method('delete')
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" form="formulario-borrar-{{$votante->id}}"
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
                        Mostrando {{ $votantes->count() }} votantes de {{ $votantes->total() }} guardados
                        {{ $votantes->onEachSide(5)->links() }}
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('scripts')                
    <script src="./public/resources/js/votantes/busquedaCC.js?ts={{time()}}"></script>
    <script src="./public/resources/js/votantes/correlacion.js?ts={{time()}}"></script>
    @if (file_exists(public_path('build/manifest.json')))    
        @vite(['resources/js/votantes/correlacion.js','resources/js/votantes/busquedaCC.js',])        
    @endif
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Variables para restaurar valores de búsqueda
        const searchMunicipioValue = "{{ request('municipio', '') }}";
        
        @if(session('error_crear'))
            document.getElementById('btn-abrir-crear').click();
            const oldMunicipio = {{ old('municipio', -1)}};
            const oldPuesto = {{ old('puesto', -1) }};
            const oldMesa = {{ old('mesa', -1) }};
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

    <script>
        // Asegurarse de que jQuery esté disponible
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Configuración común de Select2
                const select2Config = {
                    theme: 'bootstrap4',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        },
                        searching: function() {
                            return "Buscando...";
                        }
                    }
                };

                // Función para inicializar Select2 de forma segura
                function initializeSelect2(selector, customConfig = {}) {
                    const $element = $(selector);
                    
                    if ($element.length === 0) return;
                    
                    // Destruir si ya existe
                    if ($element.hasClass("select2-hidden-accessible")) {
                        $element.select2('destroy');
                    }
                    
                    // Aplicar configuración
                    $element.select2({
                        ...select2Config,
                        ...customConfig
                    });
                }

                // Modal CREAR - Inicializar al abrir
                $('#modalCrear').on('shown.bs.modal', function () {
                    initializeSelect2('#lider', {
                        placeholder: '--- Quién recomienda ---',
                        dropdownParent: $('#modalCrear .modal-content')
                    });
                });

                // Modal BUSCADOR - Inicializar al abrir
                $('#modalBuscador').on('shown.bs.modal', function () {
                    initializeSelect2('.select2-lider-buscador', {
                        placeholder: '--- Filtro líder ---',
                        dropdownParent: $('#modalBuscador .modal-content')
                    });
                });

                // Modales EDITAR - Inicializar dinámicamente al abrir cada modal
                $('[id^="modalEditar-"]').on('shown.bs.modal', function () {
                    const modal = $(this);
                    const votanteId = modal.attr('id').replace('modalEditar-', '');
                    
                    initializeSelect2(`#lider-select-${votanteId}`, {
                        placeholder: '--- Quién recomienda ---',
                        dropdownParent: modal.find('.modal-content')
                    });
                });

                // Limpiar Select2 cuando se cierran los modales
                $('#modalCrear, #modalBuscador, [id^="modalEditar-"]').on('hidden.bs.modal', function () {
                    $(this).find('.select2-lider, .select2-lider-editar, .select2-lider-buscador').each(function() {
                        if ($(this).hasClass("select2-hidden-accessible")) {
                            $(this).select2('destroy');
                        }
                    });
                });

                // Si hay un modal que debe abrirse por error de validación, inicializar Select2 después
                @if(session('error_crear'))
                    setTimeout(function() {
                        initializeSelect2('#lider', {
                            placeholder: '--- Quién recomienda ---',
                            dropdownParent: $('#modalCrear .modal-content')
                        });
                    }, 300);
                @endif

                @if(session('edit_error_id'))
                    setTimeout(function() {
                        const editErrorId = {{ session('edit_error_id', -1) }};
                        const modal = $(`#modalEditar-${editErrorId}`);
                        
                        initializeSelect2(`#lider-select-${editErrorId}`, {
                            placeholder: '--- Quién recomienda ---',
                            dropdownParent: modal.find('.modal-content')
                        });
                    }, 300);
                @endif
            });
        })(jQuery);
    </script>
@endsection