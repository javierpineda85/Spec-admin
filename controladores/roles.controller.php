<?php
require_once('modelos/roles.modelo.php');
class RolesController
{
    public static function vistaListadoRoles()
    {
        Auth::check('roles', 'vistaListadoRoles');
        $roles = ModeloRoles::listar(false);
        include 'vistas/paginas/roles/listado_roles.php';
    }

    public static function vistaCrearRol()
    {
        Auth::check('roles', 'vistaCrearRol');
        include 'vistas/paginas/roles/crear_rol.php';
    }

    public static function ctrGuardarRol()
    {
        Auth::check('roles', 'ctrGuardarRol');
        try {
            $nombre = trim($_POST['nombre'] ?? '');
            $alias  = trim($_POST['alias'] ?? '') ?: null;
            $tipo   = $_POST['tipo'] ?? 'fijo';

            if (!$nombre) throw new Exception('El nombre de rol es obligatorio');
            ModeloRoles::crear($nombre, $alias, $tipo);

            ToastifyController::success('Rol creado');
            header('Location: ?r=roles/listado');
        } catch (Exception $e) {
            ToastifyController::error($e->getMessage());
            header('Location: ?r=roles/crear');
        }
    }

    public static function vistaEditarRol()
    {
        Auth::check('roles', 'vistaEditarRol');
        $id  = (int)($_GET['id'] ?? 0);
        $rol = ModeloRoles::obtenerPorId($id);
        if (!$rol) {
            ToastifyController::error('Rol no encontrado');
            header('Location: ?r=roles/listado');
            return;
        }
        include 'vistas/paginas/roles/editar_rol.php';
    }

    public static function ctrActualizarRol()
    {
        Auth::check('roles', 'ctrActualizarRol');

        try {
            $id     = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $alias  = trim($_POST['alias'] ?? '') ?: null;
            $tipo   = $_POST['tipo'] ?? 'fijo';
            $activo = isset($_POST['activo']) ? 1 : 0;

            if (!$id || !$nombre) throw new Exception('Datos incompletos');
            ModeloRoles::actualizar($id, $nombre, $alias, $tipo, $activo);

            ToastifyController::success('Rol actualizado');
            header('Location: ?r=roles/listado');
        } catch (Exception $e) {
            ToastifyController::error($e->getMessage());
            header('Location: ?r=roles/listado');
        }
    }

    public static function ctrDesactivarRol()
    {
        Auth::check('roles', 'ctrDesactivarRol');
        try {
            $id = (int)($_POST['id'] ?? 0);
            ModeloRoles::desactivar($id);
            ToastifyController::success('Rol desactivado');
        } catch (Exception $e) {
            ToastifyController::error($e->getMessage());
        }
        header('Location: ?r=roles/listado');
    }

    // === Permisos del rol ===

    public static function vistaPermisosRol()
    {
        Auth::check('roles', 'vistaPermisosRol');

        if (session_status() === PHP_SESSION_NONE) session_start();

        $db = new Conexion;

        // 1) Cargar lista de roles (tabla roles). Fallback: desde usuarios si hiciera falta.
        $roles = ModeloRoles::listar(true); // solo activos
        if (!$roles || count($roles) === 0) {
            $roles = $db->consultas("SELECT DISTINCT rol AS nombre, 0 AS reservado FROM usuarios WHERE rol <> '' ORDER BY rol");
        }

        // 2) Rol seleccionado: GET o primero de la lista
        $nombreRol = $_GET['rol'] ?? '';
        if ($nombreRol === '' && !empty($roles)) {
            $nombreRol = $roles[0]['nombre'] ?? ($roles[0]['rol'] ?? '');
        }

        // 3) Obtener datos del rol (o construir básico si no está en tabla roles)
        $rol = ModeloRoles::obtenerPorNombre($nombreRol);
        if (!$rol) {
            $rol = [
                'nombre'    => $nombreRol,
                'reservado' => (mb_strtolower($nombreRol) === 'programador') ? 1 : 0
            ];
        }

        // 4) Cargar permisos
        $permisos = $db->consultas(
            "SELECT id, controlador, accion, descripcion
           FROM permissions
       ORDER BY controlador, accion"
        );

        // 5) Permisos asignados al rol
        $asignados = $db->consultas(
            "SELECT permission_id FROM role_permissions WHERE role = ?",
            [$nombreRol]
        );
        $idsAsignados = array_map('intval', array_column($asignados, 'permission_id'));

        // 6) Pasar todo a la vista
        include 'vistas/paginas/roles/gestionar_roles.php';
    }

    public static function ctrGuardarPermisosRol()
    {
        Auth::check('roles', 'ctrGuardarPermisosRol');
        try {
            $nombreRol = $_POST['rol'] ?? '';
            $permissionIds = array_map('intval', $_POST['permission_ids'] ?? []);
            if (!$nombreRol) throw new Exception('Rol no especificado');

            ModeloRoles::asignarPermisos($nombreRol, $permissionIds);

            ToastifyController::success('Permisos actualizados');
            header('Location: ?r=roles/permisos&rol=' . urlencode($nombreRol));
        } catch (Exception $e) {
            ToastifyController::error($e->getMessage());
            header('Location: ?r=roles/listado');
        }
    }
}
