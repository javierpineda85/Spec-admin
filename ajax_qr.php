<?php
// ajax.php
session_start();
header('Content-Type: application/json');

if (isset($_GET['action'])) {
    require_once('controladores/qr.controller.php');
    $controller = new QrController();

    switch ($_GET['action']) {
        case 'update_qr':
            $controller->updateAction();
            break;
        case 'delete_qr':
            $controller->deleteAction();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se especificó acción']);
}
