<?php
//require_once("modelos/qr.modelo.php");
//require_once('libraries/phpqrcode/qrlib.php');
// Usamos __DIR__ para construir rutas correctas
require_once __DIR__ . '/../modelos/qr.modelo.php';
require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';
session_start();

class QrController
{
    static public function generar()
    {
        $nombreRonda = isset($_POST['nombreRonda']) ? trim($_POST['nombreRonda']) : '';
        $objetivo = isset($_POST['objetivo']) ? trim($_POST['objetivo']) : '';
        $orden = isset($_POST['orden']) ? trim($_POST['orden']) : '';

        if (empty($nombreRonda)) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $currentTime = date("YmdHis");
        $qrContent = $nombreRonda . " - " . $objetivo . " - " . $orden . " - " . $currentTime;

        $folder = 'img/qrcodes';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        $filename = $folder . '/qr_' . time() . '.png';

        QRcode::png($qrContent, $filename, QR_ECLEVEL_H, 8, 2);

        $qr = [
            'data' => $qrContent,
            'image' => 'img/qrcodes/' . basename($filename)
        ];

        $qrModel = new QrModel();
        $qrModel->guardarQrEnSesion($qr);

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    static public function updateAction()
    {
        $response = ['success' => false];

        if (isset($_POST['action'], $_POST['key'], $_POST['newData'])) {
            $key = $_POST['key'];
            if (!isset($_SESSION['qr_codes'][$key])) {
                echo json_encode($response);
                exit;
            }

            // Utiliza el nuevo dato ingresado
            $newData = trim($_POST['newData']);
            $currentTime = date("YmdHis");
            $newContent = $newData . " - " . $currentTime;

            // Define rutas absolutas y relativas
            //$folderAbsolute = $_SERVER['DOCUMENT_ROOT'] . '/spec-admin/img/qrcodes';
            $folderAbsolute = $_SERVER['DOCUMENT_ROOT'] . '/img/qrcodes';
            $folderRelative = 'img/qrcodes';
            if (!is_dir($folderAbsolute)) {
                mkdir($folderAbsolute, 0777, true);
            }

            // Eliminar la imagen antigua (convertir la ruta relativa a absoluta)
            $oldImageRelative = $_SESSION['qr_codes'][$key]['image'];
            //$oldImageAbsolute = $_SERVER['DOCUMENT_ROOT'] . '/spec-admin/' . $oldImageRelative;
            $oldImageAbsolute = $_SERVER['DOCUMENT_ROOT'] . $oldImageRelative;
            if (file_exists($oldImageAbsolute)) {
                unlink($oldImageAbsolute);
            }

            // Generar el nuevo QR
            $newFilenameAbsolute = $folderAbsolute . '/qr_' . time() . '.png';
            QRcode::png($newContent, $newFilenameAbsolute, QR_ECLEVEL_H, 8, 2);

            // Actualizar la sesión: almacenar la ruta relativa para mostrar la imagen
            $_SESSION['qr_codes'][$key]['data'] = $newContent;
            $_SESSION['qr_codes'][$key]['image'] = $folderRelative . '/' . basename($newFilenameAbsolute);

            $response['success'] = true;
            $response['newImage'] = $_SESSION['qr_codes'][$key]['image'];
        }
        echo json_encode($response);
    }

    public function deleteAction()
    {
        $response = ['success' => false];
        if (isset($_POST['action'], $_POST['key'])) {
            $key = $_POST['key'];
            if (!isset($_SESSION['qr_codes'][$key])) {
                echo json_encode($response);
                exit;
            }
            // Obtener la ruta absoluta de la imagen a eliminar
            $imageRelative = $_SESSION['qr_codes'][$key]['image'];
            $imageAbsolute = $_SERVER['DOCUMENT_ROOT'] . '/spec-admin/' . $imageRelative;
            if (file_exists($imageAbsolute)) {
                unlink($imageAbsolute);
            }
            unset($_SESSION['qr_codes'][$key]);
            $_SESSION['qr_codes'] = array_values($_SESSION['qr_codes']);
            $response['success'] = true;
        }
        echo json_encode($response);
    }
}
?>
