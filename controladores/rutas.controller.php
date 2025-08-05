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
            'registrar_reporte/index'
        ];
        // ========= RUTAS QUE LLAMAN A MÉTODOS =========

        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        //Notificaciones y alertas
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_alerta_hombrevivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            AlertasController::registrarDemoraHombreVivo();
            exit;
        }

        //Vista de alertas
        if (isset($_GET['r']) && $_GET['r'] === 'alertas_supervisor') {
            require_once 'vistas/paginas/supervisores/alertas_supervisor.php';
            exit;
        }
        //Vista publicas para ver alertas
        if (isset($_GET['r']) && $_GET['r'] === 'ver_alertas') {
            AlertasController::verAlertasNoLeidas();
            exit;
        }

        //Marcar alertas como leidas
        if (isset($_GET['r']) && $_GET['r'] === 'marcar_alerta_leida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            AlertasController::marcarLeida();
            exit;
        }

        //Historial de alertas leidas
        if (isset($_GET['r']) && $_GET['r'] === 'ver_historial_alertas') {
            AlertasController::verHistorialLeidas();
            exit;
        }

        // Registrar escaneo de ronda (AJAX o GET)
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
            EscaneosController::registrar();
            return;
        }

        // Vista para escanear con cámara
        if (isset($_GET['r']) && $_GET['r'] === 'escanear') {
            RondasController::vistaEscanearRondas();
            return;
        }

        //Escaneo feedbak es la vista que retorna luego de escanear un QR
        if (isset($_GET['r']) && $_GET['r'] === 'escaneo_feedback') {
            EscaneosController::feedback();
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
            $desde = $_POST['desde'] ?? null;
            $hasta = $_POST['hasta'] ?? null;
            ControladorCronograma::crtBuscarResumenHorasPorVigilador($desde,$hasta);
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

        //Legajos
        if (isset($_GET['r']) && $_GET['r'] === 'legajos') {
            LegajosController::vistaLegajos();
            return;
        }
        // ==== RUTAS FERIADOS ====
        if (isset($_GET['r']) && $_GET['r'] === 'crear_feriados') {
            FeriadosController::vistaCrearFeriados();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'listado_feriados') {
            FeriadosController::vistaListadoFeriados();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'editar_feriado') {
            FeriadosController::vistaEditarFeriado();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'eliminar_feriado') {
            FeriadosController::ctrEliminarFeriado();
            return;
        }

        // ==== RUTAS ART ====
        if (isset($_GET['r']) && $_GET['r'] === 'credencial_art') {
            ArtController::vistaCredencialArt();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'listado_art') {
            ArtController::vistaListadoArt();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'crear_art') {
            ArtController::vistaCrearArt();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'editar_art') {
            ArtController::vistaEditarArt();
            return;
        }

        // ========= Datos personales=========
        if (isset($_GET['r']) && $_GET['r'] === 'mis_datos_personales') {
            DatosPersonalesController::vistaMisDatosPersonales();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'listado_uniformes') {
            DatosPersonalesController::vistaListadoUniformes();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'mi_salud') {
            SaludController::vistaMiSalud();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'mi_uniforme') {
            UniformesController::vistaMiUniforme();
            return;
        }
        // Noticias

        if (isset($_GET['r']) && $_GET['r'] ===  'cumpleanos') {
            NoticiasController::vistaCumple();
            exit;
        }
        // ==================== MENSAJES ====================
        if (isset($_GET['r']) && $_GET['r'] === 'bandeja-entrada') {
            require_once 'vistas/paginas/mensajes/bandeja-entrada.php';
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'mensajes-enviados') {
            require_once 'vistas/paginas/mensajes/mensajes-enviados.php';
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'nuevo-mensaje') {
            require_once 'vistas/paginas/mensajes/nuevo-mensaje.php';
            return;
        }


        // ========= MAPEO DE RUTAS A VISTAS =========
        $mapeo = [

            "cerrar_sesion"     => "usuario/salir.php",

            //Mensajes
            "bandeja-entrada"   => "mensajes/bandeja-entrada.php",
            "nuevo-mensaje"     => "mensajes/nuevo-mensaje.php",
            "mensajes-enviados" => "mensajes/mensajes-enviados.php",


            "imprimir_qr"     => "rondas/imprimir_qr.php",


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
