<?php
ob_start(); // Evita que se envíen headers antes del JSON
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json'); // Asegurar que la respuesta es JSON

require_once __DIR__ . '/../../modelos/rondas.modelo.php'; // Accede correctamente a la carpeta modelos
require_once __DIR__ . '/../../controladores/rondas.controller.php'; // Accede correctamente a la carpeta controladores


try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'save' && isset($_POST['qrData'])) {
            $rondas = json_decode($_POST['qrData'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error en el formato JSON: ' . json_last_error_msg());
            }

            // Llamar al controlador
            $respuesta = RondasController::crtGuardarRondas($rondas);
            
            // Si la respuesta no es JSON, forzar una estructura JSON válida
            if (!is_array($respuesta)) {
                throw new Exception('Respuesta inesperada del controlador.');
            }

            echo json_encode(['success' => true, 'data' => $respuesta]);
        } else {
            throw new Exception('Acción no reconocida o datos faltantes');
        }
    } else {
        throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    ob_end_clean(); // Limpia cualquier salida previa
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
