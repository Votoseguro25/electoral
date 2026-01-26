<header id="header" class="header">
    <div class="top-left">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{route('inicio')}}"><img src="{{env('APP_URL')}}/public/images/logo.png" alt="Logo"></a>
            <a class="navbar-brand hidden" href="{{route('inicio')}}">Logo #2</a>
            <a id="menuToggle" class="menutoggle"><i class="fa fa-bars"></i></a>
        </div>
    </div>
    <div class="top-right">
        <div class="header-menu">
            <div class="header-left">
                <form action="{{route('usuarios.logout')}}" method="POST" class="user-area dropdown float-right">
                    @csrf
                    <button type="submit" class="btn btn-danger">CERRAR SESION</button>
                </form>
            </div>
        </div>
    </div>
</header>