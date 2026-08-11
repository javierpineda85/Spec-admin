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
    define('RUTA_EJECUTADA', true);
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

if (isset($_GET['r']) && $_GET['r'] === 'admin_items_uniforme') {
    UniformesController::adminItems();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'guardar_item_uniforme') {
    UniformesController::guardarItem();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'cambiar_estado_item_uniforme') {
    UniformesController::cambiarEstadoItem();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'eliminar_item_uniforme') {
    UniformesController::eliminarItem();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'guardar_categoria_uniforme') {
    UniformesController::guardarCategoria();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'eliminar_categoria_uniforme') {
    UniformesController::eliminarCategoria();
    return;
}
