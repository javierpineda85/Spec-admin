<?php
//require_once 'Conexion.php';

class PermisosController
{
    public static function index()
    {
        Auth::check('permisos', 'index');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $db = new Conexion;

        // 1) Listar permisos (con alias/descripcion si existen)
        $permissions = $db->consultas(
            "SELECT id, controlador, accion, alias, descripcion
               FROM permissions
           ORDER BY controlador, accion"
        );

        // 2) Listar roles desde la tabla 'roles' (fallback a usuarios si aún no migraste)
        $roles = $db->consultas("SELECT nombre AS rol, reservado FROM roles WHERE activo=1 ORDER BY nombre");
        if (!$roles) {
            // Fallback temporal (si aún no creaste 'roles')
            $roles = $db->consultas("SELECT DISTINCT rol, 0 AS reservado FROM usuarios WHERE rol<>'' ORDER BY rol");
        }

        // 3) Rol seleccionado
        $selectedRole = $_GET['role'] ?? ($roles[0]['rol'] ?? '');

        // 4) Datos del rol seleccionado (para saber si es reservado)
        $rol = $db->consultas("SELECT * FROM roles WHERE nombre = ? LIMIT 1", [$selectedRole])[0] ?? [
            'nombre' => $selectedRole,
            'reservado' => (mb_strtolower($selectedRole) === 'programador') ? 1 : 0
        ];

        // 5) Permisos ya asignados al rol
        $assigned = $db->consultas(
            "SELECT permission_id FROM role_permissions WHERE role = ?",
            [$selectedRole]
        );
        $assignedIds = array_map('intval', array_column($assigned, 'permission_id'));

        // Compat con la vista nueva (usa estos nombres si existen)
        $permisos     = $permissions;   // alias para la vista
        $idsAsignados = $assignedIds;   // alias para la vista

        // 6) Incluir NUEVA vista
        include 'vistas/paginas/roles/gestionar_roles.php';
    }
    public static function update()
    {
        Auth::check('permisos', 'update');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $db   = new Conexion;

        // Aceptar ambos nombres por compatibilidad con la nueva vista
        $role = $_POST['rol'] ?? $_POST['role'] ?? '';
        $permIds = $_POST['permission_ids'] ?? $_POST['permissions'] ?? [];

        // Blindaje: sólo Programador puede modificar el rol Programador
        if (mb_strtolower($role) === 'programador' && !Auth::isSuperRole($_SESSION['rol'] ?? null)) {
            $_SESSION['error_message'] = 'No está autorizado para modificar permisos del rol Programador.';
            header('Location: ?r=permisos&role=' . urlencode($role));
            exit;
        }

        // 1) Borrar permisos actuales del rol
        $db->consultas("DELETE FROM role_permissions WHERE role = ?", [$role]);

        // 2) Insertar los seleccionados
        if (!empty($permIds)) {
            $sql = "INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)";
            foreach ($permIds as $pid) {
                $db->consultas($sql, [$role, (int)$pid]);
            }
        }

        // Invalidar cache de permisos en sesión (si existiera)
        unset($_SESSION['permisos_usuario']);

        ToastifyController::success('Permisos actualizados correctamente');

        // Redirigimos a la RUTA NUEVA (si querés mantener la vieja, cambia al ?r=permisos&role=...)
        header('Location: ?r=roles/permisos&rol=' . urlencode($role));
        exit;
    }
}
