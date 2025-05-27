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
}
