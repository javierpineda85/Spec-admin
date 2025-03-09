<?php
class ModeloHvivo
{

    static public function mdlGuardarRegistro($tabla, $datos)
    {

        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (id_usuario, fecha, hora) 
        VALUES (:id_usuario, :fecha, :hora)");

        $registro->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_STR);
        $registro->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
        $registro->bindParam(":hora", $datos["hora"], PDO::PARAM_STR);

        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }
}
