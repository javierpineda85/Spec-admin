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
        Auth::check('novedades', 'crtRegistrar');
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
                    -- Coordenadas en formato JSON para el front
                    JSON_OBJECT(
                        'lat', m.latitud,
                        'lng', m.longitud
                    ) AS map_data,
                    -- URL directa a OpenStreetMap (opcional)
                    CONCAT(
                        'https://www.openstreetmap.org/?mlat=',
                        m.latitud,
                        '&mlon=',
                        m.longitud,
                        '#map=18/',
                        m.latitud,
                        '/',
                        m.longitud
                    ) AS osm_url
                FROM marcaciones_servicio m
                JOIN usuarios u 
                ON m.vigilador_id = u.idUsuario
                LEFT JOIN objetivos o 
                ON m.objetivo_id = o.idObjetivo
                LEFT JOIN puestos_turnos pt
                ON pt.idPuestoTurno = (
                    SELECT pt2.idPuestoTurno
                    FROM puestos_turnos pt2
                    WHERE pt2.puesto_id = m.puesto_id
                    ORDER BY
                        CASE 
                        WHEN m.tipo_evento = 'entrada' 
                            THEN ABS(TIME_TO_SEC(TIMEDIFF(TIME(m.fecha_hora), pt2.hora_entrada)))
                        ELSE 
                            ABS(TIME_TO_SEC(TIMEDIFF(TIME(m.fecha_hora), pt2.hora_salida)))
                        END ASC
                    LIMIT 1
                    )
                ORDER BY m.fecha_hora DESC";

        $marcaciones = $db->consultas($sql);

        foreach ($marcaciones as &$m) {
            $m['badge'] = self::calcularBadge($m);
        }
        unset($m);

        include __DIR__ . '/../vistas/paginas/novedades/listado_entradaSalidas.php';
    }

    private static function calcularBadge($m)
    {
        if (empty($m['hora_entrada']) || empty($m['hora_salida'])) {
            return ['Sin horario', 'badge-secondary'];
        }

        $horaEvento = strtotime($m['fecha_hora']);
        $fechaBase  = date('Y-m-d', $horaEvento);

        $inicio = strtotime("$fechaBase {$m['hora_entrada']}");
        $fin    = strtotime("$fechaBase {$m['hora_salida']}");

        // Si el turno cruza medianoche
        if ($fin <= $inicio) {
            $fin = strtotime("$fechaBase {$m['hora_salida']} +1 day");
        }

        if ($m['tipo_evento'] === 'entrada') {
            $diffMin = ($horaEvento - $inicio) / 60;
            if ($diffMin >= -15 && $diffMin <= 0) {
                return ['En rango', 'badge-success'];
            } elseif ($diffMin > 0 && $diffMin <= 30) {
                return ['Tarde ≤ 30 min', 'badge-warning'];
            } elseif ($diffMin > 30) {
                return ['Fuera de rango', 'badge-danger'];
            } else { // $diffMin < -15
                return ['Muy temprano', 'badge-secondary'];
            }
        }

        if ($m['tipo_evento'] === 'salida') {
            $diffMin = ($horaEvento - $fin) / 60;
            if ($diffMin >= 0 && $diffMin <= 15) {
                return ['En rango', 'badge-success'];
            } elseif ($diffMin < 0) {
                return ['Salida anticipada', 'badge-danger'];
            } else { // $diffMin > 15
                return ['Extra no remunerado', 'badge-info'];
            }
        }

        return ['', ''];
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
        if (($_SESSION['rol'] ?? '') === 'Vigilador') {
            $vigiladores = $db->consultas(
                "SELECT idUsuario, apellido, nombre FROM usuarios WHERE idUsuario = ?",
                [$_SESSION['idUsuario']]
            );
        } else {
            $vigiladores = $db->consultas(
                "SELECT idUsuario, apellido, nombre FROM usuarios WHERE rol = 'Vigilador' ORDER BY apellido, nombre"
            );
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
