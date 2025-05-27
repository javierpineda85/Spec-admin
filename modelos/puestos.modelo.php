<?php

class ModeloPuestos
{

    static public function mdlGuardarPuesto($tabla, $datos)
    {

        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (puesto, objetivo_id) 
        VALUES (:puesto, :objetivo_id)");

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
}
