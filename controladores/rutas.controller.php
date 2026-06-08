<?php

class RutasController
{
    public static function cargarVista()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // ============================================
        // 1. LOGIN (GET/POST)
        // ============================================
        if (isset($_GET['r']) && $_GET['r'] === 'login') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                LoginController::procesarLogin();
            } else {
                LoginController::mostrarLogin();
            }
            return;
        }

        // ============================================
        // 2. RESET PASSWORD (GET/POST)
        // ============================================
        if (isset($_GET['r']) && $_GET['r'] === 'reset-password') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                ResetPasswordController::crtResetPassword();
            } else {
                ResetPasswordController::vistaResetPassword();
            }
            return;
        }

        // ============================================
        // 3. CARGAR RUTAS MODULARES
        // ============================================
        require_once __DIR__ . '/../rutas/rutas_alertas.php';
        require_once __DIR__ . '/../rutas/rutas_art.php';
        require_once __DIR__ . '/../rutas/rutas_configuracion.php';
        require_once __DIR__ . '/../rutas/rutas_cronogramas.php';
        require_once __DIR__ . '/../rutas/rutas_datos.php';
        require_once __DIR__ . '/../rutas/rutas_directivas.php';
        require_once __DIR__ . '/../rutas/rutas_feriados.php';
        require_once __DIR__ . '/../rutas/rutas_hvivo.php';
        require_once __DIR__ . '/../rutas/rutas_login.php';
        require_once __DIR__ . '/../rutas/rutas_marcaciones.php';
        require_once __DIR__ . '/../rutas/rutas_mensajes.php';
        require_once __DIR__ . '/../rutas/rutas_novedades.php';
        require_once __DIR__ . '/../rutas/rutas_objetivos.php';
        require_once __DIR__ . '/../rutas/rutas_permisos.php';
        require_once __DIR__ . '/../rutas/rutas_puestos.php';
        require_once __DIR__ . '/../rutas/rutas_qr.php';
        require_once __DIR__ . '/../rutas/rutas_roles.php';
        require_once __DIR__ . '/../rutas/rutas_rondas.php';
        require_once __DIR__ . '/../rutas/rutas_uniformes.php';
        require_once __DIR__ . '/../rutas/rutas_usuarios.php';

        // ============================================
        // 4. MAPEO DIRECTO A VISTAS
        // ============================================
        $mapeo = [
            "cerrar_sesion" => "usuario/salir.php",
            "imprimir_qr"   => "rondas/imprimir_qr.php",
        ];

        if (isset($_GET['r']) && array_key_exists($_GET['r'], $mapeo)) {
            $archivo = "vistas/paginas/" . $mapeo[$_GET['r']];
            include(file_exists($archivo) ? $archivo : "vistas/paginas/404.php");
            return;
        }

        // ============================================
        // 5. SI ALGUNA RUTA MODULAR YA SE EJECUTÓ → NO HACER NADA
        // ============================================
        if (defined('RUTA_EJECUTADA') && RUTA_EJECUTADA === true) {
            return;
        }

        // ============================================
        // 6. SI NO SE EJECUTÓ NADA → MOSTRAR INICIO
        // ============================================
        include("vistas/paginas/inicio.php");
    }
}