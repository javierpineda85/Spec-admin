<?php
// Defino la clase Rutas para mejor funcionamiento
class RutasController
{
    public static function cargarVista()
    {

        // ========= RUTAS QUE LLAMAN A MÉTODOS =========

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
            EscaneosController::registrar();
            exit;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'mostrar_qr') {
            QrController::mostrar();
            exit;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'delete_qr') {
            QrController::delete();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'generar_qr') {
            QrController::generar();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'actualizar_ronda') {
            RondasController::crtActualizarRonda();
            exit;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'escanear') {
            include __DIR__ . '/../vistas/paginas/rondas/escanear_rondas.php';
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'ajax_rondas') {
            require_once __DIR__ . '/../libraries/ajax/ajax_rondas.php';
            exit;
        }
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
        if (isset($_POST['idEliminar'])) {
            RondasController::crtDesactivarRonda(intval($_POST['idEliminar']));
        }

        // ========= MAPEO DE RUTAS A VISTAS =========
        $mapeo = [
            // usuario
            "crear-usuario"     => "usuario/crear-usuario.php",
            "listado-usuarios"  => "usuario/listado-usuarios.php",
            "listado-usuarios-inactivos"  => "usuario/listado-usuarios-inactivos.php",
            "perfil-usuario"    => "usuario/perfil-usuario.php",
            "cerrar_sesion"     => "usuario/salir.php",

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
