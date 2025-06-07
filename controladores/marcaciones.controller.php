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

        $tipo_evento  = $_POST['tipo_evento']  ?? null;
        $lat          = $_POST['latitud']      ?? null;
        $lng          = $_POST['longitud']     ?? null;

        // Validación mínima
        if (!$vigilador_id || !$tipo_evento || !$lat || !$lng) {
            $_SESSION['error_message'] = 'Faltan datos para registrar la marcación.';
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
                }
            }

            // Insert marcación
            $sqlIns = "INSERT INTO marcaciones_servicio (vigilador_id, objetivo_id, tipo_evento, fecha_hora, latitud, longitud) VALUES (:v, :o, :t, NOW(), :lat, :lng)
            ";
            $stmtIns = $pdo->prepare($sqlIns);
            $stmtIns->execute([
                ':v'   => $vigilador_id,
                ':o'   => $objetivo_id,
                ':t'   => $tipo_evento,
                ':lat' => $lat,
                ':lng' => $lng
            ]);

            // Commit
            $pdo->commit();

            $_SESSION['success_message'] = ucfirst($tipo_evento) . ' registrada correctamente.';
            header('Location: ?r=entradas_salidas');
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
            header('Location: ?r=entradas_salidas');
            exit;
        }
    }
}
