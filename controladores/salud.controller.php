<?php
require_once('modelos/salud.modelo.php');
class SaludController
{
    public static function vistaMiSalud()
    {
        Auth::check('salud', 'vistaMiSalud');
        require 'vistas/paginas/datos/mi_salud.php';
    }

    public static function guardarSalud()
    {
        Auth::check('salud', 'vistaMiSalud');

        $usuario_id = $_POST['usuario_id'];

        $datos = [
            'usuario_id'           => $usuario_id,
            'enfermedad_cronica'   => $_POST['enfermedad_cronica'] ?? '',
            'medicacion'           => $_POST['medicacion'] ?? '',
            'grupo_sanguineo'      => $_POST['grupo_sanguineo'] ?? '',
            'tiene_obra_social'    => $_POST['tiene_obra_social'] ?? 0,
            'obra_social_nombre'   => $_POST['obra_social_nombre'] ?? '',
            'beneficiario'         => $_POST['beneficiario'] ?? '',
            'nro_afiliado'         => $_POST['nro_afiliado'] ?? '',
            'vigencia_obra_social' => $_POST['vigencia_obra_social'] ?? null
        ];

        $existe = ModeloSalud::buscarPorUsuario($usuario_id);
        $ok = $existe
            ? ModeloSalud::actualizar($datos)
            : ModeloSalud::insertar($datos);

        if ($ok) {
            ToastifyController::success('Datos de salud guardados correctamente');
        } else {
            ToastifyController::error('Error al guardar los datos de salud');
        }

        header("Location: ?r=mi_salud");
        exit;
    }
}
