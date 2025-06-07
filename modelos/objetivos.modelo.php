<?php

class ModeloObjetivos
{
    /*INSERTAR OBJETIVO */
    static public function mdlGuardarObjetivo($tabla, $datos)
    {
        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, localidad, tipo) 
            VALUES (:nombre, :localidad, :tipo)");

        $registro->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $registro->bindParam(":localidad", $datos["localidad"], PDO::PARAM_STR);
        //$registro->bindParam(":referente", $datos["referente"], PDO::PARAM_STR);
        $registro->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);

        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }


    /*MODIFICAR OBJETIVO */
    static public function mdlModificarObjetivo($tabla, $datos)
    {
        try {
            $conexion = Conexion::conectar();
            $sql = "UPDATE $tabla SET nombre = :nombre, localidad = :localidad, tipo = :tipo WHERE idObjetivo = :idObjetivo";
    
            $stmt = $conexion->prepare($sql);
    
            $stmt->bindParam(":idObjetivo", $datos["idObjetivo"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":localidad", $datos["localidad"], PDO::PARAM_STR);
           // $stmt->bindParam(":referente", $datos["referente"], PDO::PARAM_STR);
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
    static public function mdlDesactivarObjetivo($tabla, $idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 0 WHERE idObjetivo = :id");
        $stmt->bindParam(':id', $idObjetivo, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }

        /** RESACTIVAR (soft-delete) UN OBJETIVO **/
    static public function mdlReactivarObjetivo($tabla, $idObjetivo)
    {
        $stmt = Conexion::conectar() ->prepare("UPDATE $tabla SET activo = 1 WHERE idObjetivo = :id");
        $stmt->bindParam(':id', $idObjetivo, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }


}
