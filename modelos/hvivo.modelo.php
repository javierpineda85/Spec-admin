<?php
class ModeloReporteHombreVivo
{

    static public function mdlGuardarReporte($tabla, $datos)
    {
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO $tabla (id_usuario, ronda_id, fecha_hora, demora) VALUES (:id_usuario, :ronda_id, NOW(), :demora)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':ronda_id',   $datos['ronda_id'],   PDO::PARAM_INT);
            $stmt->bindParam(':demora',     $datos['demora'],     PDO::PARAM_STR);
            return $stmt->execute() ? 'ok' : $stmt->errorInfo()[2];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
