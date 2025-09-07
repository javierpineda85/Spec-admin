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
        //Auth::check('roles', 'ctrGuardarRol');
        Auth::check('roles', 'vistaCrearRol');
        try {
            $nombre    = trim($_POST['nombre'] ?? '');
            $alias     = trim($_POST['alias'] ?? '') ?: null;
            $tipo      = $_POST['tipo'] ?? 'fijo';
            $categoria = $_POST['categoria'] ?? '';

            if (!$nombre || !$categoria) {
                throw new Exception('El nombre y la categoría son obligatorios');
            }

            // Mapeo automático de nivel según categoría
            $nivelesPorCategoria = [
                'operativo'      => 1,
                'referente'      => 2,
                'supervisor'     => 3,
                'administrativo' => 4,
                'direccion'      => 5,
                'reservado'      => 99
            ];

            // Si intentan mandar "reservado" desde el frontend y no es programador, forzar error
            $soyProgramador = isset($_SESSION['nivel']) && $_SESSION['nivel'] == 99 && $_SESSION['reservado'] == 1;
            if ($categoria === 'reservado' && !$soyProgramador) {
                throw new Exception('No tienes permiso para crear roles reservados');
            }

            $nivel = $nivelesPorCategoria[$categoria] ?? 1;
            $reservado = ($categoria === 'reservado') ? 1 : 0;

            ModeloRoles::crear($nombre, $alias, $tipo, $nivel, $categoria, $reservado);

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
        //Auth::check('roles', 'ctrActualizarRol');
        Auth::check('roles', 'vistaEditarRol');
        try {
            $id        = (int)($_POST['id'] ?? 0);
            $nombre    = trim($_POST['nombre'] ?? '');
            $alias     = trim($_POST['alias'] ?? '') ?: null;
            $tipo      = $_POST['tipo'] ?? 'fijo';
            $activo    = isset($_POST['activo']) ? 1 : 0;
            $categoria = $_POST['categoria'] ?? '';

            if (!$id || !$nombre || !$categoria) {
                throw new Exception('Datos incompletos');
            }

            // Mapeo automático de nivel según categoría
            $nivelesPorCategoria = [
                'operativo'      => 1,
                'referente'      => 2,
                'supervisor'     => 3,
                'administrativo' => 4,
                'direccion'      => 5,
                'reservado'      => 99
            ];

            $soyProgramador = isset($_SESSION['nivel']) && $_SESSION['nivel'] == 99 && $_SESSION['reservado'] == 1;
            if ($categoria === 'reservado' && !$soyProgramador) {
                throw new Exception('No tienes permiso para asignar categoría reservada');
            }

            $nivel = $nivelesPorCategoria[$categoria] ?? 1;
            $reservado = ($categoria === 'reservado') ? 1 : 0;

            ModeloRoles::actualizar($id, $nombre, $alias, $tipo, $activo, $nivel, $categoria, $reservado);

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

        // Obtener el ID del rol para usarlo en role_permissions
        $idRol = $rol['id'] ?? null;
        if (!$idRol) {
            // Intentar buscarlo en la tabla roles por nombre
            $rolDb = $db->consultas("SELECT id FROM roles WHERE nombre = ?", [$nombreRol]);
            $idRol = $rolDb[0]['id'] ?? null;
        }

        // 4) Cargar permisos
        $permisos = $db->consultas(
            "SELECT id, 
                    controlador, 
                    accion, 
                    COALESCE(alias, descripcion) AS alias, 
                    descripcion
                    FROM permissions
                    ORDER BY controlador, accion"
        );

        // 5) Permisos asignados al rol (usando role_id)
        $idsAsignados = [];
        if ($idRol) {
            $asignados = $db->consultas(
                "SELECT permission_id FROM role_permissions WHERE role_id = ?",
                [$idRol]
            );
            if (is_array($asignados) && !empty($asignados)) {
                $idsAsignados = array_map('intval', array_column($asignados, 'permission_id'));
            }
        }

        // 6) Pasar todo a la vista
        include 'vistas/paginas/roles/gestionar_roles.php';
    }

    public static function ctrGuardarPermisosRol()
    {
        Auth::check('roles', 'ctrGuardarPermisosRol');
        try {
            $nombreRol = $_POST['rol'] ?? '';
            $permissionIds = array_map('intval', $_POST['permission_ids'] ?? []);

            if (!$nombreRol) {
                throw new Exception('Rol no especificado');
            }

            $db = new Conexion();

            // Obtener el ID del rol a partir del nombre
            $rolDb = $db->consultas("SELECT id FROM roles WHERE nombre = ?", [$nombreRol]);
            $idRol = $rolDb[0]['id'] ?? null;

            if (!$idRol) {
                throw new Exception('Rol no encontrado en la base de datos');
            }

            // Limpiar permisos actuales del rol
            $db->consultas("DELETE FROM role_permissions WHERE role_id = ?", [$idRol]);

            // Insertar nuevos permisos
            if (!empty($permissionIds)) {
                foreach ($permissionIds as $pid) {
                    $db->consultas(
                        "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
                        [$idRol, $pid]
                    );
                }
            }

            ToastifyController::success('Permisos actualizados');
            header('Location: ?r=roles/permisos&rol=' . urlencode($nombreRol));
            exit;
        } catch (Exception $e) {
            ToastifyController::error($e->getMessage());
            header('Location: ?r=roles/listado');
            exit;
        }
        unset($_SESSION['permisos_usuario']);
        Auth::reloadPermisosUsuario();
    }
}
