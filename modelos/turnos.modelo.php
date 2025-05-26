<?php
class ModeloTurnos
{

    static public function mdlGuardarTurno($tabla, $datos)
    {

        $sql = "INSERT INTO $tabla (objetivo_id, puesto_id, fecha, turno, vigilador_id, actividad, entrada, salida, color) VALUES (:objetivo_id, :puesto_id, :fecha, :turno, :vigilador_id, :actividad, :entrada, :salida, :color)";


        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":objetivo_id",  $datos["objetivo_id"],  PDO::PARAM_INT);
        $stmt->bindParam(":puesto_id",    $datos["puesto_id"],    PDO::PARAM_INT);
        $stmt->bindParam(":fecha",        $datos["fecha"],        PDO::PARAM_STR);
        $stmt->bindParam(":turno",        $datos["turno"],        PDO::PARAM_STR);
        $stmt->bindParam(":vigilador_id", $datos["vigilador_id"], PDO::PARAM_INT);
        $stmt->bindParam(":actividad",    $datos["actividad"],    PDO::PARAM_STR);
        $stmt->bindParam(":entrada",      $datos["entrada"],      PDO::PARAM_STR);
        $stmt->bindParam(":salida",       $datos["salida"],       PDO::PARAM_STR);
        $stmt->bindParam(":color",        $datos["color"],        PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            // Para depuración

            print_r(Conexion::conectar()->errorInfo());
        }

        $stmt->closeCursor();
        $stmt = null;
    }

    static public function mdlObtenerTurnosPorRango($tabla, $datos)
    {
        $sql = "SELECT 
                    t.idTurno,
                    r.puesto    AS puesto,
                    t.fecha,
                    t.turno,
                    t.entrada,
                    t.salida,
                    t.color,
                    t.actividad,
                    o.nombre    AS objetivo,
                    -- campo vigilador con apellido y nombre
                    CONCAT(u.apellido, ' ', u.nombre) AS vigilador
                FROM $tabla AS t
                JOIN objetivos AS o 
                    ON t.objetivo_id = o.idObjetivo
                JOIN usuarios AS u 
                    ON t.vigilador_id = u.idUsuario
                JOIN rondas AS r
                    ON t.puesto_id = r.idRonda
                WHERE t.objetivo_id = :objetivo_id
                    AND t.fecha BETWEEN :desde AND :hasta
                ORDER BY t.fecha, t.turno, r.puesto
                ";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":objetivo_id", $datos['objetivo'], PDO::PARAM_INT);
        $stmt->bindParam(":desde",        $datos['desde'],    PDO::PARAM_STR);
        $stmt->bindParam(":hasta",        $datos['hasta'],    PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

        // No hace falta closeCursor; se cierra al salir del método
    }

    static public function mdlObtenerPorVigiladorYRango($tabla, $vigiladorId, $desde, $hasta) {
        $sql = "
          SELECT
            t.fecha,
            o.nombre    AS objetivo,
            r.puesto    AS puesto,
            t.turno,
            t.actividad,
            t.color
          FROM $tabla t
          JOIN objetivos o ON t.objetivo_id = o.idObjetivo
          JOIN rondas    r ON t.puesto_id    = r.idRonda
          WHERE t.vigilador_id = :vigilador_id
            AND t.fecha BETWEEN :desde AND :hasta
          ORDER BY t.fecha, r.puesto
        ";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':vigilador_id', $vigiladorId, PDO::PARAM_INT);
        $stmt->bindParam(':desde',        $desde,       PDO::PARAM_STR);
        $stmt->bindParam(':hasta',        $hasta,       PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
