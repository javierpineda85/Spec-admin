<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/directivas.modelo.php');

class ControladorDirectivas
{

    /*GUARDAR DIRECTIVAS */
    static public function crtGuardarDirectiva()
    {
        
        if (isset($_POST["id_objetivo"])) {

            try {
                $conexion = Conexion::conectar();

                // Verificar si ya hay una transacción activa
                if (!$conexion->inTransaction()) {
                    // Si no hay una transacción activa, iniciar una nueva
                    $conexion->beginTransaction();
                }

                $tabla = "directivas";

                $datos = array(
                    "id_objetivo" => $_POST["id_objetivo"],
                    "detalle" => $_POST["detalle"]
                );

                ModeloDirectivas::mdlGuardarDirectiva($tabla, $datos);

                // Confirmar la transacción si no hay errores
                Conexion::conectar()->commit();

                $_SESSION['success_message'] = 'Directiva creada exitosamente';
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                Conexion::conectar()->rollBack();

                // Manejar el error según sea necesario
                $_SESSION['success_message'] =  $e->getMessage();

                return false;
            }
        }
    }


}
