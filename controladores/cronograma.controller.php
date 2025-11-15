<?php

require_once('modelos/cronograma.modelo.php');
require_once 'modelos/turnos.modelo.php';
class ControladorCronograma
{
    public static function ctrGuardarCronograma()
    {

        //Auth::check('cronogramas', 'ctrGuardarCronograma');
        Auth::check('cronogramas', 'vistaCrearCronograma');
       
        if (!isset($_POST['guardar_cronograma'])) return;

        $objetivoId = intval($_POST['objetivo'] ?? 0);
        $mes        = $_POST['mes'] ?? '';

        if (!$objetivoId || !$mes) {
            ToastifyController::error("Faltan datos de objetivo o mes.");
            return;
        }

        $db = Conexion::conectar();
        try {
            if (!$db) {
                throw new Exception("No hay conexión activa a la base de datos.");
            }
            $_SESSION['cronograma_post'] = $_POST; // Guardamos los datos al inicio del try

            $db->beginTransaction();
            // Eliminamos datos previos del mismo mes y objetivo
            if (!ModeloTurnos::mdlEliminarTurnosPorMes($objetivoId, $mes)) {
                throw new Exception("No se pudieron eliminar los datos existentes del cronograma.");
            }

            $guardiasPorDia = [];   // Para validar mínimo 3 guardias por día
            $horasPorUsuario = [];  // Para alertar por exceso o defecto

            $turnosProcesados = [];

            foreach (['vigilador', 'referente'] as $rol) {
                if (!isset($_POST[$rol])) continue;

                // ===================== VIGILADORES (1 fila por usuario) =====================
                if (isset($_POST['vigilador']) && is_array($_POST['vigilador'])) {
                    foreach ($_POST['vigilador'] as $usuarioId => $dias) {
                        $usuarioId = intval($usuarioId);
                        if (!$usuarioId) continue;

                        foreach ($dias as $dia => $codigo) {
                            if ($dia === 'usuario') continue;
                            $codigo = trim((string)$codigo);
                            if ($codigo === '') continue;

                            $fecha = $mes . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);

                            // Para validaciones
                            if (in_array($codigo, ['D', 'N'])) {
                                $guardiasPorDia[$fecha][] = $codigo;
                                $horasPorUsuario[$usuarioId] = ($horasPorUsuario[$usuarioId] ?? 0) + 12;
                            }

                            $tipo = self::esLicencia($codigo) ? 'Licencia' : 'Normal';

                            $turnosProcesados[] = [
                                'usuario_id'   => $usuarioId,
                                'objetivo_id'  => $objetivoId,
                                'fecha'        => $fecha,
                                'rol'          => 'Vigilador',
                                'tipo_turno'   => $tipo,
                                'codigo_turno' => $codigo
                            ];
                        }
                    }
                }

                // ===================== REFERENTES (1 fila por usuario) =====================
                if (isset($_POST['referente']) && is_array($_POST['referente'])) {
                    foreach ($_POST['referente'] as $usuarioId => $dias) {
                        $usuarioId = intval($usuarioId);
                        if (!$usuarioId) continue;

                        foreach ($dias as $dia => $codigo) {
                            if ($dia === 'usuario') continue;
                            $codigo = trim((string)$codigo);
                            if ($codigo === '') continue;

                            $fecha = $mes . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);
                            $tipo = self::esLicencia($codigo) ? 'Licencia' : 'Normal';

                            $turnosProcesados[] = [
                                'usuario_id'   => $usuarioId,
                                'objetivo_id'  => $objetivoId,
                                'fecha'        => $fecha,
                                'rol'          => 'Referente',
                                'tipo_turno'   => $tipo,
                                'codigo_turno' => $codigo
                            ];
                        }
                    }
                }
            }

            // Validación 1: mínimo 3 tipos de guardia por día (D, N, Licencias)
            foreach ($guardiasPorDia as $fecha => $guardias) {
                // Eliminamos duplicados para contar solo los tipos únicos de guardia
                $tiposPresentes = array_unique($guardias);

                // Si hay menos de 3 tipos (por ejemplo, solo D y N), no cumple
                if (count($tiposPresentes) < 3) {
                    ToastifyController::warning("Advertencia: el día $fecha tiene menos de 3 tipos de guardias.");
                }
            }

