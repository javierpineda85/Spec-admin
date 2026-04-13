<?php

// Vista principal del reporte Hombre Vivo
if (isset($_GET['r']) && $_GET['r'] === 'hombre_vivo') {
    HombreVivoController::vistaHombreVivo();
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
    return;
}

// Registro vía AJAX
if (isset($_GET['r']) && $_GET['r'] === 'ajax_registrar_hvivo') {
    HombreVivoController::ajaxRegistrarReporte();
    return;
}