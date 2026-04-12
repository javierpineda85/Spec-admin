<?php

if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
    EscaneosController::registrar();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'escanear') {
    RondasController::vistaEscanearRondas();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'escaneo_feedback') {
    EscaneosController::feedback();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'ajax_rondas') {
    require_once __DIR__ . '/../libraries/ajax/ajax_rondas.php';
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'actualizar_ronda') {
    RondasController::crtActualizarRonda();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'desactivar_ronda') {
    RondasController::crtDesactivarRonda(intval($_POST['idEliminar'] ?? 0));
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'crear_rondas') {
    RondasController::vistaCrearRondas();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_ronda') {
    RondasController::vistaEditarRondas();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_rondas') {
    RondasController::vistaListadoRondas();
    return;
}