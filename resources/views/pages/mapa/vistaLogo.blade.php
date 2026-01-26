@extends('layouts.bootstrap')

@section('titulo', 'Inicio')

@section('contenido')
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <img src="{{ asset('public/images/logo.png') }}" alt="Logo" class="img-fluid">
    </div>
@endsection