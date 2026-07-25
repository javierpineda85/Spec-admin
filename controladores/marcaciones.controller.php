<?php

class MarcacionesController
{
    /**
     * Inicia sesión si es necesario.
     * Recoge y normaliza los datos de entrada.
     * Valida que no falten coordenadas ni tipo de evento.
     * Para roles Vigilador y Referente, obtiene el punto central y radio de la zona, calcula la distancia con Haversine y bloquea el registro si está fuera del área.
     * Inserta la marcación y redirige con mensaje de éxito.
     */
    public static function crtRegistrarMarcacion()
    {
        Auth::check('marcaciones', 'crtRegistrarMarcacion');
        // Inicia sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Recogemos datos de sesión y POST
        $vigilador_id = $_SESSION['idUsuario'] ?? null;
        $rol_usuario  = $_SESSION['rol']       ?? null;

        // Normalizamos objetivo_id
        $objetivo_id  = $_POST['objetivo_id']  ?? null;
        $objetivo_id  = !empty($objetivo_id)   ? $objetivo_id : null;

        $puesto_id    = $_POST['puesto_id'] ?? null;

        $tipo_evento  = $_POST['tipo_evento']  ?? null;
        $lat          = $_POST['latitud']      ?? null;
        $lng          = $_POST['longitud']     ?? null;
        $operacion_id = trim((string)($_SERVER['HTTP_X_SPEC_OPERATION_ID'] ?? ($_POST['operacion_id'] ?? '')));
        if ($operacion_id === '') {
            $operacion_id = bin2hex(random_bytes(16));
        }
        $fecha_evento = self::normalizarFechaEvento($_POST['fecha_evento'] ?? null);
        $jsonResponse = ($_GET['format'] ?? $_POST['format'] ?? '') === 'json'
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        // Validación mínima
        if (!$vigilador_id || !$tipo_evento || !$lat || !$lng) {
            if ($jsonResponse) {
                self::responderJson(false, 'Faltan datos para registrar la marcación', 400);
            }
            ToastifyController::error('Faltan datos para registrar la marcación');
            header('Location: ?r=entradas_salidas');
            exit;
        }

        if (!in_array($tipo_evento, ['entrada', 'salida'], true)
            || !preg_match('/^[a-zA-Z0-9-]{16,64}$/', $operacion_id)) {
            if ($jsonResponse) {
                self::responderJson(false, 'Datos de marcación inválidos', 400);
            }
            ToastifyController::error('Datos de marcación inválidos');
            header('Location: ?r=entradas_salidas');
            exit;
        }

        try {
            // Creamos conexión PDO y comenzamos transacción
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            // Validación de geofence
            if (in_array($rol_usuario, ['Vigilador', 'Referente'])) {
                $sqlGeo = "
                    SELECT latitud AS lat_o,
                           longitud AS lng_o,
                           radio_m
                      FROM objetivos
                     WHERE idObjetivo = :idObjetivo
                     LIMIT 1
                ";
                $stmtGeo = $pdo->prepare($sqlGeo);
                $stmtGeo->execute([':idObjetivo' => $objetivo_id]);
                $geo = $stmtGeo->fetchAll(PDO::FETCH_ASSOC);

                if (empty($geo)) {
                    throw new Exception('Objetivo no encontrado.');
                }

                $lat_o = (float) $geo[0]['lat_o'];
                $lng_o = (float) $geo[0]['lng_o'];
                $radio = (int)   $geo[0]['radio_m'];

                // Haversine
                $dLat = deg2rad($lat - $lat_o);
                $dLng = deg2rad($lng - $lng_o);
                $a = sin($dLat / 2) * sin($dLat / 2)
                    + cos(deg2rad($lat_o)) * cos(deg2rad($lat))
                    * sin($dLng / 2) * sin($dLng / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $dist = 6371000 * $c;

                if ($dist > $radio) {
                    throw new Exception(sprintf(
                        'Estás a %.0f m fuera del área permitida (%d m).',
                        $dist,
                        $radio
                    ));
                     ToastifyController::error('Estás a %.0f m fuera del área permitida (%d m).');
                }
            }

            // Insert marcación
            $sqlIns = "INSERT INTO marcaciones_servicio
                (vigilador_id, objetivo_id, puesto_id, tipo_evento, fecha_hora, latitud, longitud, operacion_id)
                VALUES (:v, :o, :p, :t, :fecha, :lat, :lng, :operacion)
                ON DUPLICATE KEY UPDATE operacion_id = VALUES(operacion_id)
            ";
            $stmtIns = $pdo->prepare($sqlIns);
            $stmtIns->execute([
                ':v'   => $vigilador_id,
                ':o'   => $objetivo_id,
                ':p'   => $puesto_id,
                ':t'   => $tipo_evento,
                ':fecha' => $fecha_evento,
                ':lat' => $lat,
                ':lng' => $lng,
                ':operacion' => $operacion_id
            ]);
            $duplicada = $stmtIns->rowCount() === 0;

            // Commit
            $pdo->commit();

            if ($jsonResponse) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'duplicate' => $duplicada,
                    'message' => $duplicada
                        ? 'La marcación ya estaba registrada.'
                        : ucfirst($tipo_evento) . ' registrada correctamente.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            ToastifyController::success(ucfirst($tipo_evento) . ' registrada correctamente.');
            header('Location: ?r=entradas_salidas');
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($jsonResponse) {
                self::responderJson(false, $e->getMessage(), 422);
            }
            ToastifyController::error('Error: ' . $e->getMessage());
            header('Location: ?r=entradas_salidas');
            exit;
        }
    }

    private static function responderJson(bool $success, string $message, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function normalizarFechaEvento($fecha): string
    {
        try {
            $date = $fecha ? new DateTime((string)$fecha) : new DateTime();
            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
            return $date->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return date('Y-m-d H:i:s');
        }
    }
}
