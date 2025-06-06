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
    static public function mdlResumenDiarioJornadas($tabla, $objetivoId, $desde, $hasta)
    {
        $sql = "SELECT t.fecha,
            SUM(CASE WHEN t.turno = 'Diurno' THEN 1 ELSE 0 END)   AS diurnas,
            SUM(CASE WHEN t.turno = 'Nocturno' THEN 1 ELSE 0 END) AS nocturnas
          FROM $tabla t
          WHERE t.objetivo_id = :objetivo_id
            AND t.fecha BETWEEN :desde AND :hasta
            AND t.tipo_jornada  = 'Normal'
          GROUP BY t.fecha
          ORDER BY t.fecha
        ";
        $stmt = Conexion::conectar()
                      ->prepare($sql);
        $stmt->bindParam(':objetivo_id', $objetivoId, PDO::PARAM_INT);
        $stmt->bindParam(':desde',        $desde,       PDO::PARAM_STR);
        $stmt->bindParam(':hasta',        $hasta,       PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
