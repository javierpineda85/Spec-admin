<?php
//require_once 'Conexion.php';

class PermisosController
{
    public static function index()
    {
        Auth::check('permisos', 'index');
        $db = new Conexion;

        // 1) Listar todos los permisos
        $permissions = $db->consultas(
            "SELECT * FROM permissions ORDER BY controlador, accion"
        );

        // 2) Listar roles existentes (distintos en usuarios)
        $roles = $db->consultas(
            "SELECT DISTINCT rol FROM usuarios ORDER BY rol"
        );

        // 3) Rol seleccionado (GET o primer rol)
        $selectedRole = $_GET['role'] 
            ?? ($roles[0]['rol'] ?? '');

        // 4) Permisos ya asignados al rol
        $assigned = $db->consultas(
            "SELECT permission_id FROM role_permissions WHERE role = ?",
            [$selectedRole]
        );
        $assignedIds = array_column($assigned, 'permission_id');

        include 'vistas/permisos/index.php';
    }

    public static function update()
    {
        Auth::check('permisos', 'update');
        //session_start();
        $db = new Conexion;
        $role = $_POST['role'] ?? '';

        // 1) Borra todos los permisos de ese rol
        $db->consultas(
            "DELETE FROM role_permissions WHERE role = ?",
            [$role]
        );

        // 2) Inserta los nuevos seleccionados
        if (!empty($_POST['permissions'])) {
            $sql = "INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)";
            foreach ($_POST['permissions'] as $permId) {
                $db->consultas($sql, [$role, $permId]);
            }
        }

        $_SESSION['success_message'] = "Permisos actualizados correctamente.";
        header("Location: ?r=permisos&role={$role}");
        exit;
    }
}
