<?php
require_once('modelos/mensajes.modelo.php');

class ControladorMensajes
{
    static public function crtMostrarMensajes($item, $valor)
    {
        Auth::check('mensajes', 'crtMostrarMensajes');
        $respuesta = ModeloMensajes::mdlMostrarMensajes($item, $valor);
        return $respuesta;

        exit;
    }

    static public function crtMostrarMensajesEnviados($item, $valor)
    {
        Auth::check('mensajes', 'crtMostrarMensajesEnviados');
        $respuesta = ModeloMensajes::mdlMostrarMensajesEnviados($item, $valor);
        return $respuesta;

        exit;
    }
    static public function crtMostrarUnMensaje($id)
    {
        Auth::check('mensajes', 'crtMostrarUnMensaje');
        $respuesta = ModeloMensajes::mdlMostrarUnMensaje($id);
        return $respuesta;

        exit;
    }

    static public function crtGuardarMensaje()
    {
        Auth::check('mensajes', 'crtGuardarMensaje');
        if (isset($_POST["id_destinatario"])) {
            $datos = array(
                "id_remitente"        => $_POST["id_remitente"],
                "id_destinatario"     => $_POST["id_destinatario"],
                "contenidoMensaje"    => $_POST["contenidoMensaje"],
                "fechaMensaje"        => date('Y-m-d H:i:s')
            );
            if (!self::puedeEnviar($datos["id_remitente"], $datos["id_destinatario"])) {
                ToastifyController::error('No tienes permiso para enviar este mensaje');
                return null;
            }

            $respuesta = ModeloMensajes::mdlGuardarMensaje($datos);
            ToastifyController::success('Mensaje enviado exitosamente');
            return $respuesta;
        }
    }

    static public function puedeEnviar($id_remitente, $id_destinatario)
    {
        $db = new Conexion();

        $remitente = $db->consultas("SELECT rol FROM usuarios WHERE idUsuario = $id_remitente")[0]['rol'] ?? '';
        $destinatario = $db->consultas("SELECT rol FROM usuarios WHERE idUsuario = $id_destinatario")[0]['rol'] ?? '';

        // Nivel jerárquico (menor a mayor)
        $jerarquia = [
            'vigilador' => 1,
            'referente' => 2,
            'supervisor1' => 3,
            'supervisor2' => 3,
            'diagramador' => 3,
            'administrativo' => 4,
            'gerencia' => 5
        ];

        $nivelRem = $jerarquia[strtolower($remitente)] ?? 0;
        $nivelDest = $jerarquia[strtolower($destinatario)] ?? 0;

        // Reglas base:
        if (in_array($remitente, ['administrativo', 'gerencia'])) {
            return true; // Puede enviar a cualquiera
        }

        // Si el destinatario es gerencia o administrativo
        if (in_array($destinatario, ['administrativo', 'gerencia'])) {
            // ¿Existe un mensaje previo de gerencia/adm. hacia este usuario?
            $hayMensajePrevio = $db->consultas("SELECT COUNT(*) AS total FROM mensajes WHERE remitente_id = $id_destinatario AND destinatario_id = $id_remitente")[0]['total'] ?? 0;

            if ($hayMensajePrevio > 0) {
                // ¿Ya respondió este usuario a ese remitente?
                $yaRespondio = $db->consultas("SELECT COUNT(*) AS total FROM mensajes WHERE remitente_id = $id_remitente AND destinatario_id = $id_destinatario")[0]['total'] ?? 0;
                return $yaRespondio < 1; // solo una respuesta permitida
            }
            return false; // No puede iniciar contacto
        }

        // Si ambos están en niveles intermedios (ej: referente <-> supervisor)
        return true;
    }

    static public function obtenerDestinatariosDisponibles($id_remitente)
    {
        $db = new Conexion();

        $remitente = $db->consultas("SELECT rol FROM usuarios WHERE idUsuario = $id_remitente")[0]['rol'] ?? '';
        $usuarios = $db->consultas("SELECT idUsuario, nombre, apellido, rol FROM usuarios WHERE idUsuario != $id_remitente");

        $disponibles = [];

        foreach ($usuarios as $u) {
            $permitido = self::puedeEnviar($id_remitente, $u['idUsuario']);
            if ($permitido) {
                $disponibles[] = $u;
            }
        }

        return $disponibles;
    }
}
