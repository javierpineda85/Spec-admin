<?php
class ModeloTurnos
{

    static public function mdlGuardarTurno($tabla, $datos)
    {
        // Conexión única
        $db = Conexion::conectar();

        try {
            // Iniciamos transacción
            $db->beginTransaction();

            // 1) Insert en 'turnos'
            $sql1 = "INSERT INTO $tabla (objetivo_id, puesto_id, fecha, turno, vigilador_id, tipo_jornada, is_referente, entrada, salida, color) VALUES (:objetivo_id, :puesto_id, :fecha, :turno, :vigilador_id, :tipo_jornada, :is_referente, :entrada, :salida, :color)";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam(":objetivo_id",  $datos["objetivo_id"],  PDO::PARAM_INT);
            $stmt1->bindParam(":puesto_id",    $datos["puesto_id"],    PDO::PARAM_INT);
            $stmt1->bindParam(":fecha",        $datos["fecha"],        PDO::PARAM_STR);
            $stmt1->bindParam(":turno",        $datos["turno"],        PDO::PARAM_STR);
            $stmt1->bindParam(":vigilador_id", $datos["vigilador_id"], PDO::PARAM_INT);
            $stmt1->bindParam(":tipo_jornada", $datos["tipo_jornada"], PDO::PARAM_STR);
            $stmt1->bindParam(":is_referente", $datos["is_referente"], PDO::PARAM_STR);
            $stmt1->bindParam(":entrada",      $datos["entrada"],      PDO::PARAM_STR);
            $stmt1->bindParam(":salida",       $datos["salida"],       PDO::PARAM_STR);
            $stmt1->bindParam(":color",        $datos["color"],        PDO::PARAM_STR);
            $stmt1->execute();

            // 2) Verificamos si ya existe en 'usuario_objetivo' 
            //    (para no agregar duplicados si hay varios turnos el mismo día)
            $sqlCheck = "SELECT id FROM usuario_objetivo WHERE usuario_id = :usuario_id AND objetivo_id = :objetivo_id AND fecha = :fecha";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->bindParam(":usuario_id",  $datos["vigilador_id"], PDO::PARAM_INT);
            $stmtCheck->bindParam(":objetivo_id", $datos["objetivo_id"],  PDO::PARAM_INT);
            $stmtCheck->bindParam(":fecha",       $datos["fecha"],        PDO::PARAM_STR);
            $stmtCheck->execute();

            // Si no existe, hacemos el INSERT
            if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                $sql2 = "INSERT INTO usuario_objetivo (usuario_id, objetivo_id, fecha)
                     VALUES (:usuario_id, :objetivo_id, :fecha)";
                $stmt2 = $db->prepare($sql2);
                $stmt2->bindParam(":usuario_id",  $datos["vigilador_id"], PDO::PARAM_INT);
                $stmt2->bindParam(":objetivo_id", $datos["objetivo_id"],  PDO::PARAM_INT);
                $stmt2->bindParam(":fecha",       $datos["fecha"],        PDO::PARAM_STR);
                $stmt2->execute();
            }

            // Si todo salió bien, commit
            $db->commit();
            return "ok";
        } catch (PDOException $e) {
            // Si algo falla, revertimos
            $db->rollBack();
            // Para depuración
            print_r($db->errorInfo());
            return "error";
        } finally {
            // Cerramos cursores
            if (isset($stmt1)) {
                $stmt1->closeCursor();
            }
            if (isset($stmtCheck)) {
                $stmtCheck->closeCursor();
            }
            if (isset($stmt2)) {
                $stmt2->closeCursor();
            }
        }
    }

    static public function mdlObtenerTurnosPorRango($tabla, $datos)
    {
        $sql = "SELECT 
                    t.idTurno,
                    p.puesto    AS puesto,
                    t.fecha,
                    t.turno,
                    t.entrada,
                    t.salida,
                    t.color,
                    t.tipo_jornada,
                    o.nombre    AS objetivo,
                    -- campo vigilador con apellido y nombre
                    CONCAT(u.apellido, ' ', u.nombre) AS vigilador
                FROM $tabla AS t
                JOIN objetivos AS o 
                    ON t.objetivo_id = o.idObjetivo
                JOIN usuarios AS u 
                    ON t.vigilador_id = u.idUsuario
                JOIN puestos AS p
                    ON t.puesto_id = p.idPuesto
                WHERE t.objetivo_id = :objetivo_id
                    AND t.fecha BETWEEN :desde AND :hasta
                ORDER BY t.fecha, t.turno, p.puesto
                ";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":objetivo_id", $datos['objetivo'], PDO::PARAM_INT);
        $stmt->bindParam(":desde",        $datos['desde'],    PDO::PARAM_STR);
        $stmt->bindParam(":hasta",        $datos['hasta'],    PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

        // No hace falta closeCursor; se cierra al salir del método
    }

    static public function mdlObtenerPorVigiladorYRango($tabla, $vigiladorId, $desde, $hasta)
    {
        $sql = "
          SELECT
            t.fecha,
            o.nombre    AS objetivo,
            p.puesto    AS puesto,
            t.turno,
            t.tipo_jornada,
            t.color
          FROM $tabla t
          JOIN objetivos o ON t.objetivo_id = o.idObjetivo
          JOIN puestos    p ON t.puesto_id    = p.idPuesto
          WHERE t.vigilador_id = :vigilador_id
            AND t.fecha BETWEEN :desde AND :hasta
          ORDER BY t.fecha, p.puesto
        ";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':vigilador_id', $vigiladorId, PDO::PARAM_INT);
        $stmt->bindParam(':desde',        $desde,       PDO::PARAM_STR);
        $stmt->bindParam(':hasta',        $hasta,       PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
