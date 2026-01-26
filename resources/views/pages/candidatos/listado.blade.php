@extends('layouts.bootstrap')

@section('titulo', 'Candidatos')

@section('css')
    <style>
        .foto-candidato {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: inline-block;
        }

        div.opciones {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-photo {
            border: none;
            background: none;
            padding: 0;
            cursor: pointer;
        }
    </style>
@endsection

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Candidatos</strong>

                <div class="opciones">
                    <button type="button" id="btn-abrir-crear" class="btn btn-primary" data-toggle="modal"
                        data-target="#modalCrear">
                        Agregar candidato
                        <i class="bi bi-person-add"></i>
                    </button>
                </div>

                <!-- Modal para registrar -->
                <div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-labelledby="modalCrearLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCrearLabel">Agregar candidato</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('candidatos.agregar') }}" method="post" class="form-horizontal"
                                    id="formulario-agregar" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="nombre-group">
                                                <div class="input-group-addon"><i class="bi bi-alphabet"></i></div>

                                                @php
                                                    $hayErrorNombre = $errors->has('nombre') && session('error_crear');
                                                    $valorNombre = session('error_crear') && !$errors->has('nombre') ? old('nombre') : null;
                                                @endphp

                                                <input type="text" maxlength="150" id="nombre" name="nombre"
                                                    placeholder="Nombre del candidato" value="{{$valorNombre}}"
                                                    class="{{$hayErrorNombre ? "is-invalid" : null}} form-control">
                                            </div>

                                            @if ($hayErrorNombre)
                                                <span class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="tarjeton-group">
                                                <div class="input-group-addon"><i class="fa-solid fa-check-to-slot"></i></div>

                                                @php
                                                    $hayErrorTarjeton = $errors->has('Tarjeton') && session('error_crear');
                                                    $valorTarjeton = session('error_crear') && !$errors->has('Tarjeton') ? old('Tarjeton') : null;
                                                @endphp

                                                <input type="number" min="1" id="tarjeton" name="Tarjeton"
                                                    placeholder="Número de tarjetón" value="{{$valorTarjeton}}"
                                                    class="{{$hayErrorTarjeton ? "is-invalid" : null}} form-control">
                                            </div>

                                            @if ($hayErrorTarjeton)
                                                <span class="text-danger mt-2">{{$errors->first('Tarjeton')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="foto-group">
                                                <div class="input-group-addon"><i class="bi bi-image"></i></div>

                                                @php
                                                    $hayErrorFoto = $errors->has('foto') && session('error_crear');
                                                @endphp

                                                <input type="file" id="foto" name="foto" accept="image/*"
                                                    class="{{$hayErrorFoto ? "is-invalid" : null}} form-control">
                                            </div>
                                            @if ($hayErrorFoto)
                                                <span class="text-danger mt-2">{{$errors->first('foto')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="color-group">
                                                <div class="input-group-addon"><i class="bi bi-palette"></i></div>

                                                @php
                                                    $hayErrorColor = $errors->has('color') && session('error_crear');
                                                    $valorColor = session('error_crear') && !$errors->has('color') ? old('color') : '#000000';
                                                @endphp

                                                <input type="color" id="color" name="color" value="{{$valorColor}}"
                                                    class="{{$hayErrorColor ? "is-invalid" : null}} form-control">
                                            </div>
                                            @if ($hayErrorColor)
                                                <span class="text-danger mt-2">{{$errors->first('color')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="partido-group">
                                                <div class="input-group-addon"><i class="bi bi-flag"></i></div>

                                                @php
                                                    $hayErrorPartido = $errors->has('partido') && session('error_crear');
                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('partido')) {
                                                        $idSelected = old('partido');
                                                    }
                                                @endphp

                                                <select name="partido" id="partido"
                                                    class="{{$hayErrorPartido ? "is-invalid" : null}} form-control">
                                                    <option value="0" selected>Sin partido</option>
                                                    @foreach ($partidos as $partido)
                                                        <option {{$idSelected == $partido->id ? "selected" : null}}
                                                            value="{{ $partido->id }}">{{ $partido->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($hayErrorPartido)
                                                <span class="text-danger mt-2">{{$errors->first('partido')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="departamento-group">
                                                <div class="input-group-addon"><i class="bi bi-geo-alt"></i></div>

                                                @php
                                                    $hayErrorDepartamento = $errors->has('departamento') && session('error_crear');
                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('departamento')) {
                                                        $idSelected = old('departamento');
                                                    }
                                                @endphp

                                                <select name="departamento" id="departamento"
                                                    class="{{$hayErrorDepartamento ? "is-invalid" : null}} form-control">
                                                    <option selected disabled>Seleccionar departamento</option>
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
                                                <div class="input-group-addon"><i class="bi bi-building"></i></div>

                                                @php
                                                    $hayErrorMunicipio = $errors->has('municipio') && session('error_crear');
                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('municipio')) {
                                                        $idSelected = old('municipio');
                                                    }
                                                @endphp

                                                <select name="municipio" id="municipio"
                                                    class="{{$hayErrorMunicipio ? "is-invalid" : null}} form-control" disabled>
                                                    <option selected disabled>Seleccionar municipio</option>
                                                </select>
                                            </div>
                                            @if ($hayErrorMunicipio)
                                                <span class="text-danger mt-2">{{$errors->first('municipio')}}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="input-group" id="tipo_eleccion-group">
                                                <div class="input-group-addon"><i class="bi bi-award"></i></div>

                                                @php
                                                    $hayErrorTipoEleccion = $errors->has('tipo_eleccion') && session('error_crear');
                                                    $idSelected = -1;

                                                    if (session('error_crear') && !$errors->has('tipo_eleccion')) {
                                                        $idSelected = old('tipo_eleccion');
                                                    }
                                                @endphp

                                                <select name="tipo_eleccion" id="tipo_eleccion"
                                                    class="{{$hayErrorTipoEleccion ? "is-invalid" : null}} form-control">
                                                    <option selected disabled>Tipo de elección</option>
                                                    @foreach ($tiposEleccion as $tipo)
                                                        <option {{$idSelected == $tipo->id ? "selected" : null}}
                                                            value="{{ $tipo->id }}">{{ $tipo->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($hayErrorTipoEleccion)
                                                <span class="text-danger mt-2">{{$errors->first('tipo_eleccion')}}</span>
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
                                <th class="text-center">Foto</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Tarjetón</th>
                                <th class="text-center">Color</th>
                                <th class="text-center">Partido</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidatos as $candidato)
                                <tr>
                                    <td class="text-center">
                                        @if($candidato->foto)
                                        <button type="button" class="btn-photo" data-toggle="modal" data-target="#modalPhoto-{{ $candidato->id }}">
                                            <img src="{{ env('APP_URL') }}/public/images/candidatos/{{ $candidato->foto }}" 
                                                alt="{{ $candidato->nombre }}" 
                                                class="foto-candidato">
                                        </button>

                                        <div class="modal fade bd-example-modal-lg" id="modalPhoto-{{ $candidato->id }}" tabindex="-1" role="dialog" aria-labelledby="#modalPhoto-{{ $candidato->id }}Title"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">      
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalCenterTitle">{{ $candidato->nombre }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                            <div class="modal-body">
                                                <img src="{{ env('APP_URL') }}/public/images/candidatos/{{ $candidato->foto }}" alt="Foto de {{ $candidato->nombre }}" class="img-fluid">
                                            </div>      
                                            </div>
                                        </div>
                                        </div>
                                        @else
                                            <span class="text-muted">Sin foto</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap text-center">{{ $candidato->nombre ?? 'Sin dato' }}</td>
                                    <td class="text-center">{{ $candidato->Tarjeton ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="color-preview" style="background-color: {{ $candidato->color ?? '#cccccc' }}"></span>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        {{ $candidato->partido->nombre ?? 'Sin dato' }}
                                    </td>
                                    <td class="text-nowrap text-center">{{ $candidato->campanas->first()->tipoEleccion->nombre ?? 'Sin tipo' }}</td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">

                                        <button type="button" id="btn-modal-editar-{{$candidato->id}}"
                                            class="btn btn-warning text-white btn-modal-editar" data-toggle="modal"
                                            data-target="#modalEditar-{{$candidato->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEditar-{{$candidato->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEditar-{{$candidato->id}}Label" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEditar-{{$candidato->id}}Label">Editar candidato {{$candidato->nombre}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('candidatos.editar', $candidato->id) }}"
                                                            method="post" class="form-horizontal"
                                                            id="formulario-editar-{{$candidato->id}}"
                                                            enctype="multipart/form-data">
                                                            @method('PUT')
                                                            @csrf
                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="nombre-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-alphabet"></i></div>

                                                                        @php
                                                                            $hayErrorNombre = $errors->has('nombre') && (session('edit_error_id') == $candidato->id);
                                                                        @endphp

                                                                        <input type="text" maxlength="150"
                                                                            id="nombre-{{$candidato->id}}" name="nombre"
                                                                            placeholder="Nombre del candidato"
                                                                            value="{{$hayErrorNombre ? old('nombre') : $candidato->nombre}}"
                                                                            class="{{$hayErrorNombre ? "is-invalid" : null}} form-control">
                                                                    </div>
                                                                    @if ($hayErrorNombre)
                                                                        <span class="text-danger mt-2">{{$errors->first('nombre')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="tarjeton-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="fa-solid fa-check-to-slot"></i></div>

                                                                        @php
                                                                            $hayErrorTarjeton = $errors->has('Tarjeton') && (session('edit_error_id') == $candidato->id);
                                                                            $valorTarjeton = $hayErrorTarjeton ? old('Tarjeton') : $candidato->Tarjeton;
                                                                        @endphp

                                                                        <input type="number" min="1"
                                                                            id="tarjeton-{{$candidato->id}}" name="Tarjeton"
                                                                            placeholder="Número de tarjetón"
                                                                            value="{{$valorTarjeton}}"
                                                                            class="{{$hayErrorTarjeton ? "is-invalid" : null}} form-control">
                                                                    </div>
                                                                    @if ($hayErrorTarjeton)
                                                                        <span class="text-danger mt-2">{{$errors->first('Tarjeton')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">                                                                    
                                                                    <div class="input-group" id="foto-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-image"></i></div>

                                                                        @php
                                                                            $hayErrorFoto = $errors->has('foto') && (session('edit_error_id') == $candidato->id);
                                                                        @endphp

                                                                        <input type="file" id="foto-{{$candidato->id}}" name="foto" accept="image/*"
                                                                            class="{{$hayErrorFoto ? "is-invalid" : null}} form-control">
                                                                    </div>
                                                                    @if ($hayErrorFoto)
                                                                        <span class="text-danger mt-2">{{$errors->first('foto')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="color-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-palette"></i></div>

                                                                        @php
                                                                            $hayErrorColor = $errors->has('color') && (session('edit_error_id') == $candidato->id);
                                                                            $valorColor = $hayErrorColor ? old('color') : ($candidato->color ?? '#000000');
                                                                        @endphp

                                                                        <input type="color" id="color-{{$candidato->id}}" name="color"
                                                                            value="{{$valorColor}}"
                                                                            class="{{$hayErrorColor ? "is-invalid" : null}} form-control">
                                                                    </div>
                                                                    @if ($hayErrorColor)
                                                                        <span class="text-danger mt-2">{{$errors->first('color')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="partido-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-flag"></i></div>

                                                                        @php
                                                                            $hayErrorPartido = $errors->has('partido') && (session('edit_error_id') == $candidato->id);
                                                                            $idSelected = $hayErrorPartido ? old('partido') : $candidato->partido_id;
                                                                        @endphp

                                                                        <select name="partido" id="partido-{{$candidato->id}}"
                                                                            class="{{$hayErrorPartido ? "is-invalid" : null}} form-control">
                                                                            <option value="0" {{$idSelected === null ? "selected" : null}}>Sin partido</option>
                                                                            @foreach ($partidos as $partido)
                                                                                <option {{$idSelected == $partido->id ? "selected" : null}}
                                                                                    value="{{ $partido->id }}">{{ $partido->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorPartido)
                                                                        <span class="text-danger mt-2">{{$errors->first('partido')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="departamento-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-geo-alt"></i></div>

                                                                        @php
                                                                            $hayErrorDepartamento = $errors->has('departamento') && (session('edit_error_id') == $candidato->id);
                                                                            $idSelected = $hayErrorDepartamento ? old('departamento') : ($candidato->campanas->first()->departamento_id ?? null);
                                                                        @endphp

                                                                        <select name="departamento" id="departamento-{{$candidato->id}}"
                                                                            class="{{$hayErrorDepartamento ? "is-invalid" : null}} form-control departamento-edit"
                                                                            data-candidato-id="{{$candidato->id}}">
                                                                            <option selected disabled>Seleccionar departamento</option>
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
                                                                    <div class="input-group" id="municipio-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-building"></i></div>

                                                                        @php
                                                                            $hayErrorMunicipio = $errors->has('municipio') && (session('edit_error_id') == $candidato->id);
                                                                            $idSelected = $hayErrorMunicipio ? old('municipio') : ($candidato->campanas->first()->municipio_id ?? null);
                                                                        @endphp

                                                                        <select name="municipio" id="municipio-{{$candidato->id}}"
                                                                            class="{{$hayErrorMunicipio ? "is-invalid" : null}} form-control" 
                                                                            data-municipio-selected="{{$idSelected}}" disabled>
                                                                            <option selected disabled>Seleccionar municipio</option>
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorMunicipio)
                                                                        <span class="text-danger mt-2">{{$errors->first('municipio')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row form-group">
                                                                <div class="col col-md-12">
                                                                    <div class="input-group" id="tipo_eleccion-group-{{$candidato->id}}">
                                                                        <div class="input-group-addon"><i class="bi bi-award"></i></div>

                                                                        @php
                                                                            $hayErrorTipoEleccion = $errors->has('tipo_eleccion') && (session('edit_error_id') == $candidato->id);
                                                                            $idSelected = $hayErrorTipoEleccion ? old('tipo_eleccion') : ($candidato->campanas->first()->tipo_eleccion_id ?? null);
                                                                        @endphp

                                                                        <select name="tipo_eleccion" id="tipo_eleccion-{{$candidato->id}}"
                                                                            class="{{$hayErrorTipoEleccion ? "is-invalid" : null}} form-control">
                                                                            <option selected disabled>Tipo de elección</option>
                                                                            @foreach ($tiposEleccion as $tipo)
                                                                                <option {{$idSelected == $tipo->id ? "selected" : null}}
                                                                                    value="{{ $tipo->id }}">{{ $tipo->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($hayErrorTipoEleccion)
                                                                        <span class="text-danger mt-2">{{$errors->first('tipo_eleccion')}}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary"
                                                            form="formulario-editar-{{$candidato->id}}">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-danger" data-toggle="modal"
                                            data-target="#modalEliminar-{{$candidato->id}}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>

                                        <div class="modal fade" id="modalEliminar-{{$candidato->id}}" tabindex="-1" role="dialog"
                                            aria-labelledby="modalEliminar-{{$candidato->id}}Title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalEliminar-{{$candidato->id}}Title">
                                                            Borrar candidato
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="font-weight-bold">¿Estás seguro de que quieres borrar
                                                            al candidato {{$candidato->nombre}}?</span>
                                                        <form action="{{ route('candidatos.borrar', $candidato->id) }}"
                                                            method="post" id="formulario-borrar-{{$candidato->id}}">
                                                            @csrf
                                                            @method('delete')
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="submit" form="formulario-borrar-{{$candidato->id}}"
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
                        Mostrando {{ $candidatos->count() }} candidatos de {{ $candidatos->total() }} guardados
                        {{ $candidatos->onEachSide(5)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Función para cargar municipios
        function cargarMunicipios(departamentoId, municipioSelect, municipioIdSelected = null) {
            if (!departamentoId) {
                municipioSelect.disabled = true;
                municipioSelect.innerHTML = '<option selected disabled>Seleccionar municipio</option>';
                return;
            }

            // Hacer petición AJAX
            fetch(`./departamentos/${departamentoId}/municipios`)
                .then(response => response.json())
                .then(data => {
                    municipioSelect.innerHTML = '<option selected disabled>Seleccionar municipio</option>';
                    
                    data.forEach(municipio => {
                        const option = document.createElement('option');
                        option.value = municipio.id;
                        option.textContent = municipio.nombre;
                        
                        if (municipioIdSelected && municipio.id == municipioIdSelected) {
                            option.selected = true;
                        }
                        
                        municipioSelect.appendChild(option);
                    });
                    
                    municipioSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error al cargar municipios:', error);
                    municipioSelect.innerHTML = '<option selected disabled>Error al cargar municipios</option>';
                });
        }

        // Evento para el select de departamento en formulario de crear
        document.getElementById('departamento').addEventListener('change', function() {
            const municipioSelect = document.getElementById('municipio');
            cargarMunicipios(this.value, municipioSelect);
        });

        // Eventos para los selects de departamento en formularios de editar
        document.querySelectorAll('.departamento-edit').forEach(select => {
            select.addEventListener('change', function() {
                const candidatoId = this.getAttribute('data-candidato-id');
                const municipioSelect = document.getElementById(`municipio-${candidatoId}`);
                cargarMunicipios(this.value, municipioSelect);
            });

            // Cargar municipios al abrir modal de edición si hay departamento seleccionado
            const candidatoId = select.getAttribute('data-candidato-id');
            document.getElementById(`btn-modal-editar-${candidatoId}`).addEventListener('click', function() {
                setTimeout(() => {
                    const departamentoSelect = document.getElementById(`departamento-${candidatoId}`);
                    const departamentoId = departamentoSelect.value;
                    const municipioSelect = document.getElementById(`municipio-${candidatoId}`);
                    const municipioIdSelected = municipioSelect.getAttribute('data-municipio-selected');
                    
                    if (departamentoId && departamentoId !== 'Seleccionar departamento') {
                        cargarMunicipios(departamentoId, municipioSelect, municipioIdSelected);
                    }
                }, 100);
            });
        });

        @if(session('error_crear'))
            document.getElementById('btn-abrir-crear').click();
            
            // Cargar municipios si hay departamento seleccionado en error de crear
            const departamentoCrear = document.getElementById('departamento').value;
            if (departamentoCrear && departamentoCrear !== 'Seleccionar departamento') {
                const municipioSelect = document.getElementById('municipio');
                cargarMunicipios(departamentoCrear, municipioSelect, {{ old('municipio', 'null') }});
            }
        @endif
        
        @if(session('edit_error_id'))
            document.addEventListener('DOMContentLoaded', () => {
                const edit_error_id = {{session('edit_error_id', -1)}};
                document.getElementById(`btn-modal-editar-${edit_error_id}`).click();
                
                // Cargar municipios si hay departamento seleccionado en error de editar
                const departamentoEdit = document.getElementById(`departamento-${edit_error_id}`).value;
                if (departamentoEdit && departamentoEdit !== 'Seleccionar departamento') {
                    const municipioSelect = document.getElementById(`municipio-${edit_error_id}`);
                    cargarMunicipios(departamentoEdit, municipioSelect, {{ old('municipio', 'null') }});
                }
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
