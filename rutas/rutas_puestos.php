<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear_puesto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorPuestos::ctrGuardarPuesto();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'crear_puesto') {
    ControladorPuestos::vistaCrearPuestos();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_puesto') {
    ControladorPuestos::vistaEditarPuesto();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_puestos') {
    ControladorPuestos::vistaListadoPuestos();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_puestos_inactivos') {
    ControladorPuestos::vistaListadoPuestosDesactivados();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'rotaciones_puestos') {
    ControladorPuestos::vistaRotaciones();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'guardar_rotacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorPuestos::crtGuardarRotacion();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'eliminar_rotacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorPuestos::crtEliminarRotacion();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'swap_rotacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorPuestos::crtSwapRotacion();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'auto_rotar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorPuestos::crtAutoRotarEquitativo();
    exit;
}

if (isset($_GET['r']) && $_GET['r'] === 'auto_rotar') {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'msg' => 'Método inválido']);
    exit;
}