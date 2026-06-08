<?php

if (isset($_GET['r']) && $_GET['r'] === 'credencial_art') {
    ArtController::vistaCredencialArt();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_art') {
    ArtController::vistaListadoArt();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'crear_art') {
    ArtController::vistaCrearArt();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_art') {
    ArtController::vistaEditarArt();
    define('RUTA_EJECUTADA', true);
    return;
}