<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode([]);
    exit;
}

require_once '../modelos/conexion.php';

$tipo = $_GET['tipo'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$usuarioId = $_SESSION['idUsuario'];

try {
    $db = new Conexion();

    $sql = "SELECT a.*, u.nombre, u.apellido, o.nombre AS objetivo
            FROM alertas a
            LEFT JOIN usuarios u ON a.usuario_id = u.idUsuario
            LEFT JOIN objetivos o ON a.objetivo_id = o.idObjetivo
            WHERE a.leida = 1 AND a.usuario_id = ?
            ";

    $params = [$usuarioId];

    if ($tipo) {
        $sql .= " AND a.tipo = ?";
        $params[] = $tipo;
    }

    if ($desde && $hasta) {
        $sql .= " AND DATE(a.creada_en) BETWEEN ? AND ?";
        $params[] = $desde;
        $params[] = $hasta;
    }

    $sql .= " ORDER BY a.creada_en DESC LIMIT 50";
    $resultados = $db->consultas($sql, $params);

    // Armar datos
    $alertas = [];
    foreach ($resultados as $a) {
        $alertas[] = [
            'creada_en' => $a['creada_en'],
            'tipo' => $a['tipo'],
            'mensaje' => $a['mensaje'],
            'usuario' => $a['apellido'] . ' ' . $a['nombre'],
            'objetivo' => $a['objetivo']
        ];
    }

    echo json_encode($alertas);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error interno']);
}
exit;
