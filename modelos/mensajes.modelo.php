<?php
//require_once('conexion.php');

class ModeloMensajes
{
    static public function mdlMostrarMensajes($item, $valor){


        $stmt = Conexion::conectar()->prepare("SELECT idMensaje, remitente_id, destinatario_id,contenido, DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje, DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje, nombre, apellido FROM mensajes JOIN usuarios ON remitente_id = usuarios.idUsuario WHERE $item = $valor ORDER BY fecha_hora DESC; ");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = null;
    }
    static public function mdlMostrarMensajesEnviados($item, $valor){


        $stmt = Conexion::conectar()->prepare("SELECT idMensaje, remitente_id, destinatario_id,contenido, DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje, DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje, nombre, apellido FROM mensajes JOIN usuarios ON destinatario_id = usuarios.idUsuario WHERE $item = $valor ORDER BY fecha_hora DESC");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = null;
    }
    static public function mdlMostrarUnMensaje($id){


        $stmt = Conexion::conectar()->prepare("SELECT idMensaje, remitente_id, destinatario_id,contenido, DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje, DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje, nombre, apellido FROM mensajes JOIN usuarios ON remitente_id = usuarios.idUsuario WHERE idMensaje = $id ORDER BY fecha_hora DESC; ");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = null;
    }

    static public function mdlGuardarMensaje($datos){
                /* HOLA LEANDRO*/
                
        $registro = Conexion::conectar()->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, contenidoMensaje, fechaMensaje) VALUES (:id_remitente, :id_destinatario, :contenidoMensaje, :fechaMensaje)");

        $registro->bindParam(":remitente_id", $datos["remitente_id"], PDO::PARAM_INT);
        $registro->bindParam(":destinatario_id", $datos["destinatario_id"], PDO::PARAM_INT);
        $registro->bindParam(":contenido", $datos["contenido"], PDO::PARAM_STR);
        $registro->bindParam("fecha_hora", $datos["fecha_hora"], PDO::PARAM_STR);

        if ($registro->execute()) {
            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }
}