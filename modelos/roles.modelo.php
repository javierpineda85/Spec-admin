<?php
class ModeloRoles
{
    public static function listar(bool $soloActivos = true)
    {
        $db = new Conexion;
        $sql = "SELECT id, nombre, alias, tipo, reservado, activo FROM roles";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY nombre";
        return $db->consultas($sql);
    }

    public static function obtenerPorId(int $id)
    {
        $db = new Conexion;
        $res = $db->consultas("SELECT * FROM roles WHERE id = ?", [$id]);
        return $res[0] ?? null;
    }

    public static function obtenerPorNombre(string $nombre)
    {
        $db = new Conexion;
        $res = $db->consultas("SELECT * FROM roles WHERE nombre = ?", [$nombre]);
        return $res[0] ?? null;
    }

    public static function crear(string $nombre, ?string $alias, string $tipo = 'fijo')
    {
        if (mb_strtolower(trim($nombre)) === 'programador') {
            throw new Exception('El rol "Programador" está reservado.');
        }
        $db = new Conexion;
        return $db->consultas(
            "INSERT INTO roles (nombre, alias, tipo, reservado, activo) VALUES (?, ?, ?, 0, 1)",
            [$nombre, $alias, $tipo]
        );
    }

    public static function actualizar(int $id, string $nombre, ?string $alias, string $tipo = 'fijo', int $activo = 1)
    {
        $rol = self::obtenerPorId($id);
        if (!$rol) throw new Exception('Rol no encontrado');

        if ((int)$rol['reservado'] === 1) {
            throw new Exception('Este rol es reservado y no puede editarse.');
        }
        if (mb_strtolower(trim($nombre)) === 'programador') {
            throw new Exception('El rol "Programador" está reservado.');
        }

        $db = new Conexion;
        return $db->consultas(
            "UPDATE roles SET nombre=?, alias=?, tipo=?, activo=? WHERE id=?",
            [$nombre, $alias, $tipo, $activo, $id]
        );
    }

    public static function desactivar(int $id)
    {
        $rol = self::obtenerPorId($id);
        if (!$rol) throw new Exception('Rol no encontrado');
        if ((int)$rol['reservado'] === 1) throw new Exception('Rol reservado no puede desactivarse.');
        $db = new Conexion;
        return $db->consultas("UPDATE roles SET activo=0 WHERE id=?", [$id]);
    }

    public static function asignarPermisos(string $nombreRol, array $permissionIds)
    {
        // Solo Programador puede tocar Programador
        if (mb_strtolower($nombreRol) === 'programador' && !Auth::isSuperRole($_SESSION['rol'] ?? null)) {
            throw new Exception('No autorizado para modificar permisos del rol Programador.');
        }

        $db = new Conexion;
        // Limpiar asignaciones actuales
        $db->consultas("DELETE FROM role_permissions WHERE role = ?", [$nombreRol]);

        // Insert masivo
        if (!empty($permissionIds)) {
            foreach ($permissionIds as $pid) {
                $db->consultas("INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)", [$nombreRol, (int)$pid]);
            }
        }
        // Invalida cache de permisos en sesión del usuario actual (solo si aplica)
        unset($_SESSION['permisos_usuario']);
    }
}
