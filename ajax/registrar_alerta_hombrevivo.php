<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json');

require_once '../modelos/conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
$usuarioId = intval($input['usuario_id'] ?? 0);
$objetivoId   = intval($input['objetivo_id'] ?? 0);
$tiempo    = intval($input['tiempo'] ?? 0);

if (!$usuarioId || !$rondaId) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $db = new Conexion();

    // Obtener datos del vigilador, objetivo y puesto
    $sql = "SELECT 
                u.nombre, u.apellido, u.telefono, u.rol,
                o.nombre AS objetivo,
                p.nombre AS puesto
            FROM usuarios u
            JOIN turnos t ON t.vigilador_id = u.idUsuario
            JOIN objetivos o ON t.objetivo_id = o.idObjetivo
            JOIN puestos p ON t.puesto_id = p.idPuesto
            WHERE u.idUsuario = ? AND t.fecha = CURDATE()
            LIMIT 1";

    $info = $db->consultas($sql, [$usuarioId])[0] ?? null;

    if (!$info) {
        echo json_encode(['error' => 'No se encontró información del vigilador']);
        exit;
    }

    // Formar mensaje detallado
    $mensaje = "⚠ Demora en reporte de hombre vivo:\n"
        . "👤 {$info['apellido']}, {$info['nombre']}\n"
        . "📱 {$info['telefono']}\n"
        . "🎯 Objetivo: {$info['objetivo']}\n"
        . "🛡 Puesto: {$info['puesto']}\n"
        . "⏱ Tiempo transcurrido: " . gmdate("i:s", $tiempo);

    // Insertar alerta solo si no existe ya para hoy y esta ronda
    $sql = "SELECT COUNT(*) AS total FROM alertas 
            WHERE usuario_id = ? AND ronda_id = ? AND tipo = 'hombre_vivo'
              AND DATE(creada_en) = CURDATE()";

    $existe = $db->consultas($sql, [$usuarioId, $rondaId])[0]['total'] ?? 0;

    if ($existe == 0) {
        $sql = "INSERT INTO alertas (usuario_id, ronda_id, tipo, mensaje, leida)
                VALUES (?, ?, 'hombre_vivo', ?, 0)";
        $db->consultas($sql, [$usuarioId, $rondaId, $mensaje]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("❌ Error en alerta hombre vivo: " . $e->getMessage());
    echo json_encode(['error' => 'Error en el servidor']);
}
