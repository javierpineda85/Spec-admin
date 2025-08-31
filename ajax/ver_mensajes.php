<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once '../modelos/mensajes.modelo.php';
require_once '../modelos/conexion.php';

$idUsuario = $_SESSION['idUsuario'] ?? null;

if (!$idUsuario) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$mensajes = ModeloMensajes::mdlMostrarMensajes('destinatario_id', $idUsuario);
$noLeidos = array_values(array_filter($mensajes, fn($m) => $m['leido'] == 0));

header('Content-Type: application/json');
echo json_encode($noLeidos);
