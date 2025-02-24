
<?php
session_start();
header('Content-Type: application/json');
require_once '../../controladores/qr.controller.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $qrController = new QrController();
    if ($action === 'update_qr') {
        $qrController->updateAction();
    } elseif ($action === 'delete_qr') {
        $qrController->deleteAction();
    } else {
        echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se definió acción']);
}
?>
