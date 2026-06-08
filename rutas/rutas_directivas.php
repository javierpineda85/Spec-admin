<?php

if (isset($_GET['r']) && $_GET['r'] === 'vistaCrearDirectiva') {
    ControladorDirectivas::vistaCrearDirectiva();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'vistaEditarDirectiva') {
    ControladorDirectivas::vistaEditarDirectiva();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_directivas') {
    ControladorDirectivas::vistaListadoDirectivas();
    define('RUTA_EJECUTADA', true);
    return;
}