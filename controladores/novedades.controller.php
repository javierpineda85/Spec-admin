<?php
ob_start(); // Para que los headers no tiren error

require_once ('modelos/novedades.modelo.php');

class NovedadesController
{
    static public function crtRegistrar()
    {
        if (isset($_POST['idUsuario'])) {

            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "novedades";

                // Determinar si es Entrada o Salida
                $tipoRegistro = isset($_POST['tipo_registro']) ? "Salida" : "Entrada";

                $datos = array(
                    "usuario_id"     => $_SESSION['idUsuario'],
                    "objetivo_id"    => 0, // Placeholder. Podés reemplazar por un valor real si lo estás usando
                    "fecha"          => date('Y-m-d'),
                    "hora"           => date('H:i:s'),
                    "detalle"        => $_POST['detalle'],
                    "tipo_registro"  => $tipoRegistro
                );

                $respuesta = ModeloNovedades::mdlGuardarNovedad($tabla, $datos);

                if ($respuesta == "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Registro de $tipoRegistro guardado correctamente.";
                    header("Location: index.php");
                    exit;
                } else {
                    throw new Exception("Error al guardar la novedad.");
                }
            } catch (Exception $e) {
                $conexion->rollBack();
                $_SESSION['success_message'] = "ERROR: " . $e->getMessage();
                header("Location: index.php");
                exit;
            }
        } 
    }
}


?>