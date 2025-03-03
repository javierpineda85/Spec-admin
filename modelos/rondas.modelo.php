<?php
require_once __DIR__ . '/conexion.php';
class ModeloRondas
{

    static public function mdlGuardarRonda($tabla, $datos)
    {
        try {
            $conexion = Conexion::conectar();
            $registro = $conexion->prepare("INSERT INTO $tabla (puesto, objetivo_id, tipo, orden_escaneo) 
            VALUES (:puesto, :objetivo_id, :tipo, :orden_escaneo)");
    
            $registro->bindParam(":puesto", $datos["puesto"], PDO::PARAM_STR);
            $registro->bindParam(":objetivo_id", $datos["objetivo_id"], PDO::PARAM_INT); // Revisa si esto debería ser "objetivo_id"
            $registro->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $registro->bindParam(":orden_escaneo", $datos["orden_escaneo"], PDO::PARAM_INT);
    
            if ($registro->execute()) {
                return "ok";
            } else {
                // Si falla, mostrar el error
                $error = $registro->errorInfo();
                throw new Exception("Error en la consulta: " . $error[2]);
            }
    
            $registro->closeCursor();
            $registro = null;
    
        } catch (Exception $e) {
            // Captura cualquier excepción y la muestra
            echo "Error: " . $e->getMessage();
        }
    }
    
}
