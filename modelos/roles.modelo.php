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
    static public function mdlObtenerRolesActivos()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT id, nombre, nivel
            FROM roles
            WHERE activo = 1
              AND nombre <> 'Programador'
            ORDER BY nivel ASC, nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function crear(string $nombre, ?string $alias, string $tipo = 'fijo', int $nivel = 1, string $categoria = 'operativo', int $reservado = 0)
    {
        if (mb_strtolower(trim($nombre)) === 'programador') {
            throw new Exception('El rol "Programador" está reservado.');
        }

        $db = new Conexion;
        return $db->consultas(
            "INSERT INTO roles (nombre, alias, tipo, nivel, categoria, reservado, activo) 
         VALUES (?, ?, ?, ?, ?, ?, 1)",
            [$nombre, $alias, $tipo, $nivel, $categoria, $reservado]
        );
    }

    public static function actualizar(int $id, string $nombre, ?string $alias, string $tipo = 'fijo', int $activo = 1, int $nivel = 1, string $categoria = 'operativo', int $reservado = 0)
    {
        $rol = self::obtenerPorId($id);
        if (!$rol) throw new Exception('Rol no encontrado');

        // Si el rol ya es reservado y no es programador, no permitir edición
        if ((int)$rol['reservado'] === 1 && !(isset($_SESSION['nivel']) && $_SESSION['nivel'] == 99 && $_SESSION['reservado'] == 1)) {
            throw new Exception('Este rol es reservado y no puede editarse.');
        }

        if (mb_strtolower(trim($nombre)) === 'programador') {
            throw new Exception('El rol "Programador" está reservado.');
        }

        $db = new Conexion;
        return $db->consultas(
            "UPDATE roles 
         SET nombre=?, alias=?, tipo=?, activo=?, nivel=?, categoria=?, reservado=? 
         WHERE id=?",
            [$nombre, $alias, $tipo, $activo, $nivel, $categoria, $reservado, $id]
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
        // 1) Obtener el row del rol (ID, nombre, etc.)
        $rol = self::obtenerPorNombre($nombreRol);
        if (!$rol) {
            throw new Exception("Rol “{$nombreRol}” no encontrado.");
        }
        $idRol = (int)$rol['id'];

        // 2) Validación especial Programador (opcional)
        if (
            strtolower($nombreRol) === 'programador'
            && !Auth::isSuperRole($_SESSION['rol'] ?? null)
        ) {
            throw new Exception('No autorizado para modificar permisos del rol Programador.');
        }

        $db = new Conexion();

        // 3) Borrar asignaciones antiguas vía role_id
        $db->consultas(
            "DELETE FROM role_permissions WHERE role_id = ?",
            [$idRol]
        );

        // 4) Insertar las nuevas también via role_id
        foreach ($permissionIds as $pid) {
            $pid = (int)$pid;
            $db->consultas(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
                [$idRol, $pid]
            );
        }

        // 5) Invalidate cache en sesión
        unset($_SESSION['permisos_usuario']);
    }
}
