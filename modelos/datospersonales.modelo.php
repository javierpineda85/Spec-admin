<?php
class ModeloDatosPersonales
{
    public static function buscarPorUsuario($usuario_id)
    {
        $sql = "SELECT * FROM datos_personales WHERE usuario_id = :uid LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':uid', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertar($datos)
    {
        $sql = "INSERT INTO datos_personales (usuario_id, email, estado_civil, pareja_nombre, pareja_nacimiento, pareja_dni, nivel_estudio, hijos, hijos_adoptivos, padres, hermanos, tutores_discapacidad) VALUES (:usuario_id, :email, :estado_civil, :pareja_nombre, :pareja_nacimiento, :pareja_dni, :nivel_estudio, :hijos, :hijos_adoptivos, :padres, :hermanos, :tutores_discapacidad)";

        $stmt = Conexion::conectar()->prepare($sql);
        self::bindCampos($stmt, $datos);
        return $stmt->execute();
    }

    public static function actualizar($datos)
    {
        $sql = "UPDATE datos_personales SET 
            email = :email,
            estado_civil = :estado_civil,
            nivel_estudio = :nivel_estudio,
            pareja_nombre = :pareja_nombre,
            pareja_nacimiento = :pareja_nacimiento,
            pareja_dni = :pareja_dni,
            hijos = :hijos,
            hijos_adoptivos = :hijos_adoptivos,
            padres = :padres,
            hermanos = :hermanos,
            tutores_discapacidad = :tutores_discapacidad
            WHERE usuario_id = :usuario_id";

        $stmt = Conexion::conectar()->prepare($sql);
        self::bindCampos($stmt, $datos);
        return $stmt->execute();
    }

    private static function bindCampos($stmt, $datos)
    {
        $stmt->bindParam(':usuario_id', $datos['usuario_id'], PDO::PARAM_INT);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':estado_civil', $datos['estado_civil']);
        $stmt->bindParam(':nivel_estudio', $datos['nivel_estudio']);
        $stmt->bindParam(':pareja_nombre', $datos['pareja_nombre']);
        $stmt->bindParam(':pareja_nacimiento', $datos['pareja_nacimiento']);
        $stmt->bindParam(':pareja_dni', $datos['pareja_dni']);
        $stmt->bindParam(':hijos', $datos['hijos']);
        $stmt->bindParam(':hijos_adoptivos', $datos['hijos_adoptivos']);
        $stmt->bindParam(':padres', $datos['padres']);
        $stmt->bindParam(':hermanos', $datos['hermanos']);
        $stmt->bindParam(':tutores_discapacidad', $datos['tutores_discapacidad']);
    }
}
