<?php
ob_start();
require_once('modelos/asignaciones.modelo.php');

class controlladorAsignaciones
{
    public function crtRegistrar()
    {
        if (isset($_SESSION['idUsuario']) && isset($_POST['id_usuario']) && isset($_POST['id_objetivo'])) {
            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "asignaciones";

                $datos = array(
                    "id_usuario" => $_POST['id_usuario'],    // usuario a asignar (empleado)
                    "id_objetivo" => $_POST['id_objetivo']   // puesto de trabajo
                );

                ModeloAsignaciones::mdlGuardarAsignacion($tabla, $datos);

                $conexion->commit();

                $_SESSION['success_message'] = 'Asignación registrada correctamente.';
                header("Location: index.php?r=crear_asignaciones");
                exit;
            } catch (Exception $e) {
                $conexion->rollBack();
                $_SESSION['success_message'] = "Error al asignar: " . $e->getMessage();
                header("Location: index.php?r=crear_asignaciones");
                exit;
            }
        } else {
            $_SESSION['success_message'] = 'Faltan datos para realizar la asignación.';
            header("Location: index.php?r=crear_asignaciones");
            exit;
        }
    }
    static public function agregarAsignacionTemporal()
    {
        session_start(); // Asegurate de tener la sesión activa

        if (isset($_POST['usuario_id']) && isset($_POST['objetivo_id'])) {
            $asignacion = array(
                'usuario_id' => $_POST['usuario_id'],
                'objetivo_id' => $_POST['objetivo_id']
            );

            $_SESSION['asignaciones'][] = $asignacion;
        }

        header("Location: index.php?r=crear_asignaciones");
        exit;
    }

    public function guardarAsignaciones()
    {
        session_start();

        if (!empty($_SESSION['asignaciones'])) {
            try {
                $conexion = Conexion::conectar();
                $conexion->beginTransaction();

                foreach ($_SESSION['asignaciones'] as $asignacion) {
                    ModeloAsignaciones::mdlGuardarAsignacion("usuario_objetivo", $asignacion);
                }

                $conexion->commit();
                unset($_SESSION['asignaciones']); // Limpiar luego de guardar

                $_SESSION['success_message'] = "Asignaciones guardadas correctamente.";
            } catch (Exception $e) {
                $conexion->rollBack();
                $_SESSION['error_message'] = "Error al guardar asignaciones: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "No hay asignaciones para guardar.";
        }

        header("Location: index.php?r=crear_asignaciones");
        exit;
    }
}
