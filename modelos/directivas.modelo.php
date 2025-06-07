<?php

class ModeloDirectivas
{
    /* INSERTAR DIRECTIVA */
    static public function mdlGuardarDirectiva($tabla, $datos)
    {
        // Asegúrate que la tabla tiene columna 'adjunto'
        $registro = Conexion::conectar()->prepare("
            INSERT INTO $tabla (detalle, id_objetivo, adjunto) 
            VALUES (:detalle, :id_objetivo, :adjunto)
        ");

        // Limpiar saltos de línea
        $detalleLimpio = str_replace("\r\n", "\n", $datos["detalle"]);

        $registro->bindParam(":detalle", $detalleLimpio, PDO::PARAM_STR);
        $registro->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_INT);
        // Si no hubo adjunto, se envía NULL
        if (empty($datos["adjunto"])) {
            $registro->bindValue(":adjunto", null, PDO::PARAM_NULL);
        } else {
            $registro->bindParam(":adjunto", $datos["adjunto"], PDO::PARAM_STR);
        }

        if ($registro->execute()) {
            return "ok";
        } else {
            // Imprime info de error para debugging
            // print_r(Conexion::conectar()->errorInfo());
            return "error";
        }

        $registro->closeCursor();
        $registro = null;
    }

    /* MODIFICAR DIRECTIVA */
    static public function mdlModificarDirectiva($tabla, $datos)
    {
        try {
            $conexion = Conexion::conectar();
            // Si llega adjunto nuevo, lo actualizamos; si no, dejamos el antiguo
            if (!empty($datos["adjunto"])) {
                $sql = "UPDATE $tabla 
                        SET detalle = :detalle, 
                            id_objetivo = :id_objetivo, 
                            adjunto = :adjunto
                        WHERE idDirectiva = :idDirectiva";
            } else {
                $sql = "UPDATE $tabla 
                        SET detalle = :detalle, 
                            id_objetivo = :id_objetivo
                        WHERE idDirectiva = :idDirectiva";
            }

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":idDirectiva", $datos["idDirectiva"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle",     $datos["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_INT);

            if (!empty($datos["adjunto"])) {
                $stmt->bindParam(":adjunto", $datos["adjunto"], PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    static public function mdlEliminarDirectiva($tabla, $idDirectiva)
    {
        // Preparamos el DELETE
        $stmt = Conexion::conectar()->prepare(
            "DELETE FROM $tabla WHERE idDirectiva = :id"
        );
        $stmt->bindParam(':id', $idDirectiva, PDO::PARAM_INT);

        // Ejecutamos y devolvemos ok/error
        return $stmt->execute() ? 'ok' : 'error';
    }
}
