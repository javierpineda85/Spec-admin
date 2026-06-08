<?php
ob_start(); // Para que los headers no tiren error

require_once('modelos/novedades.modelo.php');

class NovedadesController
{
    /**
     * Registra una novedad asociada a un usuario y objetivo, con manejo de transacción y errores.
     */
    public static function crtRegistrar()
    {
        //Auth::check('novedades', 'crtRegistrar');
        Auth::check('novedades', 'vistaCrearNovedades');
        if (session_status() === PHP_SESSION_NONE) session_start();
        $vigilador_id =  $_POST['vigilador_id'] ?? null;
        $objetivo_id  =  $_POST['objetivo_id'] ?? null;
        $detalle      = trim($_POST['detalle'] ?? '');
        $fecha        = date('Y-m-d');
        $hora         = date('H:i');

        if (!$vigilador_id || !$detalle) {
            ToastifyController::error('Faltan datos obligatorios');
            header('Location:?r=crear_novedad');
            exit;
        }

        try {
            $pdo = Conexion::conectar();
            if (!$pdo->inTransaction()) $pdo->beginTransaction();

            // Manejo de archivo
            $ruta = ControladorArchivos::guardarArchivo(
                $_FILES['adjunto'] ?? [],
                'img/novedades',
                'novedad_' . time()
            );

            $sql = "INSERT INTO novedades (vigilador_id, objetivo_id, fecha, hora, detalle, adjunto)
                    VALUES (:v, :o, :f, :h, :d, :a)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':v' => $vigilador_id,
                ':o' => $objetivo_id,
                ':f' => $fecha,
                ':h' => $hora,
                ':d' => $detalle,
                ':a' => $ruta
            ]);

