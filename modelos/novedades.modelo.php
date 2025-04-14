
<?php class ModeloNovedades
{
    static public function mdlGuardarNovedad($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (usuario_id, objetivo_id, fecha, hora, tipo_registro, detalle) 
                                               VALUES (:usuario_id, :objetivo_id, :fecha, :hora, :tipo_registro, :detalle)");

        $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
        $stmt->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
        $stmt->bindParam(":hora", $datos["hora"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_registro", $datos["tipo_registro"], PDO::PARAM_STR);
        $stmt->bindParam(":detalle", $datos["detalle"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            print_r($stmt->errorInfo());
            return "error";
        }

        $stmt->closeCursor();
        $stmt = null;
    }
}
?>