            // Validación 2: horas mensuales por usuario
            foreach ($horasPorUsuario as $uid => $totalHs) {
                if ($totalHs < 200 || $totalHs > 240) {
                    $msg = $totalHs < 200 ? "menos de 200" : "más de 240";
                    // Buscamos nombre del usuario
                    $stmt = $db->prepare("SELECT apellido, nombre FROM usuarios WHERE idUsuario = ?");
                    $stmt->execute([$uid]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                    $nombreCompleto = $usuario ? "{$usuario['apellido']}, {$usuario['nombre']}" : "ID $uid";
                    ToastifyController::warning("El usuario $nombreCompleto tiene $msg hs de servicio ($totalHs hs).");
                }
            }

            // Guardar turnos
            foreach ($turnosProcesados as $t) {

                $respuesta = ModeloTurnos::mdlGuardarTurno('turnos', $t);
                if ($respuesta !== 'ok') {
                    throw new Exception("Error al guardar turno del usuario " . $t['usuario_id'] . " " . $respuesta);
                }
            }
            // Guardamos las horas en sesión para mostrarlas visualmente en la vista
            $_SESSION['horas_usuario'] = $horasPorUsuario;
            $db->commit();

            ToastifyController::success("Cronograma guardado correctamente.");
            header("Location: " . $_SERVER['REQUEST_URI']);
            unset($_SESSION['cronograma_post']); // Limpiamos la sesión al finalizar correctamente
            unset($_SESSION['horas_usuario']);


            exit;
        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            ToastifyController::error("Error al guardar: " . $e->getMessage());

            // Reintentamos mostrando la vista ya poblada
            self::vistaCrearCronograma();
            return;
        }
    }
    private static function esLicencia(string $codigo): bool
    {
        // Tratamos GP/D y GP/N como licencias “laborables” para KPI,
        // pero acá el tipo se guarda igual como 'Licencia'
        $licencias = ['F', 'GP/D', 'GP/N', 'E', 'P', 'L', 'S'];
        return in_array(strtoupper($codigo), $licencias, true);
    }

    /*Funcion para mantener la escala de 4x2 en el cronograma al cargar un nuevo mes*/
    public static function generarSimulacionVacia(int $objetivoId, string $mes): array
    {
        $db = new Conexion;

        // Vigiladores y referentes vinculados al objetivo
        $vigiladores = $db->consultas(" SELECT u.idUsuario
                                    FROM usuarios u
                                    INNER JOIN roles r ON u.rol_id = r.id
                                    INNER JOIN objetivo_vigiladores ov ON ov.vigilador_id = u.idUsuario
                                    WHERE ov.objetivo_id = $objetivoId
                                    AND u.activo = 1
                                    AND r.categoria = 'operativo'
                                ");

        $referentes = $db->consultas(" SELECT u.idUsuario
                                    FROM usuarios u
                                    INNER JOIN roles r ON u.rol_id = r.id
                                    INNER JOIN objetivo_referentes orf ON orf.referente_id = u.idUsuario
                                    WHERE orf.objetivo_id = $objetivoId
                                    AND u.activo = 1
                                    AND r.categoria = 'referente'
                                ");


        $post = [
            'objetivo'  => $objetivoId,
            'mes'       => $mes,
            'vigilador' => [],
            'referente' => []
        ];

        // Patrón base 4x2
        $patternBase = ['D', 'D', 'N', 'N', 'F', 'F'];

        // Rango del mes
        $dt = DateTime::createFromFormat('Y-m', $mes);
        if (!$dt) return $post;
        $daysInMonth = (int)date('t', strtotime("$mes-01"));

        // Mes anterior (solo para elegir con qué bloque empezar)
        $dtPrev = clone $dt;
        $dtPrev->modify('-1 month');
        $mesAnterior = $dtPrev->format('Y-m');
        $iniPrev = $mesAnterior . '-01';
        $finPrev = $mesAnterior . '-' . date('t', strtotime($iniPrev));

        // Normalizador D/N/F
        $norm = function (string $c): string {
            $c = strtoupper(trim($c));
            if ($c === 'D' || preg_match('/^D\//', $c) || in_array($c, ['6H', '7H', '8H', '9H', '9RF', '9HEX', '13H', '14H', 'BE'], true)) return 'D';
            if ($c === 'N' || $c === 'N15' || preg_match('/^N\//', $c)) return 'N';
            if (in_array($c, ['F', 'GP/D', 'GP/N', 'E', 'P', 'L', 'S'], true)) return 'F';
            if (in_array($c, ['SALA', 'MIC', 'F/JUS', 'NOTT', 'GUE', 'PER', 'PAL', 'BOS', 'OFI'], true)) return 'F';
            return 'F';
        };

        // Rotar patrón para que el PRIMER par (2 días) sea el bloque $start
        $rotarDesde = function (array $base, string $start) {
            // base = [D,D,N,N,F,F] → D:0, N:2, F:4
            $map = ['D' => 0, 'N' => 2, 'F' => 4];
            $offset = $map[$start] ?? 0;
            return array_merge(array_slice($base, $offset), array_slice($base, 0, $offset));
        };

        // Último código del mes anterior por usuario+objetivo
        $ultimoCodigo = function (int $usuarioId) use ($objetivoId, $iniPrev, $finPrev) {
            $sql = "SELECT codigo_turno FROM turnos
                WHERE objetivo_id = :obj AND usuario_id = :uid
                  AND fecha BETWEEN :ini AND :fin
                ORDER BY fecha DESC LIMIT 1";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute([':obj' => $objetivoId, ':uid' => $usuarioId, ':ini' => $iniPrev, ':fin' => $finPrev]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['codigo_turno'] ?? null;
        };

        // ===== Vigiladores: 4x2 puro =====
        foreach ($vigiladores as $v) {
            $uid = (int)$v['idUsuario'];
            $post['vigilador'][$uid]['usuario'] = $uid;

            $start  = $norm($ultimoCodigo($uid) ?? 'D');   // si no hay, arranca en D
            $patron = $rotarDesde($patternBase, $start);

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $post['vigilador'][$uid][$d] = $patron[($d - 1) % 6];
            }
        }

        // ===== Referentes: 4x2 puro =====
        foreach ($referentes as $r) {
            $uid = (int)$r['idUsuario'];
            $post['referente'][$uid]['usuario'] = $uid;

            $start  = $norm($ultimoCodigo($uid) ?? 'D');
            $patron = $rotarDesde($patternBase, $start);

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $post['referente'][$uid][$d] = $patron[($d - 1) % 6];
            }
        }

        return $post;
    }

    /*Funcion para precargar cronograma del mes anterior */
    /*MOdelo: turnos */
    public static function precargarCronogramaSiExiste($objetivoId, $mes)
    {
        // 1) Mes actual
        $actual = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $mes);
        if ($actual && count($actual)) {
            $post = self::armarPostSimuladoDesdeTurnos($actual, $objetivoId, $mes);
            $_SESSION['cronograma_post'] = $post; // ✅ mantener consistencia
            return [
                'origen'       => 'actual',
                'turnos'       => $actual,
                'postSimulado' => $post,
                'mesAnterior'  => null
            ];
        }

        // 2) Mes anterior
        $dt = DateTime::createFromFormat('Y-m', $mes);
        if (!$dt) {
            $vacio = self::generarSimulacionVacia($objetivoId, $mes);
            $_SESSION['cronograma_post'] = $vacio;
            return ['origen' => 'ninguno', 'turnos' => [], 'postSimulado' => $vacio];
        }

        $dt->modify('-1 month');
        $mesAnterior = $dt->format('Y-m');

        $anterior = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $mesAnterior);
        if ($anterior && count($anterior)) {
            // 👉 Continuidad 4×2 basada en el último día del mes anterior
            $postContinuado = self::continuar4x2DesdeTurnosAnteriores($anterior, $objetivoId, $mes);
            $_SESSION['cronograma_post'] = $postContinuado;

            return [
                'origen'       => 'anterior',
                'turnos'       => $anterior,
                'postSimulado' => $postContinuado,
                'mesAnterior'  => $dt->format('F')
            ];
        }

        // 3) Vacío
        $vacio = self::generarSimulacionVacia($objetivoId, $mes);
        $_SESSION['cronograma_post'] = $vacio;
        return ['origen' => 'vacio', 'turnos' => [], 'postSimulado' => $vacio, 'mesAnterior' => null];
    }


    /*Esta funcion genera la escala 4x2 respetando la correlación del mes anterior */
    private static function continuar4x2DesdeTurnosAnteriores(array $turnosPrev, int $objetivoId, string $mes): array
    {
        $post = [
            'objetivo'  => $objetivoId,
            'mes'       => $mes,
            'vigilador' => [],
            'referente' => []
        ];

        $norm = function (string $c): string {
            $c = strtoupper(trim($c));
            if ($c === 'D' || preg_match('/^D\//', $c) || in_array($c, ['6H', '7H', '8H', '9H', '9RF', '9HEX', '13H', '14H', 'BE'], true)) return 'D';
            if ($c === 'N' || $c === 'N15' || preg_match('/^N\//', $c)) return 'N';
            if (in_array($c, ['F', 'GP/D', 'GP/N', 'E', 'P', 'L', 'S'], true)) return 'F';
            if (in_array($c, ['SALA', 'MIC', 'F/JUS', 'NOTT', 'GUE', 'PER', 'PAL', 'BOS', 'OFI'], true)) return 'F';
            return 'F';
        };

        $daysInMonth = (int)date('t', strtotime("$mes-01"));

        // Agrupar historial por vigilador
        $porUsuario = [];
        foreach ($turnosPrev as $t) {
            if (strtolower($t['rol']) !== 'vigilador') continue;
            $uid  = (int)$t['usuario_id'];
            $fec  = $t['fecha'];
            $code = $norm($t['codigo_turno'] ?? '');
            $porUsuario[$uid][] = ['fecha' => $fec, 'norm' => $code];
        }
        foreach ($porUsuario as &$arr) {
            usort($arr, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));
        }

        $order = ['D', 'N', 'F'];
        $nextBlock = function (string $c) use ($order) {
            $i = array_search($c, $order, true);
            return $order[($i === false ? 0 : ($i + 1) % 3)];
        };

        // Vigiladores vinculados al objetivo
        $db = new Conexion;
        $vigiladores = $db->consultas("SELECT u.idUsuario
                                        FROM usuarios u
                                        INNER JOIN roles r ON u.rol_id = r.id
                                        INNER JOIN objetivo_vigiladores ov ON ov.vigilador_id = u.idUsuario
                                        WHERE ov.objetivo_id = $objetivoId
                                        AND u.activo = 1
                                        AND r.categoria = 'operativo'
                                    ");

        $uidsV = array_map(fn($r) => (int)$r['idUsuario'], $vigiladores);

        foreach ($uidsV as $uid) {
            $post['vigilador'][$uid]['usuario'] = $uid;

            $hist = $porUsuario[$uid] ?? [];
            if (!empty($hist)) {
                $last = end($hist);
                $c_last = $last['norm'];

                // ✅ racha de cola solo con días consecutivos
                $racha = 1;
                $lastDate = DateTime::createFromFormat('Y-m-d', $last['fecha']);
                for ($i = count($hist) - 2; $i >= 0 && $racha < 2; $i--) {
                    if ($hist[$i]['norm'] !== $c_last) break;

                    $currDate = DateTime::createFromFormat('Y-m-d', $hist[$i]['fecha']);
                    $prevOfLast = clone $lastDate;
                    $prevOfLast->modify('-1 day');

                    if ($currDate->format('Y-m-d') !== $prevOfLast->format('Y-m-d')) break;

                    $racha++;
                    $lastDate = $currDate;
                }

                // si racha==1 → completar par con c_last; si racha==2 → siguiente bloque
                $bloque = ($racha === 1) ? $c_last : $nextBlock($c_last);
                $posPar = ($racha === 1) ? 1 : 0;
                $diasRest = 2 - $posPar;
            } else {
                // sin historial: arrancar en D y completar par normalmente
                $bloque = 'D';
                $diasRest = 2;
            }


            for ($d = 1; $d <= $daysInMonth; $d++) {
                $post['vigilador'][$uid][$d] = $bloque;
                if (--$diasRest === 0) {
                    $bloque = $nextBlock($bloque);
                    $diasRest = 2;
                }
            }
        }

        // Referentes: copiar tal cual el mismo día si existía en el mes anterior
        $referentes = $db->consultas("SELECT u.idUsuario
                                    FROM usuarios u
                                    INNER JOIN roles r ON u.rol_id = r.id
                                    INNER JOIN objetivo_referentes orf ON orf.referente_id = u.idUsuario
                                    WHERE orf.objetivo_id = $objetivoId
                                    AND u.activo = 1
                                    AND r.categoria = 'referente'
                                ");

        $uidsR = array_map(fn($r) => (int)$r['idUsuario'], $referentes);

        // map día => código del mes anterior
        $mapPrevR = [];
        foreach ($turnosPrev as $t) {
            if (strtolower($t['rol']) !== 'referente') continue;
            $uid = (int)$t['usuario_id'];
            $dia = (int)substr($t['fecha'], 8, 2);
            $mapPrevR[$uid][$dia] = $t['codigo_turno'];
        }

        // ===================== REFERENTES con continuidad 4×2 =====================

        // 1) Armar historial normalizado por referente (solo mes anterior)
        $porReferente = []; // uid => [ ['fecha'=>'YYYY-MM-DD','norm'=>'D|N|F'], ... asc ]
        foreach ($turnosPrev as $t) {
            if (strtolower($t['rol']) !== 'referente') continue;
            $uid  = (int)$t['usuario_id'];
            $fec  = $t['fecha'];
            $code = $norm($t['codigo_turno'] ?? '');
            $porReferente[$uid][] = ['fecha' => $fec, 'norm' => $code];
        }
        foreach ($porReferente as &$arrR) {
            usort($arrR, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));
        }

        // 2) Traer referentes vinculados al objetivo
        $referentes = $db->consultas("SELECT u.idUsuario
                                    FROM usuarios u
                                    INNER JOIN roles r ON u.rol_id = r.id
                                    INNER JOIN objetivo_referentes orf ON orf.referente_id = u.idUsuario
                                    WHERE orf.objetivo_id = $objetivoId
                                    AND u.activo = 1
                                    AND r.categoria = 'referente'
                                ");

        $uidsR = array_map(fn($r) => (int)$r['idUsuario'], $referentes);

        // 3) Generar mes con la continuidad 4×2 (D,D → N,N → F,F → ...)
        foreach ($uidsR as $uid) {
            $post['referente'][$uid]['usuario'] = $uid;

            $hist = $porReferente[$uid] ?? [];
            if (!empty($hist)) {
                $last = end($hist);
                $c_last = $last['norm'];

                // racha final del mismo código SOLO si los días son consecutivos (máx 2)
                $racha = 1;
                $lastDate = DateTime::createFromFormat('Y-m-d', $last['fecha']);
                for ($i = count($hist) - 2; $i >= 0 && $racha < 2; $i--) {
                    if ($hist[$i]['norm'] !== $c_last) break;

                    $currDate = DateTime::createFromFormat('Y-m-d', $hist[$i]['fecha']);
                    $prevOfLast = clone $lastDate;
                    $prevOfLast->modify('-1 day');

                    if ($currDate->format('Y-m-d') !== $prevOfLast->format('Y-m-d')) break;

                    $racha++;
                    $lastDate = $currDate;
                }

                // racha==1 → completar par con c_last; racha==2 → pasar al siguiente bloque
                $bloque  = ($racha === 1) ? $c_last : $nextBlock($c_last);
                $posPar  = ($racha === 1) ? 1 : 0;
                $diasRest = 2 - $posPar;
            } else {
                // sin historial → iniciar en D
                $bloque  = 'D';
                $diasRest = 2;
            }

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $post['referente'][$uid][$d] = $bloque;
                if (--$diasRest === 0) {
                    $bloque   = $nextBlock($bloque);
                    $diasRest = 2;
                }
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
                $sqlTurno = "SELECT codigo_turno, fecha
                         FROM turnos
                         WHERE usuario_id = ? AND objetivo_id = ?
                           AND fecha BETWEEN DATE(?) AND DATE_ADD(?, INTERVAL 1 DAY)
                         ORDER BY ABS(DATEDIFF(fecha, ?))
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
                $mapaTurno = ['D' => 1, 'N' => 2, 'I' => 3];
                $numeroTurno = $mapaTurno[strtoupper($turno['codigo_turno'])] ?? 1;

                $sqlHorario = "SELECT hora_entrada
                           FROM puestos_turnos
                           WHERE puesto_id = ? AND numero_turno = ?
                           LIMIT 1";
                $horaEntradaTurno = $db->consultas($sqlHorario, [$turno['puesto_id'], $numeroTurno])[0]['hora_entrada'] ?? null;

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
        Auth::check('cronogramas', 'calcularHorasEnVentana');

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
        $usuarios = $db->consultas("SELECT idUsuario, CONCAT(apellido, ', ', nombre) AS vigilador FROM usuarios WHERE rol = 'Vigilador'");
        $horariosTurnos = $db->consultas("SELECT  numero_turno, hora_entrada, hora_salida FROM puestos_turnos");

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
                "SELECT fecha, codigo_turno, objetivo_id
             FROM turnos 
             WHERE usuario_id = :id 
               AND fecha BETWEEN :desde AND :hasta",
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
        //include __DIR__ . '/../vistas/paginas/cronogramas/listado_cronogramas.php';
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
        $objetivos = $db->consultas("SELECT idObjetivo, nombre FROM objetivos ORDER BY nombre");
        // Recupera el reporte generado por POST (si existe)
        $reporte = $_SESSION['resumen_periodo'] ?? null;
        include __DIR__ . '/../vistas/paginas/cronogramas/reporte_horas_por_objetivo.php';
    }
}