            $pdo->commit();
            ToastifyController::success('Novedad registrada correctamente');
            header('Location:?r=crear_novedad');
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            ToastifyController::error('Error: ' . $e->getMessage());
            header('Location:?r=crear_novedad');
            exit;
        }
    }

    static public function vistaListadoNovedades()
    {
        Auth::check('novedades', 'vistaListadoNovedades');
        $db  = new Conexion();
        $rol = $_SESSION['rol'] ?? '';

        if ($rol === 'Vigilador') {
            $objetivoId = $_SESSION['objetivo_id'] ?? null;
            if (!$objetivoId) {
                $novedades = [];
                include __DIR__ . '/../vistas/paginas/novedades/listado_novedades.php';
                return;
            }
            $sql = "SELECT n.idNovedad, o.nombre AS objetivo,
                           CONCAT(u.apellido,' ',u.nombre) AS creado_por,
                           n.fecha, n.hora, n.detalle, n.created_at, n.adjunto
                    FROM novedades n
                    LEFT JOIN objetivos o ON n.objetivo_id = o.idObjetivo
                    LEFT JOIN usuarios u ON n.vigilador_id = u.idUsuario
                    WHERE n.objetivo_id = :obj
                    ORDER BY n.fecha DESC, n.hora DESC";
            $params = [':obj' => $objetivoId];
        } else {
            $sql = "SELECT n.idNovedad, o.nombre AS objetivo,
                           CONCAT(u.apellido,' ',u.nombre) AS creado_por,
                           n.fecha, n.hora, n.detalle, n.created_at, n.adjunto
                    FROM novedades n
                    LEFT JOIN objetivos o ON n.objetivo_id = o.idObjetivo
                    LEFT JOIN usuarios u ON n.vigilador_id = u.idUsuario
                    ORDER BY n.fecha DESC, n.hora DESC";
            $params = [];
        }

        $novedades = $db->consultas($sql, $params);
        include __DIR__ . '/../vistas/paginas/novedades/listado_novedades.php';
    }


    static public function vistaListadoEntradaSalida()
    {
        Auth::check('novedades', 'vistaListadoEntradaSalida');
        $db = new Conexion();

        $sql = "SELECT 
                    m.idMarcacion,
                    m.vigilador_id,
                    m.objetivo_id,
                    m.puesto_id,
                    CONCAT(u.apellido, ' ', u.nombre) AS vigilador,
                    o.nombre AS objetivo,
                    m.tipo_evento,
                    m.fecha_hora,

                    -- Turno calendarizado del mismo día
                    (t_same.idTurno IS NOT NULL) AS turno_same_en_calendario,
                    t_same.fecha        AS fecha_turno_same,
                    t_same.codigo_turno AS codigo_turno_same,

                    -- Turno calendarizado del día anterior (para salidas nocturnas)
                    (t_prev.idTurno IS NOT NULL) AS turno_prev_en_calendario,
                    t_prev.fecha        AS fecha_turno_prev,
                    t_prev.codigo_turno AS codigo_turno_prev,

                    -- Horario elegido por mejor coincidencia temporal del puesto
                    pt.hora_entrada,
                    pt.hora_salida,

                    JSON_OBJECT('lat', m.latitud, 'lng', m.longitud) AS map_data,
                    CONCAT(
                        'https://www.openstreetmap.org/?mlat=',
                        m.latitud, '&mlon=', m.longitud,
                        '#map=18/', m.latitud, '/', m.longitud
                    ) AS osm_url

                FROM marcaciones_servicio m
                JOIN usuarios u 
                    ON m.vigilador_id = u.idUsuario
                LEFT JOIN objetivos o 
                    ON m.objetivo_id = o.idObjetivo

                -- Turno del mismo día
                LEFT JOIN turnos t_same
                    ON t_same.usuario_id  = m.vigilador_id
                AND t_same.objetivo_id = m.objetivo_id
                AND t_same.fecha       = DATE(m.fecha_hora)
                AND t_same.tipo_turno  = 'Normal'

                -- Turno del día anterior (para salidas nocturnas)
                LEFT JOIN turnos t_prev
                    ON t_prev.usuario_id  = m.vigilador_id
                AND t_prev.objetivo_id = m.objetivo_id
                AND t_prev.fecha       = DATE(m.fecha_hora - INTERVAL 1 DAY)
                AND t_prev.tipo_turno  = 'Normal'

                -- Subconsulta: elegir el horario más cercano al evento
                LEFT JOIN puestos_turnos pt
                    ON pt.idPuestoTurno = (
                        SELECT pt2.idPuestoTurno
                        FROM puestos_turnos pt2
                        WHERE pt2.puesto_id = m.puesto_id
                        AND pt2.hora_entrada IS NOT NULL
                        AND pt2.hora_salida IS NOT NULL
                        ORDER BY
                            CASE 
                                WHEN m.tipo_evento LIKE '%Entrada%' 
                                    THEN ABS(TIME_TO_SEC(TIMEDIFF(TIME(m.fecha_hora), pt2.hora_entrada)))
                                ELSE 
                                    ABS(TIME_TO_SEC(TIMEDIFF(TIME(m.fecha_hora), pt2.hora_salida)))
                            END ASC,
                            pt2.created_at DESC
                        LIMIT 1
                    )

                -- No deducimos horarios si no hay turno calendarizado
                WHERE (t_same.idTurno IS NOT NULL OR t_prev.idTurno IS NOT NULL)

                ORDER BY m.fecha_hora DESC;
                ";

        $marcaciones = $db->consultas($sql);

        foreach ($marcaciones as &$m) {
            $evento = strtolower(trim($m['tipo_evento'] ?? ''));
            $esEntrada = strpos($evento, 'entrada') !== false;
            $esSalida  = strpos($evento, 'salida') !== false;

            $tieneHorasPuesto = !empty($m['hora_entrada']) && !empty($m['hora_salida']);
            $cruzaMedianoche  = $tieneHorasPuesto
                ? (strtotime($m['hora_salida']) < strtotime($m['hora_entrada']))
                : false;

            // Elegir fecha base según evento y cruce
            $fechaTurnoBase = null;
            if ($esEntrada) {
                $fechaTurnoBase = $m['fecha_turno_same'] ?? null;
            } elseif ($esSalida) {
                $fechaTurnoBase = $cruzaMedianoche
                    ? ($m['fecha_turno_prev'] ?? null)
                    : ($m['fecha_turno_same'] ?? null);
            }

            // Calcular hora esperada si hay fecha base y horas del puesto
            $horaEsperada = null;
            if ($fechaTurnoBase && $tieneHorasPuesto) {
                $horaEsperada = self::calcularHoraEsperadaConBase(
                    $evento,
                    $fechaTurnoBase,
                    $m['hora_entrada'],
                    $m['hora_salida']
                );
            }

            // Calcular diff si hay hora esperada
            $diffMin = self::calcularDiffMin($m['fecha_hora'], $horaEsperada);

            // Determinar si hay turno calendarizado
            $turnoEnCalendario = !empty($m['turno_same_en_calendario']) || !empty($m['turno_prev_en_calendario']);
            $m['turno_en_calendario'] = $turnoEnCalendario;

            // Mostrar hora esperada en formato HH:mm
            $m['hora_esperada_ts'] = $horaEsperada ? date('H:i', strtotime($horaEsperada)) : '-';
            $m['diff_min'] = isset($diffMin) ? (int)$diffMin : null;

            // ✅ Asignar badge directamente
            $m['badge'] = self::calcularBadge($m);
        }
        unset($m);

        include __DIR__ . '/../vistas/paginas/novedades/listado_entradaSalidas.php';
    }

    public static function obtenerGuardiasEnServicioInicio(?int $objetivoId = null, int $limite = 5): array
    {
        $db = new Conexion();
        $limite = max(1, (int) $limite);

        $sql = "SELECT m.idMarcacion,
                       m.vigilador_id,
                       m.objetivo_id,
                       m.puesto_id,
                       CONCAT(u.apellido, ', ', u.nombre) AS vigilador,
                       o.nombre AS objetivo,
                       COALESCE(p.puesto, '-') AS puesto,
                       DATE_FORMAT(m.fecha_hora, '%H:%i') AS hora_entrada,
                       m.fecha_hora
                FROM marcaciones_servicio m
                INNER JOIN usuarios u ON u.idUsuario = m.vigilador_id
                LEFT JOIN objetivos o ON o.idObjetivo = m.objetivo_id
                LEFT JOIN puestos p ON p.idPuesto = m.puesto_id
                WHERE m.idMarcacion = (
                    SELECT m2.idMarcacion
                    FROM marcaciones_servicio m2
                    WHERE m2.vigilador_id = m.vigilador_id
                      AND m2.objetivo_id = m.objetivo_id
                      AND COALESCE(m2.puesto_id, 0) = COALESCE(m.puesto_id, 0)
                    ORDER BY m2.fecha_hora DESC, m2.idMarcacion DESC
                    LIMIT 1
                )
                  AND LOWER(TRIM(m.tipo_evento)) LIKE 'entrada%'";

        $params = [];
        if (!empty($objetivoId)) {
            $sql .= " AND m.objetivo_id = ?";
            $params[] = (int) $objetivoId;
        }

        $sql .= " ORDER BY m.fecha_hora DESC
                  LIMIT {$limite}";

        return $db->consultas($sql, $params) ?: [];
    }
    static public function calcularHoraEsperadaConBase($evento, $fechaTurnoBase, $horaEntrada, $horaSalida)
    {
        $evento = strtolower(trim($evento));
        $cruzaMedianoche = strtotime($horaSalida) < strtotime($horaEntrada);

        if (strpos($evento, 'entrada') !== false) {
            return $fechaTurnoBase . ' ' . $horaEntrada;
        }

        if (strpos($evento, 'salida') !== false) {
            $fechaSalida = $cruzaMedianoche
                ? date('Y-m-d', strtotime($fechaTurnoBase . ' +1 day'))
                : $fechaTurnoBase;
            return $fechaSalida . ' ' . $horaSalida;
        }

        return null;
    }
    static public function calcularDiffMin($fechaMarcacion, $horaEsperada)
    {
        if (!$horaEsperada) return null;
        return (strtotime($fechaMarcacion) - strtotime($horaEsperada)) / 60;
    }
    static public function calcularEstado($evento, $diffMin, $turnoEnCalendario)
    {
        // Sin turno calendarizado → fuera de rango (no inventamos horarios)
        if (!$turnoEnCalendario) {
            return 'Fuera de rango';
        }

        // Con calendario pero sin horas del puesto → sin horario
        if ($diffMin === null) {
            return 'Sin horario';
        }

        $tolerancia = 10;
        $tardeMax   = 30;
        $evento = strtolower(trim($evento ?? ''));
        $esEntrada = (strpos($evento, 'entrada') !== false);

        if ($esEntrada) {
            if (abs($diffMin) <= $tolerancia) return 'En rango';
            if ($diffMin > $tolerancia && $diffMin <= $tardeMax) return 'Tarde ≤ 30 min';
            if ($diffMin < -11) return 'Muy temprano';
            if ($diffMin > $tardeMax) return 'Fuera de rango';
            return 'En rango';
        } else {
            if (abs($diffMin) <= $tolerancia) return 'En rango';
            if ($diffMin < -11) return 'Salida anticipada';
            if ($diffMin > 11) return 'Extra no remunerado';
            return 'En rango';
        }
    }

    public static function calcularBadge($m)
    {
        // - Sin turno calendarizado → Fuera de rango
        // - Con turno pero sin hora esperada/diff → Sin horario
        // - En rango: ±10 min
        // - Tarde ≤ 30 min (entradas): +11 a +30
        // - Muy temprano (entradas): < −11
        // - Salida anticipada (salidas): < −11
        // - Extra no remunerado (salidas): > +11
        $tolerancia = 10;
        $tardeMax   = 30;

        $evento    = strtolower(trim($m['tipo_evento'] ?? ''));
        $esEntrada = (strpos($evento, 'entrada') !== false);
        $turnoEnCalendario = !empty($m['turno_en_calendario']);
        $diffMin = isset($m['diff_min']) ? (int)$m['diff_min'] : null;

        if (!$turnoEnCalendario) {
            return ['estado' => 'Fuera de rango', 'color' => 'bg-danger'];
        }

        if ($diffMin === null) {
            return ['estado' => 'Sin horario', 'color' => 'bg-secondary'];
        }

        if ($esEntrada) {
            if ($diffMin >= -$tolerancia && $diffMin <= $tolerancia) {
                return ['estado' => 'En rango', 'color' => 'bg-success'];
            }
            if ($diffMin > $tolerancia && $diffMin <= $tardeMax) {
                return ['estado' => 'Tarde ≤ 30 min', 'color' => 'bg-warning'];
            }
            if ($diffMin < - ($tolerancia + 1)) {
                return ['estado' => 'Muy temprano', 'color' => 'bg-secondary'];
            }
            if ($diffMin > $tardeMax) {
                return ['estado' => 'Fuera de rango', 'color' => 'bg-danger'];
            }
            return ['estado' => 'En rango', 'color' => 'bg-success'];
        } else {
            if (abs($diffMin) <= $tolerancia) {
                return ['estado' => 'En rango', 'color' => 'bg-success'];
            }
            if ($diffMin < - ($tolerancia + 1)) {
                return ['estado' => 'Salida anticipada', 'color' => 'bg-danger'];
            }
            if ($diffMin > ($tolerancia + 1)) {
                return ['estado' => 'Extra no remunerado', 'color' => 'bg-info'];
            }
            return ['estado' => 'En rango', 'color' => 'bg-success'];
        }
    }

    static public function vistaEntradaSalida()
    {
        Auth::check('novedades', 'vistaEntradaSalida');
        include __DIR__ . '/../vistas/paginas/novedades/entradas_salidas.php';
    }

    static public function vistaCrearNovedades()
    {
        Auth::check('novedades', 'vistaCrearNovedades');
        include __DIR__ . '/../vistas/paginas/novedades/crear_novedades.php';
    }
    static public function vistaHistorialMarcaciones()
    {
        Auth::check('novedades', 'vistaHistorialMarcaciones');
        $db = new Conexion();

        // Siempre cargamos vigiladores
        $rolCategoria = $db->consultas(
            "SELECT categoria FROM roles WHERE id = ? LIMIT 1",
            [$_SESSION['rol_id']]
        )[0]['categoria'] ?? '';

        if ($rolCategoria === 'operativo') {
            $vigiladores = $db->consultas(
                "SELECT idUsuario, apellido, nombre FROM usuarios WHERE idUsuario = ?",
                [$_SESSION['idUsuario']]
            );
        } else {
            $vigiladores = $db->consultas("SELECT u.idUsuario, u.apellido, u.nombre
                                                FROM usuarios u
                                                INNER JOIN roles r ON u.rol_id = r.id
                                                WHERE r.categoria = 'operativo' AND u.activo = 1
                                                ORDER BY u.apellido, u.nombre ");
        }

        $filtros = [
            'vigilador' => $_POST['vigilador'] ?? '',
            'desde'     => $_POST['desde'] ?? '',
            'hasta'     => $_POST['hasta'] ?? ''
        ];

        $marcaciones = [];
        if ($filtros['vigilador'] && $filtros['desde'] && $filtros['hasta']) {
            $sql = "SELECT m.*, o.nombre AS objetivo
                FROM marcaciones_servicio m
                JOIN objetivos o ON m.objetivo_id = o.idObjetivo
                WHERE m.vigilador_id = ?
                AND DATE(m.fecha_hora) BETWEEN ? AND ?
                ORDER BY m.fecha_hora ASC";
            $marcaciones = $db->consultas($sql, [
                $filtros['vigilador'],
                $filtros['desde'],
                $filtros['hasta']
            ]);
        }

        include __DIR__ . '/../vistas/paginas/novedades/historialMarcaciones.php';
    }
}
