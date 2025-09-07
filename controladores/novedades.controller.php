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
                        m.objetivo_id,
                        m.puesto_id,
                        CONCAT(u.apellido, ' ', u.nombre) AS vigilador,
                        o.nombre AS objetivo,
                        m.tipo_evento,
                        m.fecha_hora,
                        pt.hora_entrada,
                        pt.hora_salida,
                        pt.numero_turno,
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
                    LEFT JOIN rotaciones_puestos rp
                        ON rp.usuario_id = m.vigilador_id
                        AND rp.fecha = DATE(m.fecha_hora)
                        AND rp.puesto_id = m.puesto_id
                    -- Deducción de numero_turno
                    LEFT JOIN puestos_turnos pt
                        ON pt.puesto_id = m.puesto_id
                        AND pt.numero_turno = COALESCE(
                            CASE rp.codigo_turno
                                WHEN 'D' THEN 1
                                WHEN 'N' THEN 2
                            END,
                            CASE 
                                WHEN TIME(m.fecha_hora) BETWEEN '05:00:00' AND '12:00:00' THEN 1 -- Día
                                WHEN TIME(m.fecha_hora) >= '17:00:00' OR TIME(m.fecha_hora) <= '02:00:00' THEN 2 -- Noche
                                ELSE NULL
                            END
                        )
                    ORDER BY m.fecha_hora DESC;
                    ";

        $marcaciones = $db->consultas($sql);

        foreach ($marcaciones as &$m) {
            $m['badge'] = self::calcularBadge($m);
        }
        unset($m);

        include __DIR__ . '/../vistas/paginas/novedades/listado_entradaSalidas.php';
    }

    public static function calcularBadge($m)
    {
        $toleranciaMin   = 10; // minutos de tolerancia
        $tardeHastaMin   = 30; // minutos para "Tarde ≤ 30 min"

        // Determinar si es entrada o salida de forma segura
        $evento = '';
        if (!empty($m['evento'])) {
            $evento = strtolower($m['evento']);
        } elseif (!empty($m['tipo_evento'])) {
            $evento = strtolower($m['tipo_evento']);
        }

        $esEntrada = (strpos($evento, 'entrada') !== false);

        // Validar horas
        $horaEntrada = !empty($m['hora_entrada']) ? $m['hora_entrada'] : null;
        $horaSalida  = !empty($m['hora_salida'])  ? $m['hora_salida']  : null;

        if ($horaEntrada === null || $horaSalida === null) {
            return ['estado' => 'Sin horario', 'color' => 'bg-secondary'];
        }

        $fechaMarcacion = date('Y-m-d', strtotime($m['fecha_hora']));

        // Calcular referencia
        if ($esEntrada) {
            $fechaBase = $fechaMarcacion;
            $tsReferencia = strtotime("$fechaBase $horaEntrada");
        } else {
            $fechaBase = date('Y-m-d', strtotime($fechaMarcacion . ' -1 day'));
            if (strtotime($horaSalida) <= strtotime($horaEntrada)) {
                $tsReferencia = strtotime("$fechaBase $horaSalida +1 day");
            } else {
                $tsReferencia = strtotime("$fechaBase $horaSalida");
            }
        }

        // Diferencia en minutos
        $tsMarcacion = strtotime($m['fecha_hora']);
        $diffMin = (int)round(($tsMarcacion - $tsReferencia) / 60);

        // Clasificación
        if ($esEntrada) {
            if ($diffMin > 0 && $diffMin <= $tardeHastaMin) {
                return ['estado' => 'Tarde ≤ 30 min', 'color' => 'bg-warning'];
            } elseif ($diffMin > $tardeHastaMin) {
                return ['estado' => 'Fuera de rango', 'color' => 'bg-danger'];
            } elseif ($diffMin >= -$toleranciaMin) {
                return ['estado' => 'En rango', 'color' => 'bg-success'];
            } else {
                return ['estado' => 'Muy temprano', 'color' => 'bg-info'];
            }
        } else {
            if ($diffMin < -$toleranciaMin) {
                return ['estado' => 'Salida anticipada', 'color' => 'bg-danger'];
            } elseif (abs($diffMin) <= $toleranciaMin) {
                return ['estado' => 'En rango', 'color' => 'bg-success'];
            } else {
                return ['estado' => 'Extra no remunerado', 'color' => 'bg-primary'];
            }
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
