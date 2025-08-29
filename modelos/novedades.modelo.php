
<?php class ModeloNovedades
{

    static public function mdlGuardarNovedad($tabla,  $datos)
    {
        $sql = "INSERT INTO  $tabla (vigilador_id, objetivo_id, fecha, hora, detalle, adjunto) VALUES (:vigilador_id, :objetivo_id, :fecha, :hora, :detalle, :adjunto)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':vigilador_id',  $datos['vigilador_id'], PDO::PARAM_INT);
        $stmt->bindParam(':objetivo_id',  $datos['objetivo_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha',  $datos['fecha'], PDO::PARAM_STR);
        $stmt->bindParam(':hora',  $datos['hora'], PDO::PARAM_STR);
        $stmt->bindParam(':detalle',  $datos['detalle'], PDO::PARAM_STR);
        $stmt->bindParam(':adjunto',  $datos['adjunto'], PDO::PARAM_STR);
        return  $stmt->execute() ? 'ok' : 'error';
    }

    public static function obtenerMarcacionesPorVigiladorYFecha($vigiladorId, $desde, $hasta)
    {
        $db = Conexion::conectar();
        $sql = "SELECT 
                        m.idMarcacion,
                        m.objetivo_id,
                        m.puesto_id,
                        CONCAT(v.apellido, ' ', v.nombre) AS vigilador,
                        o.nombre AS objetivo,
                        m.tipo_evento,
                        m.fecha_hora,
                        m.map_data,
                        pt.hora_entrada,
                        pt.hora_salida
                    FROM marcaciones_servicio m
                    JOIN usuarios v ON m.vigilador_id = v.idUsuario
                    JOIN objetivos o ON m.objetivo_id = o.idObjetivo
                    LEFT JOIN puestos_turnos pt ON pt.puesto_id = m.puesto_id
                    WHERE m.vigilador_id = :vigilador
                    AND DATE(m.fecha_hora) BETWEEN :desde AND :hasta
                    ORDER BY m.fecha_hora ASC
                    ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':vigilador', $vigiladorId, PDO::PARAM_INT);
        $stmt->bindParam(':desde', $desde);
        $stmt->bindParam(':hasta', $hasta);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function obtenerVigiladores()
    {
        $db = Conexion::conectar();
        $sql = "SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol = 'vigilador' ORDER BY apellido";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>