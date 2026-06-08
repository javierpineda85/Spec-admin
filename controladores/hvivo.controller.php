<?php
ob_start(); //permite enviar los headers sin interferencias
require_once('modelos/hvivo.modelo.php');

class HombreVivoController
{
    private static function puedeConfigurarHombreVivo(): bool
    {
        $categoria = $_SESSION['categoria'] ?? '';
        return !in_array($categoria, ['operativo', 'referente'], true);
    }

    private static function denegarConfiguracion(): void
    {
        header('Location: ?r=acceso_denegado/index');
        exit;
    }

    private static function determinarTurnoHombreVivo(?string $codigoTurno): string
    {
        $codigoTurno = strtoupper(trim((string)$codigoTurno));

        if (in_array($codigoTurno, ['N', 'GP/N'], true)) {
            return 'nocturno';
        }

        if (in_array($codigoTurno, ['D', 'GP/D'], true)) {
            return 'diurno';
        }

        $horaActual = (int)date('H');
        return ($horaActual >= 20 || $horaActual < 6) ? 'nocturno' : 'diurno';
    }

    public static function registrar()
    {
        Auth::check('hvivo', 'registrar');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        // Tomamos primero de la sesión (más seguro); si no, aceptamos request (GET/POST) para no romper el front actual.
        $usuarioId  = intval($_SESSION['idUsuario']   ?? ($_POST['id_usuario']  ?? $_GET['id_usuario']  ?? 0));
        $objetivoId = intval($_SESSION['objetivo_id'] ?? ($_POST['objetivo_id'] ?? $_GET['objetivo_id'] ?? 0));
        $demora     = $_POST['demora'] ?? $_GET['demora'] ?? null; // esperado: 'HH:MM:SS'

        // Validaciones mínimas
        if ($usuarioId <= 0 || $objetivoId <= 0 || !$demora) {
            echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            exit;
        }

        // Normalización/validación del TIME: permitir 1–3 dígitos de horas (MySQL TIME admite hasta 838:59:59)
        $demora = trim((string)$demora);
        if (!preg_match('/^\d{1,3}:\d{2}:\d{2}$/', $demora)) {
            echo json_encode(['success' => false, 'error' => 'Formato de demora inválido. Use HH:MM:SS']);
            exit;
        }
        // Pad de horas a dos dígitos (sin romper horas de 3 dígitos)
        [$h, $m, $s] = array_map('intval', explode(':', $demora));
        $demora = sprintf('%02d:%02d:%02d', $h, $m, $s);

        // Armado de datos según la tabla `reporte_hombre_vivo`
        $datos = [
            'id_usuario'  => $usuarioId,
            'objetivo_id' => $objetivoId,
            'demora'      => $demora
            // fecha_hora queda por DEFAULT CURRENT_TIMESTAMP
        ];

        $res = ModeloReporteHombreVivo::mdlGuardarReporte('reporte_hombre_vivo', $datos);

        if ($res === 'ok') {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $res]);
        }
        exit;
    }
    public static function vistaHombreVivo()
    {
        Auth::check('hvivo', 'vistaHombreVivo');

        $objetivoId = intval($_SESSION['objetivo_id'] ?? 0);
        $userId = $_SESSION['idUsuario'];
        $modeloUsuarios = new ModeloUsuarios();
        $db     = Conexion::conectar();
        // 1) ¿Ya marcó la entrada hoy?
        $sqlEntry = " SELECT COUNT(*) AS cnt
                            FROM marcaciones_servicio
                            WHERE vigilador_id = ?
                            AND DATE(fecha_hora) = CURDATE()
                            AND tipo_evento = 'entrada'";
        $stmtE     = $db->prepare($sqlEntry);
        $stmtE->execute([$userId]);
        $entradasHoy = $stmtE->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        $_SESSION['hVivo_tiene_entrada'] = $entradasHoy > 0;

        // ¿Ya marcó la salida hoy?
        $sqlExit = str_replace('entrada', 'salida', $sqlEntry);
        $stmtX    = $db->prepare($sqlExit);
        $stmtX->execute([$userId]);
        $salidasHoy = $stmtX->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        $_SESSION['hVivo_ya_salida'] = $salidasHoy > 0;

        // Guarda en la sesión o pasa a la vista
        $_SESSION['hVivo_ya_salida'] = $salidasHoy > 0;
        $_SESSION['ultimo_objetivo'] = $objetivoId;

        $_SESSION['turno_codigo'] = null;
        $asignacionHoy = $modeloUsuarios->getAsignacionHoy($userId);
        if ($asignacionHoy && !empty($asignacionHoy['codigo_turno'])) {
            $_SESSION['turno_codigo'] = $asignacionHoy['codigo_turno'];
        }

        $_SESSION['hVivo_config'] = ModeloReporteHombreVivo::mdlObtenerConfiguracion();
        $_SESSION['hVivo_turno'] = self::determinarTurnoHombreVivo($_SESSION['turno_codigo'] ?? null);

        include __DIR__ . '/../vistas/paginas/h-vivo/reporte_hombre_vivo.php';
    }
    public static function vistaListadoReportesHombreVivo()
    {
        Auth::check('hvivo', 'vistaListadoReportesHombreVivo');
        include __DIR__ . '/../vistas/paginas/h-vivo/listado_reportesHvivo.php';
    }
    public static function vistaConfiguracionHombreVivo()
    {
        if (!self::puedeConfigurarHombreVivo()) {
            self::denegarConfiguracion();
        }

        $config = ModeloReporteHombreVivo::mdlObtenerConfiguracion();
        include __DIR__ . '/../vistas/paginas/h-vivo/configuracion_hombre_vivo.php';
    }
    public static function guardarConfiguracionHombreVivo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?r=configuracion_hvivo');
            exit;
        }

        if (!self::puedeConfigurarHombreVivo()) {
            self::denegarConfiguracion();
        }

        $diurno = intval($_POST['minutos_diurno'] ?? 0);
        $nocturno = intval($_POST['minutos_nocturno'] ?? 0);

        if ($diurno <= 0 || $nocturno <= 0) {
            ToastifyController::error("Debes indicar minutos válidos para ambos turnos.");
            header('Location: ?r=configuracion_hvivo');
            exit;
        }

        $resDiurno = ModeloReporteHombreVivo::mdlGuardarConfiguracion('diurno', $diurno);
        $resNocturno = ModeloReporteHombreVivo::mdlGuardarConfiguracion('nocturno', $nocturno);

        if ($resDiurno === 'ok' && $resNocturno === 'ok') {
            ToastifyController::success("Configuración de Hombre Vivo guardada correctamente.");
        } else {
            $error = $resDiurno !== 'ok' ? $resDiurno : $resNocturno;
            ToastifyController::error("No se pudo guardar la configuración: " . $error);
        }

        header('Location: ?r=configuracion_hvivo');
        exit;
    }
    public static function ajaxRegistrarReporte()
    {

        Auth::check('hvivo', 'ajaxRegistrarReporte');

        header('Content-Type: application/json; charset=utf-8');

        // Lee y valida parámetros…

        $userId  = intval($_GET['id_usuario'] ?? 0);
        $objetivoId = intval($_GET['objetivo_id'] ?? ($_SESSION['ultimo_objetivo'] ?? 0));
        $demora  = $_GET['demora'] ?? '';
        if (!$objetivoId || !$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
            exit;
        }

        // Inserta en BD…
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO reporte_hombre_vivo (id_usuario, objetivo_id, demora, fecha_hora)
                VALUES (?, ?, ?, NOW())";
            $ok  = $db->prepare($sql)->execute([$userId, $objetivoId, $demora]);

            if ($ok) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
