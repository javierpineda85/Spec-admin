<?php

require_once('modelos/cronograma.modelo.php');

class ControladorCronograma
{


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
            $_SESSION['success_message'] =
                "Se encontraron " . count($_SESSION['resumen_diario'])
                . " registros.";
        }

        // 4) redirijo a la vista que tú tengas mapeada, p. ej.:
        header("Location: index.php?r=listado_resumen_diario");
        exit;
    }

    /*reporte_horas_por_objetivo */
    static public function crtBuscarResumenHoras()
    {
        Auth::check('cronogramas', 'crtBuscarResumenHoras');
        $desde      = $_POST['desde']    ?? null;
        $hasta      = $_POST['hasta']    ?? null;

        //  Validamos que tengamos fechas 
        if (!$desde || !$hasta) {
            $_SESSION['error_message'] = "Por favor completa todos los filtros.";
            header('location: ?r=reporte_porHoras');
            exit;
        }

        // Traemos la lista de objetivos
        $db = new Conexion;
        $sql = "SELECT idObjetivo, nombre FROM objetivos ORDER BY nombre";
        $objetivos = $db->consultas($sql);

        $reporte = [];
        foreach ($objetivos as $o) {
            // 1) Traemos fecha, entrada y salida
            $sql = "SELECT fecha, entrada, salida FROM turnos WHERE objetivo_id = ? AND DATE(fecha) BETWEEN ? AND ? AND tipo_jornada   = 'Normal'";

            $params = [$o['idObjetivo'], $desde, $hasta];
            $jornadas = $db->consultas($sql, $params);

            $sumDiur = 0.0;
            $sumNoct = 0.0;

            foreach ($jornadas as $j) {
                // 2) Construimos DateTime de inicio
                $start = new DateTime("{$j['fecha']} {$j['entrada']}");

                // 3) Construimos DateTime de fin, sumando un día si la salida es menor o igual
                $rawSalida = "{$j['fecha']} {$j['salida']}";
                $end = new DateTime($rawSalida);
                if ($j['salida'] <= $j['entrada']) {
                    $end->modify('+1 day');
                }

                // 4) Cálculo de horas
                $hDiur = self::calcularHorasEnVentana($start, $end, '06:00:00', '21:00:00');
                $hTotal = ($end->getTimestamp() - $start->getTimestamp()) / 3600;
                $hNoct  = $hTotal - $hDiur;

                $sumDiur += $hDiur;
                $sumNoct += $hNoct;
            }

            $reporte[] = [
                'nombre'    => $o['nombre'],
                'diurnas'   => $sumDiur,
                'nocturnas' => $sumNoct
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

    /*reporte_horas_vigilador */
    static public function crtBuscarResumenHorasPorVigilador()
    {
        Auth::check('cronogramas', 'crtBuscarResumenHorasPorVigilador');
        $desde = $_POST['desde'] ?? null;
        $hasta = $_POST['hasta'] ?? null;
        if (!$desde || !$hasta) {
            $_SESSION['error_message'] = "Por favor completa los dos campos de fecha.";
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

            $sql    = "SELECT fecha, entrada, salida, turno, tipo_jornada
                   FROM turnos
                   WHERE vigilador_id = ?
                     AND DATE(fecha) BETWEEN ? AND ? ";
            $params = [$v['idUsuario'], $desde, $hasta];
            $jornadas = $db->consultas($sql, $params);

            $sumDiur      = 0.0;
            $sumNoct      = 0.0;
            $countPasivas = 0;
            $countFrancos = 0;
            $fechasTrabajadas = [];

            foreach ($jornadas as $j) {
                // 1) Siempre contamos la fecha como jornada
                $fechasTrabajadas[] = $j['fecha'];

                // 2) Si es guardia pasiva, incrementamos contador y saltamos cómputo de horas
                if ($j['tipo_jornada'] === 'Guardia Pasiva') {
                    $countPasivas++;
                    continue;
                }
                // 3) Si es franco, incrementamos contador y saltamos cómputo de horas
                if ($j['tipo_jornada'] === 'Franco') {
                    $countFrancos++;
                    continue;
                }
                // 4) Si no es trabajo normal (p. ej. 'licencia'), saltamos cómputo de horas
                if ($j['tipo_jornada'] !== 'Normal') {
                    continue;
                }

                // 5) Para 'Normal', sí hacemos el cálculo de horas
                $start = new DateTime("{$j['fecha']} {$j['entrada']}");
                $end   = new DateTime("{$j['fecha']} {$j['salida']}");
                if ($j['salida'] <= $j['entrada']) {
                    $end->modify('+1 day');
                }

                // Calculamos horas diurnas (entre 06:00 y 21:00) y restantes como nocturnas
                $hDiur   = self::calcularHorasEnVentana($start, $end, '06:00:00', '21:00:00');
                $hTotal  = ($end->getTimestamp() - $start->getTimestamp()) / 3600.0;
                $hNoct   = $hTotal - $hDiur;

                $sumDiur += $hDiur;
                $sumNoct += $hNoct;
            }

            // Contamos fechas únicas para obtener cantidad de jornadas
            $jornadasCount = count(array_unique($fechasTrabajadas));

            $reporte[] = [
                'vigilador'         => $v['apellido'] . ' ' . $v['nombre'],
                'diurnas'           => $sumDiur,
                'nocturnas'         => $sumNoct,
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
        include __DIR__ . '/../vistas/paginas/cronogramas/listado_cronogramas.php';
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
        Auth::check('cronogramas', 'reporte_porVigilador');
        include __DIR__ . '/../vistas/paginas/cronogramas/reporte_horas_vigilador.php';
        return;
    }
}
