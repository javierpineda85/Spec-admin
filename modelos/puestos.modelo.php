<?php

class ModeloPuestos
{

    static public function mdlGuardarPuesto($tabla, $datos)
    {

        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (puesto, objetivo_id, tipo) 
        VALUES (:puesto, :objetivo_id, :tipo)");

        $registro->bindParam(":puesto", $datos["puesto"], PDO::PARAM_STR);
        $registro->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
        $registro->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }

    static public function mdlModificarPuesto($tabla, $datos)
    {
        try {
            $conexion = Conexion::conectar();
            $sql = "UPDATE $tabla SET puesto = :puesto, objetivo_id = :objetivo_id, tipo = :tipo WHERE idPuesto = :idPuesto";

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":idPuesto", $datos["idPuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":puesto", $datos["puesto"], PDO::PARAM_STR);
            $stmt->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);


            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /** DESACTIVAR (soft-delete) UN OBJETIVO **/
    static public function mdlDesactivarPuesto($tabla, $idPuesto)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 0 WHERE idPuesto = :id");
        $stmt->bindParam(':id', $idPuesto, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }

    /** REACTIVAR OBJETIVO **/
    static public function mdlReactivarPuesto($tabla, $idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 1 WHERE idPuesto = :id");
        $stmt->bindParam(':id', $idObjetivo, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }
    /** PUESTOS del objetivo (fijos y rotativos) */
    public static function mdlObtenerPuestosPorObjetivo(int $objetivo_id)
    {
        $db = new Conexion;
        return $db->consultas("SELECT p.idPuesto, p.puesto, p.tipo, p.activo
                               FROM puestos p
                               WHERE p.objetivo_id = $objetivo_id AND p.activo = 1
                               ORDER BY p.tipo='Fijo' DESC, p.puesto ASC");
    }

    /** VIGILADORES elegibles del objetivo */
    public static function mdlObtenerVigiladoresElegibles(int $objetivo_id)
    {
        $db = new Conexion;
        // vinculados al objetivo, activos y rol vigilador
        return $db->consultas("SELECT DISTINCT u.idUsuario, CONCAT(u.apellido, ', ', u.nombre) AS nombre
                           FROM usuarios u
                           INNER JOIN objetivo_vigiladores ov ON ov.vigilador_id = u.idUsuario
                           WHERE ov.objetivo_id = $objetivo_id
                             AND u.activo = 1
                           ORDER BY u.apellido, u.nombre");
    }

    /** TURNOS del mes (para validar que el vigilador tenga turno ese día y turno) */
    public static function mdlObtenerTurnosMesObjetivo(int $objetivo_id, string $mesYYYYMM)
    {
        $db = new Conexion;
        $desde = $mesYYYYMM . "-01";
        $hasta = date("Y-m-t", strtotime($desde)); // fin de mes
        return $db->consultas("SELECT idTurno, usuario_id, objetivo_id, fecha, rol, tipo_turno, codigo_turno
                                    FROM turnos
                                    WHERE objetivo_id = $objetivo_id
                                        AND fecha BETWEEN '$desde' AND '$hasta'
                                        AND rol='Vigilador'");
    }

    /** Rotaciones ya cargadas del mes */
    public static function mdlObtenerRotacionesMes(int $objetivo_id, string $mesYYYYMM)
    {
        $db = new Conexion;
        $desde = $mesYYYYMM . "-01";
        $hasta = date("Y-m-t", strtotime($desde));
        return $db->consultas("SELECT idRotacion, objetivo_id, fecha, puesto_id, usuario_id, codigo_turno
                               FROM rotaciones_puestos
                               WHERE objetivo_id = $objetivo_id
                                 AND fecha BETWEEN '$desde' AND '$hasta'");
    }
    //Para rotaciones de turnos
    public static function mdlObtenerTurnosPorPuesto($objetivo_id, $puesto_id, $mes)
    {
        $db = new Conexion;
        return $db->consultas(" SELECT DISTINCT pt.numero_turno, 
                                                CASE pt.numero_turno 
                                                    WHEN 1 THEN 'D' 
                                                    WHEN 2 THEN 'N' 
                                                    ELSE pt.numero_turno 
                                                END AS codigo_turno
                                FROM puestos_turnos pt
                                INNER JOIN puestos p ON p.idPuesto = pt.puesto_id
                                WHERE p.objetivo_id = $objetivo_id
                                AND p.idPuesto = $puesto_id
                                ORDER BY pt.numero_turno
    ");
    }
    /** Guardar/actualizar (UPSERT) una rotación */
    public static function mdlGuardarRotacion(array $r)
    // $r: objetivo_id, fecha, puesto_id, usuario_id, codigo_turno, editor_id, motivo?
    {
        $db = new Conexion;

        // ============================
        // 1) Validación: el usuario tiene turno ese día + código turno
        // ============================
        $u   = (int)$r['usuario_id'];
        $obj = (int)$r['objetivo_id'];
        $f   = $db->limpiar($r['fecha']);
        $ct  = $db->limpiar($r['codigo_turno']); // 'D' o 'N'
        $puesto_id = (int)$r['puesto_id'];

        $turnoValido = $db->consultas("SELECT idTurno FROM turnos
                                   WHERE objetivo_id=$obj AND usuario_id=$u 
                                     AND fecha='$f' AND codigo_turno='$ct' 
                                     AND rol='Vigilador' LIMIT 1");
        if (empty($turnoValido)) {
            return ['ok' => false, 'msg' => 'El vigilador no tiene turno asignado en esa fecha y turno.'];
        }

        // ============================
        // 1.b) Validación global: si ya está ocupado en otro objetivo o puesto en ese mismo día/turno
        // ============================
        $conflictoGlobal = self::mdlBuscarConflictoRotacion($obj, $f, $ct, $u, $puesto_id);
        if (!empty($conflictoGlobal)) {
            $c = $conflictoGlobal[0];
            $objetivoConf = $c['objetivo'] ?? ('objetivo #' . $c['objetivo_id']);
            $puestoConf   = $c['puesto'] ?? ('puesto #' . $c['puesto_id']);
            return [
                'ok'  => false,
                'msg' => "El vigilador ya está asignado en {$objetivoConf} / {$puestoConf} para ese día y turno."
            ];
        }

        // ============================
        // 2) Validación: ¿ya existe una rotación para ese puesto en esa fecha/turno?
        // ============================
        $existePuesto = $db->consultas("SELECT idRotacion, usuario_id 
                                    FROM rotaciones_puestos
                                    WHERE objetivo_id=$obj AND fecha='$f'
                                      AND puesto_id=$puesto_id AND codigo_turno='$ct' 
                                    LIMIT 1");

        if (!empty($existePuesto)) {
            // Ya hay alguien en ese puesto → hacemos UPDATE (reemplazo)
            $idRot = (int)$existePuesto[0]['idRotacion'];
            $usrAnt = (int)$existePuesto[0]['usuario_id'];

            // ============================
            // 3) Validación extra: ¿el usuario ya está en otro puesto ese mismo día/turno?
            // ============================
            $confUsuario = $db->consultas("SELECT idRotacion FROM rotaciones_puestos
                                       WHERE objetivo_id=$obj AND fecha='$f'
                                         AND codigo_turno='$ct' AND usuario_id=$u 
                                         AND idRotacion <> $idRot LIMIT 1");
            if (!empty($confUsuario)) {
                return ['ok' => false, 'msg' => 'El usuario ya está asignado en otro puesto ese día/turno.'];
            }

            // ============================
            // 4) Actualización
            // ============================
            $ok = $db->ejecutar("UPDATE rotaciones_puestos
                             SET usuario_id=$u
                             WHERE idRotacion=$idRot");

            if ($ok) {
                self::mdlLogRotacion([
                    'rotacion_id'     => $idRot,
                    'objetivo_id'     => $obj,
                    'fecha'           => $f,
                    'puesto_id'       => $puesto_id,
                    'usuario_anterior' => $usrAnt,
                    'usuario_nuevo'   => $u,
                    'codigo_turno'    => $ct,
                    'usuario_editor'  => (int)$r['editor_id'],
                    'accion'          => 'update',
                    'motivo'          => $db->limpiar($r['motivo'] ?? null)
                ]);
                return ['ok' => true];
            }
            return ['ok' => false, 'msg' => 'No se pudo actualizar (conflicto de unicidad).'];
        } else {
            // ============================
            // 5) Inserción nueva
            // ============================
            // Validación extra: ¿el usuario ya está en otro puesto ese mismo día/turno?
            $confUsuario = $db->consultas("SELECT idRotacion FROM rotaciones_puestos
                                       WHERE objetivo_id=$obj AND fecha='$f'
                                         AND codigo_turno='$ct' AND usuario_id=$u 
                                       LIMIT 1");
            if (!empty($confUsuario)) {
                return ['ok' => false, 'msg' => 'El usuario ya está asignado en otro puesto ese día/turno.'];
            }

            $ok = $db->ejecutar("INSERT INTO rotaciones_puestos 
                             (objetivo_id, fecha, puesto_id, usuario_id, codigo_turno)
                             VALUES ($obj, '$f', $puesto_id, $u, '$ct')");
            if ($ok) {
                $idRot = $db->lastInsertId();
                self::mdlLogRotacion([
                    'rotacion_id'     => $idRot,
                    'objetivo_id'     => $obj,
                    'fecha'           => $f,
                    'puesto_id'       => $puesto_id,
                    'usuario_anterior' => null,
                    'usuario_nuevo'   => $u,
                    'codigo_turno'    => $ct,
                    'usuario_editor'  => (int)$r['editor_id'],
                    'accion'          => 'create',
                    'motivo'          => $db->limpiar($r['motivo'] ?? null)
                ]);
                return ['ok' => true];
            }
            return ['ok' => false, 'msg' => 'No se pudo insertar (conflicto de unicidad).'];
        }
    }


    /** Eliminar una rotación puntual */
    public static function mdlEliminarRotacion(int $idRotacion, int $editor_id)
    {
        $db = new Conexion;
        $prev = $db->consultas("SELECT * FROM rotaciones_puestos WHERE idRotacion=$idRotacion LIMIT 1");
        $ok = $db->ejecutar("DELETE FROM rotaciones_puestos WHERE idRotacion=$idRotacion");
        if ($ok && !empty($prev)) {
            $p = $prev[0];
            self::mdlLogRotacion([
                'rotacion_id' => $idRotacion,
                'objetivo_id' => (int)$p['objetivo_id'],
                'fecha' => $p['fecha'],
                'puesto_id' => (int)$p['puesto_id'],
                'usuario_anterior' => (int)$p['usuario_id'],
                'usuario_nuevo' => null,
                'codigo_turno' => $p['codigo_turno'],
                'usuario_editor' => $editor_id,
                'accion' => 'delete',
                'motivo' => null
            ]);
        }
        return $ok;
    }

    /** Swap entre dos vigiladores en un rango de fechas (mismo puesto o puestos distintos) */
    public static function mdlSwapRotaciones(array $r) // objetivo_id, fecha_desde, fecha_hasta, usuario_a, usuario_b, codigo_turno, (opcional) puesto_id
    {
        $db = new Conexion;
        $obj = (int)$r['objetivo_id'];
        $ct = $db->limpiar($r['codigo_turno']);
        $desde = $r['desde'];
        $hasta = $r['hasta'];
        $uA = (int)$r['usuario_a'];
        $uB = (int)$r['usuario_b'];
        $puesto_id = isset($r['puesto_id']) ? (int)$r['puesto_id'] : null;
        $editor = (int)$r['editor_id'];

        $db->ejecutar("START TRANSACTION");

        // Tomo rotaciones existentes dentro del rango
        $filtroPuesto = $puesto_id ? "AND puesto_id=$puesto_id" : "";
        $rows = $db->consultas("SELECT idRotacion, fecha, puesto_id, usuario_id
                                FROM rotaciones_puestos
                                WHERE objetivo_id=$obj AND fecha BETWEEN '$desde' AND '$hasta'
                                  AND codigo_turno='$ct' $filtroPuesto");

        // Intercambio usuario A <-> B en cada coincidencia (validación de unicidad implícita)
        foreach ($rows as $row) {
            $nuevo = ($row['usuario_id'] == $uA) ? $uB : (($row['usuario_id'] == $uB) ? $uA : $row['usuario_id']);
            if ($nuevo != $row['usuario_id']) {
                $ok = $db->ejecutar("UPDATE rotaciones_puestos SET usuario_id={$nuevo} WHERE idRotacion={$row['idRotacion']}");
                if (!$ok) {
                    $db->ejecutar("ROLLBACK");
                    return ['ok' => false, 'msg' => 'Conflicto de unicidad en swap.'];
                }

                // log
                self::mdlLogRotacion([
                    'rotacion_id' => (int)$row['idRotacion'],
                    'objetivo_id' => $obj,
                    'fecha' => $row['fecha'],
                    'puesto_id' => (int)$row['puesto_id'],
                    'usuario_anterior' => (int)$row['usuario_id'],
                    'usuario_nuevo' => (int)$nuevo,
                    'codigo_turno' => $ct,
                    'usuario_editor' => $editor,
                    'accion' => 'swap',
                    'motivo' => null
                ]);
            }
        }

        $db->ejecutar("COMMIT");
        return ['ok' => true];
    }

    /** Autollenado equitativo (round-robin) para puestos Rotativos del mes */
    public static function mdlAutoRotarEquitativo(int $objetivo_id, string $mesYYYYMM, string $codigo_turno, int $editor_id)
    {
        $db = new Conexion;
        //1) Puestos
        $puestos = $db->consultas("SELECT idPuesto, puesto FROM puestos
                                   WHERE objetivo_id=$objetivo_id AND activo=1 AND tipo='Rotativo'
                                   ORDER BY puesto ASC");
        error_log("auto_rotar puestos=" . count($puestos));

        if (empty($puestos)) return ['ok' => true, 'msg' => 'Sin puestos rotativos.', 'count' => 0];

        //2) Vigiladores
        $vigs = self::mdlObtenerVigiladoresElegibles($objetivo_id);
        error_log("auto_rotar vigs=" . count($vigs));
        if (empty($vigs)) return ['ok' => false, 'msg' => 'Sin vigiladores elegibles.', 'count' => 0];

        // 3) turnos
        $turnos = self::mdlObtenerTurnosMesObjetivo($objetivo_id, $mesYYYYMM);
        error_log("auto_rotar turnos=" . count($turnos));
        // Agrupo turnos por fecha para saber quiénes PUEDEN ese día/turno
        $porFecha = [];
        foreach ($turnos as $t) {
            if ($t['codigo_turno'] !== $codigo_turno) continue;
            $porFecha[$t['fecha']][] = (int)$t['usuario_id'];
        }

        $desde = $mesYYYYMM . "-01";
        $hasta = date("Y-m-t", strtotime($desde));
        $period = new DatePeriod(new DateTime($desde), new DateInterval('P1D'), (new DateTime($hasta))->modify('+1 day'));

        // 4)Rotaciones existentes (no las pisamos salvo que se pida explícito)
        $exist = self::mdlObtenerRotacionesMes($objetivo_id, $mesYYYYMM);
        error_log("auto_rotar exist rotaciones=" . count($exist));
        $ocupado = [];
        foreach ($exist as $r) {
            if ($r['codigo_turno'] !== $codigo_turno) continue;
            $k1 = $r['fecha'] . '|' . $r['puesto_id'];
            $k2 = $r['fecha'] . '|' . $r['usuario_id'];
            $ocupado[$k1] = true;  // puesto ya asignado ese día
            $ocupado[$k2] = true;  // usuario ya asignado ese día
        }

        // 5) Round-robin: por cada puesto rotativo y por cada día → asigno al siguiente elegible con turno ese día que aún no fue asignado ese día
        $cursor = 0;
        $n = count($vigs);
        $asignados = 0; // contador de inserts
        $warnings = [];
        foreach ($period as $d) {
            $f = $d->format('Y-m-d');
            $habilitados = $porFecha[$f] ?? []; // los que tienen turno ese día/turno
            error_log("Fecha $f habilitados=" . count($habilitados));
            if (empty($habilitados)) continue;

            foreach ($puestos as $p) {
                $k1 = $f . '|' . $p['idPuesto'];
                if (isset($ocupado[$k1])) continue; // ya asignado manualmente o antes

                // busco al siguiente en rr que esté habilitado y libre en ese día
                $intentos = 0;
                while ($intentos < $n) {
                    $cand = (int)$vigs[$cursor % $n]['idUsuario'];
                    $cursor++;
                    $intentos++;

                    if (!in_array($cand, $habilitados, true)) continue;
                    $k2 = $f . '|' . $cand;
                    if (isset($ocupado[$k2])) continue;

                    $resultado = self::mdlGuardarRotacion([
                        'objetivo_id' => $objetivo_id,
                        'fecha' => $f,
                        'puesto_id' => (int)$p['idPuesto'],
                        'usuario_id' => $cand,
                        'codigo_turno' => $codigo_turno,
                        'editor_id' => $editor_id,
                        'motivo' => 'auto-rr'
                    ]);

                    if (!empty($resultado['ok'])) {
                        $ocupado[$k1] = true;
                        $ocupado[$k2] = true;
                        $asignados++;
                        break;
                    }

                    $msg = $resultado['msg'] ?? 'No se pudo asignar.';
                    $warningKey = $f . '|' . $p['idPuesto'] . '|' . $cand . '|' . $msg;
                    if (!isset($warnings[$warningKey])) {
                        $warnings[$warningKey] = $msg;
                    }
                }
            }
        }
        error_log("auto_rotar asignados=" . $asignados);
        return [
            'ok' => true,
            'msg' => 'Auto-rotación completada',
            'count' => $asignados,
            'warnings' => array_values($warnings)
        ];
    }

    /** Log */
    private static function mdlLogRotacion(array $l)
    {
        $db = new Conexion;
        $rot = $l['rotacion_id'] ?? 'NULL';
        $motivo = isset($l['motivo']) ? ("'" . $db->limpiar($l['motivo']) . "'") : "NULL";
        $db->ejecutar("INSERT INTO rotaciones_log
            (rotacion_id, objetivo_id, fecha, puesto_id, usuario_anterior, usuario_nuevo, codigo_turno, usuario_editor, accion, motivo)
            VALUES ($rot, {$l['objetivo_id']}, '{$l['fecha']}', {$l['puesto_id']},
                    " . ($l['usuario_anterior'] ?? 'NULL') . ", " . ($l['usuario_nuevo'] ?? 'NULL') . ",
                    '{$l['codigo_turno']}', {$l['usuario_editor']}, '{$l['accion']}', $motivo)");
    }

    /**
     * Devuelve la primera rotación conflictiva para un vigilador en un día/turno.
     * Se usa para advertir cuando el vigilador ya está activo en otro puesto u objetivo.
     */
    private static function mdlBuscarConflictoRotacion(int $objetivo_id, string $fecha, string $codigo_turno, int $usuario_id, int $puesto_id): array
    {
        $db = new Conexion;
        return $db->consultas(
            "SELECT rp.idRotacion,
                    rp.objetivo_id,
                    rp.puesto_id,
                    rp.codigo_turno,
                    COALESCE(o.nombre, CONCAT('objetivo #', rp.objetivo_id)) AS objetivo,
                    COALESCE(p.puesto, CONCAT('puesto #', rp.puesto_id)) AS puesto
               FROM rotaciones_puestos rp
               LEFT JOIN objetivos o ON o.idObjetivo = rp.objetivo_id
               LEFT JOIN puestos p ON p.idPuesto = rp.puesto_id
              WHERE rp.fecha = ?
                AND rp.usuario_id = ?
                AND rp.codigo_turno = ?
                AND NOT (rp.objetivo_id = ? AND rp.puesto_id = ?)
              ORDER BY rp.idRotacion ASC
              LIMIT 1",
            [$fecha, $usuario_id, $codigo_turno, $objetivo_id, $puesto_id]
        );
    }
}
