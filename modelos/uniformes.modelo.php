<?php
class ModeloUniformes
{
    public static function buscarPorUsuario($usuario_id)
    {
        $sql = "SELECT * FROM uniformes WHERE usuario_id = :uid LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':uid', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertar($datos)
    {
        $sql = "INSERT INTO uniformes 
            (usuario_id, talle_pantalon, talle_remera, talle_polar, talle_campera, talle_calzado)
            VALUES 
            (:usuario_id, :talle_pantalon, :talle_remera, :talle_polar, :talle_campera, :talle_calzado)";
        
        $stmt = Conexion::conectar()->prepare($sql);
        self::bind($stmt, $datos);
        return $stmt->execute();
    }

    public static function actualizar($datos)
    {
        $sql = "UPDATE uniformes SET
            talle_pantalon = :talle_pantalon,
            talle_remera   = :talle_remera,
            talle_polar    = :talle_polar,
            talle_campera  = :talle_campera,
            talle_calzado  = :talle_calzado
            WHERE usuario_id = :usuario_id";

        $stmt = Conexion::conectar()->prepare($sql);
        self::bind($stmt, $datos);
        return $stmt->execute();
    }

    private static function bind($stmt, $d)
    {
        $stmt->bindParam(':usuario_id', $d['usuario_id'], PDO::PARAM_INT);
        $stmt->bindParam(':talle_pantalon', $d['talle_pantalon']);
        $stmt->bindParam(':talle_remera', $d['talle_remera']);
        $stmt->bindParam(':talle_polar', $d['talle_polar']);
        $stmt->bindParam(':talle_campera', $d['talle_campera']);
        $stmt->bindParam(':talle_calzado', $d['talle_calzado']);
    }
}
