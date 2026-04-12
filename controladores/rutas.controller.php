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
            'registrar_reporte/index',
            'acceso_denegado/crtAccesoDenegado',
            'acceso_denegado/index'
        ];
        // ========= RUTAS QUE LLAMAN A MÉTODOS =========

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if (isset($_GET['r']) && $_GET['r'] === 'reset-password') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                ResetPasswordController::crtResetPassword();
            } else {
                ResetPasswordController::vistaResetPassword();
            }
            return;
        }
        // ===== LOGIN (GET = form, POST = procesar) =====
        if (isset($_GET['r']) && $_GET['r'] === 'login') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                LoginController::procesarLogin();
            } else {
                LoginController::mostrarLogin();
            }
            return;
        }

        //Notificaciones y alertas
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_alerta_hombrevivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            AlertasController::registrarDemoraHombreVivo();
            return;
        }

        //Vista de alertas
        if (isset($_GET['r']) && $_GET['r'] === 'alertas_supervisor') {
            require_once 'vistas/paginas/supervisores/alertas_supervisor.php';
            return;
        }
        //Vista publicas para ver alertas
        if (isset($_GET['r']) && $_GET['r'] === 'ver_alertas') {
            AlertasController::verAlertasNoLeidas();
            return;
        }

        //Marcar alertas como leidas
        if (isset($_GET['r']) && $_GET['r'] === 'marcar_alerta_leida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            AlertasController::marcarLeida();
            return;
        }

        //Historial de alertas leidas
        if (isset($_GET['r']) && $_GET['r'] === 'ver_historial_alertas') {
            AlertasController::verHistorialLeidas();
            return;
        }

        // Registrar escaneo de ronda (AJAX o GET)
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
            EscaneosController::registrar();
            return;
        }

        //============== Gestión de permisos (permisos controller y roles controller)=============
        if (isset($_GET['r']) && ($_GET['r'] === 'permisos' || $_GET['r'] === 'permisos/index')) {
            PermisosController::index();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'permisos/update') {
            PermisosController::update();
            return;
        }

        // ===== Roles: listado =====
        if (isset($_GET['r']) && $_GET['r'] === 'roles/listado') {
            RolesController::vistaListadoRoles();
            return;
        }

        // ===== Roles: crear =====
        if (isset($_GET['r']) && $_GET['r'] === 'roles/crear') {
            RolesController::vistaCrearRol();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrGuardarRol') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                RolesController::ctrGuardarRol();
            } else {
                header('Location: ?r=roles/listado');
                return;
            }
            return;
        }

        // ===== Roles: editar =====
        if (isset($_GET['r']) && $_GET['r'] === 'roles/editar') {
            RolesController::vistaEditarRol();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrActualizarRol') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                RolesController::ctrActualizarRol();
            } else {
                header('Location: ?r=roles/listado');
                return;
            }
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrDesactivarRol') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                RolesController::ctrDesactivarRol();
            } else {
                header('Location: ?r=roles/listado');
                return;
            }
            return;
        }

        // ===== Roles: permisos (gestión de permisos por rol) =====
        if (isset($_GET['r']) && $_GET['r'] === 'roles/permisos') {
            // acepta ?rol=... (nuevo). Si viniera ?role=... desde lo viejo, lo normalizamos:
            if (!isset($_GET['rol']) && isset($_GET['role'])) {
                header('Location: ?r=roles/permisos&rol=' . urlencode($_GET['role']));
                return;
            }
            RolesController::vistaPermisosRol();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrGuardarPermisosRol') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                RolesController::ctrGuardarPermisosRol();
            } else {
                header('Location: ?r=roles/listado');
                return;
            }
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

        // ========= RONDAS =========
        //
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
        if (isset($_GET['r']) && $_GET['r'] === 'ajax_rondas') {
            require_once __DIR__ . '/../libraries/ajax/ajax_rondas.php';
            return;
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
            ControladorCronogramas::vistaCrearCronograma();
            return;
        }
        //Listado Cronogramas
        if (isset($_GET['r']) && $_GET['r'] === 'listado_cronogramas') {
            ControladorCronogramas::vistaListadoCronogramas();
            return;
        }
        //Listado Cronogramas x vigilador VISTA
        if (isset($_GET['r']) && $_GET['r'] === 'listado_porVigilador') {
            ControladorCronogramas::vistaListadoCronogramaPorVigilador();
            return;
        }
        //Listado Vista Jornadas por objetivo
        if (isset($_GET['r']) && $_GET['r'] === 'listado_resumen_diario') {
            ControladorCronogramas::vistaJornadasPorObjetivo();
            return;
        }
        //Horas por Vigilador
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_porVigilador') {
            ControladorCronogramas::vistaHorasPorVigilador();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_diario') {
            ControladorCronogramas::crtBuscarResumenDiario();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas') {
            ControladorCronogramas::crtBuscarResumenHoras();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas_por_vigilador') {
            $desde = $_POST['desde'] ?? null;
            $hasta = $_POST['hasta'] ?? null;
            ControladorCronogramas::crtBuscarResumenHorasPorVigilador($desde, $hasta);
            return;
        }

        // Procesar el POST de “Horas por Objetivo”
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_porHoras' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorCronogramas::crtBuscarResumenHoras();
            return;
        }

        // Mostrar el formulario / resultado (GET)
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_porHoras') {
            ControladorCronogramas::vistaReporteHorasPorObjetivo();
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

        // Vista Historial por vigilador de ingreso / salida en el mapa
        if (isset($_GET['r']) && $_GET['r'] === 'historialMarcaciones') {
            NovedadesController::vistaHistorialMarcaciones();
            return;
        }

        // ========= OBJETIVOS =========
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

        // ========= PUESTOS =========
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
        // Vista de rotaciones
        if (isset($_GET['r']) && $_GET['r'] === 'rotaciones_puestos') {
            ControladorPuestos::vistaRotaciones();
            return;
        }

        // Guardar/actualizar una rotación (POST)
        if (isset($_GET['r']) && $_GET['r'] === 'guardar_rotacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorPuestos::crtGuardarRotacion();
            return;
        }

        // Eliminar rotación (POST)
        if (isset($_GET['r']) && $_GET['r'] === 'eliminar_rotacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorPuestos::crtEliminarRotacion();
            return;
        }

        // Intercambiar (swap) rotaciones (POST)
        if (isset($_GET['r']) && $_GET['r'] === 'swap_rotacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorPuestos::crtSwapRotacion();
            return;
        }

        // Autollenado equitativo (round robin) (POST)
        if (isset($_GET['r']) && $_GET['r'] === 'auto_rotar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            ControladorPuestos::crtAutoRotarEquitativo();
            //return;
            exit;
        }
        // Si no coincide, no devolver HTML en APIs
        if (isset($_GET['r']) && $_GET['r'] === 'auto_rotar') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'msg' => 'Método inválido']);
            exit;
        }

        // ========= RONDAS =========
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

        // ========= USUARIOS=========
        //Crear usuario
        if (isset($_GET['r']) && $_GET['r'] === 'crear-usuario') {
            ControladorUsuarios::vistaCrearUsuario();
            return;
        }
        //Perfil de usuario
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

        // ========= DATOS PERSONALES =========
        if (isset($_GET['r']) && $_GET['r'] === 'mis_datos_personales') {
            DatosPersonalesController::vistaMisDatosPersonales();
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'mi_salud') {
            SaludController::vistaMiSalud();
            return;
        }

        //Vista uniformes
        if (isset($_GET['r']) && $_GET['r'] === 'mi_uniforme') {
            UniformesController::vistaMiUniforme();
            return;
        }

        //Listado de uniformes
        if (isset($_GET['r']) && $_GET['r'] === 'listado_uniformes') {
            UniformesController::vistaListadoUniformes();
            return;
        }

        // Comprobante de entrega de uniforme
        if (isset($_GET['r']) && $_GET['r'] === 'comprobante_entrega_uniforme') {
            UniformesController::comprobanteEntrega();
            return;
        }

        // Registrar entrega múltiple
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_entrega_uniforme_multiple') {
            UniformesController::registrarEntregaMultiple();
            return;
        }
        // Registrar devolución de uniforme
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_devolucion_uniforme') {
            UniformesController::registrarDevolucion();
            return;
        }

        // Noticias

        if (isset($_GET['r']) && $_GET['r'] ===  'cumpleanos') {
            NoticiasController::vistaCumple();
            return;
        }
        // ==================== MENSAJES ====================
        if (isset($_GET['r']) && $_GET['r'] === 'bandeja-entrada') {
            require_once 'controladores/mensajes.controller.php';
            require_once 'vistas/paginas/mensajes/bandeja-entrada.php';
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'mensajes-enviados') {
            require_once 'controladores/mensajes.controller.php';
            require_once 'vistas/paginas/mensajes/mensajes-enviados.php';
            return;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'nuevo-mensaje') {
            require_once 'controladores/mensajes.controller.php';
            require_once 'vistas/paginas/mensajes/nuevo-mensaje.php';
            return;
        }

        // ===== Configuración del sistema =====
        if (isset($_GET['r']) && $_GET['r'] === 'configuracion/panel') {
            // Solo rol programador puede acceder
            Auth::check('roles', 'vistaConfigSistema');

            // Llamamos al controlador que renderiza la vista del panel
            ConfigController::vistaPanel();
            return;
        }

        // ===== Guardar configuración =====
        if (isset($_GET['r']) && $_GET['r'] === 'configuracion/ctrGuardarConfig') {
            ConfigController::ctrGuardarConfig();
            return;
        }
        // ========= MAPEO DE RUTAS A VISTAS =========
        $mapeo = [

            "cerrar_sesion"     => "usuario/salir.php",

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
