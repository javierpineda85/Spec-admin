<?php

class ModeloUniformeEntregas
{
    /* REGISTRAR ENTREGA */
    public static function insertar($datos)
    {
        $sql = "INSERT INTO uniforme_entregas 
                (usuario_id, item_id, item_libre, categoria_id, fecha_entrega, talle, cantidad, observaciones, entregado_por)
                VALUES 
                (:usuario_id, :item_id, :item_libre, :categoria_id, :fecha_entrega, :talle, :cantidad, :observaciones, :entregado_por)";

        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(':usuario_id', $datos['usuario_id'], PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $datos['item_id'], PDO::PARAM_INT);
        $stmt->bindParam(':item_libre', $datos['item_libre']);
        $stmt->bindParam(':categoria_id', $datos['categoria_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_entrega', $datos['fecha_entrega']);
        $stmt->bindParam(':talle', $datos['talle']);
        $stmt->bindParam(':cantidad', $datos['cantidad'], PDO::PARAM_INT);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':entregado_por', $datos['entregado_por']);

        return $stmt->execute();
    }

    /* LISTAR ENTREGAS POR USUARIO */
    public static function listarPorUsuario($usuario_id)
    {
        $sql = "SELECT e.*, 
                       i.nombre AS item_nombre,
                       c.nombre AS categoria_nombre
                FROM uniforme_entregas e
                LEFT JOIN uniforme_items i ON i.id = e.item_id
                INNER JOIN uniforme_categorias c ON c.id = e.categoria_id
                WHERE e.usuario_id = :usuario_id
                ORDER BY e.fecha_entrega DESC, e.id DESC";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* BUSCAR ENTREGA POR ID */
    public static function buscar($id)
    {
        $db = new Conexion;

        $sql = " SELECT ue.*,
                    uc.nombre AS categoria_nombre,
                    ui.nombre AS item_nombre
                FROM uniforme_entregas ue
                LEFT JOIN uniforme_categorias uc ON uc.id = ue.categoria_id
                LEFT JOIN uniforme_items ui ON ui.id = ue.item_id
                WHERE ue.id = $id
                LIMIT 1
        ";

        $res = $db->consultas($sql);
        return $res[0] ?? null;
    }
}
