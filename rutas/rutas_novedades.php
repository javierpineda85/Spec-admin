<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear_novedad') {
    NovedadesController::vistaCrearNovedades();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_novedades') {
    NovedadesController::vistaListadoNovedades();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'reporte_entradas_salidas') {
    NovedadesController::vistaListadoEntradaSalida();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'entradas_salidas') {
    NovedadesController::vistaEntradaSalida();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'historialMarcaciones') {
    NovedadesController::vistaHistorialMarcaciones();
    return;
}