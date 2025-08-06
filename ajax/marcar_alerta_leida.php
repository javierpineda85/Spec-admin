<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json');

require_once '../modelos/conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
$idAlerta = intval($input['id'] ?? 0);

if (!$idAlerta) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $db = new Conexion();
    $sql = "UPDATE alertas SET leida = 1 WHERE idAlerta = ?";
    $db->consultas($sql, [$idAlerta]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al marcar como leída']);
}
exit;
