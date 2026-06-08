<?php
class ModeloReporteHombreVivo
{
    public static function mdlObtenerConfiguracion(): array
    {
        $config = [
            'diurno' => 30,
            'nocturno' => 30,
            'tolerancia' => 3,
        ];

        try {
            $stmt = Conexion::conectar()->prepare("SELECT turno, minutos, tolerancia_minutos FROM hvivo_config");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return $config;
        }

        foreach ($rows as $row) {
            $turno = strtolower(trim((string)($row['turno'] ?? '')));
            $minutos = (int)($row['minutos'] ?? 0);
            $tolerancia = (int)($row['tolerancia_minutos'] ?? 3);

            if ($turno === 'diurno' && $minutos > 0) {
                $config['diurno'] = $minutos;
            }
            if ($turno === 'nocturno' && $minutos > 0) {
                $config['nocturno'] = $minutos;
            }
            if ($tolerancia > 0) {
                $config['tolerancia'] = $tolerancia;
            }
        }

        return $config;
    }

    public static function mdlGuardarConfiguracion(string $turno, int $minutos): bool|string
    {
        $turno = strtolower(trim($turno));
        if (!in_array($turno, ['diurno', 'nocturno'], true)) {
            return 'Turno inválido';
        }

        if ($minutos <= 0) {
            return 'Los minutos deben ser mayores a cero';
        }

        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO hvivo_config (turno, minutos, tolerancia_minutos)
                    VALUES (:turno, :minutos, 3)
                    ON DUPLICATE KEY UPDATE minutos = VALUES(minutos), tolerancia_minutos = VALUES(tolerancia_minutos)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':turno', $turno, PDO::PARAM_STR);
            $stmt->bindValue(':minutos', $minutos, PDO::PARAM_INT);
            return $stmt->execute() ? 'ok' : ($stmt->errorInfo()[2] ?? 'No se pudo guardar la configuración');
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    static public function mdlGuardarReporte($tabla, $datos)
    {
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO $tabla (id_usuario, ronda_id, fecha_hora, demora) VALUES (:id_usuario, :ronda_id, NOW(), :demora)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':ronda_id',   $datos['ronda_id'],   PDO::PARAM_INT);
            $stmt->bindParam(':demora',     $datos['demora'],     PDO::PARAM_STR);
            return $stmt->execute() ? 'ok' : $stmt->errorInfo()[2];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
