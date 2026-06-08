<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear-usuario') {
    ControladorUsuarios::vistaCrearUsuario();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'perfil-usuario') {
    ControladorUsuarios::vistaPerfilUsuario();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios') {
    ControladorUsuarios::vistaListadoUsuarios();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios-inactivos') {
    ControladorUsuarios::vistaListadoUsuariosInactivos();
    define('RUTA_EJECUTADA', true);
    return;
}