<?php
//require_once('conexion.php');

class ModeloMensajes
{
    static public function mdlMostrarMensajes($item, $valor)
    {


        $stmt = Conexion::conectar()->prepare("SELECT idMensaje, remitente_id, destinatario_id, contenido, leido,
                                                DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje,
                                                DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje,
                                                nombre, apellido
                                                FROM mensajes
                                                JOIN usuarios ON remitente_id = usuarios.idUsuario
                                                WHERE $item = $valor
                                                ORDER BY fecha_hora DESC;");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = null;
    }
    static public function mdlMostrarMensajesEnviados($item, $valor)
    {


        $stmt = Conexion::conectar()->prepare("SELECT idMensaje, remitente_id, destinatario_id,contenido, DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje, DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje, nombre, apellido FROM mensajes JOIN usuarios ON destinatario_id = usuarios.idUsuario WHERE $item = $valor ORDER BY fecha_hora DESC");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = null;
    }
    static public function mdlMostrarUnMensaje($id)
    {


        $stmt = Conexion::conectar()->prepare("SELECT idMensaje, remitente_id, destinatario_id,contenido, DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje, DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje, nombre, apellido FROM mensajes JOIN usuarios ON remitente_id = usuarios.idUsuario WHERE idMensaje = $id ORDER BY fecha_hora DESC; ");
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = null;
    }

    static public function mdlGuardarMensaje($datos)
    {
        $stmt = Conexion::conectar()->prepare("
        INSERT INTO mensajes (remitente_id, destinatario_id, contenido, fecha_hora)
        VALUES (:remitente_id, :destinatario_id, :contenido, :fecha_hora)
        ");

        $stmt->bindParam(":remitente_id", $datos["id_remitente"], PDO::PARAM_INT);
        $stmt->bindParam(":destinatario_id", $datos["id_destinatario"], PDO::PARAM_INT);
        $stmt->bindParam(":contenido", $datos["contenidoMensaje"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_hora", $datos["fechaMensaje"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            print_r($stmt->errorInfo());
            return "error";
        }

        $stmt->closeCursor();
        $stmt = null;
    }
    static public function mdlMarcarLeido($idMensaje)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE mensajes SET leido = 1 WHERE idMensaje = :id");
        $stmt->bindParam(":id", $idMensaje, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
