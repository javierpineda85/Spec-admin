<?php
require_once('modelos/uniformes.modelo.php');
class UniformesController
{
    public static function vistaMiUniforme()
    {
        Auth::check('uniformes', 'vistaMiUniforme');
        require 'vistas/paginas/datos/mi_uniforme.php';
    }

    public static function guardarUniforme()
    {
        Auth::check('uniformes', 'vistaMiUniforme');

        $usuario_id = $_POST['usuario_id'];

        $datos = [
            'usuario_id'     => $usuario_id,
            'talle_pantalon' => $_POST['talle_pantalon'],
            'talle_remera'   => $_POST['talle_remera'],
            'talle_polar'    => $_POST['talle_polar'],
            'talle_campera'  => $_POST['talle_campera'],
            'talle_calzado'  => $_POST['talle_calzado']
        ];

        $existe = ModeloUniformes::buscarPorUsuario($usuario_id);
        $ok = $existe
            ? ModeloUniformes::actualizar($datos)
            : ModeloUniformes::insertar($datos);

        if ($ok) {
            ToastifyController::success('Talles guardados correctamente');
        } else {
            ToastifyController::error('Error al guardar los talles');
        }

        header("Location: ?r=mi_uniforme");
        exit;
    }
    public static function vistaListadoUniformes()
{
    Auth::check('uniformes', 'vistaListadoUniformes');

    $uniformes = ModeloUniformes::mdlListarUniformes();

    include 'vistas/paginas/admin/uniformes/listado_uniformes.php';
}

}
