<?php

require_once('modelos/cronograma.modelo.php');

class ControladorCronograma
{

    static public function crtSubirCrono()
    {
        if (!empty($_FILES["imgCrono"]["name"])) {
            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "cronogramas";
                $objetivo_id = trim($_POST["id_objetivo"]);
                $fecha = date("Y-m-d H:i:s");

                // Normalizar nombre de archivos
                $nombreArchivo = preg_replace('/\s+/', '', $_FILES["imgCrono"]["name"]);

                // Guardar imagen
                $imgCrono = self::guardarImagen($_FILES["imgCrono"], "img/cronogramas/", $nombreArchivo . "Cronograma");

                $datos = array(
                    "objetivo_id" => $objetivo_id,
                    "fechaCarga" => $fecha,
                    "img_crono" => $imgCrono
                );

                $respuesta = ModeloCronograma::mdlSubirCrono($tabla, $datos);

                if ($respuesta == "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Cronograma registrado correctamente.";
                } else {
                    throw new Exception("Error al guardar en la base de datos.");
                }
            } catch (Exception $e) {
                $conexion->rollBack();
                $_SESSION['success_message'] = $e->getMessage();
                return false;
            }
        }
    }

    /* FUNCIÓN PARA GUARDAR IMÁGENES */
    static public function guardarImagen($archivo, $directorio, $nombreArchivo)
    {
        if ($archivo["error"] == UPLOAD_ERR_OK) {
            $ext = pathinfo($archivo["name"], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            // Validar formato de imagen
            $formatosPermitidos = array("jpg", "jpeg", "png", "webp", "avif");
            if (!in_array($ext, $formatosPermitidos)) {
                throw new Exception("Formato de imagen no permitido.");
            }

            // Ruta completa
            $ruta = $directorio . $nombreArchivo . "." . $ext;

            // Mover archivo al directorio
            if (!move_uploaded_file($archivo["tmp_name"], $ruta)) {
                throw new Exception("Error al subir la imagen.");
            }

            return $ruta; // Devuelve la ruta para guardarla en la base de datos
        }

        return null;
    }

    /*Funcion para buscar por resumen diario */
    static public function crtBuscarResumenDiario()
    {
        session_start();
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

    static public function crtBuscarResumenHoras()
    {
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
            $sql = "SELECT fecha, entrada, salida FROM turnos WHERE objetivo_id = ? AND DATE(fecha) BETWEEN ? AND ?";

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
    static public function crtBuscarResumenHorasPorVigilador()
    {
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
            // Traemos turno, fecha, entrada y salida
            $sql    = "SELECT fecha, entrada, salida, turno
                   FROM turnos
                   WHERE vigilador_id = ?
                     AND DATE(fecha) BETWEEN ? AND ?";
            $params = [$v['idUsuario'], $desde, $hasta];
            $jornadas = $db->consultas($sql, $params);

            $sumDiur = 0.0;
            $sumNoct = 0.0;
            $fechasTrabajadas = [];

            foreach ($jornadas as $j) {
                // 1) Siempre contamos la fecha como jornada
                $fechasTrabajadas[] = $j['fecha'];

                // 2) Si es guardia pasiva, saltamos el cómputo de horas
                if ($j['turno'] === 'Guardia Pasiva') {
                    continue;
                }

                // 3) Para Diurno/Nocturno normales, hacemos el cálculo
                $start = new DateTime("{$j['fecha']} {$j['entrada']}");
                $end   = new DateTime("{$j['fecha']} {$j['salida']}");
                if ($j['salida'] <= $j['entrada']) {
                    $end->modify('+1 day');
                }

                $hDiur = self::calcularHorasEnVentana($start, $end, '06:00:00', '21:00:00');
                $hTotal = ($end->getTimestamp() - $start->getTimestamp()) / 3600.0;
                $hNoct  = $hTotal - $hDiur;

                $sumDiur += $hDiur;
                $sumNoct += $hNoct;
            }

            $jornadasCount = count(array_unique($fechasTrabajadas));
            

            $reporte[] = [
                'vigilador' => $v['apellido'] . ' ' . $v['nombre'],
                'diurnas'   => $sumDiur,
                'nocturnas' => $sumNoct,
                'jornadas'  => $jornadasCount
            ];
        }

        $_SESSION['reporte_vigilador'] = $reporte;
        header('Location: ?r=reporte_porVigilador');
        exit;
    }
}
