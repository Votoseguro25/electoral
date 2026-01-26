@extends('layouts.bootstrap')

@section('titulo', 'Reportar Voto')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <style>
        :root {
            --primary: #3B82F6;
            --bg-light: #F9FAFB;
            --bg-dark: #1F2937;
            --card-light: #FFFFFF;
            --card-dark: #374151;
            --text-light: #111827;
            --text-dark: #F9FAFB;
            --subtext-light: #6B7280;
            --subtext-dark: #9CA3AF;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }                

        .card-custom {
            background-color: var(--card-light);
            border-radius: 0.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 500px;
            margin: auto;
        }

        .form-control-custom {
            background-color: var(--bg-light);
            border: 1px solid #D1D5DB;
            padding-left: 2.5rem;
        }        

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .btn-primary-custom {
            background-color: var(--primary);
            border: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: #2563EB;
        }

        .text-subtext {
            color: var(--subtext-light);
        }
        
        .icon-input {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--subtext-light);
        }
    </style>
@endsection

@section('contenido')        
    <div class="card-custom p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="h3 fw-bold text-dark-mode">Reportar Voto</h2>
            <p class="text-subtext mt-2">Ingrese el número de documento del votante para registrar su voto.</p>
        </div>
        
        {{-- Formulario para buscar por cédula --}}
        <form action="{{ route('votantes.reportarvoto.buscar') }}" method="POST" id="buscarForm">
            @csrf
            <div class="mb-4">
                <label for="voto" class="form-label text-subtext fw-medium">Documento del votante</label>
                <div class="position-relative">
                    <span class="material-icons icon-input">badge</span>
                    <input type="text" class="form-control form-control-custom" id="voto" name="voto" 
                        placeholder="Escribe el documento del votante" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100 py-3 d-flex align-items-center justify-content-center gap-2 text-white">
                <span class="material-icons mx-2">search</span>                    
                <span>Buscar Votante</span>
            </button>
        </form>
    </div>            
@endsection

@section('scripts')    
    {{-- Mensajes normales --}}
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

    {{-- Confirmación si el votante existe y no ha votado --}}
    @if (session('confirmacion'))
        <script>
            Swal.fire({
                icon: 'question',
                title: 'Confirmar voto',
                text: "¿Registrar voto para {{ session('nombre') }}?",
                showCancelButton: true,
                confirmButtonText: 'Sí, reportar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('votantes.reportarvoto.confirmar') }}";

                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = "{{ csrf_token() }}";

                    const cedula = document.createElement('input');
                    cedula.type = 'hidden';
                    cedula.name = 'cedula';
                    cedula.value = "{{ session('cedula') }}";

                    form.appendChild(token);
                    form.appendChild(cedula);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        </script>
    @endif

    {{-- Limpia el input después de cada búsqueda --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('buscarForm');
            form.addEventListener('submit', function () {
                setTimeout(() => form.reset(), 500);
            });
        });
    </script>
@endsection
