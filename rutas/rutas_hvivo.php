<?php

// Vista principal del reporte Hombre Vivo
if (isset($_GET['r']) && $_GET['r'] === 'hombre_vivo') {
    HombreVivoController::vistaHombreVivo();
    define('RUTA_EJECUTADA', true);
    return;
}

// Registrar reporte Hombre Vivo (POST o GET)
if (isset($_GET['r']) && $_GET['r'] === 'registrar_hvivo') {
    HombreVivoController::registrar();
    return;
}

// Listado de reportes Hombre Vivo
if (isset($_GET['r']) && $_GET['r'] === 'listado_hvivo') {
    HombreVivoController::vistaListadoReportesHombreVivo();
    define('RUTA_EJECUTADA', true);
    return;
}

// Registro vía AJAX
if (isset($_GET['r']) && $_GET['r'] === 'ajax_registrar_hvivo') {
    HombreVivoController::ajaxRegistrarReporte();
    return;
}
// Vista de reporte hombre vivo (timer)
if (isset($_GET['r']) && $_GET['r'] === 'reporte_hombre_vivo') {
    HombreVivoController::vistaHombreVivo();
    define('RUTA_EJECUTADA', true);
    return;
}

// Vista de Listado Reportes H VIVO
if (isset($_GET['r']) && $_GET['r'] === 'listado_reportes') {
    HombreVivoController::vistaListadoReportesHombreVivo();
    define('RUTA_EJECUTADA', true);
    return;
}
