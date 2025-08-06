
<?php class ModeloNovedades
{

    static public function mdlGuardarNovedad($tabla,  $datos)
    {
        $sql = "INSERT INTO  $tabla (vigilador_id, objetivo_id, fecha, hora, detalle, adjunto) VALUES (:vigilador_id, :objetivo_id, :fecha, :hora, :detalle, :adjunto)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':vigilador_id',  $datos['vigilador_id'], PDO::PARAM_INT);
        $stmt->bindParam(':objetivo_id',  $datos['objetivo_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha',  $datos['fecha'], PDO::PARAM_STR);
        $stmt->bindParam(':hora',  $datos['hora'], PDO::PARAM_STR);
        $stmt->bindParam(':detalle',  $datos['detalle'], PDO::PARAM_STR);
        $stmt->bindParam(':adjunto',  $datos['adjunto'], PDO::PARAM_STR);
        return  $stmt->execute() ? 'ok' : 'error';
    }
}
?>