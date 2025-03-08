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
        $registro->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_STR);
       
        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }
}

?>