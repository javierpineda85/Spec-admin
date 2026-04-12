<?php

if (isset($_GET['r']) && $_GET['r'] === 'crear-usuario') {
    ControladorUsuarios::vistaCrearUsuario();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'perfil-usuario') {
    ControladorUsuarios::vistaPerfilUsuario();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios') {
    ControladorUsuarios::vistaListadoUsuarios();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'listado-usuarios-inactivos') {
    ControladorUsuarios::vistaListadoUsuariosInactivos();
    return;
}