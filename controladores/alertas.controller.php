<?php

class AlertasController
{
    public static function registrarDemoraHombreVivo()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $usuarioId = intval($data['usuario_id'] ?? 0);
        $rondaId   = intval($data['ronda_id'] ?? 0);
        $tiempo    = intval($data['tiempo'] ?? 0);

        if (!$usuarioId || !$rondaId || $tiempo < 300) return;

        $db = new Conexion;

        $sql = "SELECT o.nombre AS objetivo, CONCAT(u.apellido, ' ', u.nombre) AS usuario
            FROM rondas r
            JOIN objetivos o ON r.objetivo_id = o.idObjetivo
            JOIN usuarios u ON u.idUsuario = :uid
            WHERE r.idRonda = :rid
            LIMIT 1";

        $stmt = $db->conectar()->prepare($sql);
        $stmt->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':rid', $rondaId, PDO::PARAM_INT);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$info) return;

        $mensaje = "El usuario {$info['usuario']} no registró el reporte en el objetivo {$info['objetivo']} desde hace {$tiempo} segundos.";

        self::registrarAlertaGeneral('hombre_vivo', $mensaje, $usuarioId, $rondaId);
    }

    public static function registrarAlertaGeneral(string $tipo, string $mensaje, int $usuarioId, int $objetivoId = null)
    {
        $db = new Conexion;

        // Evitar duplicados abiertos del mismo tipo y usuario
        $sqlCheck = "SELECT 1 FROM alertas WHERE tipo = :tipo AND usuario_id = :uid AND leida = 0 LIMIT 1";
        $existe = $db->consultas($sqlCheck, [':tipo' => $tipo, ':uid' => $usuarioId]);
        if ($existe) return;

        $sql = "INSERT INTO alertas (tipo, mensaje, usuario_id, objetivo_id)
            VALUES (:tipo, :mensaje, :uid, :oid)";
        $stmt = $db->conectar()->prepare($sql);
        $stmt->execute([
            ':tipo'    => $tipo,
            ':mensaje' => $mensaje,
            ':uid'     => $usuarioId,
            ':oid'     => $objetivoId
        ]);
    }

    public static function verAlertasNoLeidas()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['idUsuario'])) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $usuarioId = $_SESSION['idUsuario'];

        try {
            $db = new Conexion();
            $sql = "SELECT * FROM alertas 
                WHERE usuario_id = ? AND leida = 0 
                ORDER BY creada_en DESC 
                LIMIT 10";

            $alertas = $db->consultas($sql, [$usuarioId]);

            header('Content-Type: application/json');
            echo json_encode($alertas);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error interno']);
            exit;
        }
    }



    public static function marcarLeida()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['idUsuario']) || !isset($_POST['id'])) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
            exit;
        }

        $usuarioId = $_SESSION['idUsuario'];
        $alertaId = intval($_POST['id']);

        try {
            $db = new Conexion();
            $sql = "UPDATE alertas SET leida = 1 WHERE idAlerta = ? AND usuario_id = ?";
            $db->consultas($sql, [$alertaId, $usuarioId]);

            echo json_encode(['status' => 'ok']);
        } catch (Exception $e) {
            error_log("❌ Error al marcar alerta como leída: " . $e->getMessage());
            echo json_encode(['status' => 'error']);
        }

        exit;
    }

    public static function verHistorialLeidas()
    {
        $tipo  = $_GET['tipo']  ?? '';
        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';

        $db = new Conexion;

        $condiciones = ["leida = 1"];
        $params = [];

        if (!empty($tipo)) {
            $condiciones[] = "tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        if (!empty($desde)) {
            $condiciones[] = "DATE(creada_en) >= :desde";
            $params[':desde'] = $desde;
        }

        if (!empty($hasta)) {
            $condiciones[] = "DATE(creada_en) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $where = implode(" AND ", $condiciones);

        $sql = "SELECT a.*, 
                   CONCAT(u.apellido, ' ', u.nombre) AS usuario,
                   o.nombre AS objetivo
            FROM alertas a
            LEFT JOIN usuarios u ON a.usuario_id = u.idUsuario
            LEFT JOIN objetivos o ON a.objetivo_id = o.idObjetivo
            WHERE $where
            ORDER BY creada_en DESC
            LIMIT 100";

        $stmt = $db->conectar()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();
        $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($alertas);
    }
}
