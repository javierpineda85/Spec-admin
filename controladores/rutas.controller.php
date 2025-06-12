<?php
// Defino la clase Rutas para mejor funcionamiento
class RutasController
{
    public static function cargarVista()
    {
        $public = [
            'login/index',
            'plantillas/crtGetPlantilla',
            'plantillas/crtGetLogin',
            'cerrar_sesion',
            'acceso_denegado/index',
            'inicio/index',
        ];
        // ========= RUTAS QUE LLAMAN A MÉTODOS =========

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        // Registrar escaneo de ronda (AJAX o GET)
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
            EscaneosController::registrar();
            return;
        }
        // Gestión de permisos
        if (isset($_GET['r']) && ($_GET['r'] === 'permisos' || $_GET['r'] === 'permisos/index')) {
            PermisosController::index();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'permisos/update') {
            PermisosController::update();
            return;
        }
        // Mostrar QR dinámico
        if (isset($_GET['r']) && $_GET['r'] === 'mostrar_qr') {
            QrController::mostrar();
            return;
        }

        // Eliminar QR de sesión y draft
        if (isset($_GET['r']) && $_GET['r'] === 'delete_qr') {
            QrController::delete();
            return;
        }

        // Generar QR (crea draft en BD y en sesión)
        if (isset($_GET['r']) && $_GET['r'] === 'generar_qr') {
            QrController::generar();
            return;
        }

        // Vista para escanear con cámara
        if (isset($_GET['r']) && $_GET['r'] === 'escanear') {
            RondasController::vistaEscanearRondas();
            return;
        }

