<?php
class Conexion {
    private $conexion;
    
    public function __construct() {
        $this->conexion = $this->conectar();
    }

    static public function conectar() {
        try {
            $link = new PDO("mysql:host=localhost;port=3306;dbname=spec", "root", "");
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $link->exec("SET NAMES utf8");
            return $link;
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Ejecuta una consulta SELECT, opcionalmente con parámetros.
     * @param string $query   SQL con placeholders ?
     * @param array  $params  Valores para bindear los placeholders
     * @return array|null     Array asociativo de resultados o null en error
     */
    public function consultas(string $query, array $params = []) {
        try {
            // Si hay parámetros, usamos prepare/execute
            if (count($params) > 0) {
                $stmt = $this->conexion->prepare($query);
                $stmt->execute($params);
            } else {
                // Sin parámetros basta con query()
                $stmt = $this->conexion->query($query);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error en consulta: " . $e->getMessage();
            return null;
        }
    }
}
