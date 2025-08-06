<?php
// libraries/ajax/ajax_rondas.php

// 1️⃣ Limpia TODA salida previa de buffers
while (ob_get_level()) {
    ob_end_clean();
}

// 2️⃣ Desactiva la salida de errores (protégete contra notices/warnings)
ini_set('display_errors', 0);
error_reporting(0);

// 3️⃣ Cabecera JSON y sesión
header('Content-Type: application/json; charset=utf-8');
session_start();

// 4️⃣ Validaciones básicas
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || empty($_POST['action']) || $_POST['action'] !== 'save'
    || empty($_POST['qrData'])
) {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

// 5️⃣ Decodifica y verifica el arreglo
$rondas = json_decode($_POST['qrData'], true);
if (!is_array($rondas)) {
    echo json_encode(['success' => false, 'error' => 'JSON mal formado']);
    exit;
}

require_once __DIR__ . '/../../modelos/conexion.php';
$db = Conexion::conectar();

try {
    $ids = array_map(fn($r) => intval($r['idRonda']), $rondas);
    if (empty($ids)) throw new Exception('No hay rondas que activar');

    $in = implode(',', $ids);
    $db->beginTransaction();
    $db->exec("UPDATE rondas SET status='active' WHERE idRonda IN ($in)");
    $db->commit();

    // Ya se activaron: limpiamos sesión
    unset($_SESSION['qr_codes']);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
