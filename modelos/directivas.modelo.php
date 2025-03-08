<?php

class ModeloDirectivas
{
    /*INSERTAR DIRECTIVA */
    static public function mdlGuardarDirectiva($tabla, $datos)
    {
     
        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (detalle, id_objetivo) 
            VALUES (:detalle, :id_objetivo)");

        //Esto permite insertar saltos de linea en la BD
        $detalleLimpio = str_replace("\r\n", "\n", $datos["detalle"]);

        $registro->bindParam(":detalle", $detalleLimpio, PDO::PARAM_STR);
        $registro->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_INT);
       
        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }
    
    /*MODIFICAR DIRECTIVA */
    static public function mdlModificarDirectiva($tabla, $datos)
    {
        try {
           
            $conexion = Conexion::conectar();
            $sql = "UPDATE $tabla SET detalle = :detalle, id_objetivo = :id_objetivo 
                            WHERE idDirectiva = :idDirectiva";
    
            $stmt = $conexion->prepare($sql);
    
            $stmt->bindParam(":idDirectiva", $datos["idDirectiva"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle", $datos["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_STR);
          
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}

?>