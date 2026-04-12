<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear_feriados') {
    FeriadosController::vistaCrearFeriados();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_feriados') {
    FeriadosController::vistaListadoFeriados();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_feriado') {
    FeriadosController::vistaEditarFeriado();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'eliminar_feriado') {
    FeriadosController::ctrEliminarFeriado();
    return;
}