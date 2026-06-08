<?php

// Bandeja de entrada
if (isset($_GET['r']) && $_GET['r'] === 'bandeja-entrada') {
    require_once 'controladores/mensajes.controller.php';
    $mensajes = ControladorMensajes::crtMostrarMensajesRecibidos('destinatario_id', $_SESSION['idUsuario']);
    require 'vistas/paginas/mensajes/bandeja-entrada.php';
    define('RUTA_EJECUTADA', true);
    return;
}

// Bandeja de enviados
if (isset($_GET['r']) && $_GET['r'] === 'mensajes-enviados') {
    require_once 'controladores/mensajes.controller.php';
    $mensajes = ControladorMensajes::crtMostrarMensajesEnviados('remitente_id', $_SESSION['idUsuario']);
    require 'vistas/paginas/mensajes/mensajes-enviados.php';
    define('RUTA_EJECUTADA', true);
    return;
}

// Nuevo mensaje
if (isset($_GET['r']) && $_GET['r'] === 'nuevo-mensaje') {
    require_once 'controladores/mensajes.controller.php';
    $destinatarios = ControladorMensajes::obtenerDestinatariosDisponibles($_SESSION['idUsuario']);
    require 'vistas/paginas/mensajes/nuevo-mensaje.php';
    define('RUTA_EJECUTADA', true);
    return;
}

// Guardar mensaje
if (isset($_GET['r']) && $_GET['r'] === 'guardar_mensaje') {
    require_once 'controladores/mensajes.controller.php';
    $resultado = ControladorMensajes::crtGuardarMensaje();
    if ($resultado === 'ok') {
        ToastifyController::success('Mensaje enviado con éxito');
        header("Location: ?r=bandeja-entrada");
        exit;
    } else {
        ToastifyController::error('No se pudo enviar el mensaje');
        header("Location: ?r=nuevo-mensaje");
        exit;
    }
}

// Ver mensaje
if (isset($_GET['r']) && $_GET['r'] === 'ver-mensaje') {
    require_once 'controladores/mensajes.controller.php';
    $mensaje = ControladorMensajes::crtMostrarUnMensaje($_GET['id']);
    require 'vistas/paginas/mensajes/ver-mensaje.php';
    define('RUTA_EJECUTADA', true);
    return;
}

// Marcar leído
if (isset($_GET['r']) && $_GET['r'] === 'marcar-leido') {
    require_once 'controladores/mensajes.controller.php';
    ControladorMensajes::crtMarcarLeido($_GET['id']);
    header("Location: ?r=bandeja-entrada");
    return;
}

// Marcar no leído
if (isset($_GET['r']) && $_GET['r'] === 'marcar-no-leido') {
    require_once 'controladores/mensajes.controller.php';
    ControladorMensajes::crtMarcarNoLeido($_GET['id']);
    header("Location: ?r=bandeja-entrada");
    return;
}
