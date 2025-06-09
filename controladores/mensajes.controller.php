<?php
require_once('modelos/mensajes.modelo.php');

class ControladorMensajes
{
    static public function crtMostrarMensajes($item, $valor){
        Auth::check('mensajes', 'crtMostrarMensajes');
        $respuesta = ModeloMensajes::mdlMostrarMensajes($item, $valor);
        return $respuesta;

        exit;
    }

    static public function crtMostrarMensajesEnviados($item, $valor){
        Auth::check('mensajes', 'crtMostrarMensajesEnviados');
        $respuesta = ModeloMensajes::mdlMostrarMensajesEnviados($item, $valor);
        return $respuesta;

        exit;
    }
    static public function crtMostrarUnMensaje($id){
        Auth::check('mensajes', 'crtMostrarUnMensaje');
        $respuesta = ModeloMensajes::mdlMostrarUnMensaje($id);
        return $respuesta;

        exit;
    }

    static public function crtGuardarMensaje(){
        Auth::check('mensajes', 'crtGuardarMensaje');
        if (isset($_POST["id_destinatario"])) {
            $datos = array(
                "id_remitente"        => $_POST["id_remitente"],
                "id_destinatario"     => $_POST["id_destinatario"],
                "contenidoMensaje"    => $_POST["contenidoMensaje"],
                "fechaMensaje" => date('Y-m-d H:i:s')
            );

            $respuesta = ModeloMensajes::mdlGuardarMensaje($datos);
            $_SESSION['success_message'] = 'Mensaje enviado exitosamente';
           return $respuesta;
            
        }
    }
}