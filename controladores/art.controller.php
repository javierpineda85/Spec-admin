<?php
require_once('modelos/art.modelo.php');
class ArtController
{
    public static function ctrGuardarArt()
    {
        Auth::check('art', 'ctrGuardarArt');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'razon_social'        => $_POST['razon_social'] ?? '',
                'cuit_empresa'        => $_POST['cuit_empresa'] ?? '',
                'telefono_empresa'    => $_POST['telefono_empresa'] ?? '',
                'empresa_aseguradora' => $_POST['empresa_aseguradora'] ?? '',
                'cuit_aseguradora'    => $_POST['cuit_aseguradora'] ?? '',
                'nro_poliza'          => $_POST['nro_poliza'] ?? '',
                'telefono_aseguradora' => $_POST['telefono_aseguradora'] ?? ''
            ];

            $idArt = ModeloArt::mdlGuardarArt('art', $datos);
            if ($idArt == 'ok') {
                ToastifyController::success('ART guardada correctamente');
            } else {
                ToastifyController::error('No se pudo guardar la ART');
            }

            header('Location: ' . $_SERVER['REQUEST_URI']); // Redirige a sí misma
            exit;
        }
    }
    public static function ctrEditarArt()
    {
        Auth::check('art', 'ctrEditarArt');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idArt'])) {
            $idArt = intval($_POST['idArt']);

            $datos = [
                'idArt'               => $idArt,
                'razon_social'        => $_POST['razon_social'] ?? '',
                'cuit_empresa'        => $_POST['cuit_empresa'] ?? '',
                'telefono_empresa'    => $_POST['telefono_empresa'] ?? '',
                'empresa_aseguradora' => $_POST['empresa_aseguradora'] ?? '',
                'cuit_aseguradora'    => $_POST['cuit_aseguradora'] ?? '',
                'nro_poliza'          => $_POST['nro_poliza'] ?? '',
                'telefono_aseguradora' => $_POST['telefono_aseguradora'] ?? ''
            ];

            $resultado = ModeloArt::mdlEditarArt('art', $datos);

            if ($resultado === 'ok') {
                ToastifyController::success('ART actualizada correctamente.');
            } else {
                ToastifyController::error('No se pudo actualizar la ART.');
            }

            header('Location: ?r=listado_art');
            exit;
        }
    }

    // Vista: credencial (usuario)
    public static function vistaCredencialArt()
    {
        Auth::check('art', 'vistaCredencialArt');
        require 'vistas/paginas/admin/art/credencial_art.php';
    }

    // Vista: listado (admin)
    public static function vistaListadoArt()
    {
        Auth::check('art', 'vistaListadoArt');
        require 'vistas/paginas/admin/art/listado_art.php';
    }

    // Vista: crear (admin)
    public static function vistaCrearArt()
    {
        Auth::check('art', 'vistaCrearArt');
        require 'vistas/paginas/admin/art/crear_art.php';
    }

    // Vista: editar (admin)
    public static function vistaEditarArt()
    {
        Auth::check('art', 'vistaEditarArt');
        require 'vistas/paginas/admin/art/editar_art.php';
    }
}
