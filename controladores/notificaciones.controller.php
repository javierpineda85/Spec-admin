<?php
class NotificacionesController
{
    /** ALERTAS **/
    public static function contarAlertasNoLeidas($usuarioId)
    {
        return AlertasController::contarNoLeidas($usuarioId);
    }

    public static function obtenerAlertasNoLeidas($usuarioId, $limite = 10)
    {
        return AlertasController::obtenerNoLeidas($usuarioId, $limite);
    }

    /** MENSAJES **/
    public static function contarMensajesNoLeidos($usuarioId)
    {
        $recibidos = ControladorMensajes::crtMostrarMensajes('destinatario_id', $usuarioId);
        $noLeidos = array_filter($recibidos, fn($m) => $m['leido'] == 0);
        return count($noLeidos);
    }

    public static function obtenerMensajesNoLeidos($usuarioId, $limite = 10)
    {
        $recibidos = ControladorMensajes::crtMostrarMensajes('destinatario_id', $usuarioId);
        $noLeidos = array_filter($recibidos, fn($m) => $m['leido'] == 0);
        return array_slice($noLeidos, 0, $limite);
    }
}


?>