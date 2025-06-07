<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/objetivos.modelo.php');

class ControladorObjetivos
{

    /*GUARDAR OBJETIVOS */
    static public function crtGuardarObjetivo()
    {
        if (isset($_POST["nombreObjetivo"])) {

            try {
                $conexion = Conexion::conectar();

                // Verificar si ya hay una transacción activa
                if (!$conexion->inTransaction()) {
                    // Si no hay una transacción activa, iniciar una nueva
                    $conexion->beginTransaction();
                }

                $tabla = "objetivos";

                $datos = array(
                    "nombre" => $_POST["nombreObjetivo"],
                    "localidad" => $_POST["localidad"],
                    //"referente" => $_POST["referente"],
                    "tipo" => $_POST["tipo"]
                );

                ModeloObjetivos::mdlGuardarObjetivo($tabla, $datos);

                // Confirmar la transacción si no hay errores
                $conexion->commit();

                $_SESSION['success_message'] = 'Objetivo creado exitosamente';
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }

                // Manejar el error según sea necesario
                $_SESSION['success_message'] =  $e->getMessage();

                return false;
            }
        }
    }

    /* MODIFICAR OBJETIVO */
    static public function crtModificarObjetivo()
    {
        if (isset($_POST["nombre"])) {

            try {
                $conexion = Conexion::conectar();

                // Iniciar una transacción
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "objetivos";

                $datos = array(
                    "idObjetivo"  => $_POST["idObjetivo"],
                    "nombre"      => $_POST["nombre"],
                    "localidad"   => $_POST["localidad"],
                    //"referente"   => $_POST["referente"],
                    "tipo"        => $_POST["tipo"]
                );

                $respuesta = ModeloObjetivos::mdlModificarObjetivo($tabla, $datos);

                if ($respuesta === "ok") {
                    // Confirmar la transacción
                    $conexion->commit();
                    $_SESSION['success_message'] = 'Objetivo modificado exitosamente';
                    header("Location:?r=listado_objetivos");
                    exit;
                } else {
                    // Si algo falla, hacer rollback
                    $conexion->rollBack();
                    $_SESSION['error_message'] = 'Error al modificar el objetivo';
                    header("Location: ?r=editar_objetivo&id=" . $_POST["idObjetivo"]);
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

      /** DESACTIVAR UN OBJETIVO **/
    static public function crtDesactivarObjetivo()
    {
        if (isset($_POST['idEliminar'])) {
            $id = intval($_POST['idEliminar']);
            try {
                $db = Conexion::conectar();
                if (!$db->inTransaction()) {
                    $db->beginTransaction();
                }

                $res = ModeloObjetivos::mdlDesactivarObjetivo('objetivos', $id);
                if ($res === 'ok') {
                    $db->commit();
                    $_SESSION['success_message'] = 'Objetivo desactivado.';
                } else {
                    $db->rollBack();
                    $_SESSION['error_message'] = 'No se pudo desactivar.';
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
            }
        }
    }
    /** REACTIVAR UN OBJETIVO **/
    static public function crtReactivarObjetivo() { 
        if (isset($_POST['idReactivar'])) {
            $id = intval($_POST['idReactivar']);
            try {
                $db = Conexion::conectar();
                if (!$db->inTransaction()) {
                    $db->beginTransaction();
                }

                $res = ModeloObjetivos::mdlReactivarObjetivo('objetivos', $id);
                if ($res === 'ok') {
                    $db->commit();
                    $_SESSION['success_message'] = 'Objetivo activado.';
                } else {
                    $db->rollBack();
                    $_SESSION['error_message'] = 'No se pudo activar el objetivo.';
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
            }
        }
    }

}
