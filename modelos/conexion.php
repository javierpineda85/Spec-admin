<?php
class Conexion
{
    private static $link = null;
    private $conexion;

    public function __construct()
    {
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
            $link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $link->exec("SET NAMES utf8");
            self::$link = $link; // clave para evitar recursividad
            return self::$link;
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return null;
        }
    }

    /**
     * SELECT seguro. Si se usa con INSERT/UPDATE/DELETE devuelve [] (no rompe).
     */
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

            // Si no hay columnas (DML), devolvemos [] en lugar de intentar fetchAll
            if ($stmt instanceof PDOStatement && $stmt->columnCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        } catch (PDOException $e) {
            echo "Error en consulta: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE (o DDL). Devuelve true en éxito.
     * Usar en lugar de consultas() para DML.
     */
    public function ejecutar(string $query, array $params = []): bool
    {
        try {
            if (!$this->conexion) {
                throw new Exception("No hay conexión activa");
            }
            $stmt = $this->conexion->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            echo "Error en ejecución: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Último ID autoincremental insertado en esta conexión.
     */
    public function lastInsertId(): int
    {
        try {
            return (int)$this->conexion->lastInsertId();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Escapa/quote seguro para armar SQL manual.
     * OJO: lo ideal es usar parámetros; esto es para casos puntuales.
     * Devuelve el string sin las comillas externas de PDO::quote.
     */
    public function limpiar($valor): string
    {
        if ($valor === null) return '';
        $s = (string)$valor;

        if (!$this->conexion) {
            // Fallback mínimo si la conexión no está disponible
            return addslashes($s);
        }

        $quoted = $this->conexion->quote($s); // incluye comillas
        if ($quoted === false) {
            return addslashes($s);
        }
        // Removemos las comillas externas para que puedas usar "'".$db->limpiar($x)."'" si querés
        return substr($quoted, 1, -1);
    }

    /* --- (Opcional) Atajos para transacciones si alguna vez los querés usar ---
    public function begin(): bool { return $this->conexion->beginTransaction(); }
    public function commit(): bool { return $this->conexion->commit(); }
    public function rollback(): bool { return $this->conexion->rollBack(); }
    -----------------------------------------------------------------------------*/
}
