<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear_feriados') {
    FeriadosController::vistaCrearFeriados();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_feriados') {
    FeriadosController::vistaListadoFeriados();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_feriado') {
    FeriadosController::vistaEditarFeriado();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'eliminar_feriado') {
    FeriadosController::ctrEliminarFeriado();
    return;
}