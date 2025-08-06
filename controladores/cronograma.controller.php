<?php

require_once('modelos/cronograma.modelo.php');
require_once 'modelos/turnos.modelo.php';
class ControladorCronograma
{
    public static function ctrGuardarCronograma()
    {

        Auth::check('cronogramas', 'ctrGuardarCronograma');

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

                // ===================== VIGILADORES =====================
                if ($rol === 'vigilador') {
                    /**
                     * Estructura esperada:
                     * $_POST['vigilador'][idPuesto][Diurno|Nocturno|Licencias][día] = código_turno
                     * También incluye: [usuario] dentro de uno de esos bloques
                     */
                    foreach ($_POST[$rol] as $puestoId => $tiposTurno) {
                        // Buscamos el usuario_id en cualquiera de los tipos de turno
                        $usuarioId = null;
                        foreach ($tiposTurno as $datosTurno) {
                            if (isset($datosTurno['usuario']) && is_numeric($datosTurno['usuario'])) {
                                $usuarioId = intval($datosTurno['usuario']);
                                break;
                            }
                        }

                        if (!$usuarioId) continue; // No procesamos si no hay usuario seleccionado

                        foreach ($tiposTurno as $tipoTurno => $datosTurno) {
                            foreach ($datosTurno as $dia => $codigo) {
                                if ($dia === 'usuario' || trim($codigo) === '') continue;

                                $fecha = $mes . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);

                                // Guardias válidas para validación (D y N)
                                if (in_array($codigo, ['D', 'N'])) {
                                    $guardiasPorDia[$fecha][] = $codigo;
                                }

                                // Acumulamos horas si es D o N
                                $hs = in_array($codigo, ['D', 'N']) ? 12 : 0;
                                $horasPorUsuario[$usuarioId] = ($horasPorUsuario[$usuarioId] ?? 0) + $hs;

                                // Determinamos tipo de turno
                                $tipo = ($tipoTurno === 'Licencias') ? 'Licencia' : 'Normal';

                                $turnosProcesados[] = [
                                    'usuario_id'   => $usuarioId,
                                    'puesto_id'    => $puestoId,
                                    'objetivo_id'  => $objetivoId,
                                    'fecha'        => $fecha,
                                    'rol'          => ucfirst($rol),
                                    'tipo_turno'   => $tipo,
                                    'codigo_turno' => $codigo
                                ];
                            }
                        }
                    }
                }

