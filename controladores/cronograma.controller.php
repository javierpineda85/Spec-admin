<?php

require_once('modelos/cronograma.modelo.php');
require_once 'modelos/turnos.modelo.php';
require_once __DIR__ . '/../modelos/cronograma_validacion.modelo.php';
class ControladorCronogramas
{
    public static function ctrGuardarCronograma()
    {
        Auth::check('cronogramas', 'vistaCrearCronograma');
        if (!isset($_POST['guardar_cronograma'])) return;
        try {
            $datos = self::leerPost($_POST);
            $_SESSION['cronograma_post'] = $datos;
            $avisos = self::guardarDatos($datos);
            $_SESSION['cronograma_avisos'] = $avisos;
            unset($_SESSION['cronograma_post'], $_SESSION['cronograma_error']);
            ToastifyController::success('Cronograma guardado correctamente.');
            header('Location: ?r=crear_cronograma&objetivo=' . (int)$_POST['objetivo'] . '&mes=' . rawurlencode($_POST['mes']));
        } catch (Throwable $e) {
            ToastifyController::error($e->getMessage());
            $_SESSION['cronograma_error'] = $datos ?? $_POST;
            header('Location: ?r=crear_cronograma');
        }
        exit;
    }

    private static function leerPost(array $post): array
    {
        if (isset($post['cronograma_json'])) {
            $filas = json_decode($post['cronograma_json'], true, 64, JSON_THROW_ON_ERROR);
            if (!is_array($filas)) throw new InvalidArgumentException('La tabla enviada no es válida.');
            $post['vigilador'] = $filas['vigilador'] ?? [];
            $post['referente'] = $filas['referente'] ?? [];
            unset($post['cronograma_json']);
        }
        return $post;
    }

