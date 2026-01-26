@extends('layouts.bootstrap')

@section('titulo', 'lideres')

@section('contenido')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">Lideres</strong>

                <form method="get" action="{{route('lideres.listado')}}" id="buscador" class="d-flex mb-3 mt-3">
                    <input type="text" name="consulta" id="buscador" class="form-control" value="{{ $consulta ?? ''}}"
                        placeholder="Escriba nombre o cédula">

                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
                <div class="card-body">
                    <table id="bootstrap-data-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Cedula</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Telefono</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lideres as $lider)
                                <tr>
                                    <td class="text-nowrap text-center">{{ $lider->cedula }}</td>
                                    <td class="text-nowrap text-center">{{ $lider->nombre }}</td>
                                    <td class="text-nowrap text-center">{{ $lider->telefono }}</td>
                                    <td class="d-flex flex-row justify-content-center acciones-container">
                                        <a href="{{ route('lideres.descargar.votantes', $lider->lider_id) }}" 
                                           class="btn btn-success">
                                            <i class="bi bi-file-earmark-excel"></i> Descargar listado
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        Mostrando {{ $lideres->count() }} lideres de {{ $lideres->total() }} guardados
                        {{ $lideres->onEachSide(5)->links() }}
                    </div>
                </div>
            </div>
        </div>
@endsection

    @section('scripts')
        <script src="./public/resources/js/votantes/busquedaCC.js?ts={{time()}}"></script>
        <script src="./public/resources/js/lider/correlacion.js?ts={{time()}}"></script>

        <script>
            @if(session('error_crear'))
                document.getElementById('btn-abrir-crear').click();

                const oldMunicipio = {{ old('municipio', -1)}};
                const oldCorregimiento = {{ old('corregimiento', -1) }};
                const oldBarrio = {{ old('barrio', -1) }};
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
    @endsection