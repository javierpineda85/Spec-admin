<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/objetivos.modelo.php');

class ControladorObjetivos
{

    /*GUARDAR OBJETIVOS */
    static public function crtGuardarObjetivo()
    {
        //Auth::check('objetivos', 'crtGuardarObjetivo');
        Auth::check('objetivos', 'vistaCrearObjetivo');
        if (isset($_POST['nombreObjetivo'])) {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();
            $datos = [
                'nombre'    => $_POST['nombreObjetivo'],
                'domicilio' => $_POST['domicilio'],
                'latitud'   => $_POST['latitud'],
                'longitud'  => $_POST['longitud'],
                'radio_m'   => $_POST['radio_m'],
                'localidad' => $_POST['localidad'],
                'tipo'      => $_POST['tipo']
            ];
            // Guardar objetivo principal
            $idObjetivo = ModeloObjetivos::mdlGuardarObjetivo('objetivos', $datos); // devuelve el ultimo id

            // Guardar vigiladores si vienen
            if (!empty($_POST['vigiladores']) && is_array($_POST['vigiladores'])) {
                ModeloObjetivos::mdlGuardarVigiladoresObjetivo($idObjetivo, $_POST['vigiladores']);
            }
            if (!$idObjetivo) {
                $conexion->rollBack();
                ToastifyController::error('No se pudo crear el objetivo');
                return;
            }
            // Guardar referentes si vienen
            if (!empty($_POST['referentes']) && is_array($_POST['referentes'])) {
                ModeloObjetivos::mdlGuardarReferentesObjetivo($idObjetivo, $_POST['referentes']);
            }

            // Guardar Base Operativa
            if (!empty($_POST['base_operativa']) && is_array($_POST['base_operativa'])) {
                ModeloObjetivos::mdlGuardarBaseOperativaObjetivo($idObjetivo, $_POST['base_operativa']);
            }

            // Guardar siglas dinámicas
            if (!empty($_POST['siglas']) && is_array($_POST['siglas'])) {
                foreach ($_POST['siglas'] as $s) {
                    ModeloObjetivos::mdlInsertarSigla(
                        $idObjetivo,
                        $s['sigla'],
                        $s['descripcion'],
                        $s['horas']
                    );
                }
            }
            $conexion->commit();
            ToastifyController::success('Objetivo creado exitosamente');
        }
    }

    /*MODIFICAR OBJETIVOS */
    static public function crtModificarObjetivo()
    {
        //Auth::check('objetivos', 'crtModificarObjetivo');
        Auth::check('objetivos', 'vistaEditarObjetivo');

        if (isset($_POST['idObjetivo'], $_POST['nombreObjetivo'])) {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            $datos = [
                'idObjetivo' => $_POST['idObjetivo'],
                'nombre'     => $_POST['nombreObjetivo'],
                'domicilio'  => $_POST['domicilio'],
                'latitud'    => $_POST['latitud'],
                'longitud'   => $_POST['longitud'],
                'radio_m'    => $_POST['radio_m'],
                'localidad'  => $_POST['localidad'],
                'tipo'       => $_POST['tipo']
            ];

            ModeloObjetivos::mdlModificarObjetivo('objetivos', $datos);

            // Eliminar y reinsertar relaciones
            // === VIGILADORES ===
            $actualesVigiladores = ModeloObjetivos::mdlObtenerVigiladoresPorObjetivo($datos['idObjetivo']);
            $nuevosVigiladores = $_POST['vigiladores'] ?? [];

            sort($actualesVigiladores);
            sort($nuevosVigiladores);

            if ($actualesVigiladores !== $nuevosVigiladores) {
                ModeloObjetivos::mdlEliminarVigiladoresObjetivo($datos['idObjetivo']);
                if (!empty($nuevosVigiladores)) {
                    ModeloObjetivos::mdlGuardarVigiladoresObjetivo($datos['idObjetivo'], $nuevosVigiladores);
                }
            }

            // === REFERENTES ===
            $actualesReferentes = ModeloObjetivos::mdlObtenerReferentesPorObjetivo($datos['idObjetivo']);
            $nuevosReferentes = $_POST['referentes'] ?? [];

            sort($actualesReferentes);
            sort($nuevosReferentes);

            if ($actualesReferentes !== $nuevosReferentes) {
                ModeloObjetivos::mdlEliminarReferentesObjetivo($datos['idObjetivo']);
                if (!empty($nuevosReferentes)) {
                    ModeloObjetivos::mdlGuardarReferentesObjetivo($datos['idObjetivo'], $nuevosReferentes);
                }
            }
            /*
            if (!empty($_POST['vigiladores']) && is_array($_POST['vigiladores'])) {
                ModeloObjetivos::mdlGuardarVigiladoresObjetivo($datos['idObjetivo'], $_POST['vigiladores']);
            }

            if (!empty($_POST['referentes']) && is_array($_POST['referentes'])) {
                ModeloObjetivos::mdlGuardarReferentesObjetivo($datos['idObjetivo'], $_POST['referentes']);
            }*/
            // === BASE OPERATIVA ===
            $actualesBase = ModeloObjetivos::mdlObtenerBaseOperativaPorObjetivo($datos['idObjetivo']);
            $nuevaBase = $_POST['base_operativa'] ?? [];

            sort($actualesBase);
            sort($nuevaBase);

            if ($actualesBase !== $nuevaBase) {
                ModeloObjetivos::mdlEliminarBaseOperativaObjetivo($datos['idObjetivo']);
                if (!empty($nuevaBase)) {
                    ModeloObjetivos::mdlGuardarBaseOperativaObjetivo($datos['idObjetivo'], $nuevaBase);
                }
            }

            // SIGLAS DINÁMICAS
            $actuales = ModeloObjetivos::mdlObtenerSiglasPorObjetivo($datos['idObjetivo']);
            $nuevas = $_POST['siglas'] ?? [];

            $idsActuales = array_column($actuales, 'id');
            $idsNuevas = array_column($nuevas, 'id');

            // Eliminar siglas quitadas
            foreach ($idsActuales as $id) {
                if (!in_array($id, $idsNuevas)) {
                    ModeloObjetivos::mdlEliminarSigla($id);
                }
            }

            // Insertar o actualizar siglas
            foreach ($nuevas as $s) {
                if (empty($s['id'])) {
                    ModeloObjetivos::mdlInsertarSigla($datos['idObjetivo'], $s['sigla'], $s['descripcion'], $s['horas']);
                } else {
                    ModeloObjetivos::mdlActualizarSigla($s['id'], $s['sigla'], $s['descripcion'], $s['horas'], 1);
                }
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
    public static function apiSiglas()
    {
        $siglas = ModeloObjetivos::mdlObtenerSiglas();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($siglas, JSON_UNESCAPED_UNICODE);
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
