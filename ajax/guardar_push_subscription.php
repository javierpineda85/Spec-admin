<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../modelos/push.modelo.php';

if (!isset($_SESSION['idUsuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Suscripción inválida']);
    exit;
}

$res = ModeloPush::mdlGuardarSuscripcion((int)$_SESSION['idUsuario'], $data);

if ($res === 'ok') {
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => $res]);