    /** Reemplazo atómico. Ningún error elimina el cronograma anterior. */
    public static function guardarDatos(array $post): array
    {
        $post = self::leerPost($post);
        $objetivo = filter_var($post['objetivo'] ?? null, FILTER_VALIDATE_INT);
        $mes = (string)($post['mes'] ?? '');
        $inicio = CronogramaReglas::mes($mes);
        if (!$objetivo || $objetivo < 1) throw new InvalidArgumentException('Selecciona un objetivo válido.');
        if (($post['cronograma_completo'] ?? '') !== '1'
            || (string)($post['cronograma_objetivo'] ?? '') !== (string)$objetivo
            || ($post['cronograma_mes'] ?? '') !== $mes) {
            throw new InvalidArgumentException('La tabla no corresponde al objetivo y mes, o el envío está incompleto. Vuelve a cargar el cronograma.');
        }
        $turnos = [];
        $usuarios = [];
        $filas = [];
        foreach (['vigilador' => 'Vigilador', 'referente' => 'Referente'] as $key => $rol) {
            if (!is_array($post[$key] ?? [])) throw new InvalidArgumentException('La nómina enviada no es válida.');
            foreach ($post[$key] ?? [] as $uid => $dias) {
                $uid = filter_var($uid, FILTER_VALIDATE_INT);
                if (!$uid || $uid < 1 || !is_array($dias) || (int)($dias['usuario'] ?? 0) !== $uid) throw new InvalidArgumentException('Hay una fila sin usuario válido.');
                if (isset($filas[$uid])) throw new InvalidArgumentException('Un usuario aparece en más de una fila del objetivo.');
                $filas[$uid] = $rol;
                for ($dia = 1; $dia <= (int)$inicio->format('t'); $dia++) {
                    if (!array_key_exists($dia, $dias)) throw new InvalidArgumentException('El envío está incompleto: faltan días de la tabla.');
                }
                foreach ($dias as $dia => $valor) {
                    if ($dia === 'usuario') continue;
                    if (!ctype_digit((string)$dia) || (int)$dia < 1 || (int)$dia > (int)$inicio->format('t') || !is_string($valor)) throw new InvalidArgumentException('Hay una fecha o código inválido.');
                    $codigo = CronogramaReglas::codigo($valor);
                    if ($codigo === '') continue;
                    if (!preg_match('/^[A-Z0-9\/,\.]{1,5}$/D', $codigo)) throw new InvalidArgumentException('Código de turno inválido: ' . $codigo);
                    if (preg_match('/^([0-9]+(?:[.,][0-9]+)?)H$/D', $codigo, $m) && ((float)str_replace(',', '.', $m[1]) <= 0 || (float)str_replace(',', '.', $m[1]) > 24)) throw new InvalidArgumentException('Las jornadas deben durar más de 0 y hasta 24 horas.');
                    $turnos[] = ['usuario_id' => $uid, 'objetivo_id' => $objetivo, 'fecha' => sprintf('%s-%02d', $mes, $dia),
                        'rol' => $rol, 'tipo_turno' => self::esLicencia($codigo) ? 'Licencia' : 'Normal', 'codigo_turno' => $codigo];
                    $usuarios[$uid] = $uid;
                }
            }
        }
        if (!$turnos) throw new InvalidArgumentException('El cronograma está vacío. No se eliminaron los turnos existentes.');
        if (count($filas) !== (int)($post['cronograma_filas'] ?? -1)) throw new InvalidArgumentException('La cantidad de filas enviada está incompleta.');
        $db = Conexion::conectar();
        if (!$db) throw new RuntimeException('No hay conexión activa a la base de datos.');
        $engine = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'turnos'")->fetchColumn();
        if (strtoupper((string)$engine) !== 'INNODB') throw new RuntimeException('La tabla de turnos necesita la migración transaccional antes de guardar.');
        try {
            $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $db->beginTransaction();
            $s = $db->prepare('SELECT idObjetivo FROM objetivos WHERE idObjetivo = ? AND activo = 1 FOR UPDATE');
            $s->execute([$objetivo]);
            if (!$s->fetchColumn()) throw new InvalidArgumentException('El objetivo no existe o está inactivo.');
            $previos = ModeloTurnos::mdlBuscarTurnosPorMes($objetivo, $mes);
            foreach ($previos as $t) $usuarios[(int)$t['usuario_id']] = (int)$t['usuario_id'];
            sort($usuarios, SORT_NUMERIC);
            $s = $db->prepare('SELECT idUsuario FROM usuarios WHERE idUsuario IN (' . implode(',', array_fill(0, count($usuarios), '?')) . ') ORDER BY idUsuario FOR UPDATE');
            $s->execute(array_values($usuarios));
            $s->fetchAll(PDO::FETCH_COLUMN);
            $permitidos = [];
            foreach (['Vigilador' => ['objetivo_vigiladores', 'vigilador_id', 'operativo'], 'Referente' => ['objetivo_referentes', 'referente_id', 'referente']] as $rol => [$tabla, $campo, $categoria]) {
                $s = $db->prepare("SELECT u.idUsuario FROM usuarios u JOIN roles r ON r.id = u.rol_id JOIN $tabla v ON v.$campo = u.idUsuario WHERE v.objetivo_id = ? AND u.activo = 1 AND r.categoria = ?");
                $s->execute([$objetivo, $categoria]);
                foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $id) $permitidos[$rol][(int)$id] = true;
            }
            $historicos = [];
            foreach ($previos as $t) {
                $permitidos[$t['rol']][(int)$t['usuario_id']] = true;
                $historicos[$t['usuario_id'] . ':' . $t['fecha']] = $t['codigo_turno'];
                if (!isset($filas[(int)$t['usuario_id']])) throw new InvalidArgumentException('Falta una persona del cronograma guardado. Recarga la tabla para conservar su historial.');
            }
            $validador = new ModeloCronogramaValidacion($db);
            $horasSiglas = self::obtenerHorasSiglasObjetivo($objetivo);
            $horas = array_fill_keys(array_keys($filas), 0);
            $avisos = [];
            foreach ($turnos as $t) {
                if (!isset($permitidos[$t['rol']][$t['usuario_id']])) throw new InvalidArgumentException('Un usuario no pertenece a la nómina del objetivo.');
                $categoria = CronogramaReglas::categoria($t['codigo_turno'], $objetivo, $validador->siglas());
                if ($categoria === 'desconocido' && ($historicos[$t['usuario_id'] . ':' . $t['fecha']] ?? null) !== $t['codigo_turno']) throw new InvalidArgumentException('Configura la sigla ' . $t['codigo_turno'] . ' antes de asignarla.');
                $resultado = $validador->validar($t);
                if ($resultado['estado'] !== 'libre') throw new InvalidArgumentException($t['fecha'] . ', usuario ' . $t['usuario_id'] . ': ' . $resultado['mensaje']);
                $horas[$t['usuario_id']] += self::horasCodigoCronograma($t['codigo_turno'], $horasSiglas);
            }
            if (!ModeloTurnos::mdlEliminarTurnosPorMes($objetivo, $mes)) throw new RuntimeException('No se pudo reemplazar el cronograma.');
            foreach ($turnos as $t) {
                $respuesta = ModeloTurnos::mdlGuardarTurno('turnos', $t);
                if ($respuesta !== 'ok') throw new RuntimeException($respuesta === 'duplicado' ? 'Hay un turno duplicado; se conservó el cronograma anterior.' : 'No se pudo guardar el turno. Verifica el esquema de turnos y vuelve a intentar.');
            }
            foreach ($horas as $uid => $total) if ($total < 200 || $total > 240) $avisos[] = "Usuario $uid: $total horas planificadas (rango de referencia: 200–240).";
            $db->commit();
            return $avisos;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public static function validarTurnoAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['idUsuario']) || !Auth::hasPermission('cronogramas', 'vistaCrearCronograma')) {
            http_response_code(403);
            echo json_encode(['estado' => 'error', 'mensaje' => 'No tienes permiso para validar cronogramas.']);
            return;
        }
        try {
            $uid = (int)($_GET['usuario'] ?? 0);
            $obj = (int)($_GET['objetivo_actual'] ?? 0);
            $dia = (int)($_GET['dia'] ?? 0);
            $mes = (int)($_GET['mes'] ?? 0);
            $anio = (int)($_GET['anio'] ?? 0);
            if ($uid < 1 || $obj < 1 || !checkdate($mes, $dia, $anio)) throw new InvalidArgumentException('Usuario, objetivo o fecha inválidos.');
            $db = Conexion::conectar();
            if (!$db) throw new RuntimeException('No se pudo consultar el horario.');
            $resultado = (new ModeloCronogramaValidacion($db))->validar(['usuario_id' => $uid, 'objetivo_id' => $obj,
                'fecha' => sprintf('%04d-%02d-%02d', $anio, $mes, $dia), 'codigo_turno' => CronogramaReglas::codigo((string)($_GET['codigo'] ?? ''))]);
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(422);
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private static function obtenerHorasSiglasObjetivo(int $objetivoId): array
    {
        $s = Conexion::conectar()->prepare('SELECT sigla, horas FROM objetivo_siglas WHERE objetivo_id = ? AND activo = 1');
        $s->execute([$objetivoId]);
        $horas = [];
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $row) $horas[CronogramaReglas::codigo($row['sigla'])] = (float)$row['horas'];
        return $horas;
    }

    private static function horasCodigoCronograma(string $codigo, array $horasSiglasObjetivo): float
    {
        return CronogramaReglas::horas($codigo, $horasSiglasObjetivo);
    }

    private static function esLicencia(string $codigo): bool
    {
        return in_array(CronogramaReglas::codigo($codigo), array_merge(CronogramaReglas::AUSENCIAS, CronogramaReglas::PASIVAS), true);
    }

    public static function generarSimulacionVacia(int $objetivoId, string $mes): array
    {
        return self::continuar4x2DesdeTurnosAnteriores([], $objetivoId, $mes);
    }

    public static function precargarCronogramaSiExiste($objetivoId, $mes)
    {
        $fecha = CronogramaReglas::mes($mes);
        if ((int)$objetivoId < 1) throw new InvalidArgumentException('Selecciona un objetivo válido.');
        $_SESSION['cronograma_avisos'] = [];
        $actual = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $mes);
        if ($actual) {
            $post = self::armarPostSimuladoDesdeTurnos($actual, $objetivoId, $mes);
            $origen = 'actual';
            $anterior = null;
        } else {
            $anterior = $fecha->modify('-1 month')->format('Y-m');
            $turnos = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $anterior);
            $post = self::continuar4x2DesdeTurnosAnteriores($turnos ?: [], $objetivoId, $mes);
            $origen = $turnos ? 'anterior' : 'vacio';
        }
        $_SESSION['cronograma_post'] = $post;
        return ['origen' => $origen, 'turnos' => $actual ?: ($turnos ?? []), 'postSimulado' => $post, 'mesAnterior' => $anterior];
    }

    private static function continuar4x2DesdeTurnosAnteriores(array $turnosPrev, int $objetivoId, string $mes): array
    {
        $fecha = CronogramaReglas::mes($mes);
        $finAnterior = $fecha->modify('-1 day')->format('Y-m-d');
        $post = ['objetivo' => $objetivoId, 'mes' => $mes, 'vigilador' => [], 'referente' => []];
        $db = new Conexion;
        $patron = ['D', 'D', 'N', 'N', 'F', 'F'];
        $indice = 0;
        foreach (['vigilador' => ['objetivo_vigiladores', 'vigilador_id', 'operativo', 'ov'], 'referente' => ['objetivo_referentes', 'referente_id', 'referente', 'orf']] as $rol => [$tabla, $columna, $categoria, $alias]) {
            $usuarios = $db->consultas("SELECT DISTINCT u.idUsuario FROM usuarios u JOIN roles r ON u.rol_id = r.id JOIN $tabla $alias ON $alias.$columna = u.idUsuario WHERE $alias.objetivo_id = $objetivoId AND u.activo = 1 AND r.categoria = '$categoria' ORDER BY u.idUsuario");
            foreach ($usuarios as $usuario) {
                $uid = (int)$usuario['idUsuario'];
                $post[$rol][$uid] = ['usuario' => $uid];
                $hist = array_values(array_filter($turnosPrev, fn($t) => (int)$t['usuario_id'] === $uid && strtolower($t['rol']) === $rol));
                usort($hist, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));
                $offset = ($indice++ % 3) * 2;
                $pendiente = false;
                if ($hist) {
                    $last = $hist[count($hist) - 1];
                    $fase = CronogramaReglas::fase($last['codigo_turno']);
                    if ($last['fecha'] !== $finAnterior || $fase === null) {
                        $pendiente = true;
                        $_SESSION['cronograma_avisos'][] = "Usuario $uid: completa el ciclo manualmente; el último registro no permite determinar la fase al cierre del mes.";
                    } else {
                        $prev = $hist[count($hist) - 2] ?? null;
                        $dos = $prev && $prev['fecha'] === $fecha->modify('-2 days')->format('Y-m-d') && CronogramaReglas::fase($prev['codigo_turno']) === $fase;
                        $offset = (['D' => 0, 'N' => 2, 'F' => 4][$fase] + ($dos ? 2 : 1)) % 6;
                    }
                }
                for ($dia = 1; $dia <= (int)$fecha->format('t'); $dia++) $post[$rol][$uid][$dia] = $pendiente ? '' : $patron[($offset + $dia - 1) % 6];
            }
        }
        return $post;
    }

    private static function armarPostSimuladoDesdeTurnos(array $turnos, int $objetivoId, string $mes): array
    {
        $post = [
            'objetivo'  => $objetivoId,
            'mes'       => $mes,
            'vigilador' => [],
            'referente' => []
        ];

        foreach ($turnos as $t) {
            $dia       = (int)substr($t['fecha'], 8, 2);
            $usuarioId = (int)$t['usuario_id'];
            $rol       = strtolower($t['rol']); // 'vigilador' | 'referente'

            if (!isset($post[$rol][$usuarioId]['usuario'])) {
                $post[$rol][$usuarioId]['usuario'] = $usuarioId;
            }
            $post[$rol][$usuarioId][$dia] = $t['codigo_turno']; // D, N, F, GP/D, etc.
        }

        return $post;
    }


    /*Funcion para buscar por resumen diario de jornadas trabajadas*/
    static public function crtBuscarResumenDiario()
    {
        //Auth::check('cronogramas', 'crtBuscarResumenDiario');
        Auth::check('cronogramas', 'vistaJornadasPorObjetivo');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_POST['buscar_resumen_diario'])) {
            // 1) guardo filtros
            $_SESSION['filtros_resumen'] = [
                'objetivo' => $_POST['objetivo'],
                'desde'    => $_POST['desde'],
                'hasta'    => $_POST['hasta']
            ];

            // 2) obtengo datos del modelo
            $f = $_SESSION['filtros_resumen'];
            $_SESSION['resumen_diario'] = ModeloCronograma::mdlResumenDiarioJornadas(
                $f['objetivo'],
                $f['desde'],
                $f['hasta']
            );

            // 3) opcional, mensaje de éxito
            ToastifyController::success("Se encontraron " . count($_SESSION['resumen_diario'])
                . " registros.");
        }

        // 4) redirijo a la vista que tú tengas mapeada, p. ej.:
        header("Location: index.php?r=listado_resumen_diario");
        exit;
    }

    /* Calcula el total de horas trabajas en un periodo de tiempo. VISTA: reporte_horas_por_objetivo */
    /* Se emparejan entradas y salidas por vigilador y objetivo (el GROUP BY m1.idMarcacion previene múltiples pareos). */
    static public function crtBuscarResumenHoras()
    {
        //Auth::check('cronogramas', 'crtBuscarResumenHoras');
        Auth::check('cronogramas', 'vistaReporteHorasPorObjetivo');

        $desde = $_POST['desde'] ?? null;
        $hasta = $_POST['hasta'] ?? null;

        if (!$desde || !$hasta) {
            ToastifyController::error("Por favor completa todos los filtros.");
            exit;
        }

        $db = new Conexion;
        $objetivos = $db->consultas("SELECT idObjetivo, nombre FROM objetivos ORDER BY nombre");

        // 1. Traemos TODAS las marcaciones del período
        $marcaciones = $db->consultas("SELECT vigilador_id, objetivo_id, tipo_evento, fecha_hora
                FROM marcaciones_servicio
                WHERE fecha_hora BETWEEN ? AND ?
                ORDER BY vigilador_id, objetivo_id, fecha_hora
            ", ["$desde 00:00:00", "$hasta 23:59:59"]);

        // 2. Armamos jornadas reales
        $jornadas = [];
        $entradaPendiente = [];

        foreach ($marcaciones as $m) {
            $key = "{$m['vigilador_id']}_{$m['objetivo_id']}";

            if ($m['tipo_evento'] === 'entrada') {
                $entradaPendiente[$key] = $m['fecha_hora'];
            } elseif ($m['tipo_evento'] === 'salida' && isset($entradaPendiente[$key])) {
                $jornadas[] = [
                    'vigilador_id' => $m['vigilador_id'],
                    'objetivo_id'  => $m['objetivo_id'],
                    'entrada'      => $entradaPendiente[$key],
                    'salida'       => $m['fecha_hora']
                ];
                unset($entradaPendiente[$key]);
            }
        }

        $reporte = [];

        foreach ($objetivos as $o) {
            $idObjetivo = $o['idObjetivo'];
            $sumDiur = 0.0;
            $sumNoct = 0.0;

            foreach ($jornadas as $j) {
                if ($j['objetivo_id'] != $idObjetivo) continue;

                $entrada = new DateTime($j['entrada']);
                $salida  = new DateTime($j['salida']);
                if ($salida <= $entrada) $salida->modify('+1 day');

                // Evitamos horas antes del turno, pero permitimos salidas posteriores
                // Buscar el turno para obtener la fecha de referencia
                $sqlTurno = "SELECT t.codigo_turno, t.fecha, rp.puesto_id
                         FROM turnos t
                         LEFT JOIN rotaciones_puestos rp
                           ON rp.usuario_id = t.usuario_id AND rp.objetivo_id = t.objetivo_id
                          AND rp.fecha = t.fecha AND rp.codigo_turno = t.codigo_turno
                         WHERE t.usuario_id = ? AND t.objetivo_id = ?
                           AND t.fecha BETWEEN DATE(?) AND DATE_ADD(?, INTERVAL 1 DAY)
                         ORDER BY ABS(DATEDIFF(t.fecha, ?))
                         LIMIT 1";
                $turno = $db->consultas($sqlTurno, [
                    $j['vigilador_id'],
                    $idObjetivo,
                    $entrada->format('Y-m-d'),
                    $entrada->format('Y-m-d'),
                    $entrada->format('Y-m-d')
                ])[0] ?? null;

                if (!$turno) continue;

                // Si tiene código de turno, lo usamos para buscar el rango (pero sin recortar)
                $mapaTurno = ['D' => 1, 'N' => 2];
                $numeroTurno = $mapaTurno[strtoupper(trim($turno['codigo_turno']))] ?? null;

                $sqlHorario = "SELECT hora_entrada
                           FROM puestos_turnos
                           WHERE puesto_id = ? AND numero_turno = ?
                           LIMIT 1";
                $horaEntradaTurno = ($numeroTurno !== null && !empty($turno['puesto_id']))
                    ? ($db->consultas($sqlHorario, [$turno['puesto_id'], $numeroTurno])[0]['hora_entrada'] ?? null)
                    : null;

                if ($horaEntradaTurno) {
                    $inicioTeorico = new DateTime("{$turno['fecha']} $horaEntradaTurno");
                    if ($inicioTeorico > $entrada) {
                        // El vigilador entró antes de su turno → no se cuenta
                        $entrada = $inicioTeorico;
                    }
                }

                if ($salida <= $entrada) continue; // jornada inválida

                // 🧠 Hasta acá tenemos la jornada real: entrada → salida
                $hDiur = self::calcularHorasEnVentana($entrada, $salida, '06:00:00', '21:59:59');
                $hNoct = self::calcularHorasEnVentana($entrada, $salida, '22:00:00', '05:59:59');

                $sumDiur += round($hDiur, 2);
                $sumNoct += round($hNoct, 2);
            }

            $reporte[] = [
                'nombre'    => $o['nombre'],
                'diurnas'   => round($sumDiur, 2),
                'nocturnas' => round($sumNoct, 2)
            ];
        }

        $_SESSION['resumen_periodo'] = $reporte;
        $_SESSION['filtros_resumen'] = ['desde' => $desde, 'hasta' => $hasta];
        header('Location: ?r=reporte_porHoras');
        exit;
    }

    /**
     * Devuelve las horas (float) entre $start y $end que caen dentro de la ventana diaria [$horaDesde, $horaHasta].
     */
    static private function calcularHorasEnVentana(DateTime $start, DateTime $end, string $horaDesde, string $horaHasta): float
    {
        if ($start >= $end) return 0;

        $segundosEnVentana = 0;
        $cursor = clone $start;

        while ($cursor < $end) {
            $fechaBase = $cursor->format('Y-m-d');

            $inicioVentana = new DateTime("$fechaBase $horaDesde");
            $finVentana    = new DateTime("$fechaBase $horaHasta");

            // Si la ventana cruza medianoche (ej: 22:00 → 05:59)
            if ($finVentana <= $inicioVentana) {
                $finVentana->modify('+1 day');
            }

            // Si el fin real del rango cruzado también supera el fin total
            if ($finVentana > $end) {
                $finVentana = clone $end;
            }

            // Calcular intersección entre la jornada y la ventana
            $inicioSolape = max($start, $inicioVentana);
            $finSolape    = min($end, $finVentana);

            if ($finSolape > $inicioSolape) {
                $segundosEnVentana += $finSolape->getTimestamp() - $inicioSolape->getTimestamp();
            }

            // Avanzamos el cursor al día siguiente
            $cursor->modify('+1 day')->setTime(0, 0);
        }

        return $segundosEnVentana / 3600; // devolver en horas
    }

    /* Calcula la cantidad de horas trabajadas por vigilador. 
    *Tiene en cuenta si ha trabajo una GP o FRANCO. VISTA: reporte_horas_vigilador 
    */

    public static function crtBuscarResumenHorasPorVigilador()
    {
        //Auth::check('cronograma', 'crtBuscarResumenHorasPorVigilador');
        Auth::check('cronogramas', 'vistaHorasPorVigilador');

        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';

        if (!$desde || !$hasta) {
            $_SESSION['error_message'] = 'Debes indicar un rango de fechas válido';
            header('Location: ?r=reporte_porVigilador');
            exit;
        }

        $db = new Conexion;
        $usuarios = $db->consultas("SELECT u.idUsuario, CONCAT(u.apellido, ', ', u.nombre) AS vigilador
                                            FROM usuarios u
                                            JOIN roles r ON u.rol_id = r.id
                                            WHERE r.nombre = 'Vigilador';");
        $horariosTurnos = $db->consultas("SELECT puesto_id, numero_turno, hora_entrada, hora_salida FROM puestos_turnos");

        $mapeoTurnos = [
            'D'     => 1,
            'N'     => 2,
            'I'     => 3
        ];

        $rows = [];
        $_SESSION['diferencias_horarias'] = [];

        foreach ($usuarios as $usuario) {
            $id = $usuario['idUsuario'];
            $nombre = $usuario['vigilador'];

            $marcaciones = $db->consultas(
                "SELECT * 
             FROM marcaciones_servicio 
             WHERE vigilador_id = :id 
               AND fecha_hora BETWEEN :desde AND :hasta 
             ORDER BY fecha_hora",
                [
                    'id' => $id,
                    'desde' => "$desde 00:00:00",
                    'hasta' => "$hasta 23:59:59"
                ]
            );

            $turnosAsignados = $db->consultas(
                "SELECT t.fecha, t.codigo_turno, t.objetivo_id, rp.puesto_id
             FROM turnos t
             LEFT JOIN rotaciones_puestos rp
               ON rp.usuario_id = t.usuario_id AND rp.objetivo_id = t.objetivo_id
              AND rp.fecha = t.fecha AND rp.codigo_turno = t.codigo_turno
             WHERE t.usuario_id = :id
               AND t.fecha BETWEEN :desde AND :hasta",
                [
                    'id' => $id,
                    'desde' => $desde,
                    'hasta' => $hasta
                ]
            );

            $turnosCompletos = [];
            $francos = 0;
            $guardiasPasivasDiurnas = 0;
            $guardiasPasivasNocturnas = 0;

            foreach ($turnosAsignados as $turno) {
                $codigo = trim(strtoupper($turno['codigo_turno']));

                // Verificar si ese día tiene marcaciones
                $tieneMarcaciones = false;
                foreach ($marcaciones as $m) {
                    if (substr($m['fecha_hora'], 0, 10) === $turno['fecha']) {
                        $tieneMarcaciones = true;
                        break;
                    }
                }

                // Si es F, GP/D o GP/N y hay marcaciones → contar como jornada extra
                if ($codigo === 'F' && $tieneMarcaciones) {
                    $francos++;
                    continue;
                }
                if ($codigo === 'GP/D' && $tieneMarcaciones) {
                    $guardiasPasivasDiurnas++;
                    continue;
                }
                if ($codigo === 'GP/N' && $tieneMarcaciones) {
                    $guardiasPasivasNocturnas++;
                    continue;
                }

                // Turnos normales
                $numeroTurno = $mapeoTurnos[$codigo] ?? null;
                if ($numeroTurno) {
                    foreach ($horariosTurnos as $horario) {
                        if ($horario['puesto_id'] == $turno['puesto_id'] && $horario['numero_turno'] == $numeroTurno) {
                            $turnosCompletos[] = [
                                'fecha' => $turno['fecha'],
                                'objetivo_id' => $turno['objetivo_id'],
                                'hora_entrada' => $horario['hora_entrada'],
                                'hora_salida' => $horario['hora_salida']
                            ];
                            break;
                        }
                    }
                }
            }

            $jornadas = self::emparejarMarcaciones($marcaciones);

            $diurnas = 0;
            $nocturnas = 0;

            foreach ($jornadas as $j) {
                $fechaJ = substr($j['entrada'], 0, 10);
                $encontrado = false;

                foreach ($turnosCompletos as $turno) {
                    if ($turno['fecha'] === $fechaJ && $turno['objetivo_id'] == $j['objetivo_id']) {
                        $encontrado = true;

                        $entradaReal = new DateTime($j['entrada']);
                        $salidaReal = new DateTime($j['salida']);
                        $entradaTurno = new DateTime("$fechaJ {$turno['hora_entrada']}");
                        $salidaTurno = new DateTime("$fechaJ {$turno['hora_salida']}");

                        if ($salidaTurno < $entradaTurno) {
                            $salidaTurno->modify('+1 day');
                        }

                        $margen = new DateInterval('PT15M');
                        $entradaMin = (clone $entradaTurno)->sub($margen);
                        $salidaMax = (clone $salidaTurno)->add($margen);

                        $inicio = max($entradaReal, $entradaMin);
                        $fin = min($salidaReal, $salidaMax);

                        if ($inicio >= $fin) {
                            continue;
                        }

                        // Diferencia total en minutos
                        $minDifEntrada = ($entradaReal->getTimestamp() - $entradaTurno->getTimestamp()) / 60;
                        $minDifSalida  = ($salidaReal->getTimestamp() - $salidaTurno->getTimestamp()) / 60;

                        if (abs($minDifEntrada) > 15 || abs($minDifSalida) > 15) {
                            $_SESSION['diferencias_horarias'][] = [
                                'vigilador' => $nombre,
                                'fecha' => $fechaJ,
                                'entrada_real' => $entradaReal->format('H:i'),
                                'entrada_turno' => $entradaTurno->format('H:i'),
                                'salida_real' => $salidaReal->format('H:i'),
                                'salida_turno' => $salidaTurno->format('H:i')
                            ];
                        }

                        $hDiur = self::calcularHorasEnVentana($inicio, $fin, '06:00', '21:59');
                        $hNoct = self::calcularHorasEnVentana($inicio, $fin, '22:00', '05:59');

                        $diurnas += round($hDiur, 2);
                        $nocturnas += round($hNoct, 2);

                        break;
                    }
                }

                if (!$encontrado) {
                    $entradaReal = new DateTime($j['entrada']);
                    $salidaReal = new DateTime($j['salida']);

                    $hDiur = self::calcularHorasEnVentana($entradaReal, $salidaReal, '06:00', '21:59');
                    $hNoct = self::calcularHorasEnVentana($entradaReal, $salidaReal, '22:00', '05:59');

                    $diurnas += round($hDiur, 2);
                    $nocturnas += round($hNoct, 2);
                }
            }

            $rows[] = [
                'vigilador' => $nombre,
                'diurnas' => $diurnas,
                'nocturnas' => $nocturnas,
                'guardias_diurnas' => $guardiasPasivasDiurnas,
                'guardias_nocturnas' => $guardiasPasivasNocturnas,
                'francos' => $francos,
                'jornadas' => count($jornadas) + $francos + $guardiasPasivasDiurnas + $guardiasPasivasNocturnas
            ];
        }

        $_SESSION['reporte_vigilador'] = $rows;
        header('Location: ?r=reporte_porVigilador');
        exit;
    }

    private static function emparejarMarcaciones($marcaciones)
    {
        $jornadas = [];
        $entradasPendientes = [];

        // Ordenar marcaciones cronológicamente
        usort($marcaciones, function ($a, $b) {
            return strcmp($a['fecha_hora'], $b['fecha_hora']);
        });

        foreach ($marcaciones as $m) {
            $clave = $m['vigilador_id'] . '-' . $m['objetivo_id'];

            if ($m['tipo_evento'] == 'entrada') {
                // Si ya existe una entrada para esta clave, forzar cierre
                if (isset($entradasPendientes[$clave])) {
                    $jornadas[] = [
                        'entrada' => $entradasPendientes[$clave]['fecha_hora'],
                        'salida' => $m['fecha_hora'],
                        'objetivo_id' => $m['objetivo_id']
                    ];
                }
                $entradasPendientes[$clave] = $m;
            } elseif ($m['tipo_evento'] == 'salida' && isset($entradasPendientes[$clave])) {
                $entrada = $entradasPendientes[$clave];

                // Solo crear jornada si la salida es posterior a la entrada
                if (strtotime($m['fecha_hora']) > strtotime($entrada['fecha_hora'])) {
                    $jornadas[] = [
                        'entrada' => $entrada['fecha_hora'],
                        'salida' => $m['fecha_hora'],
                        'objetivo_id' => $m['objetivo_id']
                    ];
                }
                unset($entradasPendientes[$clave]);
            }
        }

        // Manejar entradas sin salida
        foreach ($entradasPendientes as $clave => $entrada) {
            $jornadas[] = [
                'entrada' => $entrada['fecha_hora'],
                'salida' => date('Y-m-d H:i:s'),
                'objetivo_id' => $entrada['objetivo_id'],
                'incompleta' => true
            ];
        }

        return $jornadas;
    }

    static public function vistaCrearCronograma()
    {
        Auth::check('cronogramas', 'vistaCrearCronograma');
        include __DIR__ . '/../vistas/paginas/cronogramas/crear_cronograma.php';
        return;
    }
    static public function vistaListadoCronogramas()
    {
        Auth::check('cronogramas', 'vistaListadoCronogramas');
        include __DIR__ . '/../vistas/paginas/cronogramas/listado_cronogramas.php';
        return;
    }
    static public function vistaListadoCronogramaPorVigilador()
    {
        Auth::check('cronogramas', 'vistaListadoCronogramaPorVigilador');
        $rol = $_SESSION['rol'] ?? '';
        $categoria = $_SESSION['categoria'] ?? '';
        $esRestringido = in_array($rol, ['Vigilador', 'Referente'], true)
            || in_array($categoria, ['operativo', 'referente'], true);

        if ($esRestringido) {
            $filtros = $_SESSION['filtros_vigilador'] ?? [];
            $miId = (int)($_SESSION['idUsuario'] ?? 0);
            if (($filtros['vigilador'] ?? 0) !== $miId) {
                unset($_SESSION['filtros_vigilador'], $_SESSION['turnos_porVigilador'], $_SESSION['dias_rango'], $_SESSION['feriados_rango']);
            }
        }

        include __DIR__ . '/../vistas/paginas/cronogramas/listado_porVigilador.php';
        return;
    }
    static public function vistaJornadasPorObjetivo()
    {
        Auth::check('cronogramas', 'vistaJornadasPorObjetivo');
        include __DIR__ . '/../vistas/paginas/cronogramas/resumen_diario_jornadas.php';
        return;
    }
    static public function vistaHorasPorVigilador()
    {
        Auth::check('cronogramas', 'vistaHorasPorVigilador');
        include __DIR__ . '/../vistas/paginas/cronogramas/reporte_horas_vigilador.php';
        return;
    }
    public static function vistaReporteHorasPorObjetivo()
    {
        Auth::check('cronogramas', 'vistaReporteHorasPorObjetivo');
        // Carga lista de objetivos
        $db = new Conexion();
        $objetivos = $db->consultas("SELECT idObjetivo, nombre FROM objetivos WHERE activo=1 ORDER BY nombre");
        // Recupera el reporte generado por POST (si existe)
        $reporte = $_SESSION['resumen_periodo'] ?? null;
        include __DIR__ . '/../vistas/paginas/cronogramas/reporte_horas_por_objetivo.php';
    }
}
