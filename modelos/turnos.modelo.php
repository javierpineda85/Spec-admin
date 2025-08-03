<?php
class ModeloTurnos
{

    static public function mdlGuardarTurno($tabla, $datos)
    {
        $db = Conexion::conectar();

        try {

            // 1) Insert en tabla turnos
            $sql = "INSERT INTO $tabla 
                (usuario_id, puesto_id, objetivo_id, fecha, rol, tipo_turno, codigo_turno)
                VALUES 
                (:usuario_id, :puesto_id, :objetivo_id, :fecha, :rol, :tipo_turno, :codigo_turno)";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":usuario_id",   $datos["usuario_id"],   PDO::PARAM_INT);
            $stmt->bindParam(":puesto_id",    $datos["puesto_id"],    PDO::PARAM_INT);
            $stmt->bindParam(":objetivo_id",  $datos["objetivo_id"],  PDO::PARAM_INT);
            $stmt->bindParam(":fecha",        $datos["fecha"],        PDO::PARAM_STR);
            $stmt->bindParam(":rol",          $datos["rol"],          PDO::PARAM_STR); // Vigilador o Referente
            $stmt->bindParam(":tipo_turno",   $datos["tipo_turno"],   PDO::PARAM_STR); // Normal o Licencia
            $stmt->bindParam(":codigo_turno", $datos["codigo_turno"], PDO::PARAM_STR); // D, N, etc.
            $stmt->execute();


            return "ok";
        } catch (PDOException $e) {

            return $e->getMessage();
        } finally {
            if (isset($stmt)) $stmt->closeCursor();
        }
    }

    static public function mdlObtenerTurnosPorRango($tabla, $datos)
    {
        $sql = "SELECT 
                t.idTurno,
                t.fecha,
                t.rol,
                t.tipo_turno,
                t.codigo_turno,
                p.puesto         AS puesto,
                o.nombre         AS objetivo,
                CONCAT(u.apellido, ' ', u.nombre) AS usuario
            FROM $tabla AS t
            JOIN objetivos AS o 
                ON t.objetivo_id = o.idObjetivo
            JOIN usuarios AS u 
                ON t.usuario_id = u.idUsuario
            JOIN puestos AS p
                ON t.puesto_id = p.idPuesto
            WHERE t.objetivo_id = :objetivo_id
                AND t.fecha BETWEEN :desde AND :hasta
            ORDER BY t.fecha, t.codigo_turno, p.puesto
           ";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":objetivo_id", $datos['objetivo'], PDO::PARAM_INT);
        $stmt->bindParam(":desde",        $datos['desde'],    PDO::PARAM_STR);
        $stmt->bindParam(":hasta",        $datos['hasta'],    PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    static public function mdlObtenerPorVigiladorYRango($tabla, $usuarioId, $desde, $hasta)
    {
        $sql = "SELECT
                    t.fecha,
                    o.nombre         AS objetivo,
                    p.puesto         AS puesto,
                    t.rol,
                    t.tipo_turno,
                    t.codigo_turno
                FROM $tabla t
                JOIN objetivos o ON t.objetivo_id = o.idObjetivo
                JOIN puestos   p ON t.puesto_id   = p.idPuesto
                WHERE t.usuario_id = :usuario_id
                    AND t.fecha BETWEEN :desde AND :hasta
                ORDER BY t.fecha, p.puesto
                    ";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':desde',      $desde,     PDO::PARAM_STR);
        $stmt->bindParam(':hasta',      $hasta,     PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*Funcion para traer el cronograma / turno del mes anterior */
    // vista: crear_cronograma.php 
    // Controlador: CronogramaController
    static public function mdlBuscarTurnosPorMes($objetivoId, $mes)
    {
        $db = Conexion::conectar();
        $sql = "SELECT * FROM turnos 
        WHERE objetivo_id = ? 
          AND fecha LIKE ? 
        ORDER BY fecha, puesto_id, usuario_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([$objetivoId, "$mes%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    static public function mdlEliminarTurnosPorMes($objetivoId, $mes)
    {
        $db = Conexion::conectar();

        try {
            $stmt = $db->prepare("DELETE FROM turnos WHERE objetivo_id = ? AND fecha LIKE ?");
            $stmt->execute([$objetivoId, "$mes-%"]);

            return true;
        } catch (PDOException $e) {
            return false;
        } finally {
            if (isset($stmt)) $stmt->closeCursor();
        }
    }
}
