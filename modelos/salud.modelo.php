<?php
class ModeloSalud
{
    public static function buscarPorUsuario($usuario_id)
    {
        $sql = "SELECT * FROM salud WHERE usuario_id = :uid LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':uid', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertar($datos)
    {
        $sql = "INSERT INTO salud 
            (usuario_id, enfermedad_cronica, medicacion, grupo_sanguineo, tiene_obra_social, obra_social_nombre, beneficiario, nro_afiliado, vigencia_obra_social)
            VALUES 
            (:usuario_id, :enfermedad_cronica, :medicacion, :grupo_sanguineo, :tiene_obra_social, :obra_social_nombre, :beneficiario, :nro_afiliado, :vigencia_obra_social)";
        
        $stmt = Conexion::conectar()->prepare($sql);
        self::bindCampos($stmt, $datos);
        return $stmt->execute();
    }

    public static function actualizar($datos)
    {
        $sql = "UPDATE salud SET
            enfermedad_cronica = :enfermedad_cronica,
            medicacion = :medicacion,
            grupo_sanguineo = :grupo_sanguineo,
            tiene_obra_social = :tiene_obra_social,
            obra_social_nombre = :obra_social_nombre,
            beneficiario = :beneficiario,
            nro_afiliado = :nro_afiliado,
            vigencia_obra_social = :vigencia_obra_social
            WHERE usuario_id = :usuario_id";

        $stmt = Conexion::conectar()->prepare($sql);
        self::bindCampos($stmt, $datos);
        return $stmt->execute();
    }

    private static function bindCampos($stmt, $datos)
    {
        $stmt->bindParam(':usuario_id', $datos['usuario_id'], PDO::PARAM_INT);
        $stmt->bindParam(':enfermedad_cronica', $datos['enfermedad_cronica']);
        $stmt->bindParam(':medicacion', $datos['medicacion']);
        $stmt->bindParam(':grupo_sanguineo', $datos['grupo_sanguineo']);
        $stmt->bindParam(':tiene_obra_social', $datos['tiene_obra_social'], PDO::PARAM_INT);
        $stmt->bindParam(':obra_social_nombre', $datos['obra_social_nombre']);
        $stmt->bindParam(':beneficiario', $datos['beneficiario']);
        $stmt->bindParam(':nro_afiliado', $datos['nro_afiliado']);
        $stmt->bindParam(':vigencia_obra_social', $datos['vigencia_obra_social']);
    }
}
