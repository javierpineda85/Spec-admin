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
    static public function mdlResumenDiarioJornadas($tabla, $objetivoId, $desde, $hasta)
    {
        $sql = "SELECT 
                    DATE(m.fecha_hora) AS fecha,
                    SUM(CASE
                    WHEN TIME(m.fecha_hora) BETWEEN '06:00:00' AND '21:59:59' THEN 1
                        ELSE 0
                    END) AS diurnas,
                    SUM(CASE 
                        WHEN TIME(m.fecha_hora) BETWEEN '22:00:00' AND '23:59:59'
                        OR TIME(m.fecha_hora) BETWEEN '00:00:00' AND '05:59:59' THEN 1
                        ELSE 0
                    END) AS nocturnas
        FROM $tabla m
        WHERE m.tipo_evento = 'entrada'
            AND m.objetivo_id = :objetivo_id
            AND DATE(m.fecha_hora) BETWEEN :desde AND :hasta
        GROUP BY DATE(m.fecha_hora)
        ORDER BY DATE(m.fecha_hora)
        ";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':objetivo_id', $objetivoId, PDO::PARAM_INT);
        $stmt->bindParam(':desde', $desde, PDO::PARAM_STR);
        $stmt->bindParam(':hasta', $hasta, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
