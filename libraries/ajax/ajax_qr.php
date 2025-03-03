<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require_once '../../controladores/qr.controller.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'error' => 'No se definió acción']);
    exit;
}

$action = $_POST['action'];

if ($action === 'delete') {
    QrController::deleteAction();
} else {
    echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
    exit;
}
?>