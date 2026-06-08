<?php

class ModeloDirectivas
{
    /* INSERTAR DIRECTIVA */
    static public function mdlGuardarDirectiva($tabla, $datos)
    {
        $registro = Conexion::conectar()->prepare(" INSERT INTO $tabla (detalle, id_objetivo, adjunto, tipo) 
        VALUES (:detalle, :id_objetivo, :adjunto, :tipo)
            ");

        $detalleLimpio = str_replace("\r\n", "\n", $datos["detalle"]);
        $registro->bindParam(":detalle", $detalleLimpio, PDO::PARAM_STR);
        $registro->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_INT);
        $registro->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);

        if (empty($datos["adjunto"])) {
            $registro->bindValue(":adjunto", null, PDO::PARAM_NULL);
        } else {
            $registro->bindParam(":adjunto", $datos["adjunto"], PDO::PARAM_STR);
        }

        return $registro->execute() ? "ok" : "error";
    }


    /* MODIFICAR DIRECTIVA */
    static public function mdlModificarDirectiva($tabla, $datos)
    {
        try {
            $conexion = Conexion::conectar();

            if (!empty($datos["adjunto"])) {
                $sql = "UPDATE $tabla 
                    SET detalle = :detalle, 
                        id_objetivo = :id_objetivo, 
                        tipo = :tipo,
                        adjunto = :adjunto
                    WHERE idDirectiva = :idDirectiva";
            } else {
                $sql = "UPDATE $tabla 
                    SET detalle = :detalle, 
                        id_objetivo = :id_objetivo,
                        tipo = :tipo
                    WHERE idDirectiva = :idDirectiva";
            }

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":idDirectiva", $datos["idDirectiva"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle",     $datos["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(":id_objetivo", $datos["id_objetivo"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo",        $datos["tipo"], PDO::PARAM_STR);

            if (!empty($datos["adjunto"])) {
                $stmt->bindParam(":adjunto", $datos["adjunto"], PDO::PARAM_STR);
            }

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }



    static public function mdlEliminarDirectiva($tabla, $idDirectiva)
    {
        $conexion = Conexion::conectar();

        // Verificar tipo
        $check = $conexion->prepare("SELECT tipo FROM $tabla WHERE idDirectiva = :id");
        $check->bindParam(':id', $idDirectiva, PDO::PARAM_INT);
        $check->execute();
        $tipo = $check->fetchColumn();

        if ($tipo === 'general') {
            return 'no_permitido';
        }

        // Eliminar si no es general
        $stmt = $conexion->prepare("DELETE FROM $tabla WHERE idDirectiva = :id");
        $stmt->bindParam(':id', $idDirectiva, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }


    static public function mdlObtenerTodas()
    {
        $db = new Conexion;
        $sql = "SELECT d.*, o.nombre 
        FROM directivas d 
        JOIN objetivos o ON d.id_objetivo = o.idObjetivo 
        ORDER BY d.id_objetivo";
        $directivas = $db->consultas($sql);
        return $directivas;
    }
}
