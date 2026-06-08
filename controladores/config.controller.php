<?php
require_once('modelos/config.modelo.php');
class ConfigController
{
    public static function vistaPanel()
    {

        // Solo rol programador
        Auth::check('roles', 'vistaConfigSistema');

        require_once('modelos/config.modelo.php');
        $configModel = new Configuracion();
        $config = [];

        foreach ($configModel->obtenerTodo() as $row) {
            $config[$row['clave']] = $row['valor'];
        }

        // Renderizar la vista
        require_once __DIR__ . '/../vistas/paginas/config/configuracion.php';
    }

    public static function ctrGuardarConfig()
    {
        Auth::check('roles', 'ctrGuardarConfig');

        require_once('modelos/config.modelo.php');
        $configModel = new Configuracion();

        foreach ($_POST as $clave => $valor) {
            $valor = trim($valor);
            if ($valor !== '') {
                $configModel->guardar($clave, $valor);
            }
        }

        $_SESSION['success_message'] = "Configuración actualizada correctamente.";
        header("Location: ?r=configuracion/panel");
        exit;
    }
}
