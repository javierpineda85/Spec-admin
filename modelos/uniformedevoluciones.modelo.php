<?php

class ModeloUniformeDevoluciones
{
    /* REGISTRAR DEVOLUCIÓN */
    public static function insertar($datos)
    {
        $sql = "INSERT INTO uniforme_devoluciones 
                (entrega_id, fecha_devolucion, estado_devolucion, recibido_por, observaciones)
                VALUES 
                (:entrega_id, :fecha_devolucion, :estado_devolucion, :recibido_por, :observaciones)";

        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(':entrega_id', $datos['entrega_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_devolucion', $datos['fecha_devolucion']);
        $stmt->bindParam(':estado_devolucion', $datos['estado_devolucion']);
        $stmt->bindParam(':recibido_por', $datos['recibido_por']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);

        return $stmt->execute();
    }

    /* BUSCAR DEVOLUCIÓN POR ENTREGA
       (cada entrega puede tener 0 o 1 devolución) */
    public static function buscarPorEntrega($entrega_id)
    {
        $sql = "SELECT * FROM uniforme_devoluciones 
                WHERE entrega_id = :entrega_id 
                LIMIT 1";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':entrega_id', $entrega_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* LISTAR TODAS LAS DEVOLUCIONES (opcional) */
    public static function listar()
    {
        $sql = "SELECT d.*, 
                       e.usuario_id,
                       e.item_id,
                       e.item_libre,
                       e.fecha_entrega
                FROM uniforme_devoluciones d
                INNER JOIN uniforme_entregas e ON e.id = d.entrega_id
                ORDER BY d.fecha_devolucion DESC";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}