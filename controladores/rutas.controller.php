<?php
// Defino la clase Rutas para mejor funcionamiento
class RutasController
{
    public static function cargarVista()
    {

        // ========= RUTAS QUE LLAMAN A MÉTODOS =========

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        // Registrar escaneo de ronda (AJAX o GET)
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
            EscaneosController::registrar();
            exit;
        }

        // Mostrar QR dinámico
        if (isset($_GET['r']) && $_GET['r'] === 'mostrar_qr') {
            QrController::mostrar();
            exit;
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

        // Cámara para escanear
        if (isset($_GET['r']) && $_GET['r'] === 'escanear') {
            include __DIR__ . '/../vistas/paginas/rondas/escanear_rondas.php';
            return;
        }

        // Vista de reporte hombre vivo (timer)
        if (isset($_GET['r']) && $_GET['r'] === 'reporte_hombre_vivo') {
            include __DIR__ . '/../vistas/paginas/h-vivo/reporte_hombre_vivo.php';
            return;
        }

        // Endpoint AJAX para registrar reporte hombre vivo
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_reporte') {
            require_once __DIR__ . '/hvivo.controller.php';
            HombreVivoController::registrar();
            exit;
        }

        if (isset($_GET['r']) && $_GET['r'] === 'ajax_rondas') {
            require_once __DIR__ . '/../libraries/ajax/ajax_rondas.php';
            exit;
        }
        // Actualizar ronda (formulario de edición)
        if (isset($_GET['r']) && $_GET['r'] === 'actualizar_ronda') {
            RondasController::crtActualizarRonda();
            exit;
        }

        // Desactivar ronda (listado)
        if (isset($_GET['r']) && $_GET['r'] === 'desactivar_ronda') {
            require_once __DIR__ . '/rondas.controller.php';
            RondasController::crtDesactivarRonda(intval($_POST['idEliminar'] ?? 0));
            exit;
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



        // ========= MAPEO DE RUTAS A VISTAS =========
        $mapeo = [
            // usuario
            "crear-usuario"     => "usuario/crear-usuario.php",
            "listado-usuarios"  => "usuario/listado-usuarios.php",
            "listado-usuarios-inactivos"  => "usuario/listado-usuarios-inactivos.php",
            "perfil-usuario"    => "usuario/perfil-usuario.php",
            "cerrar_sesion"     => "usuario/salir.php",

            //hombre-vivo
            "listado_reportes"      => "h-vivo/listado_reportes.php",

            //Mensajes
            "bandeja-entrada"   => "mensajes/bandeja-entrada.php",
            "nuevo-mensaje"     => "mensajes/nuevo-mensaje.php",
            "mensajes-enviados" => "mensajes/mensajes-enviados.php",

            //objetivos
            "crear_objetivo"    => "objetivos/crear_objetivo.php",
            "editar_objetivo"   => "objetivos/editar_objetivo.php",
            "listado_objetivos" => "objetivos/listado_objetivos.php",
            "listado_objetivos_inactivos" => "objetivos/listado_objetivos_desactivados.php",

            //directivas
            "crear_directivas"        => "directivas/crear_directivas.php",
            "modificar_directivas"    => "directivas/modificar_directivas.php",
            "listado_directivas"      => "directivas/listado_directivas.php",

            // rondas
            "crear_rondas"    => "rondas/crear_rondas.php",
            "imprimir_qr"     => "rondas/imprimir_qr.php",
            "editar_ronda"    => "rondas/editar_ronda.php",
            "listado_rondas"  => "rondas/listado_rondas.php",

            //Cronograma
            "crear_cronograma"      => "cronogramas/crear_cronograma.php",
            "listado_cronogramas"   => "cronogramas/listado_cronogramas.php",
            "listado_porVigilador"  => "cronogramas/listado_porVigilador.php",
            "listado_resumen_diario" => "cronogramas/resumen_diario_jornadas.php",
            "reporte_porHoras"      => "cronogramas/reporte_horas_por_objetivo.php",
            "reporte_porVigilador"  => "cronogramas/reporte_horas_vigilador.php",

            //Novedades
            "entradas_salidas"      => "novedades/entradas_salidas.php",
            "crear_novedad"      => "novedades/crear_novedades.php",
            "listado_novedades"      => "novedades/listado_novedades.php",
            "reporte_entradas_salidas"      => "novedades/listado_entradaSalidas.php",

            //Puestos
            "crear_puesto"      => "puestos/crear_puesto.php",
            "listado_puestos"   => "puestos/listado_puestos.php",
            "editar_puesto"     => "puestos/editar_puesto.php",
            "listado_puestos_inactivos"     => "puestos/listado_puestos_desactivados.php",

            //web
            "login"             => "login.php",
            "forgot"            => "web/forgot-password.php"

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
