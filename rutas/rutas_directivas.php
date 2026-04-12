<?php

if (isset($_GET['r']) && $_GET['r'] === 'vistaCrearDirectiva') {
    ControladorDirectivas::vistaCrearDirectiva();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'vistaEditarDirectiva') {
    ControladorDirectivas::vistaEditarDirectiva();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_directivas') {
    ControladorDirectivas::vistaListadoDirectivas();
    return;
}