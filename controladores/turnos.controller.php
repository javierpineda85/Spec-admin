<?php
require_once('modelos/turnos.modelo.php');

class ControladorTurnos
{
    /* Recorre $_SESSION['turnos'] y los guarda en BD. */
    public static function ctrRegistrarPlanilla()
    {

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
                    $tipo = strtolower($t['actividad']);
                    if ($tipo === 'guardia') {
                        $tipo = 'guardia pasiva';
                    }

                    $datos = [
                        "objetivo_id"   => $t['objetivo'],
                        "puesto_id"     => $t['puesto'],
                        "fecha"         => $t['fecha'],
                        "turno"         => $t['turno'],
                        "vigilador_id"  => $t['vigilador'],
                        "actividad"     => $t['actividad'],
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
                $_SESSION['success_message'] = "Planilla guardada correctamente.";
                $_SESSION['turnos'] = [];  // vaciar planilla

                // Redirigir a la misma página o a donde quieras
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['error_message'] = $e->getMessage();
            }
        }
    }

    static public function crtBuscarTurnosPorRango()
    {
        //session_start();
        if (isset($_POST['buscar_turnos'])) {
            // Guardamos filtros para “pintar” el form luego
            $_SESSION['filtros'] = [
                'objetivo' => $_POST['objetivo'],
                'desde'    => $_POST['desde'],
                'hasta'    => $_POST['hasta']
            ];

            // Traemos los datos del modelo
            $datos = $_SESSION['filtros'];
            $_SESSION['turnos'] = ModeloTurnos::mdlObtenerTurnosPorRango('turnos', $datos);

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
        session_start();
        if (isset($_POST['buscar_por_vigilador'])) {
            // Guardamos filtros en sesión
            $_SESSION['filtros_vigilador'] = [
                'vigilador' => $_POST['vigilador'],
                'desde'     => $_POST['desde'],
                'hasta'     => $_POST['hasta']
            ];
            $f = $_SESSION['filtros_vigilador'];
            // Traemos los turnos para ese vigilador y rango
            $_SESSION['turnos_porVigilador'] =
                ModeloTurnos::mdlObtenerPorVigiladorYRango(
                    'turnos',
                    $f['vigilador'],
                    $f['desde'],
                    $f['hasta']
                );
            $_SESSION['success_message'] =
                "Se encontraron " .
                count($_SESSION['turnos_porVigilador']) .
                " registros para el vigilador.";
        }
        // Redirigimos a la vista
        header("Location: index.php?r=listado_porVigilador");
        exit;
    }
}
