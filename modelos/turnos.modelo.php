<?php
class ModeloTurnos
{
   
    static public function mdlGuardarTurno($tabla, $datos)
    {
     
        $sql = "INSERT INTO $tabla (objetivo_id, puesto_id, fecha, turno, vigilador_id, actividad, entrada, salida, color) VALUES (:objetivo_id, :puesto_id, :fecha, :turno, :vigilador_id, :actividad, :entrada, :salida, :color)";

        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":objetivo_id",  $datos["objetivo_id"],  PDO::PARAM_INT);
        $stmt->bindParam(":puesto_id",    $datos["puesto_id"],    PDO::PARAM_INT);
        $stmt->bindParam(":fecha",        $datos["fecha"],        PDO::PARAM_STR);
        $stmt->bindParam(":turno",        $datos["turno"],        PDO::PARAM_STR);
        $stmt->bindParam(":vigilador_id", $datos["vigilador_id"], PDO::PARAM_INT);
        $stmt->bindParam(":actividad",    $datos["actividad"],    PDO::PARAM_STR);
        $stmt->bindParam(":entrada",      $datos["entrada"],      PDO::PARAM_STR);
        $stmt->bindParam(":salida",       $datos["salida"],       PDO::PARAM_STR);
        $stmt->bindParam(":color",        $datos["color"],        PDO::PARAM_STR);
       
        if ($stmt->execute()) {
            return "ok";
            
        } else {
            // Para depuración
           
            print_r(Conexion::conectar()->errorInfo());
        }

        $stmt->closeCursor();
        $stmt = null;
    }
}
