<?php
require_once 'modelos/conexion.php';

class CheckPermissionMiddleware
{
    /**
     * Llama antes de cargar el controlador/acción.
     * @param string $controlador
     * @param string $accion
     */
    public static function handle(string $controlador, string $accion)
    {
        //session_start();
        // Evitar bucles infinitos o validaciones innecesarias
        $rutaActual = "$controlador/$accion";
        $rutasIgnoradas = [
            'login/crtMostrarLogin',
            'login/crtProcesarLogin',
            'login/crtLogout',
            'acceso_denegado/crtAccesoDenegado'
        ];
        if (in_array($rutaActual, $rutasIgnoradas)) {
            return;
        }

        // 1) Si no hay sesión activa, redirigir al login
        if (empty($_SESSION['rol'])) {
            header('Location: ?r=login');
            exit;
        }

        $role = $_SESSION['rol'];
        $db   = new Conexion;

        // 2) Verifica si existe el permiso
        $sql = "SELECT COUNT(*) AS cnt
                  FROM role_permissions rp
                  JOIN permissions p
                    ON rp.permission_id = p.id
                 WHERE rp.role = ?
                   AND p.controlador = ?
                   AND p.accion = ?";
        
        $res = $db->consultas($sql, [$role, $controlador, $accion]);
        if (!$res || $res[0]['cnt'] == 0) {
            $_SESSION['success_error'] = "¡ACCESO RESTRINGIDO! No posee autorización para acceder al recurso. Si creés que se trata de un error, por favor contactá al proveedor del sistema.";
            header('Location: ?r=acceso_denegado');
            exit;
        }
    }
}
