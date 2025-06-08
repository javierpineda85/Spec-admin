<?php

require_once('modelos/qr.modelo.php');
require_once('libraries/phpqrcode/qrlib.php');
require_once('modelos/conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class QrController
{
    public static function generar()
    {

        // 1) Validar datos
        $puesto       = trim($_POST['puesto']        ?? '');
        $objetivoId   = intval($_POST['objetivo_id'] ?? 0);
        $tipo         = trim($_POST['tipo']          ?? '');
        $orden        = intval($_POST['orden']       ?? 0);

        if (!$puesto || !$objetivoId || !$tipo || !$orden) {
            $_SESSION['error_message'] = 'Faltan datos para generar la ronda.';
            header("Location: ?r=crear_rondas");
            exit;
        }

        // 2) Insertar la ronda y obtener su ID
        $datosRonda = [
            'puesto'        => $puesto,
            'objetivo_id'   => $objetivoId,  
            'tipo'          => $tipo,
            'orden_escaneo' => $orden,
            'status'        => 'draft'
            
        ];
        $idRonda = ModeloRondas::mdlGuardarRonda('rondas', $datosRonda);

        // 3) Verificar éxito
        if (!is_numeric($idRonda) || intval($idRonda) <= 0) {
            $_SESSION['error_message'] = 'Error al crear la ronda: ' . $idRonda;
            header("Location: ?r=crear_rondas");
            exit;
        }
        $idRonda = intval($idRonda);

        // 4) Traer nombre del objetivo para mostrar
        $db   = Conexion::conectar();
        $stmt = $db->prepare("SELECT nombre FROM objetivos WHERE idObjetivo = ?");
        $stmt->execute([$objetivoId]);
        $nombreObj = $stmt->fetchColumn() ?: '';

        // 5) Guardar en sesión para la vista (sin escribir archivo)
        if (!isset($_SESSION['qr_codes']) || !is_array($_SESSION['qr_codes'])) {
            $_SESSION['qr_codes'] = [];
        }
        $_SESSION['qr_codes'][] = [
            'idRonda'         => $idRonda,
            'objetivo_id'     => $objetivoId,
            'objetivo_nombre' => $nombreObj,
            'puesto'          => $puesto,
            'orden'           => $orden
        ];

        $_SESSION['success_message'] = 'QR creado con éxito.';
        header("Location: ?r=crear_rondas");
        exit;
    }

    public static function mostrar()
    {

        // 1️⃣ Asegura buffer limpio
        while (ob_get_level()) {
            ob_end_clean();
        }

        //  biblioteca

        require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';

        // Parámetros mínimos
        $rondaId = intval($_GET['ronda_id'] ?? 0);
        if (!$rondaId) {
            header('HTTP/1.1 400 Bad Request');
            exit('Falta parámetro ronda_id');
        }

        //  Construye la URL que va dentro del QR
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host  = $_SERVER['HTTP_HOST'];
        $scanUrl = sprintf(
            '%s://%s/index.php?r=registrar_escaneo&ronda_id=%d&sector_id=%d&vigilador_id=%d',
            $proto,
            $host,
            $rondaId,
            $rondaId,
            intval($_GET['vigilador_id'] ?? 0)
        );

        // 5 Sirve el PNG limpio
        header('Content-Type: image/png');
        QRcode::png($scanUrl, false, QR_ECLEVEL_H, 8, 2);
        exit;
    }
// controladores/qr.controller.php
public static function delete()
{
    session_start();
    // 1️ Limpia cualquier buffer accidental
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    // 2️ Recoge el key vía POST
    $key = isset($_POST['key']) ? intval($_POST['key']) : null;
    if ($key === null || !isset($_SESSION['qr_codes'][$key])) {
        echo json_encode(['success' => false]);
        exit;
    }

    // 3️ Recupera el idRonda del borrador
    $idRonda = intval($_SESSION['qr_codes'][$key]['idRonda']);

    // 4️⃣ Eliminar de la sesión y reindexar
    unset($_SESSION['qr_codes'][$key]);
    $_SESSION['qr_codes'] = array_values($_SESSION['qr_codes']);

    // 5️ Eliminar el draft de la BD
    require_once __DIR__ . '/../modelos/conexion.php';
    $db = Conexion::conectar();
    $stmt = $db->prepare("DELETE FROM rondas WHERE idRonda = :idRonda AND status = 'draft'");
    $stmt->bindParam(':idRonda', $idRonda, PDO::PARAM_INT);
    $stmt->execute();

    // 6️⃣ Devuelve el JSON limpio
    echo json_encode(['success' => true]);
    exit;
}

}
