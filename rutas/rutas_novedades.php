<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear_novedad') {
    NovedadesController::vistaCrearNovedades();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_novedades') {
    NovedadesController::vistaListadoNovedades();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'reporte_entradas_salidas') {
    NovedadesController::vistaListadoEntradaSalida();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'entradas_salidas') {
    NovedadesController::vistaEntradaSalida();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'historialMarcaciones') {
    NovedadesController::vistaHistorialMarcaciones();
    define('RUTA_EJECUTADA', true);
    return;
}