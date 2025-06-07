<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/objetivos.modelo.php');

class ControladorObjetivos
{

    /*GUARDAR OBJETIVOS */
    static public function crtGuardarObjetivo()
    {
        if (isset($_POST['nombreObjetivo'])) {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();
            $datos = [
                'nombre'    => $_POST['nombreObjetivo'],
                'localidad' => $_POST['localidad'],
                'tipo'      => $_POST['tipo'],
                'latitud'   => $_POST['latitud'],
                'longitud'  => $_POST['longitud'],
                'radio_m'   => $_POST['radio_m'],
            ];
            ModeloObjetivos::mdlGuardarObjetivo('objetivos', $datos);
            $conexion->commit();
            $_SESSION['success_message']='Objetivo creado exitosamente';
        }
    }

    /*MODIFICAR OBJETIVOS */
    static public function crtModificarObjetivo()
    {
        if (isset($_POST['idObjetivo'])) {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();
            $datos = [
                'idObjetivo'=> $_POST['idObjetivo'],
                'nombre'    => $_POST['nombreObjetivo'],
                'localidad' => $_POST['localidad'],
                'tipo'      => $_POST['tipo'],
                'latitud'   => $_POST['latitud'],
                'longitud'  => $_POST['longitud'],
                'radio_m'   => $_POST['radio_m'],
            ];
            $resp = ModeloObjetivos::mdlModificarObjetivo('objetivos',$datos);
            if($resp==='ok'){
                $conexion->commit();
                $_SESSION['success_message']='Objetivo modificado exitosamente';
                header('Location:?r=listado_objetivos'); exit;
            } else {
                $conexion->rollBack();
                $_SESSION['error_message']='Error al modificar';
                header('Location:?r=editar_objetivo&id='.$_POST['idObjetivo']); exit;
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
