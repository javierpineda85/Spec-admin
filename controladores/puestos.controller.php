<?php
require_once('modelos/puestos.modelo.php');

class ControladorPuestos
{
    public static function ctrGuardarPuesto()
    {
        Auth::check('puestos', 'ctrGuardarPuesto');
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
                    ToastifyController::success('Puesto registrado correctamente');
                    header("Location:?r=listado_puestos");
                    exit;
                } else {
                    throw new Exception("Error al guardar en la base de datos.");
                }
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollBack();

                // Manejar el error según sea necesario
                ToastifyController::error('Error: ' . $e->getMessage());

                return false;
            }
        }
    }

    static public function crtModificarPuesto()
    {
        Auth::check('puestos', 'crtModificarPuesto');
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
                    ToastifyController::success('Puesto actualizado correctamente');
                    header("Location:?r=listado_puestos");
                    exit;
                } else {
                    // Si algo falla, hacer rollback
                    $conexion->rollBack();
                    ToastifyController::error('Error al modificar el puesto');
                    header("Location: ?r=editar_puesto&id=" . $_POST["idPuesto"]);
                    exit;
                }
            } catch (Exception $e) {
                // En caso de error, revertir la transacción
                $conexion->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
                header("Location: ?r=editar_puesto.php&id=" . $_POST["idPuesto"]);
                exit;
            }
        }
    }

    /** DESACTIVAR UN PUESTO **/
    static public function crtDesactivarPuesto()
    {
        Auth::check('puestos', 'crtDesactivarPuesto');
        if (isset($_POST['idEliminar'])) {
            $id = intval($_POST['idEliminar']);
            try {
                $db = Conexion::conectar();
                if (!$db->inTransaction()) {
                    $db->beginTransaction();
                }

                $res = ModeloPuestos::mdlDesactivarPuesto('puestos', $id);
                if ($res === 'ok') {
                    $db->commit();
                    ToastifyController::success('Puesto activado.');
                } else {
                    $db->rollBack();
                    ToastifyController::error('No se pudo activar el puesto.');
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
            }
        }
    }

    /** REACTIVAR UN PUESTO **/
    static public function crtReactivarPuesto()
    {
        Auth::check('puestos', 'crtReactivarPuesto');
        if (isset($_POST['idReactivar'])) {
            $id = intval($_POST['idReactivar']);
            try {
                $db = Conexion::conectar();
                if (!$db->inTransaction()) {
                    $db->beginTransaction();
                }

                $res = ModeloPuestos::mdlReactivarPuesto('puestos', $id);
                if ($res === 'ok') {
                    $db->commit();
                    ToastifyController::success('Puesto activado.');
                    $db->rollBack();
                    ToastifyController::error('No se pudo activar el puesto');
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
            }
        }
    }
    static public function vistaListadoPuestos()
    {
        Auth::check('puestos', 'vistaListadoPuestos');

        $db = new Conexion;
        $sql = "SELECT p.idPuesto, p.puesto, p.objetivo_id, p.tipo, o.nombre as objetivo FROM puestos p JOIN objetivos o ON p.objetivo_id = o.idObjetivo WHERE p.activo = 1 ORDER BY p.objetivo_id ";
        $objetivos = $db->consultas($sql);

        include __DIR__ . '/../vistas/paginas/puestos/listado_puestos.php';
        return;
    }
    static public function vistaListadoPuestosDesactivados()
    {
        Auth::check('puestos', 'vistaListadoPuestosDesactivados');
        $db = new Conexion;
        $sql = "SELECT p.idPuesto, p.puesto, p.objetivo_id, p.tipo, o.nombre as objetivo FROM puestos p JOIN objetivos o ON p.objetivo_id = o.idObjetivo WHERE p.activo = 0 ORDER BY p.objetivo_id ";
        $objetivos = $db->consultas($sql);
        include __DIR__ . '/../vistas/paginas/puestos/listado_puestos_desactivados.php';
        return;
    }
    static public function vistaCrearPuestos()
    {
        Auth::check('puestos', 'vistaCrearPuestos');
        include __DIR__ . '/../vistas/paginas/puestos/crear_puesto.php';
        return;
    }
    static public function vistaEditarPuesto()
    {
        Auth::check('puestos', 'vistaEditarPuesto');
        include __DIR__ . '/../vistas/paginas/puestos/editar_puesto.php';
        return;
    }
}
