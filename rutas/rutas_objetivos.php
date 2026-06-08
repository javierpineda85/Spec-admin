<?php

if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos') {
    ControladorObjetivos::vistaListadoObjetivos();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'crear_objetivo') {
    // Si es POST, procesar antes de cargar la vista
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        ControladorObjetivos::crtGuardarObjetivo();
    }

    ControladorObjetivos::vistaCrearObjetivo();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'api_siglas') {
    ControladorObjetivos::apiSiglas();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_objetivo') {
    ControladorObjetivos::vistaEditarObjetivo();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos_inactivos') {
    ControladorObjetivos::vistaListadoObjetivosInactivos();
    define('RUTA_EJECUTADA', true);
    return;
}
