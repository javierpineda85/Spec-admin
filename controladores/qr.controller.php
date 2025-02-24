<?php
require_once("modelos/qr.modelo.php");
require_once('libraries/phpqrcode/qrlib.php');
session_start();
//require_once __DIR__ . '/../../libraries/phpqrcode/qrlib.php';
//require_once __DIR__ . '/../models/QrModel.php';

class QrController {

    static public function generar() {
        // Recupera datos enviados desde el formulario
        $nombreRonda = isset($_POST['nombreRonda']) ? trim($_POST['nombreRonda']) : '';
        $objetivo = isset($_POST['objetivo']) ? trim($_POST['objetivo']) : '';
        $orden = isset($_POST['orden']) ? trim($_POST['orden']) : '';

        if(empty($nombreRonda)) {
            // Redirige o muestra error si falta dato
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        // Genera el contenido del QR 
        $currentTime = date("YmdH:i:s");
        $qrContent = $nombreRonda . " - " . $objetivo . " - " . $orden . " - " . $currentTime;

        //Se guarda la img dentro de la carpeta de img
        $folder = 'img/qrcodes';
        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }
        $filename = $folder . '/qr_' . time() . '.png';

        // Genera el código QR 
        QRcode::png($qrContent, $filename, QR_ECLEVEL_H, 8, 2);

        // Prepara el array con datos del QR
        $qr = [
            'data' => $qrContent,
            'image' => 'img/qrcodes/' . basename($filename)
        ];

        // Guarda en sesión utilizando el modelo
        $qrModel = new QrModel();
        $qrModel->guardarQrEnSesion($qr);

        // Redirige de vuelta a la vista (o carga la vista con los datos actualizados)
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
    public function updateAction() {
       
        $nombreRonda = isset($_POST['nombreRonda']) ? trim($_POST['nombreRonda']) : '';
        $objetivo = isset($_POST['objetivo']) ? trim($_POST['objetivo']) : '';
        $orden = isset($_POST['orden']) ? trim($_POST['orden']) : '';
        $response = ['success' => false];
        
        if(isset($_POST['action'], $_POST['key'], $_POST['newData'])) {
            $key = $_POST['key'];
            if(!isset($_SESSION['qr_codes'][$key])){
                echo json_encode($response);
                exit;
            }

            $currentTime = date("H:i:s");
            $newContent = $nombreRonda . " - " . $objetivo . " - " . $orden . " - " . $currentTime;

            $folder = 'img/qrcodes';
            if(!is_dir($folder)){
                mkdir($folder, 0777, true);
            }
            if(file_exists($_SESSION['qr_codes'][$key]['image'])){
                unlink($_SESSION['qr_codes'][$key]['image']);
            }
            $newFilename = $folder . '/qr_' . time() . '.png';
            QRcode::png($newContent, $newFilename, QR_ECLEVEL_H, 8, 2);

            $_SESSION['qr_codes'][$key]['data'] = $newContent;
            $_SESSION['qr_codes'][$key]['image'] = $newFilename;

            $response['success'] = true;
            $response['newImage'] = $newFilename;
        }
        echo json_encode($response);
    }

    public function deleteAction() {
        session_start();
        $response = ['success' => false];
        if(isset($_POST['action'], $_POST['key'])) {
            $key = $_POST['key'];
            if(!isset($_SESSION['qr_codes'][$key])){
                echo json_encode($response);
                exit;
            }
            if(file_exists($_SESSION['qr_codes'][$key]['image'])){
                unlink($_SESSION['qr_codes'][$key]['image']);
            }
            unset($_SESSION['qr_codes'][$key]);
            $_SESSION['qr_codes'] = array_values($_SESSION['qr_codes']);
            $response['success'] = true;
        }
        echo json_encode($response);
    }
}
?>
