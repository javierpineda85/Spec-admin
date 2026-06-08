<?php
class Configuracion
{
    private $db;

    public function __construct()
    {
        $this->db = new Conexion;
    }

    // Obtener todas las configuraciones
    public function obtenerTodo()
    {
        $sql = "SELECT clave, valor FROM configuracion";
        return $this->db->consultas($sql);
    }

    // Obtener una configuración por clave
    public function obtener($clave)
    {
        $sql = "SELECT valor FROM configuracion WHERE clave = ?";
        $result = $this->db->consultas($sql, [$clave]);
        return $result && count($result) > 0 ? $result[0]['valor'] : null;
    }

    // Guardar o actualizar configuración
    public function guardar($clave, $valor)
    {
        // Si existe, actualizar; si no, insertar
        $sql = "INSERT INTO configuracion (clave, valor) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
        return $this->db->ejecutar($sql, [$clave, $valor]);
    }
}
