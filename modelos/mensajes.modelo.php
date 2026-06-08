<?php
//require_once('conexion.php');
require_once __DIR__ . "/conexion.php";

class ModeloMensajes
{
    //Bandeja de entrada
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
        $stmt = Conexion::conectar()->prepare("SELECT 
                                                idMensaje, 
                                                remitente_id, 
                                                destinatario_id,
                                                contenido, 
                                                leido, 
                                                DATE_FORMAT(fecha_hora, '%d/%m/%Y') AS fMensaje, 
                                                DATE_FORMAT(fecha_hora, '%H:%i') AS horaMensaje, 
                                                nombre, 
                                                apellido 
                                            FROM mensajes 
                                            JOIN usuarios ON destinatario_id = usuarios.idUsuario 
                                            WHERE $item = :valor 
                                            ORDER BY fecha_hora DESC ");
        $stmt->bindParam(':valor', $valor, PDO::PARAM_INT);

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
    public static function mdlGuardarMensaje(array $datos)
    {
        $db = new Conexion();

        // Validar datos mínimos
        if (empty($datos['id_remitente']) || empty($datos['id_destinatario']) || empty($datos['contenidoMensaje'])) {
            throw new Exception('Datos incompletos para guardar el mensaje.');
        }

        // Insertar mensaje con objetivo_id si está disponible
        $resultado = $db->consultas(
            "INSERT INTO mensajes (remitente_id, destinatario_id, contenido, fecha_hora, objetivo_id)
     VALUES (?, ?, ?, ?, ?)",
            [
                $datos['id_remitente'],
                $datos['id_destinatario'],
                $datos['contenidoMensaje'],
                $datos['fechaMensaje'],
                $datos['objetivo_id'] ?? null
            ]
        );

        return $resultado ? 'ok' : 'error';
    }

    static public function mdlMarcarLeido($idMensaje)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE mensajes SET leido = 1 WHERE idMensaje = :id");
        $stmt->bindParam(":id", $idMensaje, PDO::PARAM_INT);
        return $stmt->execute();
    }

    static public function mdlMarcarNoLeido($id)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE mensajes SET leido = 0 WHERE idMensaje = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
