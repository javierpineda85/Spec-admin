<?php

if (isset($_GET['r']) && $_GET['r'] === 'configuracion/panel') {
    Auth::check('roles', 'vistaConfigSistema');
    ConfigController::vistaPanel();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'configuracion/ctrGuardarConfig') {
    ConfigController::ctrGuardarConfig();
    return;
}