                // ===================== REFERENTES =====================
                elseif ($rol === 'referente') {
                    /**
                     * Estructura esperada:
                     * $_POST['referente'][Diurno|Nocturno|Licencias][día] = código_turno
                     * El usuario puede estar en cualquiera de esos bloques, según cuál se tocó primero.
                     */

                    // Intentamos detectar el usuario desde cualquier bloque disponible
                    $usuarioId = 0;
                    foreach (['Diurno', 'Nocturno', 'Licencias'] as $tipo) {
                        if (isset($_POST['referente'][$tipo]['usuario']) && is_numeric($_POST['referente'][$tipo]['usuario'])) {
                            $usuarioId = intval($_POST['referente'][$tipo]['usuario']);
                            break;
                        }
                    }


                    if (!$usuarioId) continue; // Si no encontramos usuario en ningún bloque, salteamos

                    foreach ($_POST[$rol] as $tipoTurno => $datosTurno) {

                        foreach ($datosTurno as $dia => $codigo) {
                            if ($dia === 'usuario' || trim($codigo) === '') continue;

                            $fecha = $mes . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);
                            $tipo = ($tipoTurno === 'Licencias') ? 'Licencia' : 'Normal';

                            $turnosProcesados[] = [
                                'usuario_id'   => $usuarioId,
                                'puesto_id'    => null, // Los referentes no tienen puesto
                                'objetivo_id'  => $objetivoId,
                                'fecha'        => $fecha,
                                'rol'          => ucfirst($rol),
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

    /*Funcion para precargar cronograma del mes anterior */
    /*MOdelo: turnos */
    public static function precargarCronogramaAnterior($objetivoId, $mes)
    {
        // Obtener mes anterior en formato YYYY-MM
        $dt = DateTime::createFromFormat('Y-m', $mes);
        if (!$dt) return [];
        $dt->modify('-1 month');
        $mesAnterior = $dt->format('Y-m');

        $turnos = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $mesAnterior);
        if (!$turnos) return [];

        $postSimulado = [];

        foreach ($turnos as $t) {
            $dia = intval(substr($t['fecha'], 8, 2));
            $usuarioId = $t['usuario_id'];
            $puestoId = $t['puesto_id'] ?? '-';
            $rol = strtolower($t['rol']);
            $tipoTurno = ($t['tipo_turno'] === 'Licencia') ? 'Licencias' : ($t['codigo_turno'] === 'D' ? 'Diurno' : 'Nocturno');

            // Armar estructura simulando $_POST['vigilador'][...][...][...]
            if (!isset($postSimulado[$rol][$puestoId][$tipoTurno]['usuario'])) {
                $postSimulado[$rol][$puestoId][$tipoTurno]['usuario'] = $usuarioId;
            }

            $postSimulado[$rol][$puestoId][$tipoTurno][$dia] = $t['codigo_turno'];
        }

        return $postSimulado;
    }

    public static function precargarCronogramaSiExiste($objetivoId, $mes)
    {
        // 1. Buscar turnos del mes actual
        $actual = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $mes);
        if ($actual && count($actual)) {
            return ['origen' => 'actual', 'turnos' => $actual];
        }

        // 2. Si no hay datos del mes actual, buscamos el mes anterior
        $dt = DateTime::createFromFormat('Y-m', $mes);
        if (!$dt) return ['origen' => 'ninguno', 'turnos' => []];

        $dt->modify('-1 month');
        $mesAnterior = $dt->format('Y-m');

        $anterior = ModeloTurnos::mdlBuscarTurnosPorMes($objetivoId, $mesAnterior);
        if ($anterior && count($anterior)) {
            return [
                'origen' => 'anterior',
                'turnos' => $anterior,
                'mesAnterior' => $dt->format('F')
            ];
        }

        // 3. Si tampoco hay del mes anterior, generamos cronograma vacío
        $datosPrevios = self::generarSimulacionVacia($objetivoId, $mes);
        $_SESSION['cronograma_post'] = $datosPrevios;

        return ['origen' => 'vacio', 'turnos' => []];
    }

    public static function generarSimulacionVacia($objetivoId, $mes)
    {
        $vigiladores = ModeloObjetivos::mdlObtenerVigiladoresParaCronograma($objetivoId); // trae idUsuario
        $referentes  = ModeloObjetivos::mdlObtenerReferentesParaCronograma($objetivoId);  // trae solo IDs

        $post = [];

        // Detectamos cuántos días tiene el mes seleccionado
        $anioMes = explode('-', $mes);
        $anio = intval($anioMes[0]);
        $mesNum = intval($anioMes[1]);
        $cantidadDias = cal_days_in_month(CAL_GREGORIAN, $mesNum, $anio);

        // ===================== VIGILADORES =====================
        foreach ($vigiladores as $index => $v) {
            $puestoId = 'ficticio_' . $index;
            foreach (['Diurno', 'Nocturno', 'Licencias'] as $tipo) {
                $post['vigilador'][$puestoId][$tipo]['usuario'] = $v['idUsuario'];
                for ($dia = 1; $dia <= $cantidadDias; $dia++) {
                    $post['vigilador'][$puestoId][$tipo][$dia] = '';
                }
            }
        }

        // ===================== REFERENTES =====================
        foreach ($referentes as $r) {
            foreach (['Diurno', 'Nocturno', 'Licencias'] as $tipo) {
                $post['referente'][$tipo]['usuario'] = $r['idUsuario']; // ← valor escalar
                for ($dia = 1; $dia <= $cantidadDias; $dia++) {
                    $post['referente'][$tipo][$dia] = '';
                }
            }
        }


        return $post;
    }

    /*Funcion para buscar por resumen diario de jornadas trabajadas*/
    static public function crtBuscarResumenDiario()
    {
        Auth::check('cronogramas', 'crtBuscarResumenDiario');
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
        Auth::check('cronogramas', 'crtBuscarResumenHoras');

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
                $sqlTurno = "SELECT puesto_id, codigo_turno, fecha
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
        Auth::check('cronograma', 'crtBuscarResumenHorasPorVigilador');

        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';

        if (!$desde || !$hasta) {
            $_SESSION['error_message'] = 'Debes indicar un rango de fechas válido';
            header('Location: ?r=reporte_porVigilador');
            exit;
        }

        $db = new Conexion;
        $usuarios = $db->consultas("SELECT idUsuario, CONCAT(apellido, ', ', nombre) AS vigilador FROM usuarios WHERE rol = 'Vigilador'");

        // Obtener todos los horarios de turnos
        $horariosTurnos = $db->consultas("SELECT puesto_id, numero_turno, hora_entrada, hora_salida FROM puestos_turnos");

        // Mapeo de códigos de turno a números
        $mapeoTurnos = [
            'D' => 1,
            'N' => 2,
            'I' => 3
        ];

        $rows = [];
        $_SESSION['diferencias_horarias'] = []; // Para registrar diferencias

        foreach ($usuarios as $usuario) {
            $id = $usuario['idUsuario'];
            $nombre = $usuario['vigilador'];

            // Obtener marcaciones del usuario
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

            // Obtener turnos asignados
            $turnosAsignados = $db->consultas(
                " SELECT fecha, codigo_turno, puesto_id, objetivo_id
                        FROM turnos 
                        WHERE usuario_id = :id 
                            AND fecha BETWEEN :desde AND :hasta",
                [
                    'id' => $id,
                    'desde' => $desde,
                    'hasta' => $hasta
                ]
            );

            // Combinar turnos con horarios
            $turnosCompletos = [];
            foreach ($turnosAsignados as $turno) {
                $codigo = trim(strtoupper($turno['codigo_turno']));
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

            // Emparejar marcaciones
            $jornadas = self::emparejarMarcaciones($marcaciones);

            $diurnas = 0;
            $nocturnas = 0;
            $guardiasPasivas = 0;
            $francos = 0;

            foreach ($jornadas as $j) {
                $fechaJ = substr($j['entrada'], 0, 10);
                $encontrado = false;

                foreach ($turnosCompletos as $turno) {
                    if ($turno['fecha'] === $fechaJ && $turno['objetivo_id'] == $j['objetivo_id']) {
                        $encontrado = true;

                        // Objetos DateTime
                        $entradaReal = new DateTime($j['entrada']);
                        $salidaReal = new DateTime($j['salida']);
                        $entradaTurno = new DateTime("$fechaJ {$turno['hora_entrada']}");
                        $salidaTurno = new DateTime("$fechaJ {$turno['hora_salida']}");

                        // Ajustar turno nocturno
                        if ($salidaTurno < $entradaTurno) {
                            $salidaTurno->modify('+1 day');
                        }

                        // Margen de tolerancia (15 minutos)
                        $margen = new DateInterval('PT15M');
                        $entradaMin = (clone $entradaTurno)->sub($margen);
                        $salidaMax = (clone $salidaTurno)->add($margen);

                        // Recortar marcaciones al turno con margen
                        $inicio = max($entradaReal, $entradaMin);
                        $fin = min($salidaReal, $salidaMax);

                        if ($inicio >= $fin) {
                            continue; // Jornada inválida
                        }

                        // Registrar diferencias significativas (>15 min)
                        if ($entradaReal < $entradaMin || $salidaReal > $salidaMax) {
                            $diferenciaEntrada = $entradaReal->diff($entradaTurno);
                            $diferenciaSalida = $salidaReal->diff($salidaTurno);

                            if ($diferenciaEntrada->i > 15 || $diferenciaSalida->i > 15) {
                                $_SESSION['diferencias_horarias'][] = [
                                    'vigilador' => $nombre,
                                    'fecha' => $fechaJ,
                                    'entrada_real' => $entradaReal->format('H:i'),
                                    'entrada_turno' => $entradaTurno->format('H:i'),
                                    'salida_real' => $salidaReal->format('H:i'),
                                    'salida_turno' => $salidaTurno->format('H:i')
                                ];
                            }
                        }

                        // CALCULAR HORAS USANDO EL MÉTODO QUE FUNCIONA
                        $hDiur = self::calcularHorasEnVentana($inicio, $fin, '06:00', '21:59');
                        $hNoct = self::calcularHorasEnVentana($inicio, $fin, '22:00', '05:59');

                        $diurnas += round($hDiur, 2);
                        $nocturnas += round($hNoct, 2);

                        break;
                    }
                }

                if (!$encontrado) {
                    // Registrar jornada sin turno asociado
                    $_SESSION['advertencias'][] = "Vigilador $nombre tiene jornada sin turno asignado el $fechaJ";

                    // Si no hay turno, calcular horas directamente
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
                'guardias_pasivas' => round($guardiasPasivas, 2),
                'francos' => $francos,
                'jornadas' => count($jornadas)
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
        Auth::check('cronogramas', 'crtBuscarResumenHoras');
        // Carga lista de objetivos
        $db = new Conexion();
        $objetivos = $db->consultas("SELECT idObjetivo, nombre FROM objetivos ORDER BY nombre");
        // Recupera el reporte generado por POST (si existe)
        $reporte = $_SESSION['resumen_periodo'] ?? null;
        include __DIR__ . '/../vistas/paginas/cronogramas/reporte_horas_por_objetivo.php';
    }
}
