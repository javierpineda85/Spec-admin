<?php

class ModeloPuestos
{

    static public function mdlGuardarPuesto($tabla, $datos)
    {

        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (puesto, objetivo_id, tipo) 
        VALUES (:puesto, :objetivo_id, :tipo)");

        $registro->bindParam(":puesto", $datos["puesto"], PDO::PARAM_STR);
        $registro->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
        $registro->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }

    static public function mdlModificarPuesto($tabla, $datos)
    {
        try {
            $conexion = Conexion::conectar();
            $sql = "UPDATE $tabla SET puesto = :puesto, objetivo_id = :objetivo_id, tipo = :tipo WHERE idPuesto = :idPuesto";

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":idPuesto", $datos["idPuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":puesto", $datos["puesto"], PDO::PARAM_STR);
            $stmt->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /** DESACTIVAR (soft-delete) UN OBJETIVO **/
    static public function mdlDesactivarPuesto($tabla, $idPuesto)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 0 WHERE idPuesto = :id");
        $stmt->bindParam(':id', $idPuesto, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }

    /** REACTIVAR OBJETIVO **/
    static public function mdlReactivarPuesto($tabla, $idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 1 WHERE idPuesto = :id");
        $stmt->bindParam(':id', $idObjetivo, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }
}
