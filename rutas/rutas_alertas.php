<?php

// Registrar alerta hombre vivo
if (isset($_GET['r']) && $_GET['r'] === 'registrar_alerta_hombrevivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    AlertasController::registrarDemoraHombreVivo();
    return;
}

// Vista alertas supervisor
if (isset($_GET['r']) && $_GET['r'] === 'alertas_supervisor') {
    require_once 'vistas/paginas/supervisores/alertas_supervisor.php';
    define('RUTA_EJECUTADA', true);
    return;
}

// Vista alertas públicas
if (isset($_GET['r']) && $_GET['r'] === 'ver_alertas') {
    AlertasController::verAlertasNoLeidas();
    return;
}

// Marcar alerta como leída
if (isset($_GET['r']) && $_GET['r'] === 'marcar_alerta_leida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    AlertasController::marcarLeida();
    return;
}

// Historial alertas leídas
if (isset($_GET['r']) && $_GET['r'] === 'ver_historial_alertas') {
    AlertasController::verHistorialLeidas();
    return;
}