<?php

class ModeloEscaneos
{
    /**
     * Inserta un escaneo y devuelve 'ok' o mensaje de error.
     */
    static public function mdlGuardarEscaneo($tabla, $datos)
    {
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO $tabla (ronda_id, sector_id, vigilador_id) VALUES (:ronda_id, :sector_id, :vigilador_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':ronda_id',     $datos['ronda_id'],     PDO::PARAM_INT);
            $stmt->bindParam(':sector_id',    $datos['sector_id'],    PDO::PARAM_INT);
            $stmt->bindParam(':vigilador_id', $datos['vigilador_id'], PDO::PARAM_INT);
            return $stmt->execute() ? 'ok' : $stmt->errorInfo()[2];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public static function mdlObtenerListadoEscaneos($tabla)
    {
        $db = new Conexion;
        $sql = "SELECT 
                    e.idEscaneo,
                    e.fecha_hora,
                    r.puesto, 
                    p.puesto as nombre_sector,
                    u.nombre,
                    u.apellido
                FROM $tabla AS e
                LEFT JOIN rondas r ON e.ronda_id = r.idRonda
                LEFT JOIN puestos p ON e.sector_id = p.idPuesto
                LEFT JOIN usuarios u ON e.vigilador_id = u.idUsuario
                ORDER BY e.fecha_hora DESC";
        return $db->consultas($sql);
    }
}
