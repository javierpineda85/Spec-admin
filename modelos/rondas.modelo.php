<?php
require_once('modelos/conexion.php');
class ModeloRondas
{

    /**
     * Inserta una nueva ronda y devuelve su id.
     * En caso de error retorna el mensaje de error.
     */
    static public function mdlGuardarRonda($tabla, $datos)
    {
        try {
            // Conexión y preparación
            $db = Conexion::conectar();
            $sql = "INSERT INTO $tabla (puesto, objetivo_id, tipo, orden_escaneo, status) VALUES (:puesto, :objetivo_id, :tipo, :orden_escaneo, :status)";
            $stmt = $db->prepare($sql);

            // Bind con las claves EXACTAS que espera la consulta
            $stmt->bindParam(':puesto',        $datos['puesto'],        PDO::PARAM_STR);
            $stmt->bindParam(':objetivo_id',   $datos['objetivo_id'],   PDO::PARAM_INT);
            $stmt->bindParam(':tipo',          $datos['tipo'],          PDO::PARAM_STR);
            $stmt->bindParam(':orden_escaneo', $datos['orden_escaneo'], PDO::PARAM_INT);
            $stmt->bindParam(':status',        $datos['status'],        PDO::PARAM_STR);

            // Ejecuta y devuelve el ID
            if ($stmt->execute()) {
                return $db->lastInsertId();
            } else {
                $error = $stmt->errorInfo();
                throw new Exception("Error en la consulta: " . $error[2]);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    static public function mdlDesactivarRonda($tabla, $idRonda)
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("UPDATE $tabla SET status = 'inactive' WHERE idRonda = :idRonda");
            $stmt->bindParam(':idRonda', $idRonda, PDO::PARAM_INT);
            return $stmt->execute() ? 'ok' : $stmt->errorInfo()[2];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

        static public function mdlActualizarRonda($tabla, $datos)
    {
        try {
            $db = Conexion::conectar();
            $sql = "UPDATE $tabla
                       SET puesto        = :puesto,
                           objetivo_id   = :objetivo_id,
                           tipo          = :tipo,
                           orden_escaneo = :orden_escaneo
                     WHERE idRonda = :idRonda";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':puesto',        $datos['puesto'],        PDO::PARAM_STR);
            $stmt->bindParam(':objetivo_id',   $datos['objetivo_id'],   PDO::PARAM_INT);
            $stmt->bindParam(':tipo',          $datos['tipo'],          PDO::PARAM_STR);
            $stmt->bindParam(':orden_escaneo', $datos['orden_escaneo'], PDO::PARAM_INT);
            $stmt->bindParam(':idRonda',       $datos['idRonda'],       PDO::PARAM_INT);
            return $stmt->execute() ? 'ok' : $stmt->errorInfo()[2];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
