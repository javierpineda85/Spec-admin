<?php

if (isset($_GET['r']) && ($_GET['r'] === 'permisos' || $_GET['r'] === 'permisos/index')) {
    PermisosController::index();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'permisos/update') {
    PermisosController::update();
    return;
}