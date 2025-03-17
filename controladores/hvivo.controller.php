<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/hvivo.modelo.php');

class hVivo
{
    public function registrar()
    {

        if (isset($_SESSION['idUsuario'])) {

            try {
                $conexion = Conexion::conectar();

                // Verificar si ya hay una transacción activa
                if (!$conexion->inTransaction()) {
                    // Si no hay una transacción activa, iniciar una nueva
                    $conexion->beginTransaction();
                }

                $tabla = "reporte_hombre_vivo";

                $datos = array(
                    "id_usuario" => $_SESSION['idUsuario'],
                    "fecha"      => date('Y-m-d'),
                    "hora"       => date('H:i:s')
                );

                ModeloHvivo::mdlGuardarRegistro($tabla, $datos);

                // Confirmar la transacción si no hay errores
                Conexion::conectar()->commit();

                $_SESSION['success_message'] = 'Reporte de hombre vivo creado exitosamente';


                header("Location: index.php");  
                exit;
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                Conexion::conectar()->rollBack();

                // Manejar el error según sea necesario
                $_SESSION['success_message'] =  $e->getMessage();
               
                return false;
            }
        }else{
            $_SESSION['success_message'] = 'ERROR al registrar el reporte';
            header("Location: index.php"); 
        }
    }
}
