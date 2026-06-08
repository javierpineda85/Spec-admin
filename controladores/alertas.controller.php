<?php
require_once __DIR__ . '/../modelos/conexion.php';
require_once __DIR__ . '/../modelos/push.modelo.php';

class AlertasController
{
    private static function usuariosPorCategorias(array $categorias): array
    {
        $categorias = array_values(array_filter(array_map('strval', $categorias)));
        if (!$categorias) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categorias), '?'));
        $sql = "SELECT u.idUsuario
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.activo = 1
                  AND r.activo = 1
                  AND r.categoria IN ($placeholders)
                ORDER BY u.apellido, u.nombre";

        $db = new Conexion();
        $rows = $db->consultas($sql, $categorias);

        return array_map(static fn($r) => (int)($r['idUsuario'] ?? 0), $rows ?: []);
    }

    private static function destinatariosHombreVivo(): array
    {
        // El vigilador recibe su propia notificación, y además los supervisores activos.
        // Si no hay supervisores activos, caemos a direccion como respaldo operativo.
        $vigilador = intval($_SESSION['idUsuario'] ?? 0);
        $supervisores = self::usuariosPorCategorias(['supervisor']);
        if (!$supervisores) {
            $supervisores = self::usuariosPorCategorias(['supervisor', 'direccion']);
        }

        return array_values(array_unique(array_merge([$vigilador], $supervisores)));
    }

    private static function registrarAlertasParaUsuarios(array $usuariosIds, string $tipo, string $mensaje, ?int $objetivoId = null): array
    {
        $usuariosIds = array_values(array_unique(array_filter(array_map('intval', $usuariosIds))));
        if (!$usuariosIds) {
            return [];
        }

        $db = new Conexion();
        $insertados = [];

        foreach ($usuariosIds as $usuarioId) {
            $sqlCheck = "SELECT 1 FROM alertas
                         WHERE tipo = ?
                           AND usuario_id = ?
                           AND leida = 0";
            $paramsCheck = [$tipo, $usuarioId];

            if ($objetivoId !== null) {
                $sqlCheck .= " AND objetivo_id = ?";
                $paramsCheck[] = $objetivoId;
            }

            $sqlCheck .= " LIMIT 1";
            $existe = $db->consultas($sqlCheck, $paramsCheck);
            if ($existe) {
                continue;
            }

            $sql = "INSERT INTO alertas (tipo, mensaje, usuario_id, objetivo_id, leida, creada_en)
                    VALUES (?, ?, ?, ?, 0, NOW())";
            $ok = $db->ejecutar($sql, [$tipo, $mensaje, $usuarioId, $objetivoId]);
            if ($ok) {
                $insertados[] = $usuarioId;
            }
        }

        if ($insertados) {
            ModeloPush::enviarPushAUsuarios($insertados);
        }

        return $insertados;
    }

    public static function registrarDemoraHombreVivo()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $usuarioId = intval($_SESSION['idUsuario'] ?? ($data['usuario_id'] ?? 0));
        $objetivoId = intval($data['objetivo_id'] ?? ($_SESSION['ultimo_objetivo'] ?? 0));
        $rondaId   = intval($data['ronda_id'] ?? 0);
        $tiempo    = intval($data['tiempo'] ?? 0);
        $fase      = strtolower(trim((string)($data['fase'] ?? 'vencido')));

        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Sesion no iniciada']);
            return;
        }

        if ($tiempo < 0) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos o tiempo insuficiente']);
            return;
        }

        $db = new Conexion();

        if (!$objetivoId && $rondaId) {
            $sql = "SELECT r.objetivo_id, o.nombre AS objetivo, CONCAT(u.apellido, ' ', u.nombre) AS usuario
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
        } else {
            $sql = "SELECT o.idObjetivo AS objetivo_id,
                           o.nombre AS objetivo,
                           CONCAT(u.apellido, ' ', u.nombre) AS usuario
                    FROM objetivos o
                    JOIN usuarios u ON u.idUsuario = :uid
                    WHERE o.idObjetivo = :oid
                    LIMIT 1";

            $stmt = $db->conectar()->prepare($sql);
            $stmt->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(':oid', $objetivoId, PDO::PARAM_INT);
            $stmt->execute();
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$info) {
            echo json_encode(['success' => false, 'error' => 'No se pudo obtener el contexto de la alerta']);
            return;
        }

        $objetivoId = intval($info['objetivo_id'] ?? $objetivoId);
        if ($fase === 'excedido') {
            $tipo = 'hombre_vivo_excedido';
            $mensaje = "Demora en reporte de hombre vivo: {$info['usuario']} supero la tolerancia de 3 minutos en el objetivo {$info['objetivo']} ({$tiempo} segundos de retraso).";
        } else {
            $tipo = 'hombre_vivo_vencido';
            $mensaje = "Reporte de hombre vivo vencido: {$info['usuario']} debe registrar el reporte en el objetivo {$info['objetivo']}.";
        }

        $destinatarios = self::destinatariosHombreVivo();
        self::registrarAlertasParaUsuarios($destinatarios, $tipo, $mensaje, $objetivoId);

        echo json_encode(['success' => true]);
    }

    public static function obtenerAlertasHombreVivoInicio(int $usuarioId, int $limite = 5): array
    {
        $usuarioId = (int) $usuarioId;
        $limite = max(1, (int) $limite);

        if ($usuarioId <= 0) {
            return [];
        }

        $db = new Conexion();
        $sql = "SELECT a.idAlerta,
                       a.tipo,
                       a.mensaje,
                       a.creada_en,
                       a.objetivo_id,
                       o.nombre AS objetivo
                FROM alertas a
                LEFT JOIN objetivos o ON a.objetivo_id = o.idObjetivo
                WHERE a.usuario_id = ?
                  AND a.leida = 0
                  AND a.tipo LIKE 'hombre_vivo%'
                ORDER BY a.creada_en DESC
                LIMIT {$limite}";

        return $db->consultas($sql, [$usuarioId]) ?: [];
    }

    public static function registrarAlertaGeneral(string $tipo, string $mensaje, int $usuarioId, int $objetivoId = null)
    {
        self::registrarAlertasParaUsuarios([$usuarioId], $tipo, $mensaje, $objetivoId);
    }

    public static function contarNoLeidas($usuarioId)
    {
        $db = new Conexion();
        $res = $db->consultas("SELECT COUNT(*) AS total FROM alertas WHERE usuario_id = ? AND leida = 0", [$usuarioId]);
        return $res[0]['total'] ?? 0;
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
            error_log("Error al marcar alerta como leida: " . $e->getMessage());
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

    public static function obtenerNoLeidas($usuarioId, $limite = 10)
    {
        $db = new Conexion();
        return $db->consultas(
            "SELECT * FROM alertas
         WHERE usuario_id = ? AND leida = 0
         ORDER BY creada_en DESC
         LIMIT $limite",
            [$usuarioId]
        );
    }
}
