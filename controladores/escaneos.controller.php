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
        Auth::check('escaneos', 'registrar');
        header('Content-Type: text/html; charset=utf-8');

        $rondaId     = intval($_GET['ronda_id']     ?? 0);
        $sectorId    = intval($_GET['sector_id']    ?? 0);
        $vigiladorId = intval($_GET['vigilador_id'] ?? 0);

        if (!$rondaId || !$sectorId || !$vigiladorId) {
            http_response_code(400);
            exit('Parámetros incompletos');
        }

        $data = [
            'ronda_id'     => $rondaId,
            'sector_id'    => $sectorId,
            'vigilador_id' => $vigiladorId
        ];

        $res = ModeloEscaneos::mdlGuardarEscaneo('escaneos', $data);

        if ($res === 'ok') {
            $_SESSION['success_message'] = "Escaneo registrado correctamente.";
        } else {
            $_SESSION['error_message'] = "<h3>Error al registrar:</h3><pre>" . htmlspecialchars($res) . "</pre>";
        }
        header('Location: ?r=escaneo_feedback');
        exit;
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
