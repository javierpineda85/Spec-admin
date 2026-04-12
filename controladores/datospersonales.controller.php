<?php
require('modelos/datospersonales.modelo.php');

class DatosPersonalesController
{
    public static function vistaMisDatosPersonales()
    {
        Auth::check('datos_personales', 'verMisDatos');
        require 'vistas/paginas/datos/mis_datos_personales.php';
    }

    /**
     * Normaliza cualquier campo JSON enviado desde el formulario.
     * Acepta:
     * - array (correcto)
     * - string "[]" (lo convierte a array vacío)
     * - campo inexistente (devuelve array vacío)
     */
    private static function normalizarArray($campo)
    {
        if (!isset($_POST[$campo]) || !is_array($_POST[$campo])) {
            return json_encode([]);
        }

        // Si viene como string "[]"
        if (is_string($_POST[$campo])) {
            $decoded = json_decode($_POST[$campo], true);
            return json_encode(is_array($decoded) ? $decoded : []);
        }

        // Si viene como array
        if (is_array($_POST[$campo])) {
            return json_encode(array_values($_POST[$campo]));
        }

        return json_encode([]);
    }

    public static function guardarDatos()
    {
        Auth::check('datos_personales', 'verMisDatos');

        $usuario_id = $_POST['usuario_id'];

        $datos = [
            'usuario_id'        => $usuario_id,
            'email'             => $_POST['email'] ?? '',
            'estado_civil'      => $_POST['estado_civil'] ?? '',
            'nivel_estudio'     => $_POST['nivel_estudio'] ?? '',
            'pareja_nombre'     => $_POST['pareja_nombre'] ?? '',
            'pareja_nacimiento' => $_POST['pareja_nacimiento'] ?? null,
            'pareja_dni'        => $_POST['pareja_dni'] ?? '',

            // JSON normalizados
            'hijos'               => self::normalizarArray('hijos'),
            'hijos_adoptivos'     => self::normalizarArray('hijos_adoptivos'),
            'padres'              => self::normalizarArray('padres'),
            'hermanos'            => self::normalizarArray('hermanos'),
            'tutores_discapacidad' => self::normalizarArray('tutores_discapacidad'),
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

        header("Location: ?r=mis_datos_personales&id=" . $usuario_id);
        exit;
    }
}
