<?php
require_once('modelos/puestos.modelo.php');

class ControladorPuestos
{
    public static function ctrGuardarPuesto()
    {
        if (isset($_POST["puesto"])) {

            try {
                $conexion = Conexion::conectar();

                // Verificar si ya hay una transacción activa
                if (!$conexion->inTransaction()) {
                    // Si no hay una transacción activa, iniciar una nueva
                    $conexion->beginTransaction();
                }

                $tabla = "puestos";

                $puesto = trim($_POST["puesto"]);
                $objetivo_id = $_POST["objetivo_id"];
                $tipo = $_POST["tipo"];


                $datos = array(
                    "puesto" => $puesto,
                    "objetivo_id" => $objetivo_id,
                    "tipo" => $tipo
                );

                $respuesta = ModeloPuestos::mdlGuardarPuesto($tabla, $datos);

                // Confirmar la transacción si no hay errores

                if ($respuesta == "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Puesto registrado correctamente.";
                } else {
                    throw new Exception("Error al guardar en la base de datos.");
                }
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollBack();

                // Manejar el error según sea necesario
                $_SESSION['success_message'] =  $e->getMessage();

                return false;
            }
        }
    }

    static public function crtModificarPuesto()
    {
        if (isset($_POST["puesto"])) {

            try {
                $conexion = Conexion::conectar();

                // Iniciar una transacción
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "puestos";

                $datos = array(
                    "idPuesto"  => $_POST["idPuesto"],
                    "puesto"      => $_POST["puesto"],
                    "objetivo_id"   => $_POST["objetivo_id"],
                    "tipo"        => $_POST["tipo"]
                );

                $respuesta = ModeloPuestos::mdlModificarPuesto($tabla, $datos);

                if ($respuesta === "ok") {
                    // Confirmar la transacción
                    $conexion->commit();
                    $_SESSION['success_message'] = 'Puesto modificado exitosamente';
                    header("Location:?r=listado_puestos");
                    exit;
                } else {
                    // Si algo falla, hacer rollback
                    $conexion->rollBack();
                    $_SESSION['error_message'] = 'Error al modificar el puesto';
                    header("Location: ?r=editar_puesto&id=" . $_POST["idPuesto"]);
                    exit;
                }
            } catch (Exception $e) {
                // En caso de error, revertir la transacción
                $conexion->rollBack();
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                header("Location: ?r=editar_puesto.php&id=" . $_POST["idPuesto"]);
                exit;
            }
        }
    }
}
