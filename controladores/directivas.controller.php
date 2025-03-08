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

        /* MODIFICAR DIRECTIVAS */
        static public function crtModificarDirectiva()
        {
            if (isset($_POST["idDirectiva"])) {
  
                try {
                    $conexion = Conexion::conectar();
    
                    // Iniciar una transacción
                    if (!$conexion->inTransaction()) {
                        $conexion->beginTransaction();
                    }
    
                    $tabla = "directivas";
    
                    $datos = array(
                        "idDirectiva"  => $_POST["idDirectiva"],
                        "id_objetivo"  => $_POST["id_objetivo"],
                        "detalle"      => $_POST["detalle"]
                    );
    
                    $respuesta = ModeloDirectivas::mdlModificarDirectiva($tabla, $datos);
    
                    if ($respuesta === "ok") {
                        // Confirmar la transacción
                        $conexion->commit();
                        $_SESSION['success_message'] = 'Directiva modificada exitosamente';
                        header("Location:?r=listado_directivas");
                        exit;
                    } else {
                        // Si algo falla, hacer rollback
                        $conexion->rollBack();
                        $_SESSION['error_message'] = 'Error al modificar la directiva';
                        header("Location: ?r=modificar_directivas&id=" . $_POST["idDirectiva"]);
                        exit;
                    }
                } catch (Exception $e) {
                    // En caso de error, revertir la transacción
                    $conexion->rollBack();
                    $_SESSION['error_message'] = "Error: " . $e->getMessage();
                    header("Location: ?r=editar_objetivo.php&id=" . $_POST["idObjetivo"]);
                    exit;
                }
            }
        }


}
