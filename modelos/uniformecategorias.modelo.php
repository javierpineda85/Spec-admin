<?php

class ModeloUniformeCategorias
{
    public static function listar()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT c.*,
                    COUNT(i.id) AS cantidad_items
             FROM uniforme_categorias c
             LEFT JOIN uniforme_items i ON i.categoria_id = c.id
             GROUP BY c.id, c.nombre
             ORDER BY c.nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscar($id)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM uniforme_categorias WHERE id = :id LIMIT 1"
        );
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertar($nombre)
    {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO uniforme_categorias (nombre) VALUES (:nombre)"
        );
        return $stmt->execute([':nombre' => $nombre]);
    }

    public static function actualizar($id, $nombre)
    {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE uniforme_categorias SET nombre = :nombre WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => (int) $id,
            ':nombre' => $nombre
        ]);
    }

    public static function eliminar($id)
    {
        $stmt = Conexion::conectar()->prepare(
            "DELETE FROM uniforme_categorias WHERE id = :id"
        );
        return $stmt->execute([':id' => (int) $id]);
    }

    public static function tieneItems($id)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) FROM uniforme_items WHERE categoria_id = :id"
        );
        $stmt->execute([':id' => (int) $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
