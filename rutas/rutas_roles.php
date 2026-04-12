<?php

if (isset($_GET['r']) && $_GET['r'] === 'roles/listado') {
    RolesController::vistaListadoRoles();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/crear') {
    RolesController::vistaCrearRol();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrGuardarRol') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        RolesController::ctrGuardarRol();
    } else {
        header('Location: ?r=roles/listado');
    }
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/editar') {
    RolesController::vistaEditarRol();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrActualizarRol') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        RolesController::ctrActualizarRol();
    } else {
        header('Location: ?r=roles/listado');
    }
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrDesactivarRol') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        RolesController::ctrDesactivarRol();
    } else {
        header('Location: ?r=roles/listado');
    }
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/permisos') {
    if (!isset($_GET['rol']) && isset($_GET['role'])) {
        header('Location: ?r=roles/permisos&rol=' . urlencode($_GET['role']));
        return;
    }
    RolesController::vistaPermisosRol();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'roles/ctrGuardarPermisosRol') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        RolesController::ctrGuardarPermisosRol();
    } else {
        header('Location: ?r=roles/listado');
    }
    return;
}