<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/objetivos.modelo.php');

class ControladorObjetivos
{

    /*GUARDAR OBJETIVOS */
    static public function crtGuardarObjetivo()
    {
        Auth::check('objetivos', 'crtGuardarObjetivo');
        if (isset($_POST['nombreObjetivo'])) {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();
            $datos = [
                'nombre'    => $_POST['nombreObjetivo'],
                'latitud'   => $_POST['latitud'],
                'longitud'  => $_POST['longitud'],
                'radio_m'   => $_POST['radio_m'],
                'localidad' => $_POST['localidad'],
                'tipo'      => $_POST['tipo']
            ];
            // Guardar objetivo principal
            ModeloObjetivos::mdlGuardarObjetivo('objetivos', $datos);

            // Obtener ID del nuevo objetivo
            $idObjetivo = $conexion->lastInsertId();

            // Guardar vigiladores si vienen
            if (!empty($_POST['vigiladores']) && is_array($_POST['vigiladores'])) {
                ModeloObjetivos::mdlGuardarVigiladoresObjetivo($idObjetivo, $_POST['vigiladores']);
            }

            // Guardar referentes si vienen
            if (!empty($_POST['referentes']) && is_array($_POST['referentes'])) {
                ModeloObjetivos::mdlGuardarReferentesObjetivo($idObjetivo, $_POST['referentes']);
            }
            $conexion->commit();
            ToastifyController::success('Objetivo creado exitosamente');
        }
    }

    /*MODIFICAR OBJETIVOS */
    static public function crtModificarObjetivo()
    {
        Auth::check('objetivos', 'crtModificarObjetivo');

        if (isset($_POST['idObjetivo'], $_POST['nombreObjetivo'])) {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            $datos = [
                'idObjetivo' => $_POST['idObjetivo'],
                'nombre'     => $_POST['nombreObjetivo'],
                'latitud'    => $_POST['latitud'],
                'longitud'   => $_POST['longitud'],
                'radio_m'    => $_POST['radio_m'],
                'localidad'  => $_POST['localidad'],
                'tipo'       => $_POST['tipo']
            ];

            ModeloObjetivos::mdlModificarObjetivo('objetivos', $datos);

            // Eliminar y reinsertar relaciones
            ModeloObjetivos::mdlEliminarVigiladoresObjetivo($datos['idObjetivo']);
            ModeloObjetivos::mdlEliminarReferentesObjetivo($datos['idObjetivo']);

            if (!empty($_POST['vigiladores']) && is_array($_POST['vigiladores'])) {
                ModeloObjetivos::mdlGuardarVigiladoresObjetivo($datos['idObjetivo'], $_POST['vigiladores']);
            }

            if (!empty($_POST['referentes']) && is_array($_POST['referentes'])) {
                ModeloObjetivos::mdlGuardarReferentesObjetivo($datos['idObjetivo'], $_POST['referentes']);
            }

            $conexion->commit();
            ToastifyController::success('Objetivo actualizado correctamente');
        }
    }

    /** DESACTIVAR UN OBJETIVO **/
    static public function crtDesactivarObjetivo()
    {
        Auth::check('objetivos', 'crtDesactivarObjetivo');
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
                    ToastifyController::success('Objetivo desactivado');
                } else {
                    $db->rollBack();
                    ToastifyController::error('No se pudo desactivar');
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
            }
        }
    }
    /** REACTIVAR UN OBJETIVO **/
    static public function crtReactivarObjetivo()
    {
        Auth::check('objetivos', 'crtReactivarObjetivo');
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
                    ToastifyController::success('Objetivo activado');
                    
                } else {
                    $db->rollBack();
                    ToastifyController::error('No se pudo activar el objetivo');
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
            }
        }
    }

    static public function vistaListadoObjetivos()
    {
        Auth::check('objetivos', 'vistaListadoObjetivos');
        $db = new Conexion;
        $sql = "SELECT * FROM objetivos WHERE activo = 1 ORDER BY nombre";
        $objetivos = $db->consultas($sql);

        include __DIR__ . '/../vistas/paginas/objetivos/listado_objetivos.php';
        return;
    }
    static public function vistaListadoObjetivosInactivos()
    {
        Auth::check('objetivos', 'vistaListadoObjetivosInactivos');
        $db = new Conexion;
        $sql = "SELECT * FROM objetivos WHERE activo = 0 ORDER BY nombre";
        $objetivos = $db->consultas($sql);
        include __DIR__ . '/../vistas/paginas/objetivos/listado_objetivos_desactivados.php';
        return;
    }
    static public function vistaCrearObjetivo()
    {
        Auth::check('objetivos', 'vistaCrearObjetivo');
        include __DIR__ . '/../vistas/paginas/objetivos/crear_objetivo.php';
        return;
    }
    static public function vistaEditarObjetivo()
    {
        Auth::check('objetivos', 'vistaEditarObjetivo');
        include __DIR__ . '/../vistas/paginas/objetivos/editar_objetivo.php';
        return;
    }
}
