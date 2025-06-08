<?php

class ModeloEscaneos
{
 /**
     * Inserta un escaneo y devuelve 'ok' o mensaje de error.
     */
    static public function mdlGuardarEscaneo($tabla, $datos)
    {
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO $tabla (ronda_id, sector_id, vigilador_id) VALUES (:ronda_id, :sector_id, :vigilador_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':ronda_id',     $datos['ronda_id'],     PDO::PARAM_INT);
            $stmt->bindParam(':sector_id',    $datos['sector_id'],    PDO::PARAM_INT);
            $stmt->bindParam(':vigilador_id', $datos['vigilador_id'], PDO::PARAM_INT);
            return $stmt->execute() ? 'ok' : $stmt->errorInfo()[2];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
