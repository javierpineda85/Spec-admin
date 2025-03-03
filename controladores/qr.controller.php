<?php
// Usamos __DIR__ para construir rutas correctas
require_once __DIR__ . '/../modelos/qr.modelo.php';
require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class QrController
{
    static public function generar()
    {
        $puesto = isset($_POST['puesto']) ? trim($_POST['puesto']) : '';
        $objetivo = isset($_POST['objetivo_id']) ? trim($_POST['objetivo_id']) : '';
        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
        $orden = isset($_POST['orden']) ? trim($_POST['orden']) : '';

        if (empty($puesto)) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $currentTime = date("YmdHis");
        $qrContent = "Sector: " . $puesto . " - Objetivo: " . $objetivo . "- Tipo: ". $tipo ." - Orden: " . $orden . " - Idtemp:" . $currentTime;

        $folder = 'img/qrcodes';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        $filename = $folder . '/qr_' . time() . '.png';
        //$filename = $folder .'/'. $currentTime .'.png';
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


    static public function deleteAction()
    {
        $response = ['success' => false];

        if (isset($_POST['action'], $_POST['key'])) {
            $key = intval($_POST['key']); // Asegurar que es un número

            if (!isset($_SESSION['qr_codes'][$key])) {
                echo json_encode($response);
                exit;
            }

            // 1️⃣ Obtener la ruta absoluta de la imagen
            $imageRelative = $_SESSION['qr_codes'][$key]['image'];

            //$imageAbsolute = $_SERVER['DOCUMENT_ROOT'] . '/img/qrcodes/' . $imageRelative;
            $imageAbsolute = __DIR__ . '/../' . $imageRelative;
            // 2️⃣ Eliminar la imagen si existe
            if (file_exists($imageAbsolute)) {
                unlink($imageAbsolute);
                // Buscar y eliminar el archivo de errores si existe
                $errorFile = __DIR__ . '/../libraries/phpqrcode/' . basename($imageAbsolute) . '-errors.txt';
                if (file_exists($errorFile)) {
                    unlink($errorFile);
                }
            }

            // 3️⃣ Eliminar el QR de la sesión
            unset($_SESSION['qr_codes'][$key]);

            // 4️⃣ Reindexar el array de sesiones para evitar problemas con `data-key`
            $_SESSION['qr_codes'] = array_values($_SESSION['qr_codes']);

            // 5️⃣ Enviar respuesta con los QR restantes
            $response = [
                'success' => true,
                'remaining_qr' => $_SESSION['qr_codes']
            ];
        }

        // 6️⃣ Imprimir JSON correctamente
        echo json_encode($response);
        exit;
    }
}
