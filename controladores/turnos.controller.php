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
}
