<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// ✅ Incluir clase de conexión
require_once '../modelos/conexion.php';

// ✅ Incluir modelo de mensajes
require_once '../modelos/mensajes.modelo.php';

if (!isset($_POST['idMensaje']) || !is_numeric($_POST['idMensaje'])) {
    echo json_encode(['exito' => false, 'error' => 'ID no válido']);
    exit;
}

$id = intval($_POST['idMensaje']);
$mensaje = ModeloMensajes::mdlMostrarUnMensaje($id);

if ($mensaje && count($mensaje)) {
    $m = $mensaje[0];

    // Validación de acceso
    if ($_SESSION['idUsuario'] != $m['remitente_id'] && $_SESSION['idUsuario'] != $m['destinatario_id']) {
        echo json_encode(['exito' => false, 'error' => 'Acceso denegado']);
        exit;
    }

    // ✅ Marcar como leído
    ModeloMensajes::mdlMarcarLeido($id);

    echo json_encode([
        'exito' => true,
        'nombre' => $m['nombre'],
        'apellido' => $m['apellido'],
        'contenido' => nl2br(htmlspecialchars($m['contenido'])),
        'fecha' => $m['fMensaje'],
        'hora' => $m['horaMensaje']
    ]);
} else {
    echo json_encode(['exito' => false, 'error' => 'Mensaje no encontrado']);
}
