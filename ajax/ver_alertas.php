<?php
require_once '../modelos/conexion.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode([]);
    exit;
}

$usuarioId = $_SESSION['idUsuario'];

try {
    $db = new Conexion();
    $sql = "SELECT * FROM alertas 
            WHERE usuario_id = ? AND leida = 0 
            ORDER BY creada_en DESC 
            LIMIT 10";
    $alertas = $db->consultas($sql, [$usuarioId]);
    echo json_encode($alertas);
} catch (Exception $e) {
    error_log("❌ Error en verAlertasNoLeidas: " . $e->getMessage());
    echo json_encode([]);
}
