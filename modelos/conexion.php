<?php
class Conexion
{
    private static $link = null;

    private $conexion;

    public function __construct()
    {
        //error_log("[Conexion creada] " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)));
        if (!self::$link) {
            self::$link = self::conectar();
        }
        $this->conexion = self::$link;
    }

    static public function conectar()
    {
        if (self::$link) return self::$link;

        try {
            $link = new PDO("mysql:host=localhost;port=3306;dbname=spec", "root", "");
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $link->exec("SET NAMES utf8");
            self::$link = $link; // ESTA LÍNEA ES FUNDAMENTAL PARA EVITAR RECURSIVIDAD
            return self::$link;
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return null;
        }
    }

    public function consultas(string $query, array $params = [])
    {
        try {
            if (!$this->conexion) {
                throw new Exception("No hay conexión activa");
            }

            if (!empty($params)) {
                $stmt = $this->conexion->prepare($query);
                $stmt->execute($params);
            } else {
                $stmt = $this->conexion->query($query);
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error en consulta: " . $e->getMessage();
            return null;
        }
    }
}
