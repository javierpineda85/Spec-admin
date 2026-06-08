<?php

if (isset($_GET['r']) && $_GET['r'] === 'mi_uniforme') {
    UniformesController::vistaMiUniforme();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado_uniformes') {
    UniformesController::vistaListadoUniformes();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'comprobante_entrega_uniforme') {
    UniformesController::comprobanteEntrega();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'registrar_entrega_uniforme_multiple') {
    UniformesController::registrarEntregaMultiple();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'registrar_devolucion_uniforme') {
    UniformesController::registrarDevolucion();
    return;
}