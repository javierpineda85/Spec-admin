<?php
class ModeloTurnos
{

    static public function mdlGuardarTurno($tabla, $datos)
    {
        $db = Conexion::conectar();

        try {
            // Verificar si ya existe
            $check = $db->prepare("SELECT COUNT(*) FROM $tabla 
                               WHERE usuario_id = :usuario_id 
                                 AND objetivo_id = :objetivo_id 
                                 AND fecha = :fecha");
            $check->execute([
                ':usuario_id'  => $datos["usuario_id"],
                ':objetivo_id' => $datos["objetivo_id"],
                ':fecha'       => $datos["fecha"]
            ]);

            if ($check->fetchColumn() > 0) {
                // Ya existe, no insertamos
                error_log("⚠ Turno duplicado detectado y omitido: usuario {$datos['usuario_id']} fecha {$datos['fecha']}");
                return "duplicado";
            }

            // Insertar si no existe
            $sql = "INSERT INTO $tabla 
                (usuario_id, objetivo_id, fecha, rol, tipo_turno, codigo_turno)
                VALUES 
                (:usuario_id,  :objetivo_id, :fecha, :rol, :tipo_turno, :codigo_turno)";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":usuario_id",   $datos["usuario_id"],   PDO::PARAM_INT);
            $stmt->bindParam(":objetivo_id",  $datos["objetivo_id"],  PDO::PARAM_INT);
            $stmt->bindParam(":fecha",        $datos["fecha"],        PDO::PARAM_STR);
            $stmt->bindParam(":rol",          $datos["rol"],          PDO::PARAM_STR);
            $stmt->bindParam(":tipo_turno",   $datos["tipo_turno"],   PDO::PARAM_STR);
            $stmt->bindParam(":codigo_turno", $datos["codigo_turno"], PDO::PARAM_STR);
            $stmt->execute();

            return "ok";
        } catch (PDOException $e) {
            return $e->getMessage();
        } finally {
            if (isset($stmt)) $stmt->closeCursor();
        }
    }


    static public function mdlObtenerTurnos($tabla, $filtros)
    {
        $sql = "SELECT 
                    t.idTurno,
                    t.fecha,
                    t.rol,
                    t.tipo_turno,
                    t.codigo_turno,
                    p.puesto AS puesto,
                    o.nombre AS objetivo,
                    CONCAT(u.apellido, ' ', u.nombre) AS usuario,
                    rp.idRotacion,
                    CONCAT(ur.apellido, ' ', ur.nombre) AS usuario_rotacion
                FROM $tabla AS t
                JOIN objetivos AS o 
                    ON t.objetivo_id = o.idObjetivo
                JOIN usuarios AS u 
                    ON t.usuario_id = u.idUsuario
                LEFT JOIN rotaciones_puestos AS rp
                    ON rp.objetivo_id  = t.objetivo_id
                AND rp.fecha        = t.fecha
                AND rp.codigo_turno = t.codigo_turno
                LEFT JOIN puestos AS p
                    ON rp.puesto_id = p.idPuesto
                LEFT JOIN usuarios AS ur
                    ON rp.usuario_id = ur.idUsuario
                WHERE 1=1";

        // Array para bindParam
        $params = [];

        // Filtro por objetivo
        if (!empty($filtros['objetivo'])) {
            $sql .= " AND t.objetivo_id = :objetivo_id";
            $params[':objetivo_id'] = [$filtros['objetivo'], PDO::PARAM_INT];
        }

        // Filtro por usuario/vigilador
        if (!empty($filtros['vigilador'])) {
            $sql .= " AND t.usuario_id = :usuario_id";
            $params[':usuario_id'] = [$filtros['vigilador'], PDO::PARAM_INT];
        }

        // Filtro por rango de fechas
        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $sql .= " AND t.fecha BETWEEN :desde AND :hasta";
            $params[':desde'] = [$filtros['desde'], PDO::PARAM_STR];
            $params[':hasta'] = [$filtros['hasta'], PDO::PARAM_STR];
        }

        $sql .= " ORDER BY t.fecha, t.codigo_turno, p.puesto";

        $stmt = Conexion::conectar()->prepare($sql);

        // Bind dinámico
        foreach ($params as $key => [$value, $type]) {
            $stmt->bindValue($key, $value, $type);
        }

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
        ORDER BY fecha, usuario_id";
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

    /*Esta funcion permite calcular correctamente las horas trabajadas teniendo en cuenta que
    * si alguien ingresa antes, no cuenta como hora extra
    * Si alguien ingresa despues, cuenta como tardanza
    * si alguien sale antes, cuenta como que debe horas
    * si alguien sale despues, no cuenta como hora extra    
    */
    public static function obtenerHorarioEsperado($usuarioId, $objetivoId, $fecha)
    {
        $conexion = Conexion::conectar();

        // 1. Buscar el turno asignado
        $sqlTurno = "SELECT codigo_turno 
                 FROM turnos 
                 WHERE usuario_id = :usuario_id 
                   AND objetivo_id = :objetivo_id 
                   AND fecha = :fecha
                 LIMIT 1";

        $stmt = $conexion->prepare($sqlTurno);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':objetivo_id', $objetivoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();

        $turno = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$turno) return null;

        // 2. Buscar el número de turno asociado (D = 1, N = 2, etc.)
        // Adaptable a tu codificación interna si usás D, N, M...
        $mapaCodigos = [
            'D' => 1,
            'M' => 2,
            'N' => 3
        ];

        $numeroTurno = $mapaCodigos[$turno['codigo_turno']] ?? 1;

        // 3. Buscar el horario esperado en puestos_turnos
        $sqlHorario = "SELECT hora_entrada, hora_salida
                   FROM puestos_turnos
                   WHERE puesto_id = :puesto_id AND numero_turno = :numero_turno
                   LIMIT 1";

        $stmt2 = $conexion->prepare($sqlHorario);
        $stmt2->bindParam(':puesto_id', $turno['puesto_id'], PDO::PARAM_INT);
        $stmt2->bindParam(':numero_turno', $numeroTurno, PDO::PARAM_INT);
        $stmt2->execute();

        $horario = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$horario) return null;

        return [
            'hora_entrada' => $horario['hora_entrada'],
            'hora_salida'  => $horario['hora_salida'],
            'numero_turno' => $numeroTurno
        ];
    }
}
