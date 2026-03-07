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
                    <div class="modal-dialog modal-lg" role="document">
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
                                                <input type="text"
                                                    class="form-control {{ $errors->has('email') ? 'is-invalid' : null }}"
                                                    placeholder="usuario de acceso" id="email" name="email" required
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
                                                        <option
                                                            value="{{ $rol->id }}"
                                                            data-slug="{{ $rol->slug }}"
                                                            {{ old('role') == $rol->id ? 'selected' : '' }}>
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

                                    {{-- Estado del usuario --}}
                                    <div class="row form-group">
                                        <div class="col col-md-12">
                                            <div class="d-flex align-items-center">
                                                <div class="input-group-addon mr-2"><i class="bi bi-toggle-on"></i></div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="estado" name="estado" value="1" checked>
                                                    <label class="custom-control-label" for="estado">Usuario habilitado</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Campos adicionales para testigo --}}
                                    <div id="testigo-fields" style="display:none;">
                                        <hr>
                                        <p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> Asignación de mesa para el testigo</p>

                                        {{-- Departamento --}}
                                        <div class="row form-group">
                                            <div class="col col-md-12">
                                                <label class="form-label">Departamento</label>
                                                <select id="departamento_id" name="departamento_id"
                                                    class="form-control {{ $errors->has('departamento_id') ? 'is-invalid' : null }}">
                                                    <option value="" selected disabled>Seleccione departamento</option>
                                                    @foreach ($departamentos as $dep)
                                                        <option value="{{ $dep->id }}"
                                                            {{ old('departamento_id') == $dep->id ? 'selected' : '' }}>
                                                            {{ $dep->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('departamento_id'))
                                                    <div class="text-danger mt-2">{{ $errors->first('departamento_id') }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Municipio --}}
                                        <div class="row form-group">
                                            <div class="col col-md-12">
                                                <label class="form-label">Municipio</label>
                                                <select id="municipio_id" name="municipio_id"
                                                    class="form-control {{ $errors->has('municipio_id') ? 'is-invalid' : null }}" disabled>
                                                    <option value="" selected disabled>Seleccione departamento primero</option>
                                                </select>
                                                @if ($errors->has('municipio_id'))
                                                    <div class="text-danger mt-2">{{ $errors->first('municipio_id') }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Puesto --}}
                                        <div class="row form-group">
                                            <div class="col col-md-12">
                                                <label class="form-label">Puesto de votación</label>
                                                <select id="puesto_id" name="puesto_id"
                                                    class="form-control {{ $errors->has('puesto_id') ? 'is-invalid' : null }}" disabled>
                                                    <option value="" selected disabled>Seleccione municipio primero</option>
                                                </select>
                                                @if ($errors->has('puesto_id'))
                                                    <div class="text-danger mt-2">{{ $errors->first('puesto_id') }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Mesa --}}
                                        <div class="row form-group">
                                            <div class="col col-md-12">
                                                <label class="form-label">Mesa</label>
                                                <select id="mesa_id" name="mesa_id"
                                                    class="form-control {{ $errors->has('mesa_id') ? 'is-invalid' : null }}" disabled>
                                                    <option value="" selected disabled>Seleccione puesto primero</option>
                                                </select>
                                                @if ($errors->has('mesa_id'))
                                                    <div class="text-danger mt-2">{{ $errors->first('mesa_id') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>{{-- /testigo-fields --}}
                                </form>
                            </div>{{-- /modal-body --}}
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
                        placeholder="Escriba nombre o usuario de acceso">

                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
                <div class="card-body">
                    <table id="bootstrap-data-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">usuario de acceso</th>
                                <th class="text-center">Rol</th>
                                <th class="text-center">Estado</th>
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
                                        @if($usuario->estado)
                                            <span class="badge badge-success">Habilitado</span>
                                        @else
                                            <span class="badge badge-danger">Deshabilitado</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        {{ $usuario->last_seen() ?? 'Nunca' }}
                                    </td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">
                                        <button class="btn btn-warning text-white" id="btn-modal-editar-{{$usuario->id}}"
                                            data-toggle="modal" data-target="#modalEditar-{{$usuario->id}}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        @php
                                            $editErrId = session('edit_error_id');
                                            $oldMesaEd = old('mesa_id_editar');
                                            $mEd  = ($editErrId == $usuario->id && $oldMesaEd)
                                                        ? App\Models\Mesa::find($oldMesaEd)
                                                        : $usuario->mesa;
                                            $pEd  = $mEd?->puesto;
                                            $muEd = $pEd?->municipio;
                                            $dEd  = $muEd?->departamento_id;
                                        @endphp
                                        <div class="modal fade" id="modalEditar-{{$usuario->id}}"
                                            data-dep-id="{{ $dEd ?? '' }}"
                                            data-mun-id="{{ $muEd?->id ?? '' }}"
                                            data-puesto-id="{{ $pEd?->id ?? '' }}"
                                            data-mesa-id="{{ $mEd?->id ?? '' }}"
                                            tabindex="-1" role="dialog"
                                            aria-labelledby="modalEditarLabel-{{$usuario->id}}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
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
                                                            id="form-editar-usuario-{{$usuario->id}}" method="post">
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
                                                                        <input type="text"
                                                                            class="form-control {{ $errors->has('email_editar') ? 'is-invalid' : null }}"
                                                                            placeholder="usuario de acceso" id="email_editar"
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
                                                                        <select name="role_editar" id="role_editar_{{$usuario->id}}"
                                                                            class="form-control {{ $errors->has('role_editar') ? 'is-invalid' : null }}" required>
                                                                            <option value="" disabled>Seleccione un rol</option>
                                                                            @foreach ($roles as $rol)
                                                                                <option value="{{ $rol->id }}"
                                                                                    data-slug="{{ $rol->slug }}"
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
                                                            {{-- Estado del usuario --}}
                                            <div class="row form-group">
                                                <div class="col col-md-12">
                                                    <div class="d-flex align-items-center">
                                                        <div class="input-group-addon mr-2"><i class="bi bi-toggle-on"></i></div>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="estado_editar_{{$usuario->id}}"
                                                                name="estado_editar" value="1"
                                                                {{ $usuario->estado ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="estado_editar_{{$usuario->id}}">Usuario habilitado</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Campos de testigo para edición --}}
                                                            <div id="testigo-fields-edit-{{$usuario->id}}" style="display:none;">
                                                                <hr>
                                                                <p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> Asignación de mesa</p>

                                                                {{-- Departamento --}}
                                                                <div class="row form-group">
                                                                    <div class="col col-md-12">
                                                                        <label class="form-label">Departamento</label>
                                                                        <select id="dep_edit_{{$usuario->id}}" class="form-control">
                                                                            <option value="" disabled selected>Seleccione departamento</option>
                                                                            @foreach ($departamentos as $dep)
                                                                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                {{-- Municipio --}}
                                                                <div class="row form-group">
                                                                    <div class="col col-md-12">
                                                                        <label class="form-label">Municipio</label>
                                                                        <select id="mun_edit_{{$usuario->id}}" class="form-control" disabled>
                                                                            <option value="" disabled selected>Seleccione departamento primero</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                {{-- Puesto --}}
                                                                <div class="row form-group">
                                                                    <div class="col col-md-12">
                                                                        <label class="form-label">Puesto de votación</label>
                                                                        <select id="puesto_edit_{{$usuario->id}}" class="form-control" disabled>
                                                                            <option value="" disabled selected>Seleccione municipio primero</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                {{-- Mesa --}}
                                                                <div class="row form-group">
                                                                    <div class="col col-md-12">
                                                                        <label class="form-label">Mesa</label>
                                                                        <select id="mesa_edit_{{$usuario->id}}" name="mesa_id_editar"
                                                                            class="form-control {{ $errors->has('mesa_id_editar') && session('edit_error_id') == $usuario->id ? 'is-invalid' : null }}" disabled>
                                                                            <option value="" disabled selected>Seleccione puesto primero</option>
                                                                        </select>
                                                                        @if ($errors->has('mesa_id_editar') && session('edit_error_id') == $usuario->id)
                                                                            <div class="text-danger mt-2">{{ $errors->first('mesa_id_editar') }}</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>{{-- /testigo-fields-edit --}}
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">cerrar</button>
                                                        <button type="submit" form="form-editar-usuario-{{$usuario->id}}"
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
            // ===== Testigo cascading selects =====
            (function () {
                const roleSelect    = document.getElementById('role');
                const testigoFields = document.getElementById('testigo-fields');
                const depSelect     = document.getElementById('departamento_id');
                const munSelect     = document.getElementById('municipio_id');
                const puestoSelect  = document.getElementById('puesto_id');
                const mesaSelect    = document.getElementById('mesa_id');

                // Cache de municipios (con puestos y mesas anidados) del departamento actual
                let municipiosCache = [];

                function isTestigo() {
                    const opt = roleSelect.options[roleSelect.selectedIndex];
                    return opt && opt.dataset.slug === 'testigo';
                }

                function toggleTestigoFields() {
                    if (isTestigo()) {
                        testigoFields.style.display = 'block';
                        depSelect.setAttribute('required', 'required');
                        munSelect.setAttribute('required', 'required');
                        puestoSelect.setAttribute('required', 'required');
                        mesaSelect.setAttribute('required', 'required');
                    } else {
                        testigoFields.style.display = 'none';
                        depSelect.removeAttribute('required');
                        munSelect.removeAttribute('required');
                        puestoSelect.removeAttribute('required');
                        mesaSelect.removeAttribute('required');
                    }
                }

                function resetSelect(sel, placeholder) {
                    sel.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
                    sel.disabled = true;
                }

                function populateSelect(sel, items, valueKey, labelKey, selectedValue) {
                    sel.innerHTML = '<option value="" disabled selected>Seleccione…</option>';
                    items.forEach(function (item) {
                        const opt = document.createElement('option');
                        opt.value = item[valueKey];
                        opt.textContent = item[labelKey];
                        if (String(item[valueKey]) === String(selectedValue)) {
                            opt.selected = true;
                        }
                        sel.appendChild(opt);
                    });
                    sel.disabled = false;
                }

                // Al seleccionar departamento: UNA sola llamada que trae municipios + puestos + mesas
                depSelect.addEventListener('change', function () {
                    municipiosCache = [];
                    resetSelect(munSelect, 'Cargando…');
                    resetSelect(puestoSelect, 'Seleccione municipio primero');
                    resetSelect(mesaSelect, 'Seleccione puesto primero');
                    const depId = this.value;
                    if (!depId) return;
                    fetch(`{{ config('app.url') }}/departamentos/${depId}/municipios`)
                        .then(r => r.json())
                        .then(function (data) {
                            municipiosCache = data;
                            populateSelect(munSelect, data, 'id', 'nombre', null);
                        });
                });

                // Al seleccionar municipio: usar datos ya en memoria
                munSelect.addEventListener('change', function () {
                    resetSelect(puestoSelect, 'Seleccione…');
                    resetSelect(mesaSelect, 'Seleccione puesto primero');
                    const munId = String(this.value);
                    const municipio = municipiosCache.find(m => String(m.id) === munId);
                    if (!municipio || !municipio.puestos) return;
                    populateSelect(puestoSelect, municipio.puestos, 'id', 'nombre', null);
                });

                // Al seleccionar puesto: usar datos ya en memoria
                puestoSelect.addEventListener('change', function () {
                    resetSelect(mesaSelect, 'Seleccione…');
                    const munId    = String(munSelect.value);
                    const puestoId = String(this.value);
                    const municipio = municipiosCache.find(m => String(m.id) === munId);
                    if (!municipio) return;
                    const puesto = municipio.puestos.find(p => String(p.id) === puestoId);
                    if (!puesto || !puesto.mesas) return;
                    populateSelect(mesaSelect, puesto.mesas, 'id', 'descripcion', null);
                });

                roleSelect.addEventListener('change', toggleTestigoFields);

                // Restaurar estado si hubo errores de validación (old values)
                const oldRole   = '{{ old("role") }}';
                const oldDep    = '{{ old("departamento_id") }}';
                const oldMun    = '{{ old("municipio_id") }}';
                const oldPuesto = '{{ old("puesto_id") }}';
                const oldMesa   = '{{ old("mesa_id") }}';

                if (oldRole) {
                    roleSelect.value = oldRole;
                    toggleTestigoFields();

                    if (isTestigo() && oldDep) {
                        depSelect.value = oldDep;
                        fetch(`{{ config('app.url') }}/departamentos/${oldDep}/municipios`)
                            .then(r => r.json())
                            .then(function (data) {
                                municipiosCache = data;
                                populateSelect(munSelect, data, 'id', 'nombre', oldMun);
                                if (!oldMun) return;
                                const municipio = data.find(m => String(m.id) === String(oldMun));
                                if (!municipio || !municipio.puestos) return;
                                populateSelect(puestoSelect, municipio.puestos, 'id', 'nombre', oldPuesto);
                                if (!oldPuesto) return;
                                const puesto = municipio.puestos.find(p => String(p.id) === String(oldPuesto));
                                if (!puesto || !puesto.mesas) return;
                                populateSelect(mesaSelect, puesto.mesas, 'id', 'descripcion', oldMesa);
                            });
                    }
                }
                // ===== Precarga de ubicación por defecto =====
                // Para desactivar: comentar la línea de llamada al final de este bloque
                function precargarUbicacion({ departamento, municipio }) {
                    // No precargar si hay valores previos (errores de validación)
                    if (oldDep) return;

                    const depOption = Array.from(depSelect.options)
                        .find(o => o.text.trim().toUpperCase() === departamento.toUpperCase());
                    if (!depOption) return;

                    depSelect.value = depOption.value;
                    resetSelect(munSelect, 'Cargando…');
                    resetSelect(puestoSelect, 'Seleccione municipio primero');
                    resetSelect(mesaSelect, 'Seleccione puesto primero');

                    fetch(`{{ config('app.url') }}/departamentos/${depOption.value}/municipios`)
                        .then(r => r.json())
                        .then(function (data) {
                            municipiosCache = data;
                            populateSelect(munSelect, data, 'id', 'nombre', null);

                            if (!municipio) return;
                            const munOption = Array.from(munSelect.options)
                                .find(o => o.text.trim().toUpperCase() === municipio.toUpperCase());
                            if (!munOption) return;
                            munSelect.value = munOption.value;

                            const mun = municipiosCache.find(m => String(m.id) === String(munOption.value));
                            if (mun && mun.puestos) {
                                populateSelect(puestoSelect, mun.puestos, 'id', 'nombre', null);
                            }
                        });
                }

                precargarUbicacion({ departamento: 'CHOCO', municipio: 'QUIBDO' });
                // ===== Fin precarga =====

            })();
            // ===== Fin testigo cascading =====

            // ===== Edit modal: testigo cascading =====
            (function () {
                const appUrl = '{{ config("app.url") }}';

                function resetSelect(sel, placeholder) {
                    sel.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
                    sel.disabled = true;
                }

                function populateSelect(sel, items, valueKey, labelKey, selectedValue) {
                    sel.innerHTML = '<option value="" disabled selected>Seleccione…</option>';
                    items.forEach(function (item) {
                        const opt = document.createElement('option');
                        opt.value = item[valueKey];
                        opt.textContent = item[labelKey];
                        if (String(item[valueKey]) === String(selectedValue)) opt.selected = true;
                        sel.appendChild(opt);
                    });
                    sel.disabled = false;
                }

                document.querySelectorAll('[id^="modalEditar-"]').forEach(function (modal) {
                    const uid        = modal.id.replace('modalEditar-', '');
                    const roleSelect = modal.querySelector(`#role_editar_${uid}`);
                    const fieldsDiv  = modal.querySelector(`#testigo-fields-edit-${uid}`);
                    const depSel     = modal.querySelector(`#dep_edit_${uid}`);
                    const munSel     = modal.querySelector(`#mun_edit_${uid}`);
                    const puestoSel  = modal.querySelector(`#puesto_edit_${uid}`);
                    const mesaSel    = modal.querySelector(`#mesa_edit_${uid}`);
                    let cache        = [];

                    function isTestigoEdit() {
                        const opt = roleSelect.options[roleSelect.selectedIndex];
                        return opt && opt.dataset.slug === 'testigo';
                    }

                    function toggleFields() {
                        if (isTestigoEdit()) {
                            fieldsDiv.style.display = 'block';
                            mesaSel.setAttribute('required', 'required');
                        } else {
                            fieldsDiv.style.display = 'none';
                            mesaSel.removeAttribute('required');
                        }
                    }

                    roleSelect.addEventListener('change', toggleFields);

                    depSel.addEventListener('change', function () {
                        cache = [];
                        resetSelect(munSel, 'Cargando…');
                        resetSelect(puestoSel, 'Seleccione municipio primero');
                        resetSelect(mesaSel, 'Seleccione puesto primero');
                        if (!this.value) return;
                        fetch(`${appUrl}/departamentos/${this.value}/municipios`)
                            .then(r => r.json())
                            .then(function (data) {
                                cache = data;
                                populateSelect(munSel, data, 'id', 'nombre', null);
                            });
                    });

                    munSel.addEventListener('change', function () {
                        resetSelect(puestoSel, 'Seleccione…');
                        resetSelect(mesaSel, 'Seleccione puesto primero');
                        const mun = cache.find(m => String(m.id) === String(this.value));
                        if (mun && mun.puestos) populateSelect(puestoSel, mun.puestos, 'id', 'nombre', null);
                    });

                    puestoSel.addEventListener('change', function () {
                        resetSelect(mesaSel, 'Seleccione…');
                        const mun    = cache.find(m => String(m.id) === String(munSel.value));
                        const puesto = mun && mun.puestos.find(p => String(p.id) === String(this.value));
                        if (puesto && puesto.mesas) populateSelect(mesaSel, puesto.mesas, 'id', 'descripcion', null);
                    });

                    // Pre-cargar al abrir el modal
                    const triggerBtn = document.getElementById(`btn-modal-editar-${uid}`);
                    if (triggerBtn) {
                        triggerBtn.addEventListener('click', function () {
                            toggleFields();
                            if (!isTestigoEdit()) return;

                            const depId    = modal.dataset.depId;
                            const munId    = modal.dataset.munId;
                            const puestoId = modal.dataset.puestoId;
                            const mesaId   = modal.dataset.mesaId;
                            if (!depId) return;

                            depSel.value = depId;
                            resetSelect(munSel, 'Cargando…');
                            resetSelect(puestoSel, 'Seleccione municipio primero');
                            resetSelect(mesaSel, 'Seleccione puesto primero');

                            fetch(`${appUrl}/departamentos/${depId}/municipios`)
                                .then(r => r.json())
                                .then(function (data) {
                                    cache = data;
                                    populateSelect(munSel, data, 'id', 'nombre', munId);
                                    if (!munId) return;
                                    const mun = data.find(m => String(m.id) === String(munId));
                                    if (!mun || !mun.puestos) return;
                                    populateSelect(puestoSel, mun.puestos, 'id', 'nombre', puestoId);
                                    if (!puestoId) return;
                                    const puesto = mun.puestos.find(p => String(p.id) === String(puestoId));
                                    if (!puesto || !puesto.mesas) return;
                                    populateSelect(mesaSel, puesto.mesas, 'id', 'descripcion', mesaId);
                                });
                        });
                    }
                });
            })();
            // ===== Fin edit testigo cascading =====

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