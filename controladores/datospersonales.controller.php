<?php
require('modelos/datospersonales.modelo.php');

class DatosPersonalesController
{
    public static function vistaMisDatosPersonales()
    {
        Auth::check('datos_personales', 'verMisDatos');
        require 'vistas/paginas/datos/mis_datos_personales.php';
    }

    public static function guardarDatos()
    {
        Auth::check('datos_personales', 'verMisDatos');

        $usuario_id = $_POST['usuario_id'];

        $datos = [
            'usuario_id'        => $usuario_id,
            'email'             => $_POST['email'] ?? '',
            'estado_civil'      => $_POST['estado_civil'] ?? '',
            'pareja_nombre'     => $_POST['pareja_nombre'] ?? '',
            'pareja_nacimiento' => $_POST['pareja_nacimiento'] ?? null,
            'pareja_dni'        => $_POST['pareja_dni'] ?? '',
            'hijos'             => isset($_POST['hijos']) ? json_encode(array_values($_POST['hijos'])) : null,
            'hijos_adoptivos'   => isset($_POST['hijos_adoptivos']) ? json_encode(array_values($_POST['hijos_adoptivos'])) : null,
            'padres'            => isset($_POST['padres']) ? json_encode(array_values($_POST['padres'])) : null,
            'hermanos'          => isset($_POST['hermanos']) ? json_encode(array_values($_POST['hermanos'])) : null,
            'tutores_discapacidad' => isset($_POST['tutores_discapacidad']) ? json_encode(array_values($_POST['tutores_discapacidad'])) : null,
        ];

        $existe = ModeloDatosPersonales::buscarPorUsuario($usuario_id);

        $ok = $existe
            ? ModeloDatosPersonales::actualizar($datos)
            : ModeloDatosPersonales::insertar($datos);

        if ($ok) {
            ToastifyController::success('Datos guardados correctamente');
        } else {
            ToastifyController::error('Error al guardar los datos');
        }

        header("Location: ?r=mis_datos_personales");
        exit;
    }


}
