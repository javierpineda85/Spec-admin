<?php

// Registrar marcación (entrada o salida)
if (isset($_GET['r']) && $_GET['r'] === 'registrar_marcacion') {
    MarcacionesController::crtRegistrarMarcacion();
    return;
}

// Vista de marcaciones (si existe en tu sistema)
if (isset($_GET['r']) && $_GET['r'] === 'entradas_salidas') {
    include 'vistas/paginas/marcaciones/entradas_salidas.php';
    return;
}