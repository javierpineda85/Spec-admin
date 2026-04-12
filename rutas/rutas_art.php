<?php

if (isset($_GET['r']) && $_GET['r'] === 'credencial_art') {
    ArtController::vistaCredencialArt();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_art') {
    ArtController::vistaListadoArt();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'crear_art') {
    ArtController::vistaCrearArt();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'editar_art') {
    ArtController::vistaEditarArt();
    return;
}