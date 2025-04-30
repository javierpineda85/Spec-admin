<?php

class ModeloAsignaciones
{
    static public function mdlGuardarAsignacion($tabla, $datos)
    {
        $registro = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (id_usuario, id_objetivo)
             VALUES (:id_usuario, :id_objetivo)"
        );

        $registro->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $registro->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_INT);

        if ($registro->execute()) {
            return "ok";
        } else {
            print_r($registro->errorInfo());
            return "error";
        }

        $registro->closeCursor();
        $registro = null;
    }
}
