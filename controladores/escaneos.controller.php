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
            // Mensaje de confirmación sencillo
            echo "<h3>¡Escaneo registrado!</h3>";
            echo "<p>Ronda ID: $rondaId<br>Sector ID: $sectorId</p>";
        } else {
            echo "<h3>Error al registrar:</h3><pre>" . htmlspecialchars($res) . "</pre>";
        }
        exit;
    }
}
