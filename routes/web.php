<?php

use App\Http\Controllers\indexController;
use App\Http\Controllers\municipioController;
use App\Http\Controllers\testigoController;
use App\Http\Controllers\usuariosController;
use App\Http\Controllers\rolesController;
use App\Http\Controllers\LiderController;
use App\Http\Controllers\reporteController;
use App\Http\Controllers\candidatosController;
use App\Http\Controllers\barriosController;
use App\Http\Controllers\partidoController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\validarTestigoReporteCrear;
use App\Http\Middleware\validarUsuariosCrear;
use App\Http\Middleware\validarUsuariosEditar;
use App\Http\Middleware\validarRolesCrear;
use App\Http\Middleware\validarRolesEditar;
use App\Http\Middleware\validarVotantesCrear;
use App\Http\Middleware\validarVotantesEditar;
use App\Http\Middleware\validarCandidatosCrear;
use App\Http\Middleware\validarCandidatosEditar;
use App\Http\Middleware\validarBarriosCrear;
use App\Http\Middleware\validarBarriosEditar;
use App\Http\Middleware\validarPartidosCrear;
use App\Http\Middleware\validarPartidosEditar;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\votantesController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\MovilizacionController;

Route::get('/', [indexController::class, 'index'])->middleware(Authenticate::class)->name('inicio');

Route::get('/AK', [ReporteController::class, 'AK'])->name('AK.index');
Route::post('/gemini/process', [ReporteController::class, 'process'])->name('gemini.process');

Route::middleware('role:testigo,administrador-de-campana')->group(function () {
    Route::get('/reportar/e14', [ReporteController::class, 'crearReporteE14'])->name('testigos.reportare14');
    Route::post('/reportar/e14/guardar', [ReporteController::class, 'guardarReporteE14'])->name('testigos.reportare14.guardar');
    Route::post('/reportar/e14/ia', [ReporteController::class, 'procesarIA'])->name('testigos.reportare14.ia');

    Route::get('/reportar/e14Camara', [ReporteController::class, 'crearReporteE14Camara'])->name('testigos.reportare14Camara');
    Route::post('/reportar/e14/iaCamara', [ReporteController::class, 'procesarIACamara'])->name('testigos.reportare14.iaCamara');
    Route::post('/reportar/e14/guardarCamara', [ReporteController::class, 'guardarReporteE14Camara'])->name('testigos.reportare14.guardarCamara');


    Route::prefix('testigos/e14')->name('testigos.e14.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/listar', [ReporteController::class, 'listar'])->name('listar');
        Route::get('/{id}', [ReporteController::class, 'ver'])->name('ver');
        Route::put('/{id}', [ReporteController::class, 'actualizar'])->name('actualizar');
        Route::delete('/{id}', [ReporteController::class, 'eliminar'])->name('eliminar');
    });
});


Route::prefix('usuarios')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [usuariosController::class, 'loginVista'])->name('usuarios.login.vista');
        Route::post('/login', [usuariosController::class, 'loginUsuario'])->name('usuarios.login');
    });

    Route::middleware(Authenticate::class)->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/registrar', [usuariosController::class, 'registrarVista'])->name('usuarios.registrar.vista');

            Route::post('/registrar', [usuariosController::class, 'registrarUsuario'])
                ->middleware(validarUsuariosCrear::class)
                ->name('usuarios.registrar');

            Route::put('/{id}', [usuariosController::class, 'editarUsuario'])
                ->middleware(validarUsuariosEditar::class)
                ->name('usuarios.editar');

            Route::delete('/{id}', [usuariosController::class, 'borrarUsuario'])->name('usuarios.borrar');
        });

        Route::post('/logout', [usuariosController::class, 'logoutUsuario'])->name('usuarios.logout');
    });
});


