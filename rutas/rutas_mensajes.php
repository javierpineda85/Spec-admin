<?php

if (isset($_GET['r']) && $_GET['r'] === 'bandeja-entrada') {
    require_once 'controladores/mensajes.controller.php';
    require_once 'vistas/paginas/mensajes/bandeja-entrada.php';
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'mensajes-enviados') {
    require_once 'controladores/mensajes.controller.php';
    require_once 'vistas/paginas/mensajes/mensajes-enviados.php';
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'nuevo-mensaje') {
    require_once 'controladores/mensajes.controller.php';
    require_once 'vistas/paginas/mensajes/nuevo-mensaje.php';
    return;
}