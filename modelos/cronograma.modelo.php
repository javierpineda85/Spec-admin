<?php
class ModeloCronograma
{

    static public function mdlSubirCrono($tabla, $datos)
    {
        $conexion = Conexion::conectar();
        $registro = $conexion->prepare("INSERT INTO $tabla (objetivo_id, fechaCarga, imgCrono) 
        VALUES (:objetivo_id, :fechaCarga, :img_crono)");
    
        $registro->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
        $registro->bindParam(":fechaCarga", $datos["fechaCarga"], PDO::PARAM_STR);
        $registro->bindParam(":img_crono", $datos["img_crono"], PDO::PARAM_STR);
    
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
