<?php

if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos') {
    ControladorObjetivos::vistaListadoObjetivos();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'crear_objetivo') {
    ControladorObjetivos::vistaCrearObjetivo();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_objetivo') {
    ControladorObjetivos::vistaEditarObjetivo();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos_inactivos') {
    ControladorObjetivos::vistaListadoObjetivosInactivos();
    return;
}