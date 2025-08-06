<?php

class ModeloBajas
{
    /**
     * Inserta un registro de baja de usuario.
     *
     * @param string $tabla      Nombre de la tabla (debe ser 'bajas').
     * @param array  $datos      Claves: usuario_id, motivo, fecha, eliminado_por.
     * @return string            'ok' o 'error'.
     */
    static public function mdlCrearBaja($tabla, $datos)
    {
        $sql = "INSERT INTO $tabla (usuario_id, motivo, fecha, eliminado_por)
                VALUES (:usuario_id, :motivo, :fecha, :eliminado_por)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':usuario_id',   $datos['usuario_id'],   PDO::PARAM_INT);
        $stmt->bindParam(':motivo',       $datos['motivo'],       PDO::PARAM_STR);
        $stmt->bindParam(':fecha',        $datos['fecha'],        PDO::PARAM_STR);
        $stmt->bindParam(':eliminado_por',$datos['eliminado_por'],PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }
}
