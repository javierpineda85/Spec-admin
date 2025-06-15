<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/hvivo.modelo.php');

class HombreVivoController
{
    public static function registrar()
    {
        Auth::check('hvivo', 'registrar');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $rondaId   = intval($_GET['ronda_id']   ?? 0);
        $usuarioId = intval($_GET['id_usuario'] ?? 0);
        $demora    = $_GET['demora'] ?? null; // '00:02:15'

        if (!$rondaId || !$usuarioId || !$demora) {
            echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            exit;
        }

        $datos = [
            'id_usuario' => $usuarioId,
            'ronda_id'   => $rondaId,
            'demora'     => $demora
        ];
        $res = ModeloReporteHombreVivo::mdlGuardarReporte('reporte_hombre_vivo', $datos);

        if ($res === 'ok') {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $res]);
        }
        exit;
    }
    public static function vistaHombreVivo()
    {
        Auth::check('hvivo', 'vistaHombreVivo');

        $objetivoId = intval($_SESSION['objetivo_id'] ?? 0);
        $userId = $_SESSION['idUsuario'];
        $db     = Conexion::conectar();
        // 1) ¿Ya marcó la entrada hoy?
        $sqlEntry = " SELECT COUNT(*) AS cnt
                            FROM marcaciones_servicio
                            WHERE vigilador_id = ?
                            AND DATE(fecha_hora) = CURDATE()
                            AND tipo_evento = 'entrada'";
        $stmtE     = $db->prepare($sqlEntry);
        $stmtE->execute([$userId]);
        $entradasHoy = $stmtE->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        $_SESSION['hVivo_tiene_entrada'] = $entradasHoy > 0;

        // ¿Ya marcó la salida hoy?
        $sqlExit = str_replace('entrada', 'salida', $sqlEntry);
        $stmtX    = $db->prepare($sqlExit);
        $stmtX->execute([$userId]);
        $salidasHoy = $stmtX->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        $_SESSION['hVivo_ya_salida'] = $salidasHoy > 0;

        // Guarda en la sesión o pasa a la vista
        $_SESSION['hVivo_ya_salida'] = $salidasHoy > 0;
        $_SESSION['ultimo_objetivo'] = $objetivoId;

        include __DIR__ . '/../vistas/paginas/h-vivo/reporte_hombre_vivo.php';
    }
    public static function vistaListadoReportesHombreVivo()
    {
        Auth::check('hvivo', 'vistaListadoReportesHombreVivo');
        include __DIR__ . '/../vistas/paginas/h-vivo/listado_reportesHvivo.php';
    }
    public static function ajaxRegistrarReporte()
    {

        Auth::check('hvivo', 'ajaxRegistrarReporte');

        header('Content-Type: application/json; charset=utf-8');

        // Lee y valida parámetros…

        $userId  = intval($_GET['id_usuario'] ?? 0);
        $objetivoId = intval($_GET['objetivo_id'] ?? ($_SESSION['ultimo_objetivo'] ?? 0));
        $demora  = $_GET['demora'] ?? '';
        if (!$objetivoId || !$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
            exit;
        }

        // Inserta en BD…
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO reporte_hombre_vivo (id_usuario, objetivo_id, demora, fecha_hora)
                VALUES (?, ?, ?, NOW())";
            $ok  = $db->prepare($sql)->execute([$userId, $objetivoId, $demora]);

            if ($ok) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
