<?php
class ModeloCronograma
{

    static public function mdlSubirCrono($tabla, $datos)
    {
        $conexion = Conexion::conectar();
        $registro = $conexion->prepare("INSERT INTO $tabla (objetivo_id, fechaCarga, imgCrono) 
        VALUES (:objetivo_id, :fechaCarga, :img_crono)");

        $registro->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT);
        $registro->bindParam(":fechaCarga", $datos["fechaCarga"], PDO::PARAM_STR);
        $registro->bindParam(":img_crono", $datos["img_crono"], PDO::PARAM_STR);

        if ($registro->execute()) {
            return "ok";
        } else {
            print_r($registro->errorInfo());
            return "error";
        }

        $registro->closeCursor();
        $registro = null;
    }
    /*Es funcion permite calcular la cantidad de jornadas trabajadas en un objetivo. Vista: resumen_diario_jornadas*/
    static public function mdlResumenDiarioJornadas($objetivoId, $desde, $hasta)
    {
        $sql = "SELECT 
                DATE(m.fecha_hora) AS fecha,
                SUM(CASE WHEN t.codigo_turno = 'D' THEN 1 ELSE 0 END) AS diurnas,
                SUM(CASE WHEN t.codigo_turno = 'N' THEN 1 ELSE 0 END) AS nocturnas
            FROM marcaciones_servicio m
            INNER JOIN turnos t 
                ON t.usuario_id = m.vigilador_id
                AND t.objetivo_id = m.objetivo_id
                AND t.fecha = DATE(m.fecha_hora)
            WHERE m.tipo_evento = 'entrada'
              AND m.objetivo_id = :objetivo_id
              AND DATE(m.fecha_hora) BETWEEN :desde AND :hasta
            GROUP BY DATE(m.fecha_hora)
            ORDER BY DATE(m.fecha_hora)";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':objetivo_id', $objetivoId, PDO::PARAM_INT);
        $stmt->bindParam(':desde', $desde, PDO::PARAM_STR);
        $stmt->bindParam(':hasta', $hasta, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
