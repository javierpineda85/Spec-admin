<?php

class RutasController
{
    public static function cargarVista()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // ============================
        // 1) Cargar rutas por módulos
        // ============================

        require_once __DIR__ . '/../rutas/rutas_login.php';
        require_once __DIR__ . '/../rutas/rutas_alertas.php';
        require_once __DIR__ . '/../rutas/rutas_qr.php';
        require_once __DIR__ . '/../rutas/rutas_rondas.php';
        require_once __DIR__ . '/../rutas/rutas_permisos.php';
        require_once __DIR__ . '/../rutas/rutas_roles.php';
        require_once __DIR__ . '/../rutas/rutas_cronogramas.php';
        require_once __DIR__ . '/../rutas/rutas_directivas.php';
        require_once __DIR__ . '/../rutas/rutas_novedades.php';
        require_once __DIR__ . '/../rutas/rutas_objetivos.php';
        require_once __DIR__ . '/../rutas/rutas_puestos.php';
        require_once __DIR__ . '/../rutas/rutas_usuarios.php';
        require_once __DIR__ . '/../rutas/rutas_feriados.php';
        require_once __DIR__ . '/../rutas/rutas_art.php';
        require_once __DIR__ . '/../rutas/rutas_datos.php';
        require_once __DIR__ . '/../rutas/rutas_uniformes.php';
        require_once __DIR__ . '/../rutas/rutas_mensajes.php';
        require_once __DIR__ . '/../rutas/rutas_configuracion.php';

        // ============================
        // 2) Mapeo simple a vistas
        // ============================

        $mapeo = [
            "cerrar_sesion" => "usuario/salir.php",
            "imprimir_qr"   => "rondas/imprimir_qr.php",
        ];

        if (isset($_GET['r']) && array_key_exists($_GET['r'], $mapeo)) {
            $archivo = "vistas/paginas/" . $mapeo[$_GET['r']];
            include(file_exists($archivo) ? $archivo : "vistas/paginas/404.php");
            return;
        }

        // ============================
        // 3) Vista por defecto
        // ============================

        include("vistas/paginas/inicio.php");
    }
}