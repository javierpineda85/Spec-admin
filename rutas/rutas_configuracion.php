<?php

if (isset($_GET['r']) && $_GET['r'] === 'configuracion/panel') {
    Auth::check('roles', 'vistaConfigSistema');
    ConfigController::vistaPanel();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'configuracion/ctrGuardarConfig') {
    ConfigController::ctrGuardarConfig();
    return;
}