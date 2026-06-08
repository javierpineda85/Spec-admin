<?php

class ModeloUniformeItems
{
    /* LISTAR ITEMS (solo activos o todos)  */
    public static function listar($soloActivos = true)
    {
        $sql = "SELECT i.*, c.nombre AS categoria 
                FROM uniforme_items i
                INNER JOIN uniforme_categorias c ON c.id = i.categoria_id";

        if ($soloActivos) {
            $sql .= " WHERE i.estado = 'activo'";
        }

        $sql .= " ORDER BY c.nombre, i.nombre";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* BUSCAR ITEM POR ID */
    public static function buscar($id)
    {
        $sql = "SELECT * FROM uniforme_items WHERE id = :id LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* INSERTAR ITEM */
    public static function insertar($datos)
    {
        $sql = "INSERT INTO uniforme_items (categoria_id, nombre, descripcion, estado)
                VALUES (:categoria_id, :nombre, :descripcion, :estado)";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':categoria_id', $datos['categoria_id'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':estado', $datos['estado']);
        return $stmt->execute();
    }

    /* ACTUALIZAR ITEM*/
    public static function actualizar($datos)
    {
        $sql = "UPDATE uniforme_items SET
                    categoria_id = :categoria_id,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    estado = :estado
                WHERE id = :id";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':id', $datos['id'], PDO::PARAM_INT);
        $stmt->bindParam(':categoria_id', $datos['categoria_id'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':estado', $datos['estado']);
        return $stmt->execute();
    }

    /* CAMBIAR ESTADO (activar / desactivar)*/
    public static function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE uniforme_items SET estado = :estado WHERE id = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}