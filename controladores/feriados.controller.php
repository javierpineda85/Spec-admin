<?php

require_once('modelos/feriados.modelo.php');
class FeriadosController
{
    public static function ctrGuardarFeriados()
    {
        Auth::check('feriados', 'ctrGuardarFeriados');

        if (!empty($_POST['feriados']) && is_array($_POST['feriados'])) {
            $db = Conexion::conectar();
            $db->beginTransaction();

            try {
                foreach ($_POST['feriados'] as $feriado) {
                    ModeloFeriados::mdlGuardarFeriado('feriados', $feriado);
                }
                $db->commit();
                ToastifyController::success('Feriados guardados correctamente');
            } catch (Exception $e) {
                $db->rollBack();
                ToastifyController::error('Error al guardar los feriados');
            }

            header("Location: ?r=listado_feriados");
            exit;
        }
    }

    public static function ctrEditarFeriado()
    {
        Auth::check('feriados', 'ctrEditarFeriado');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['idFeriado'];
            $datos = [
                'fecha'        => $_POST['fecha'],
                'motivo'       => $_POST['motivo'],
                'tipo_feriado' => $_POST['tipo_feriado']
            ];

            $ok = ModeloFeriados::mdlActualizarFeriado('feriados', $id, $datos);

            if ($ok) {
                ToastifyController::success('Feriado actualizado');
            } else {
                ToastifyController::error('Error al actualizar el feriado');
            }


            header("Location: ?r=listado_feriados");
            exit;
        }
    }

    public static function ctrEliminarFeriado()
    {
        Auth::check('feriados', 'ctrEliminarFeriado');

        if (!empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $ok = ModeloFeriados::mdlEliminarFeriado('feriados', $id);
            if ($ok) {
                ToastifyController::success('Feriado eliminado');
            } else {
                ToastifyController::error('Error al eliminar el feriado');
            }

            header("Location: ?r=listado_feriados");
            exit;
        }
    }

    // Vista: crear_feriados
    public static function vistaCrearFeriados()
    {
        Auth::check('feriados', 'vistaCrearFeriados');
        require 'vistas/paginas/admin/feriados/crear_feriados.php';
    }

    // Vista: listado_feriados
    public static function vistaListadoFeriados()
    {
        Auth::check('feriados', 'vistaListadoFeriados');
        require 'vistas/paginas/admin/feriados/listado_feriados.php';
    }

    // Vista: editar_feriado
    public static function vistaEditarFeriado()
    {
        Auth::check('feriados', 'vistaCrearFeriado'); // o un permiso especial si lo preferís
        require 'vistas/paginas/admin/feriados/editar_feriado.php';
    }

}
