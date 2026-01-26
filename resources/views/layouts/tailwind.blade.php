<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>        
    @yield('css')        
</head>

<body>
    @yield('contenido')
    
    @yield('scripts')
</body>

</html>