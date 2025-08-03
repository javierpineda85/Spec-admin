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
                'turnos',
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
        $sql = "SELECT idObjetivo, nombre FROM objetivos ORDER BY nombre";
        $objetivos = $db->consultas($sql);

        $reporte = [];

        foreach ($objetivos as $o) {
            $sql = "SELECT 
                    m1.vigilador_id,
                    m1.objetivo_id,
                    m1.fecha_hora AS entrada,
                    m2.fecha_hora AS salida
                FROM marcaciones_servicio m1
                JOIN marcaciones_servicio m2
                    ON m1.vigilador_id = m2.vigilador_id
                    AND m1.objetivo_id = m2.objetivo_id
                    AND m1.tipo_evento = 'entrada'
                    AND m2.tipo_evento = 'salida'
                    AND m2.fecha_hora > m1.fecha_hora
                WHERE m1.objetivo_id = ?
                    AND DATE(m1.fecha_hora) BETWEEN ? AND ?
                GROUP BY m1.idMarcacion
                ORDER BY m1.fecha_hora";

            $params = [$o['idObjetivo'], $desde, $hasta];
            $jornadas = $db->consultas($sql, $params);

            $sumDiur = 0.0;
            $sumNoct = 0.0;

            foreach ($jornadas as $j) {
                $start = new DateTime($j['entrada']);
                $end   = new DateTime($j['salida']);

                // Si la salida es menor, asumimos turno nocturno cruzando la medianoche
                if ($end <= $start) {
                    $end->modify('+1 day');
                }

                $hDiur = self::calcularHorasEnVentana($start, $end, '06:00:00', '21:00:00');
                $hTotal = ($end->getTimestamp() - $start->getTimestamp()) / 3600;
                $hNoct = $hTotal - $hDiur;

                $sumDiur += $hDiur;
                $sumNoct += $hNoct;
            }

            $reporte[] = [
                'nombre'    => $o['nombre'],
                'diurnas'   => round($sumDiur, 2),
                'nocturnas' => round($sumNoct, 2)
            ];
        }

        $_SESSION['resumen_periodo'] = $reporte;
        header('Location: ?r=reporte_porHoras');
        exit;
    }

    /**
     * Devuelve las horas (float) entre $start y $end que caen dentro de la ventana diaria [$horaDesde, $horaHasta].
     */
    static private function calcularHorasEnVentana(DateTime $start, DateTime $end, string $horaDesde, string $horaHasta): float
    {
        Auth::check('cronogramas', 'calcularHorasEnVentana');
        $segDiurnos = 0;
        $cursor = clone $start;

        while ($cursor < $end) {
            $fecha = $cursor->format('Y-m-d');
            $ventIni = new DateTime("$fecha $horaDesde");
            $ventFin = new DateTime("$fecha $horaHasta");

            // Calculamos solape entre [$cursor, $end] y ventana
            $solapeIni = $cursor > $ventIni ? $cursor : $ventIni;
            $solapeFin = $end < $ventFin    ? $end    : $ventFin;

            if ($solapeFin > $solapeIni) {
                $segDiurnos += $solapeFin->getTimestamp() - $solapeIni->getTimestamp();
            }

            // Avanzamos al siguiente día
            $cursor = (new DateTime("$fecha 23:59:59"))->modify('+1 second');
        }

        return $segDiurnos / 3600;
    }

    /* Calcula la cantidad de horas trabajadas por vigilador. 
    *Tiene en cuenta si ha trabajo una GP o FRANCO. VISTA: reporte_horas_vigilador 
    */
    static public function crtBuscarResumenHorasPorVigilador()
    {
        Auth::check('cronogramas', 'crtBuscarResumenHorasPorVigilador');
        $desde = $_POST['desde'] ?? null;
        $hasta = $_POST['hasta'] ?? null;
        if (!$desde || !$hasta) {
            ToastifyController::error('Por favor completa los dos campos de fecha.');
            header('Location: ?r=reporte_porVigilador');
            exit;
        }

        $db = new Conexion;
        $sql = "SELECT idUsuario, nombre, apellido
        FROM usuarios
        WHERE rol = 'Vigilador'
        ORDER BY apellido DESC";
        $vigiladores = $db->consultas($sql);

        $reporte = [];

        foreach ($vigiladores as $v) {

            // 1) Traemos pares entrada/salida del vigilador
            $sql = "SELECT 
                    m1.fecha_hora AS entrada,
                    m2.fecha_hora AS salida
                FROM marcaciones_servicio m1
                JOIN marcaciones_servicio m2
                    ON m1.vigilador_id = m2.vigilador_id
                    AND m1.objetivo_id = m2.objetivo_id
                    AND m1.tipo_evento = 'entrada'
                    AND m2.tipo_evento = 'salida'
                    AND m2.fecha_hora > m1.fecha_hora
                WHERE m1.vigilador_id = ?
                    AND DATE(m1.fecha_hora) BETWEEN ? AND ?
                GROUP BY m1.idMarcacion
                ORDER BY m1.fecha_hora";

            $params = [$v['idUsuario'], $desde, $hasta];
            $jornadas = $db->consultas($sql, $params);

            $sumDiur = 0.0;
            $sumNoct = 0.0;
            $fechasTrabajadas = [];

            foreach ($jornadas as $j) {
                // 2) Siempre contamos la fecha como jornada
                $fecha = substr($j['entrada'], 0, 10);
                $fechasTrabajadas[] = $fecha;

                // 3) Construimos DateTime de inicio y fin
                $start = new DateTime($j['entrada']);
                $end   = new DateTime($j['salida']);

                if ($end <= $start) {
                    $end->modify('+1 day');
                }

                // 4) Calculamos horas diurnas (entre 06:00 y 21:00) y restantes como nocturnas
                $hDiur   = self::calcularHorasEnVentana($start, $end, '06:00:00', '21:00:00');
                $hTotal  = ($end->getTimestamp() - $start->getTimestamp()) / 3600.0;
                $hNoct   = $hTotal - $hDiur;

                $sumDiur += $hDiur;
                $sumNoct += $hNoct;
            }

            // 5) Contamos fechas únicas para obtener cantidad de jornadas
            $jornadasCount = count(array_unique($fechasTrabajadas));

            // 6) Contamos guardias pasivas trabajadas
            $sqlPasivas = "SELECT COUNT(*) AS total
            FROM turnos t
            WHERE t.usuario_id = ?
              AND t.tipo_turno = 'Guardia Pasiva'
              AND t.fecha BETWEEN ? AND ?
              AND EXISTS (
                SELECT 1 FROM marcaciones_servicio m
                WHERE m.vigilador_id = t.usuario_id
                  AND DATE(m.fecha_hora) = t.fecha
                  AND m.tipo_evento = 'entrada'
              )";
            $countPasivas = $db->consultas($sqlPasivas, [$v['idUsuario'], $desde, $hasta])[0]['total'];

            // 7) Contamos francos trabajados
            $sqlFrancos = "SELECT COUNT(*) AS total
            FROM turnos t
            WHERE t.usuario_id = ?
              AND t.tipo_turno = 'Franco'
              AND t.fecha BETWEEN ? AND ?
              AND EXISTS (
                SELECT 1 FROM marcaciones_servicio m
                WHERE m.vigilador_id = t.usuario_id
                  AND DATE(m.fecha_hora) = t.fecha
                  AND m.tipo_evento = 'entrada'
              )";
            $countFrancos = $db->consultas($sqlFrancos, [$v['idUsuario'], $desde, $hasta])[0]['total'];

            $reporte[] = [
                'vigilador'         => $v['apellido'] . ' ' . $v['nombre'],
                'diurnas'           => round($sumDiur, 2),
                'nocturnas'         => round($sumNoct, 2),
                'jornadas'          => $jornadasCount,
                'guardias_pasivas'  => $countPasivas,
                'francos'           => $countFrancos
            ];
        }

        $_SESSION['reporte_vigilador'] = $reporte;
        header('Location: ?r=reporte_porVigilador');
        exit;
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
