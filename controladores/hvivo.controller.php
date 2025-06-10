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
        include __DIR__ . '/../vistas/paginas/h-vivo/reporte_hombre_vivo.php';
    }
    public static function vistaListadoReportesHombreVivo()
    {
        Auth::check('hvivo', 'vistaListadoReportesHombreVivo');
        include __DIR__ . '/../vistas/paginas/h-vivo/listado_reportesHvivo.php';
    }
}
