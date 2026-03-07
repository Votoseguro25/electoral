<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">
        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li>
                    <a href="{{route('inicio')}}"><i class="menu-icon fa fa-laptop"></i>Inicio </a>
                </li>
                
                @canAccessRoute('mapa.votantes.registrados')
                <li>
                    <a href="{{route('mapa.votantes.registrados')}}"><i class="menu-icon fa fa-map-marked-alt"></i>Mapa Votantes</a>
                </li>
                @endcanAccessRoute
                
                @canAccessRoute('movilizacion.index')
                <li>
                    <a href="{{route('movilizacion.index')}}"><i class="menu-icon fa fa-bullseye"></i>Movilizacion Dia D</a>
                </li>
                @endcanAccessRoute

                @php
                    $tieneDatosBasicos = auth()->user()?->canAccessRoute('usuarios.registrar.vista') ||
                        auth()->user()?->canAccessRoute('roles.listado') ||
                        auth()->user()?->canAccessRoute('partidos.listado');
                @endphp

                @if($tieneDatosBasicos)
                    <li class="menu-title">Datos básicos</li><!-- /.menu-title -->
                @endif

                @canAccessRoute('usuarios.registrar.vista')
                <li>
                    <a href="{{route('usuarios.registrar.vista')}}"> <i
                            class="menu-icon fa-solid fa-users-gear"></i>Usuarios</a>
                </li>
                @endcanAccessRoute

                @canAccessRoute('roles.listado')
                <li>
                    <a href="{{route('roles.listado')}}"> <i class="menu-icon fa-solid fa-shield-halved"></i>Roles</a>
                </li>
                @endcanAccessRoute

                @canAccessRoute('partidos.listado')
                <li>
                    <a href="{{route('partidos.listado')}}"> <i class="menu-icon fa-solid fa-flag fa-lg"></i> Partidos
                    </a>
                </li>
                @endcanAccessRoute

                @php
                    $tienePersonas = auth()->user()?->canAccessRoute('votantes.listado') ||
                        auth()->user()?->canAccessRoute('lideres.listado') ||
                        auth()->user()?->canAccessRoute('candidatos.listado');
                @endphp

                @if($tienePersonas)
                    <li class="menu-title">Personas</li><!-- /.menu-title -->
                @endif


                @canAccessRoute('votantes.listado')
                <li>
                    <a href="{{route('votantes.listado')}}"> <i class="menu-icon fa-solid fa-person fa-lg"></i> Votantes
                    </a>
                </li>
                @endcanAccessRoute


                @canAccessRoute('lideres.listado')
                <li>
                    <a href="{{route('lideres.listado')}}"> <i class="menu-icon bi bi-person-arms-up fa-lg"></i> Líderes
                    </a>
                </li>
                @endcanAccessRoute

                @canAccessRoute('candidatos.listado')
                <li>
                    <a href="{{route('candidatos.listado')}}"> <i class="menu-icon fa-solid fa-user-tie fa-lg"></i>
                        Candidatos
                    </a>
                </li>
                @endcanAccessRoute

                {{-- <li class="menu-title">Mapas</li><!-- /.menu-title --> --}}

                @php
                    $tieneEstadisticas = auth()->user()?->canAccessRoute('votantes.reportarvoto.vista') ||
                        auth()->user()?->canAccessRoute('reporte.votos') ||
                        auth()->user()?->canAccessRoute('testigos.reportare14') ||
                        auth()->user()?->canAccessRoute('reporte.candidatosCamara') ||
                        auth()->user()?->canAccessRoute('reporte.CandidatosCamaraFiltros') ||
                        auth()->user()?->canAccessRoute('reporte.partidosCamara') ||
                        auth()->user()?->canAccessRoute('testigos.e14.index');
                @endphp

                @if($tieneEstadisticas)
                    <li class="menu-title">Estadisticas</li><!-- /.menu-title -->
                @endif

                @canAccessRoute('votantes.reportarvoto.vista')
                <li>
                    <a href="{{route('votantes.reportarvoto.vista')}}"> <i class="fa-solid fa-check-to-slot"></i>
                        Reportar Voto
                    </a>
                </li>
                @endcanAccessRoute

                @canAccessRoute('reporte.votos')
                <li>
                    <a href="{{route('reporte.votos')}}"> <i class="fa-solid fa-chart-pie"></i>
                        Votos
                    </a>
                </li>
                @endcanAccessRoute

                <!--@canAccessRoute('reporte.candidatos')-->
                <!-- <li>-->
                <!--    <a href="{{route('reporte.candidatos')}}"> <i class="fa-solid  fa-list"></i>-->
                <!--        Votos candidatos-->
                <!--    </a>-->
                <!--</li>-->
                <!--@endcanAccessRoute-->

                <!--@canAccessRoute('reporte.partidos')-->
                <!--<li>-->
                <!--    <a href="{{route('reporte.partidos')}}"> <i class="fa-solid fa-layer-group"></i>-->
                <!--        Votos partidos-->
                <!--    </a>-->
                <!--</li>-->
                <!--@endcanAccessRoute-->

                @canAccessRoute('reporte.candidatosCamara')
                <li>
                    <a href="{{route('reporte.candidatosCamara')}}"> <i class="fa-solid  fa-bar-chart"></i>
                        Resultados
                    </a>
                </li>
                @endcanAccessRoute



                @canAccessRoute('reporte.CandidatosCamaraFiltros')
                <li>
                    <a href="{{route('reporte.CandidatosCamaraFiltros')}}"> <i class="fa-solid  fa-list"></i>
                        Votos Por Puesto/Mesa
                    </a>
                </li>
                @endcanAccessRoute

                @canAccessRoute('reporte.partidosCamara')
                <li>
                    <a href="{{route('reporte.partidosCamara')}}"> <i class="fa-solid fa-layer-group"></i>
                        Votos Por Partido
                    </a>
                </li>
                @endcanAccessRoute



                @canAccessRoute('testigos.reportare14Camara')
                <li>
                    <a href="{{route('testigos.reportare14Camara')}}"> <i class="fa-solid fa-pencil-square-o"></i>
                        Reportar E14
                    </a>
                </li>
                @endcanAccessRoute



                @canAccessRoute('testigos.e14.index')
                <li>
                    <a href="{{ route('testigos.e14.index') }}">
                        <i class="fa-solid fa-clipboard-list"></i> Ver E14
                    </a>
                </li>
                @endcanAccessRoute

            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</aside>