Route::middleware([Authenticate::class])->group(function () {

    // Rutas para filtros
    Route::get('/api/camara/departamentos', [ReporteController::class, 'getDepartamentosCamara']);
    Route::get('/api/camara/municipios', [ReporteController::class, 'getMunicipiosCamara']);
    Route::get('/api/camara/puestos', [ReporteController::class, 'getPuestosCamara']);
    Route::get('/api/camara/mesas', [ReporteController::class, 'getMesasCamara']);
    Route::get('/api/reportecandidatosCamara', [ReporteController::class, 'reporteCandidatosCamaraFiltros']);
        
    Route::get('/votos/candidatos-camara', [ReporteController::class, 'CandidatosCamaraFiltros'])->middleware('role:admin,administrador-de-campana')->name('reporte.CandidatosCamaraFiltros');
    

    Route::middleware('role:admin,administrador-de-campana')->group(function () {
        Route::get('/reporteVotos', [reporteController::class, 'reporteVotos'])->name('reporte.votos');
        Route::get('/votos/candidatos', [ReporteController::class, 'Candidatos'])->name('reporte.candidatos');
        Route::get('/api/reporte-candidatos', [ReporteController::class, 'reporteCandidatos']);
        Route::get('/votos/candidatosCamara', [ReporteController::class, 'CandidatosCamara'])->name('reporte.candidatosCamara');
        Route::get('/api/reporte-candidatosCamara', [ReporteController::class, 'reporteCandidatosCamara']);
    });

    Route::get('/votos/partidos', [ReporteController::class, 'Partidos'])->name('reporte.partidos');
    Route::get('/api/reporte-partidos', [ReporteController::class, 'reportePartidos']);

    Route::get('/votos/partidosCamara', [ReporteController::class, 'PartidosCamara'])->middleware('role:admin,administrador-de-campana')->name('reporte.partidosCamara');
    Route::get('/api/reporte-partidosCamara', [ReporteController::class, 'reportePartidosCamara']);

    Route::get('/api/votos', [reporteController::class, 'apiVotos']);
    Route::get('/export-votos', [reporteController::class, 'exportAll'])->name('export.todos');

    Route::prefix('votantes')->group(function () {
        Route::get('/reportarvoto', [votantesController::class, 'reportarVotoVista'])->middleware('role:lider,administrador-de-campana')->name('votantes.reportarvoto.vista');
        Route::middleware('role:admin,administrador-de-campana')->group(function () {
            Route::post('/reportarvoto/buscar', [reporteController::class, 'buscarVotante'])->name('votantes.reportarvoto.buscar');
            Route::post('/reportarvoto/confirmar', [reporteController::class, 'confirmarVoto'])->name('votantes.reportarvoto.confirmar');
        });

        Route::middleware('role:digitador')->group(function () {
            Route::get('/', [votantesController::class, 'listado'])->name('votantes.listado');
            Route::post('/agregar', [votantesController::class, 'guardar'])->middleware(validarVotantesCrear::class)->name('votantes.agregar');
            Route::put('/{id}', [votantesController::class, 'modificar'])->middleware(validarVotantesEditar::class)->name('votantes.editar');
            Route::delete('/{id}', [votantesController::class, 'borrar'])->name('votantes.borrar');
        });
    });

    Route::prefix('lideres')->middleware('role:admin')->group(function () {
        Route::get('/', [LiderController::class, 'listado'])->name('lideres.listado');
        Route::get('/{id}/descargar-votantes', [LiderController::class, 'descargarVotantes'])->name('lideres.descargar.votantes');
    });

    Route::prefix('departamentos')->group(function () {
        Route::get('/{departamentoId}/municipios', [municipioController::class, 'obtenerMunicipios'])->middleware(['throttle:jsons'])->name('municipios.obtenerCorregimientos');
    });

    Route::prefix('municipios')->group(function () {
        Route::get('/{municipioId}/corregimientos', [municipioController::class, 'obtenerCorregimientos'])->middleware(['throttle:jsons'])->name('corregimientos.obtener');
    });



    Route::prefix('candidatos')->middleware('role:admin')->group(function () {
        Route::get('/', [candidatosController::class, 'listado'])->name('candidatos.listado');
        Route::post('/agregar', [candidatosController::class, 'guardar'])->middleware(validarCandidatosCrear::class)->name('candidatos.agregar');
        Route::put('/{id}', [candidatosController::class, 'modificar'])->middleware(validarCandidatosEditar::class)->name('candidatos.editar');
        Route::delete('/{id}', [candidatosController::class, 'borrar'])->name('candidatos.borrar');
    });

    Route::prefix('barrios')->group(function () {
        Route::get('/', [barriosController::class, 'listado'])->name('barrios.listado');
        Route::post('/agregar', [barriosController::class, 'guardar'])->middleware(validarBarriosCrear::class)->name('barrios.agregar');
        Route::put('/{id}', [barriosController::class, 'modificar'])->middleware(validarBarriosEditar::class)->name('barrios.editar');
        Route::delete('/{id}', [barriosController::class, 'borrar'])->name('barrios.borrar');
    });

    Route::prefix('roles')->middleware('role:admin')->group(function () {
        Route::get('/', [rolesController::class, 'listado'])->name('roles.listado');
        Route::post('/agregar', [rolesController::class, 'guardar'])->middleware(validarRolesCrear::class)->name('roles.agregar');
        Route::put('/{id}', [rolesController::class, 'modificar'])->middleware(validarRolesEditar::class)->name('roles.editar');
        Route::delete('/{id}', [rolesController::class, 'borrar'])->name('roles.borrar');
    });

    Route::prefix('partidos')->middleware('role:admin')->group(function () {
        Route::get('/', [partidoController::class, 'listado'])->name('partidos.listado');
        Route::post('/agregar', [partidoController::class, 'guardar'])->middleware(validarPartidosCrear::class)->name('partidos.agregar');
        Route::put('/{id}', [partidoController::class, 'modificar'])->middleware(validarPartidosEditar::class)->name('partidos.editar');
        Route::delete('/{id}', [partidoController::class, 'borrar'])->name('partidos.borrar');
    });



    Route::prefix('mapa')->group(function () {
        Route::get('/resultado', [MapaController::class, 'mostrarResultado']);
        Route::get('/votantes-registrados', [MapaController::class, 'mostrarVotantesRegistrados'])->name('mapa.votantes.registrados');

    });


    Route::prefix('api')->group(function(){
        // Resultados agregados
        Route::get('/resultados/departamentos',          [MapaController::class, 'resultadosPorDepartamento']);
        Route::get('/resultados/municipios',             [MapaController::class, 'resultadosPorMunicipio']);

        // Ganadores
        Route::get('/resultados/departamentos/ganador',  [MapaController::class, 'ganadorPorDepartamento']);
        Route::get('/resultados/municipios/ganador',     [MapaController::class, 'ganadorPorMunicipio']);

        // Resúmenes
        Route::get('/resultados/departamentos/resumen',  [MapaController::class, 'resumenDepartamentos']);
        Route::get('/resultados/municipios/resumen',     [MapaController::class, 'resumenMunicipios']);

        // Votantes registrados
        Route::get('/votantes/departamentos',            [MapaController::class, 'votantesPorDepartamento']);
        Route::get('/votantes/municipios',               [MapaController::class, 'votantesPorMunicipio']);

    });

    // Movilizacion Dia D
    Route::prefix('movilizacion')->group(function () {
        Route::get('/', [MovilizacionController::class, 'index'])->name('movilizacion.index');
        Route::get('/exportar', [MovilizacionController::class, 'exportarPendientes'])->name('movilizacion.exportar');

        // API endpoints
        Route::prefix('api')->group(function () {
            Route::get('/estadisticas', [MovilizacionController::class, 'estadisticas'])->name('movilizacion.api.estadisticas');
            Route::get('/buscar', [MovilizacionController::class, 'buscarPorCedula'])->name('movilizacion.api.buscar');
            Route::post('/confirmar', [MovilizacionController::class, 'confirmarVoto'])->name('movilizacion.api.confirmar');
            Route::post('/revertir', [MovilizacionController::class, 'revertirVoto'])->name('movilizacion.api.revertir');
            Route::get('/pendientes', [MovilizacionController::class, 'pendientes'])->name('movilizacion.api.pendientes');
            Route::get('/lideres', [MovilizacionController::class, 'estadisticasPorLider'])->name('movilizacion.api.lideres');
            Route::get('/mesas/{puesto}', [MovilizacionController::class, 'getMesasPorPuesto'])->name('movilizacion.api.mesas');
        });
    });

});