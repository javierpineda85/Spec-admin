<?php
class ModeloTurnos
{

    static public function mdlGuardarTurno($tabla, $datos)
    {
        $db = Conexion::conectar();

        foreach (['usuario_id', 'objetivo_id', 'fecha', 'rol', 'tipo_turno', 'codigo_turno'] as $campo) {
            if (!isset($datos[$campo])) return 'Falta el campo de turno ' . $campo;
        }
        $propia = !$db->inTransaction();

        try {
            require_once __DIR__ . '/cronograma_validacion.modelo.php';
            if ($propia) {
                $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                $db->beginTransaction();
            }
            $lock = $db->prepare('SELECT idUsuario FROM usuarios WHERE idUsuario = ? FOR UPDATE');
            $lock->execute([$datos['usuario_id']]);
            if (!$lock->fetchColumn()) throw new RuntimeException('Usuario inexistente.');
            $validacion = (new ModeloCronogramaValidacion($db))->validar($datos);
            if ($validacion['estado'] !== 'libre') throw new RuntimeException($validacion['mensaje']);
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
                if ($propia) $db->rollBack();
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

            if ($propia) $db->commit();

            return "ok";
        } catch (Throwable $e) {
            if ($propia && $db->inTransaction()) $db->rollBack();
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
                AND rp.usuario_id   = t.usuario_id
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

    /*Trae los turnos de un mes en especifico */
    // vista: listado_cronogramas.php
    static public function mdlObtenerTurnosConPuestos($tabla, $filtros)
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
               AND rp.usuario_id   = t.usuario_id
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
        $sqlTurno = "SELECT t.codigo_turno, rp.puesto_id
                 FROM turnos t
                 LEFT JOIN rotaciones_puestos rp
                   ON rp.usuario_id = t.usuario_id
                  AND rp.objetivo_id = t.objetivo_id
                  AND rp.fecha = t.fecha
                  AND rp.codigo_turno = t.codigo_turno
                 WHERE t.usuario_id = :usuario_id
                   AND t.objetivo_id = :objetivo_id
                   AND t.fecha = :fecha
                 LIMIT 1";

        $stmt = $conexion->prepare($sqlTurno);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':objetivo_id', $objetivoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();

        $turno = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$turno) return null;

        if (empty($turno['puesto_id'])) return null;
        $mapaCodigos = ['D' => 1, 'N' => 2];
        $numeroTurno = $mapaCodigos[strtoupper(trim($turno['codigo_turno']))] ?? null;
        if ($numeroTurno === null) return null;

        // 3. Buscar el horario esperado en puestos_turnos
        $sqlHorario = "SELECT hora_entrada, hora_salida
                   FROM puestos_turnos
                   WHERE puesto_id = :puesto_id AND numero_turno = :numero_turno
                   LIMIT 1";

        $stmt2 = $conexion->prepare($sqlHorario);
        $stmt2->bindValue(':puesto_id', (int)$turno['puesto_id'], PDO::PARAM_INT);
        $stmt2->bindValue(':numero_turno', $numeroTurno, PDO::PARAM_INT);
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
