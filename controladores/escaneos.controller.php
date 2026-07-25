<?php

require_once('modelos/escaneos.modelo.php');
class EscaneosController
{
    /**
     * URL de llamada: 
     *  index.php?r=registrar_escaneo
     *    &ronda_id=123
     *    &sector_id=123
     *    &vigilador_id=5
     */
    public static function registrar()
    {
        //Auth::check('escaneos', 'registrar');
        $jsonResponse = ($_GET['format'] ?? '') === 'json';
        header('Content-Type: ' . ($jsonResponse ? 'application/json' : 'text/html') . '; charset=utf-8');

        $rondaId     = intval($_GET['ronda_id']     ?? 0);
        $sectorId    = intval($_GET['sector_id']    ?? 0);
        $vigiladorId = intval($_SESSION['idUsuario'] ?? 0);
        $operacionId = trim((string)($_SERVER['HTTP_X_SPEC_OPERATION_ID'] ?? ($_GET['operacion_id'] ?? '')));
        $fechaEvento = self::normalizarFechaEvento($_GET['fecha_evento'] ?? null);
        if ($operacionId === '') {
            $operacionId = bin2hex(random_bytes(16));
        }

        if (!$rondaId || !$sectorId || !$vigiladorId) {
            http_response_code(400);
            exit('Parámetros incompletos');
        }

        if (!preg_match('/^[a-zA-Z0-9-]{16,64}$/', $operacionId)) {
            http_response_code(400);
            exit($jsonResponse
                ? json_encode(['success' => false, 'error' => 'Identificador de operación inválido'])
                : 'Identificador de operación inválido');
        }

        $data = [
            'ronda_id'     => $rondaId,
            'sector_id'    => $sectorId,
            'vigilador_id' => $vigiladorId,
            'fecha_hora'   => $fechaEvento,
            'operacion_id' => $operacionId
        ];

        $res = ModeloEscaneos::mdlGuardarEscaneo('escaneos', $data);

        if ($res === 'ok') {
            ToastifyController::success('Escaneo registrado correctamente');
            // Guardamos IDs en sesión para el feedback
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['ultima_ronda_id']  = $rondaId;
            $_SESSION['ultimo_sector_id'] = $sectorId;
        } else {
            ToastifyController::error("<h3>Error al registrar:</h3><pre>" . htmlspecialchars($res) . "</pre>");
        }

        if ($jsonResponse) {
            echo json_encode([
                'success' => $res === 'ok',
                'error' => $res === 'ok' ? null : $res
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: ?r=escaneo_feedback');
        exit;
    }

    private static function normalizarFechaEvento($fecha): string
    {
        try {
            $date = $fecha ? new DateTime((string)$fecha) : new DateTime();
            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
            return $date->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return date('Y-m-d H:i:s');
        }
    }

    public static function feedback()
    {
        // Asegúrate de que session esté iniciado
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $db = new Conexion();

        // IDs almacenados tras el registrar()
        $rondaId  = $_SESSION['ultima_ronda_id']  ?? 0;
        $sectorId = $_SESSION['ultimo_sector_id'] ?? 0;

        // Traemos datos de la ronda incluyendo su puesto y el objetivo asociado
        $sql = "SELECT  r.puesto, r.objetivo_id, o.nombre AS objetivo_nombre
                    FROM rondas r
                    JOIN objetivos o
                    ON r.objetivo_id = o.idObjetivo
                    WHERE r.idRonda = ?
                    LIMIT 1 ";
        $res = $db->consultas($sql, [$rondaId]);
        if (!empty($res)) {
            $nombrePuesto    = $res[0]['puesto'];
            $nombreObjetivo  = $res[0]['objetivo_nombre'];
        } else {
            $nombrePuesto   = "Puesto #$rondaId";
            $nombreObjetivo = "Objetivo desconocido";
        }

        include __DIR__ . '/../vistas/paginas/rondas/feedback.php';
    }
}
