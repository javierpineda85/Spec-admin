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
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute([$role, $controlador, $accion]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res || $res['cnt'] == 0) {
            // acceso denegado
            $_SESSION['success_error']= "¡ACESSO RESTRINGIDO!. No posee autorización para acceder al recurso. Si crees que se trata de un error, por favor contacta al proveedor del sistema";
            header('Location: ?r=acceso_denegado');
            exit;
        }
    }
}
