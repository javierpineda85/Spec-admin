<?php
class ModeloFeriados
{
    public static function mdlGuardarFeriado($tabla, $datos)
    {
        $sql = "INSERT INTO $tabla (fecha, motivo, tipo_feriado) VALUES (:fecha, :motivo, :tipo)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":fecha", $datos['fecha']);
        $stmt->bindParam(":motivo", $datos['motivo']);
        $stmt->bindParam(":tipo", $datos['tipo_feriado']);
        return $stmt->execute();
    }

    public static function mdlActualizarFeriado($tabla, $id, $datos)
    {
        $sql = "UPDATE $tabla SET fecha = :fecha, motivo = :motivo, tipo_feriado = :tipo WHERE idFeriado = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":fecha", $datos['fecha']);
        $stmt->bindParam(":motivo", $datos['motivo']);
        $stmt->bindParam(":tipo", $datos['tipo_feriado']);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function mdlEliminarFeriado($tabla, $id)
    {
        $sql = "DELETE FROM $tabla WHERE idFeriado = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function mdlObtenerFeriado($tabla, $id)
    {
        $sql = "SELECT * FROM $tabla WHERE idFeriado = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