        // Vista de reporte hombre vivo (timer)
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_hombre_vivo') {
            HombreVivoController::vistaHombreVivo();
            return;
        }

        // Vista de Listado Reportes H VIVO
        if (isset($_GET['r']) && $_GET['r'] === 'listado_reportes') {
            HombreVivoController::vistaListadoReportesHombreVivo();
            return;
        }


        // Endpoint AJAX para registrar reporte hombre vivo
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_reporte') {
            //require_once __DIR__ . '/hvivo.controller.php';
            HombreVivoController::registrar();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'ajax_rondas') {
            require_once __DIR__ . '/../libraries/ajax/ajax_rondas.php';
            exit;
        }
        // Actualizar ronda (formulario de edición)
        if (isset($_GET['r']) && $_GET['r'] === 'actualizar_ronda') {
            RondasController::crtActualizarRonda();
            return;
        }

        // Desactivar ronda (listado)
        if (isset($_GET['r']) && $_GET['r'] === 'desactivar_ronda') {
            // require_once __DIR__ . '/rondas.controller.php';
            RondasController::crtDesactivarRonda(intval($_POST['idEliminar'] ?? 0));
            return;
        }

        // Rutas de búsqueda de cronogramas (ejemplo)
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_cronogramas') {
            ControladorTurnos::crtBuscarTurnosPorRango();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'buscar_porVigilador') {
            ControladorTurnos::crtBuscarPorVigilador();
            return;
        }

        //Crear Cronograma
        if (isset($_GET['r']) && $_GET['r'] === 'crear_cronograma') {
            ControladorCronograma::vistaCrearCronograma();
            return;
        }
        //Listado Cronogramas
        if (isset($_GET['r']) && $_GET['r'] === 'listado_cronogramas') {
            ControladorCronograma::vistaListadoCronogramas();
            return;
        }
        //Listado Cronogramas x vigilador VISTA
        if (isset($_GET['r']) && $_GET['r'] === 'listado_porVigilador') {
            ControladorCronograma::vistaListadoCronogramaPorVigilador();
            return;
        }
        //Listado Vista Jornadas por objetivo
        if (isset($_GET['r']) && $_GET['r'] === 'listado_resumen_diario') {
            ControladorCronograma::vistaJornadasPorObjetivo();
            return;
        }
        //Horas por Vigilador
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_porVigilador') {
            ControladorCronograma::vistaHorasPorVigilador();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_diario') {
            ControladorCronograma::crtBuscarResumenDiario();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas') {
            ControladorCronograma::crtBuscarResumenHoras();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas_por_vigilador') {
            ControladorCronograma::crtBuscarResumenHorasPorVigilador();
            return;
        }

        // Procesar el POST de “Horas por Objetivo”
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_porHoras' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorCronograma::crtBuscarResumenHoras();
            return;
        }

        // Mostrar el formulario / resultado (GET)
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_porHoras') {
            ControladorCronograma::vistaReporteHorasPorObjetivo();
            return;
        }

        //Vista crear directivas
        if (isset($_GET['r']) && $_GET['r'] === 'vistaCrearDirectiva') {
            ControladorDirectivas::vistaCrearDirectiva();
            return;
        }
        //Vista editar directivas
        if (isset($_GET['r']) && $_GET['r'] === 'vistaEditarDirectiva') {
            ControladorDirectivas::vistaEditarDirectiva();
            return;
        }
        //Vista listado de directivas
        if (isset($_GET['r']) && $_GET['r'] === 'listado_directivas') {
            ControladorDirectivas::vistaListadoDirectivas();
            return;
        }

        //Vista crear novedad
        if (isset($_GET['r']) && $_GET['r'] === 'crear_novedad') {
            NovedadesController::vistaCrearNovedades();
            return;
        }
        //Vista listado de novedades
        if (isset($_GET['r']) && $_GET['r'] === 'listado_novedades') {
            NovedadesController::vistaListadoNovedades();
            return;
        }
        //Vista Reporte entradas y salidas
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_entradas_salidas') {
            NovedadesController::vistaListadoEntradaSalida();
            return;
        }

        // Vista marcar ingreso / salida del servicio
        if (isset($_GET['r']) && $_GET['r'] === 'entradas_salidas') {
            NovedadesController::vistaEntradaSalida();
            return;
        }
        //Listado de objetivos
        if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos') {
            ControladorObjetivos::vistaListadoObjetivos();
            return;
        }
        //Crear objetivos
        if (isset($_GET['r']) && $_GET['r'] === 'crear_objetivo') {
            ControladorObjetivos::vistaCrearObjetivo();
            return;
        }
        //editar objetivos
        if (isset($_GET['r']) && $_GET['r'] === 'editar_objetivo') {
            ControladorObjetivos::vistaEditarObjetivo();
            return;
        }
        //Listado de objetivos
        if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos_inactivos') {
            ControladorObjetivos::vistaListadoObjetivosInactivos();
            return;
        }

        //Guardar un puesto
        if (isset($_GET['r']) && $_GET['r'] === 'crear_puesto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorPuestos::ctrGuardarPuesto();
            return;
        }

        //Crear puestos
        if (isset($_GET['r']) && $_GET['r'] === 'crear_puesto') {
            ControladorPuestos::vistaCrearPuestos();
            return;
        }
        //Editar puestos
        if (isset($_GET['r']) && $_GET['r'] === 'editar_puesto') {
            ControladorPuestos::vistaEditarPuesto();
            return;
        }
        //Listado de puestos activos
        if (isset($_GET['r']) && $_GET['r'] === 'listado_puestos') {
            ControladorPuestos::vistaListadoPuestos();
            return;
        }
        //Listado de puestos desactivados
        if (isset($_GET['r']) && $_GET['r'] === 'listado_puestos_inactivos') {
            ControladorPuestos::vistaListadoPuestosDesactivados();
            return;
        }

        //Crear rondas
        if (isset($_GET['r']) && $_GET['r'] === 'crear_rondas') {
            RondasController::vistaCrearRondas();
            return;
        }

        //Editar rondas
        if (isset($_GET['r']) && $_GET['r'] === 'editar_ronda') {
            RondasController::vistaEditarRondas();
            return;
        }
        //Listado de rondas
        if (isset($_GET['r']) && $_GET['r'] === 'listado_rondas') {
            RondasController::vistaListadoRondas();
            return;
        }

        //Crear rondas
        if (isset($_GET['r']) && $_GET['r'] === 'crear-usuario') {
            ControladorUsuarios::vistaCrearUsuario();
            return;
        }
        //Crear rondas
        if (isset($_GET['r']) && $_GET['r'] === 'perfil-usuario') {
            ControladorUsuarios::vistaPerfilUsuario();
            return;
        }
        //Listado de usuarios
        if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios') {
            ControladorUsuarios::vistaListadoUsuarios();
            return;
        }
        //Listado de usuarios inactivos
        if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios-inactivos') {
            ControladorUsuarios::vistaListadoUsuariosInactivos();
            return;
        }

        //Archivos
        if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios-inactivos') {
            ControladorUsuarios::vistaListadoUsuariosInactivos();
            return;
        }



        // ========= MAPEO DE RUTAS A VISTAS =========
        $mapeo = [
            // usuario
            //"crear-usuario"     => "usuario/crear-usuario.php",
            //"listado-usuarios"  => "usuario/listado-usuarios.php",
            //"listado-usuarios-inactivos"  => "usuario/listado-usuarios-inactivos.php",
            // "perfil-usuario"    => "usuario/perfil-usuario.php",
            "cerrar_sesion"     => "usuario/salir.php",

            //hombre-vivo
            //"listado_reportes"      => "h-vivo/listado_reportes.php",

            //Mensajes
            "bandeja-entrada"   => "mensajes/bandeja-entrada.php",
            "nuevo-mensaje"     => "mensajes/nuevo-mensaje.php",
            "mensajes-enviados" => "mensajes/mensajes-enviados.php",

            //objetivos
            //"crear_objetivo"    => "objetivos/crear_objetivo.php",
            //"editar_objetivo"   => "objetivos/editar_objetivo.php",
            //"listado_objetivos" => "objetivos/listado_objetivos.php",
            // "listado_objetivos_inactivos" => "objetivos/listado_objetivos_desactivados.php",

            //directivas
            //"crear_directivas"        => "directivas/crear_directivas.php",
            //"modificar_directivas"    => "directivas/modificar_directivas.php",
            //"listado_directivas"      => "directivas/listado_directivas.php",

            // rondas
            //"crear_rondas"    => "rondas/crear_rondas.php",
            "imprimir_qr"     => "rondas/imprimir_qr.php",
            //"editar_ronda"    => "rondas/editar_ronda.php",
            //"listado_rondas"  => "rondas/listado_rondas.php",

            //Cronograma
            //"crear_cronograma"      => "cronogramas/crear_cronograma.php",
            // "listado_cronogramas"   => "cronogramas/listado_cronogramas.php",
            // "listado_porVigilador"  => "cronogramas/listado_porVigilador.php",
            //"listado_resumen_diario" => "cronogramas/resumen_diario_jornadas.php",
            //"reporte_porHoras"      => "cronogramas/reporte_horas_por_objetivo.php",
            //"reporte_porVigilador"  => "cronogramas/reporte_horas_vigilador.php",

            //Novedades
            // "entradas_salidas"      => "novedades/entradas_salidas.php",
            // "crear_novedad"      => "novedades/crear_novedades.php",
            //"listado_novedades"      => "novedades/listado_novedades.php",
            //"reporte_entradas_salidas"      => "novedades/listado_entradaSalidas.php",

            //Puestos
            //"crear_puesto"      => "puestos/crear_puesto.php",
            //"listado_puestos"   => "puestos/listado_puestos.php",
            //"editar_puesto"     => "puestos/editar_puesto.php",
            //"listado_puestos_inactivos"     => "puestos/listado_puestos_desactivados.php",

            //web
            //  "login"             => "login.php",
            // "forgot"            => "web/forgot-password.php"

        ];

        if (isset($_GET['r']) && array_key_exists($_GET['r'], $mapeo)) {
            $archivo = "vistas/paginas/" . $mapeo[$_GET['r']];
            include(file_exists($archivo)
                ? $archivo
                : "vistas/paginas/404.php"
            );
        } else {
            include("vistas/paginas/inicio.php");
        }
    }
}
