<?php
require_once('modelos/turnos.modelo.php');

class ControladorTurnos
{
    /* Recorre $_SESSION['turnos'] y los guarda en BD. */
    public static function ctrRegistrarPlanilla()
    {
        Auth::check('turnos', 'ctrRegistrarPlanilla');
        // Solo si llegó la petición de guardar y hay turnos en sesión
        if (isset($_POST['guardar_todos']) && !empty($_SESSION['turnos'])) {

            try {
                $db = Conexion::conectar();
                // Inicio transacción si no hay una
                if (!$db->inTransaction()) {
                    $db->beginTransaction();
                }

                foreach ($_SESSION['turnos'] as $t) {
                    // Ajuste de tipo: guardia → guardia_pasiva
                    $tipo = strtolower($t['tipo_jornada']);
                    if ($tipo === 'guardia') {
                        $tipo = 'guardia pasiva';
                    }

                    $datos = [
                        "objetivo_id"   => $t['objetivo'],
                        "fecha"         => $t['fecha'],
                        "turno"         => $t['turno'],
                        "vigilador_id"  => $t['vigilador'],
                        "tipo_jornada"  => $t['tipo_jornada'],
                        'is_referente'  => (!empty($_POST['is_referente'])) ? 1 : 0,
                        "entrada"       => $t['entrada'],
                        "salida"        => $t['salida'],
                        "color"         => $t['color'],
                    ];

                    $respuesta = ModeloTurnos::mdlGuardarTurno("turnos", $datos);
                    if ($respuesta !== "ok") {
                        throw new Exception("Error al guardar turno");
                    }
                }

                // Commit y limpieza
                $db->commit();
                ToastifyController::success('Planilla guardada correctamente');

                $_SESSION['turnos'] = [];  // vaciar planilla

                // Redirigir a la misma página o a donde quieras
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
            }
        }
    }

    /*listado_cronograma.php */
    static public function crtBuscarTurnosPorRango()
    {
        Auth::check('turnos', 'crtBuscarTurnosPorRango');
        //session_start();
        if (isset($_POST['buscar_turnos'])) {
            // Guardamos filtros para “pintar” el form luego
            $_SESSION['filtros'] = [
                'objetivo' => $_POST['objetivo'],
                'desde'    => $_POST['desde'],
                'hasta'    => $_POST['hasta']
            ];

            // Traemos los datos del modelo
            //$_SESSION['turnos'] = ModeloTurnos::mdlObtenerTurnos('turnos', $_SESSION['filtros']);
            $_SESSION['turnos'] = ModeloTurnos::mdlObtenerTurnosConPuestos('turnos', $_SESSION['filtros']);

            // Mensaje opcional
            $_SESSION['success_message'] =
                "Se encontraron " . count($_SESSION['turnos']) . " registros.";
        }
        // Volvemos al listado
        header("Location: index.php?r=listado_cronogramas");
        exit;
    }

    static public function crtBuscarPorVigilador()
    {
        Auth::check('turnos', 'crtBuscarPorVigilador');
        if (!isset($_POST['vigilador'], $_POST['desde'], $_POST['hasta'])) {
            ToastifyController::error('Faltan datos para buscar');
            header('Location: index.php?r=listado_porVigilador');
            exit;
        }

        $usuarioId = intval($_POST['vigilador']);
        $desde     = $_POST['desde'];
        $hasta     = $_POST['hasta'];

        // Guardamos los filtros en sesión
        $_SESSION['filtros_vigilador'] = [
            'vigilador' => $usuarioId,
            'desde'     => $desde,
            'hasta'     => $hasta
        ];
        // Generar días del rango (array de fechas Y-m-d)
        $diasRango = [];
        $actual = new DateTime($desde);
        $fin    = new DateTime($hasta);
        while ($actual <= $fin) {
            $diasRango[] = $actual->format('Y-m-d');
            $actual->modify('+1 day');
        }
        // Obtenemos los turnos
        $turnos = ModeloTurnos::mdlObtenerTurnos('turnos', $_SESSION['filtros_vigilador']);


        foreach ($turnos as &$t) {

            // También renombramos campos para compatibilidad con la vista
            $t['tipo_jornada'] = $t['tipo_turno']; // por compatibilidad con la vista actual
            $t['turno'] = $t['codigo_turno'];     // por compatibilidad con la vista actual
        }
        // Consulta de feriados
        $feriados = [];
        $stmt2 = Conexion::conectar()->prepare("SELECT fecha FROM feriados WHERE fecha BETWEEN :desde AND :hasta");
        $stmt2->bindParam(':desde', $desde, PDO::PARAM_STR);
        $stmt2->bindParam(':hasta', $hasta, PDO::PARAM_STR);
        $stmt2->execute();
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $feriados[] = $f['fecha'];
        }

        // Guardamos en sesión
        $_SESSION['filtros_vigilador']      = ['vigilador' => $usuarioId, 'desde' => $desde, 'hasta' => $hasta];
        $_SESSION['dias_rango']             = $diasRango;
        $_SESSION['feriados_rango']         = $feriados;
        // Lo mandamos a sesión
        $turnosPorFecha = [];
        foreach ($turnos as $t) {
            $fecha = $t['fecha'];
            $turnosPorFecha[$fecha] = [
                'turno'  => $t['codigo_turno'],
                'puesto' => $t['puesto'],
                'objetivo' => $t['objetivo']
            ];
        }
        $_SESSION['turnos_porVigilador'] = $turnosPorFecha;

        header('Location: index.php?r=listado_porVigilador');
        exit;
    }
}
