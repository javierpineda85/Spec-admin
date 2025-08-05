<?php
require_once('modelos/puestos.modelo.php');

class ControladorPuestos
{
    public static function ctrGuardarPuesto()
    {
        Auth::check('puestos', 'ctrGuardarPuesto');

        if (empty($_POST['puesto']) || empty($_POST['objetivo_id']) || empty($_POST['tipo'])) {
            ToastifyController::error('Faltan campos obligatorios.');
            return;
        }

        $db = new Conexion;

        // 1. Insertar puesto principal
        $sql = "INSERT INTO puestos (puesto, objetivo_id, tipo)
            VALUES (:puesto, :objetivo_id, :tipo)";
        $stmt = $db->conectar()->prepare($sql);
        $stmt->bindParam(':puesto', $_POST['puesto'], PDO::PARAM_STR);
        $stmt->bindParam(':objetivo_id', $_POST['objetivo_id'], PDO::PARAM_INT);
        $stmt->bindParam(':tipo', $_POST['tipo'], PDO::PARAM_STR);

        if (!$stmt->execute()) {
            ToastifyController::error('No se pudo registrar el puesto.');
            return;
        }

        $idPuesto = $db->conectar()->lastInsertId();

        // 2. Insertar turnos si están cargados
        $turnos = $_POST['turnos'] ?? [];

        $sqlTurno = "INSERT INTO puestos_turnos (puesto_id, numero_turno, hora_entrada, hora_salida)
                 VALUES (:puesto_id, :numero_turno, :hora_entrada, :hora_salida)";
        $stmtTurno = $db->conectar()->prepare($sqlTurno);

        foreach ($turnos as $turno) {
            $entrada = $turno['hora_entrada'] ?? '';
            $salida  = $turno['hora_salida'] ?? '';

            // Validamos que ambos campos estén completos
            if ($entrada && $salida) {
                $stmtTurno->bindParam(':puesto_id', $idPuesto, PDO::PARAM_INT);
                $stmtTurno->bindParam(':numero_turno', $turno['numero_turno'], PDO::PARAM_INT);
                $stmtTurno->bindParam(':hora_entrada', $entrada, PDO::PARAM_STR);
                $stmtTurno->bindParam(':hora_salida', $salida, PDO::PARAM_STR);
                $stmtTurno->execute();
            }
        }

        ToastifyController::success('Puesto y turnos registrados correctamente.');
    }


    static public function crtModificarPuesto()
    {
        Auth::check('puestos', 'crtModificarPuesto');
        if (isset($_POST["puesto"])) {

            try {
                $conexion = Conexion::conectar();

                // Iniciar transacción
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                // 1. Actualizar datos del puesto
                $tabla = "puestos";

                $datos = array(
                    "idPuesto"     => $_POST["idPuesto"],
                    "puesto"       => $_POST["puesto"],
                    "objetivo_id"  => $_POST["objetivo_id"],
                    "tipo"         => $_POST["tipo"]
                    // "duracion_turno" eliminado porque ya no se usa
                );

                $respuesta = ModeloPuestos::mdlModificarPuesto($tabla, $datos);

                if ($respuesta !== "ok") {
                    $conexion->rollBack();
                    ToastifyController::error('Error al modificar el puesto');
                    header("Location: ?r=editar_puesto&id=" . $_POST["idPuesto"]);
                    exit;
                }

                // 2. Eliminar los turnos anteriores del puesto
                $sqlDel = "DELETE FROM puestos_turnos WHERE puesto_id = ?";
                $stmtDel = $conexion->prepare($sqlDel);
                $stmtDel->execute([$_POST["idPuesto"]]);

                // 3. Insertar los nuevos turnos enviados en el formulario
                $turnos = $_POST['turnos'] ?? [];
                $sqlIns = "INSERT INTO puestos_turnos (puesto_id, numero_turno, hora_entrada, hora_salida)
                       VALUES (:puesto_id, :numero_turno, :hora_entrada, :hora_salida)";
                $stmtIns = $conexion->prepare($sqlIns);

                foreach ($turnos as $turno) {
                    $entrada = $turno['hora_entrada'] ?? '';
                    $salida  = $turno['hora_salida'] ?? '';

                    if ($entrada && $salida) {
                        $stmtIns->bindParam(':puesto_id', $_POST["idPuesto"], PDO::PARAM_INT);
                        $stmtIns->bindParam(':numero_turno', $turno['numero_turno'], PDO::PARAM_INT);
                        $stmtIns->bindParam(':hora_entrada', $entrada, PDO::PARAM_STR);
                        $stmtIns->bindParam(':hora_salida', $salida, PDO::PARAM_STR);
                        $stmtIns->execute();
                    }
                }

                // 4. Confirmar transacción
                $conexion->commit();
                ToastifyController::success('Puesto y turnos actualizados correctamente');
                header("Location:?r=listado_puestos");
                exit;
            } catch (Exception $e) {
                $conexion->rollBack();
                ToastifyController::error('Error: ' . $e->getMessage());
                header("Location: ?r=editar_puesto&id=" . $_POST["idPuesto"]);
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
